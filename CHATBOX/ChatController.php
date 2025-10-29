<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Favorite;
use App\Models\SavedMessage;
use App\Models\UserSetting;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get or create user settings
            $userSettings = $user->settings ?? new UserSetting(['user_id' => $user->id]);
            if (!$userSettings->exists) {
                $userSettings->save();
            }
            
            // Get all conversations for the user
            $conversations = $this->getConversations($user);
            
            // Get favorites - handle case where relationship might not exist
            $favorites = collect();
            if (method_exists($user, 'favorites')) {
                $favorites = $user->favorites()->with('favoriteUser')->get();
            }
            
            // Get saved messages - handle case where relationship might not exist
            $savedMessages = collect();
            if (method_exists($user, 'savedMessages')) {
                $savedMessages = $user->savedMessages()->with('message.sender')->get();
            }
            
            return view('chat.index', compact('conversations', 'favorites', 'savedMessages', 'userSettings'))
                ->with('currentUser', $user)
                ->with('userSettings', $userSettings);
                
        } catch (\Exception $e) {
            // Fallback for when database is not available
            $user = Auth::user();
            $userSettings = new UserSetting([
                'dark_mode' => false,
                'chat_color' => '#6366f1',
                'show_online_status' => true,
                'show_typing_indicator' => true,
                'play_sound' => true
            ]);
            
            return view('chat.index', [
                'conversations' => collect(),
                'favorites' => collect(),
                'savedMessages' => collect(),
                'userSettings' => $userSettings,
                'currentUser' => $user
            ]);
        }
    }

    public function getConversationsApi()
    {
        $user = Auth::user();
        $conversations = $this->getConversations($user);
        
        return response()->json(['conversations' => $conversations]);
    }

    public function getFavorites()
    {
        try {
            $user = Auth::user();
            $favorites = $user->favorites()->with('favoriteUser')->get();
            
            return response()->json(['favorites' => $favorites]);
        } catch (\Exception $e) {
            return response()->json(['favorites' => []]);
        }
    }

    public function getSavedMessages()
    {
        try {
            $user = Auth::user();
            $savedMessages = $user->savedMessages()->with('message.sender')->get();
            
            return response()->json(['savedMessages' => $savedMessages]);
        } catch (\Exception $e) {
            return response()->json(['savedMessages' => []]);
        }
    }

    public function getConversations($user)
    {
        try {
            // Get all users that have conversations with the current user
            $sentMessages = $user->sentMessages()->with('receiver')->get();
            $receivedMessages = $user->receivedMessages()->with('sender')->get();
            
            $conversations = collect();
            
            // Add conversations from sent messages
            foreach ($sentMessages as $message) {
                $conversations->put($message->receiver_id, [
                    'user' => $message->receiver,
                    'last_message' => $message,
                    'unread_count' => 0
                ]);
            }
            
            // Add conversations from received messages
            foreach ($receivedMessages as $message) {
                if (!$conversations->has($message->sender_id)) {
                    $conversations->put($message->sender_id, [
                        'user' => $message->sender,
                        'last_message' => $message,
                        'unread_count' => 0
                    ]);
                } else {
                    // Update with the latest message
                    if ($message->created_at > $conversations[$message->sender_id]['last_message']->created_at) {
                        $conversations[$message->sender_id]['last_message'] = $message;
                    }
                }
                
                // Count unread messages
                if (!$message->is_read) {
                    $conversations[$message->sender_id]['unread_count']++;
                }
            }
            
            return $conversations->sortByDesc(function ($conversation) {
                return $conversation['last_message']->created_at;
            });
        } catch (\Exception $e) {
            // Return empty collection if database is not available
            return collect();
        }
    }

    public function getMessages(Request $request, $userId)
    {
        $currentUser = Auth::user();
        
        $messages = Message::where(function ($query) use ($currentUser, $userId) {
            $query->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $currentUser->id);
        })
        ->with(['sender', 'receiver', 'attachments'])
        ->orderBy('created_at', 'asc')
        ->get();
        
        // Mark messages as read
        Message::where('sender_id', $userId)
               ->where('receiver_id', $currentUser->id)
               ->where('is_read', false)
               ->update([
                   'is_read' => true,
                   'read_at' => now()
               ]);
        
        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'in:text,emoji,image,file'
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'text',
            'is_read' => false
        ]);
        
        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('attachments', $filename, 'public');
                
                Attachment::create([
                    'message_id' => $message->id,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_type' => $this->getFileType($file->getMimeType())
                ]);
            }
        }
        
        $message->load(['sender', 'receiver', 'attachments']);
        
        return response()->json($message);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $users = User::where('id', '!=', Auth::id())
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'profile_picture']);
        
        return response()->json($users);
    }

    public function addToFavorites(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $favorite = Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'favorite_user_id' => $request->user_id
        ]);
        
        return response()->json(['success' => true]);
    }

    public function removeFromFavorites(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        Favorite::where('user_id', Auth::id())
                ->where('favorite_user_id', $request->user_id)
                ->delete();
        
        return response()->json(['success' => true]);
    }

    public function saveMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id',
            'note' => 'nullable|string|max:500'
        ]);
        
        $savedMessage = SavedMessage::firstOrCreate([
            'user_id' => Auth::id(),
            'message_id' => $request->message_id
        ], [
            'note' => $request->note
        ]);
        
        return response()->json(['success' => true]);
    }

    public function unsaveMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id'
        ]);
        
        SavedMessage::where('user_id', Auth::id())
                   ->where('message_id', $request->message_id)
                   ->delete();
        
        return response()->json(['success' => true]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'dark_mode' => 'boolean',
            'chat_color' => 'string|max:7',
            'show_online_status' => 'boolean',
            'show_typing_indicator' => 'boolean',
            'play_sound' => 'boolean'
        ]);
        
        $user = Auth::user();
        $settings = $user->settings ?? UserSetting::create(['user_id' => $user->id]);
        
        $settings->update($request->only([
            'dark_mode', 'chat_color', 'show_online_status', 
            'show_typing_indicator', 'play_sound'
        ]));
        
        return response()->json(['success' => true, 'settings' => $settings]);
    }

    public function deleteConversation(Request $request, $userId)
    {
        $currentUser = Auth::user();
        
        // Delete all messages between the two users
        Message::where(function ($query) use ($currentUser, $userId) {
            $query->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $currentUser->id);
        })->delete();
        
        return response()->json(['success' => true]);
    }

    public function getSharedPhotos(Request $request, $userId)
    {
        $currentUser = Auth::user();
        
        $attachments = Attachment::whereHas('message', function ($query) use ($currentUser, $userId) {
            $query->where(function ($q) use ($currentUser, $userId) {
                $q->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($currentUser, $userId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $currentUser->id);
            });
        })
        ->where('file_type', 'image')
        ->with('message')
        ->orderBy('created_at', 'desc')
        ->get();
        
        return response()->json($attachments);
    }

    public function setTyping(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'is_typing' => 'required|boolean'
        ]);
        
        // Update typing status for the last message or create a new one
        $message = Message::where('sender_id', Auth::id())
                         ->where('receiver_id', $request->receiver_id)
                         ->latest()
                         ->first();
        
        if ($message) {
            $message->update(['is_typing' => $request->is_typing]);
        }
        
        return response()->json(['success' => true]);
    }

    private function getFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } else {
            return 'document';
        }
    }
}
