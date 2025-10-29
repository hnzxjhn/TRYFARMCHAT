<?php
// Complete Routes for FARMCHAT
// Add these routes to routes/web.php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// FARMCHAT routes - Add these to your existing web.php file
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Main chat interface
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    
    // API endpoints for chat functionality
    Route::get('/chat/conversations', [ChatController::class, 'getConversationsApi'])->name('chat.conversations');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage'])->name('chat.send-message');
    
    // Search functionality
    Route::get('/chat/search-users', [ChatController::class, 'searchUsers'])->name('chat.search-users');
    
    // Favorites management
    Route::post('/chat/add-favorite', [ChatController::class, 'addToFavorites'])->name('chat.add-favorite');
    Route::post('/chat/remove-favorite', [ChatController::class, 'removeFromFavorites'])->name('chat.remove-favorite');
    Route::get('/chat/favorites', [ChatController::class, 'getFavorites'])->name('chat.favorites');
    
    // Saved messages
    Route::get('/chat/saved-messages', [ChatController::class, 'getSavedMessages'])->name('chat.saved-messages');
    Route::post('/chat/save-message', [ChatController::class, 'saveMessage'])->name('chat.save-message');
    Route::post('/chat/unsave-message', [ChatController::class, 'unsaveMessage'])->name('chat.unsave-message');
    
    // User settings
    Route::post('/chat/update-settings', [ChatController::class, 'updateSettings'])->name('chat.update-settings');
    
    // Conversation management
    Route::delete('/chat/delete-conversation/{userId}', [ChatController::class, 'deleteConversation'])->name('chat.delete-conversation');
    
    // Shared photos
    Route::get('/chat/shared-photos/{userId}', [ChatController::class, 'getSharedPhotos'])->name('chat.shared-photos');
    
    // Typing indicator
    Route::post('/chat/set-typing', [ChatController::class, 'setTyping'])->name('chat.set-typing');
});

// Example of complete routes/web.php structure:
/*
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Contact form route (public, no auth required)
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/set-password', [ProfileController::class, 'setPassword'])->name('profile.set-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

use App\Http\Controllers\PostController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\MessagesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/comment', [\App\Http\Controllers\DashboardController::class, 'comment'])->name('posts.comment');
    Route::post('/posts/{post}/toggle-solved', [\App\Http\Controllers\DashboardController::class, 'toggleSolved'])->name('posts.toggle-solved');
    Route::get('/my-questions', [\App\Http\Controllers\DashboardController::class, 'myQuestions'])->name('my-questions');
    Route::get('/topics/{slug}', [TopicController::class, 'show'])->name('topics.show');

    // Friends routes
    Route::get('/friends', [FriendsController::class, 'index'])->name('friends');
    Route::post('/friends/request', [FriendsController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/accept', [FriendsController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/reject', [FriendsController::class, 'rejectRequest'])->name('friends.reject');

    // Messages routes
    Route::post('/messages/send', [MessagesController::class, 'sendMessage'])->name('messages.send');
    Route::get('/messages/conversation/{user}', [MessagesController::class, 'getConversation'])->name('messages.conversation');
    Route::get('/messages/unread-count', [MessagesController::class, 'getUnreadCount'])->name('messages.unread-count');

    // FARMCHAT routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [ChatController::class, 'getConversationsApi'])->name('chat.conversations');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::get('/chat/search-users', [ChatController::class, 'searchUsers'])->name('chat.search-users');
    Route::post('/chat/add-favorite', [ChatController::class, 'addToFavorites'])->name('chat.add-favorite');
    Route::post('/chat/remove-favorite', [ChatController::class, 'removeFromFavorites'])->name('chat.remove-favorite');
    Route::get('/chat/favorites', [ChatController::class, 'getFavorites'])->name('chat.favorites');
    Route::get('/chat/saved-messages', [ChatController::class, 'getSavedMessages'])->name('chat.saved-messages');
    Route::post('/chat/save-message', [ChatController::class, 'saveMessage'])->name('chat.save-message');
    Route::post('/chat/unsave-message', [ChatController::class, 'unsaveMessage'])->name('chat.unsave-message');
    Route::post('/chat/update-settings', [ChatController::class, 'updateSettings'])->name('chat.update-settings');
    Route::delete('/chat/delete-conversation/{userId}', [ChatController::class, 'deleteConversation'])->name('chat.delete-conversation');
    Route::get('/chat/shared-photos/{userId}', [ChatController::class, 'getSharedPhotos'])->name('chat.shared-photos');
    Route::post('/chat/set-typing', [ChatController::class, 'setTyping'])->name('chat.set-typing');

    // Debug route for logout
    Route::get('/debug-logout', function() {
        return response()->json([
            'user' => auth()->user() ? auth()->user()->name : 'Not logged in',
            'csrf_token' => csrf_token(),
            'session_id' => session()->getId()
        ]);
    });
});
*/
?>
