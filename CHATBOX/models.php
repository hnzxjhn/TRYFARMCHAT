<?php
// Complete Database Models for FARMCHAT

// 1. Message Model (app/Models/Message.php)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'message_type',
        'is_read',
        'read_at',
        'is_typing',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_typing' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function savedMessages()
    {
        return $this->hasMany(SavedMessage::class);
    }
}

// 2. Favorite Model (app/Models/Favorite.php)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favorite_user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoriteUser()
    {
        return $this->belongsTo(User::class, 'favorite_user_id');
    }
}

// 3. SavedMessage Model (app/Models/SavedMessage.php)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavedMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}

// 4. UserSetting Model (app/Models/UserSetting.php)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dark_mode',
        'chat_color',
        'show_online_status',
        'show_typing_indicator',
        'play_sound'
    ];

    protected $casts = [
        'dark_mode' => 'boolean',
        'show_online_status' => 'boolean',
        'show_typing_indicator' => 'boolean',
        'play_sound' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// 5. Attachment Model (app/Models/Attachment.php)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'filename',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'file_type'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}

// 6. Updated User Model (app/Models/User.php) - Add these relationships
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'auth_provider',
        'has_password',
        'role',
        'profile_picture',
        'location',
        'farm_type',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Existing relationships...
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id', 'id');
    }

    public function friendshipsAsAddressee()
    {
        return $this->hasMany(Friendship::class, 'friend_id', 'id');
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id', 'id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id', 'id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id');
    }

    // NEW CHAT RELATIONSHIPS
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->hasMany(Favorite::class, 'favorite_user_id');
    }

    public function savedMessages()
    {
        return $this->hasMany(SavedMessage::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }
}
?>
