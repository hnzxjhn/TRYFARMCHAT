<?php
// Complete Database Migrations for FARMCHAT

// 1. Update Messages Table Migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File: database/migrations/2025_10_29_001102_update_messages_table_for_chat.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('message_type')->default('text')->after('message');
            $table->boolean('is_read')->default(false)->change();
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->boolean('is_typing')->default(false)->after('read_at');
            $table->json('metadata')->nullable()->after('is_typing');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['message_type', 'read_at', 'is_typing', 'metadata']);
        });
    }
};

// 2. Create Favorites Table Migration
// File: database/migrations/2025_10_29_001113_create_favorites_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('favorite_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'favorite_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};

// 3. Create Saved Messages Table Migration
// File: database/migrations/2025_10_29_001139_create_saved_messages_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_messages');
    }
};

// 4. Create User Settings Table Migration
// File: database/migrations/2025_10_29_001146_create_user_settings_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('dark_mode')->default(false);
            $table->string('chat_color')->default('#6366f1');
            $table->boolean('show_online_status')->default(true);
            $table->boolean('show_typing_indicator')->default(true);
            $table->boolean('play_sound')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};

// 5. Create Attachments Table Migration
// File: database/migrations/2025_10_29_001150_create_attachments_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('file_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
?>
