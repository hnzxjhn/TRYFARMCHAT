@extends('layouts.chat')

@section('title', 'FARMCHAT - Real-time Messaging')

@section('content')
<div class="chat-container" id="chatApp">
    <!-- Left Panel - Messages List -->
    <div class="messages-panel" :class="{ 'dark-mode': isDarkMode }">
        <!-- Header -->
        <div class="messages-header">
            <h2>MESSAGES</h2>
            <button @click="showSettings = !showSettings" class="settings-btn">
                <i class="fas fa-cog"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" v-model="searchQuery" @input="searchUsers" placeholder="Search..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>

        <!-- Favorites Section -->
        <div class="favorites-section">
            <h3>Favorites</h3>
            <div class="favorites-list">
                <div v-for="favorite in favorites" :key="favorite.id" class="favorite-item" @click="selectUser(favorite.favorite_user)">
                    <img :src="favorite.favorite_user.profile_picture || '/assets/img/default-avatar.svg'" :alt="favorite.favorite_user.name" class="favorite-avatar">
                    <span class="favorite-name">@{{ favorite.favorite_user.name }}</span>
                </div>
            </div>
        </div>

        <!-- Your Space Section -->
        <div class="your-space-section">
            <h3>Your Space</h3>
            <div class="saved-messages-item" @click="showSavedMessages = true">
                <i class="fas fa-bookmark"></i>
                <div>
                    <span>Saved Messages</span>
                    <small>Save messages secretly</small>
                </div>
                <span>You</span>
            </div>
        </div>

        <!-- All Messages Section -->
        <div class="all-messages-section">
            <h3>All Messages</h3>
            <div class="messages-list">
                <div v-for="conversation in filteredConversations" :key="conversation.user.id" 
                     class="message-item" 
                     :class="{ 'active': selectedUser && selectedUser.id === conversation.user.id }"
                     @click="selectUser(conversation.user)">
                    <img :src="conversation.user.profile_picture || '/assets/img/default-avatar.svg'" 
                         :alt="conversation.user.name" class="message-avatar">
                    <div class="message-content">
                        <div class="message-header">
                            <span class="user-name">@{{ conversation.user.name }}</span>
                            <span class="message-time">@{{ formatTime(conversation.last_message.created_at) }}</span>
                        </div>
                        <div class="message-preview">
                            <span v-if="conversation.last_message.sender_id === currentUser.id">You: </span>
                            <span v-if="conversation.last_message.message_type === 'image'">📷 Image</span>
                            <span v-else-if="conversation.last_message.message_type === 'file'">📎 Attachment</span>
                            <span v-else>@{{ conversation.last_message.message }}</span>
                        </div>
                    </div>
                    <div v-if="conversation.unread_count > 0" class="unread-badge">
                        @{{ conversation.unread_count }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Panel - Chat Area -->
    <div class="chat-panel" :class="{ 'dark-mode': isDarkMode }">
        <div v-if="!selectedUser" class="no-chat-selected">
            <div class="welcome-message">
                <h2>Welcome to FARMCHAT</h2>
                <p>Select a conversation to start chatting</p>
            </div>
        </div>

        <div v-else class="chat-area">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-user-info">
                    <img :src="selectedUser.profile_picture || '/assets/img/default-avatar.svg'" 
                         :alt="selectedUser.name" class="chat-user-avatar">
                    <div>
                        <h3>@{{ selectedUser.name }}</h3>
                        <span class="online-status" :class="{ 'online': selectedUser.is_online }">
                            @{{ selectedUser.is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button @click="toggleFavorite(selectedUser.id)" class="action-btn">
                        <i class="fas fa-star" :class="{ 'favorited': isFavorited(selectedUser.id) }"></i>
                    </button>
                    <button @click="showUserDetails = true" class="action-btn">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-area" ref="messagesArea">
                <div v-for="message in messages" :key="message.id" 
                     class="message-bubble" 
                     :class="{ 'sent': message.sender_id === currentUser.id, 'received': message.sender_id !== currentUser.id }">
                    
                    <div v-if="message.sender_id !== currentUser.id" class="message-avatar">
                        <img :src="message.sender.profile_picture || '/assets/img/default-avatar.svg'" 
                             :alt="message.sender.name">
                    </div>
                    
                    <div class="message-content">
                        <div v-if="message.message_type === 'image'" class="message-image">
                            <img :src="'/storage/' + message.attachments[0].file_path" :alt="message.attachments[0].original_name">
                        </div>
                        <div v-else-if="message.message_type === 'file'" class="message-file">
                            <i class="fas fa-file"></i>
                            <span>@{{ message.attachments[0].original_name }}</span>
                        </div>
                        <div v-else class="message-text">@{{ message.message }}</div>
                        
                        <div class="message-meta">
                            <span class="message-time">@{{ formatTime(message.created_at) }}</span>
                            <span v-if="message.sender_id === currentUser.id" class="message-status">
                                <i v-if="message.is_read" class="fas fa-check-double read"></i>
                                <i v-else class="fas fa-check"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Typing Indicator -->
                <div v-if="isTyping" class="typing-indicator">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <div class="message-input">
                    <button @click="showEmojiPicker = !showEmojiPicker" class="input-btn">
                        <i class="fas fa-smile"></i>
                    </button>
                    <input type="text" v-model="newMessage" @keyup.enter="sendMessage" 
                           @input="handleTyping" placeholder="Type a message..." class="message-field">
                    <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*,application/pdf,.doc,.docx" style="display: none;">
                    <button @click="$refs.fileInput.click()" class="input-btn">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <button @click="sendMessage" class="send-btn" :disabled="!newMessage.trim()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel - User Details -->
    <div v-if="showUserDetails && selectedUser" class="user-details-panel" :class="{ 'dark-mode': isDarkMode }">
        <div class="user-details-header">
            <h3>User Details</h3>
            <button @click="showUserDetails = false" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="user-profile">
            <img :src="selectedUser.profile_picture || '/assets/img/default-avatar.svg'" 
                 :alt="selectedUser.name" class="user-profile-avatar">
            <h4>@{{ selectedUser.name }}</h4>
            <p>@{{ selectedUser.email }}</p>
        </div>
        
        <div class="user-actions">
            <button @click="deleteConversation(selectedUser.id)" class="danger-btn">
                <i class="fas fa-trash"></i>
                Delete Conversation
            </button>
        </div>
        
        <div class="shared-photos">
            <h4>Shared Photos</h4>
            <div class="photos-grid">
                <div v-for="photo in sharedPhotos" :key="photo.id" class="photo-item">
                    <img :src="'/storage/' + photo.file_path" :alt="photo.original_name">
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Panel -->
    <div v-if="showSettings" class="settings-panel" :class="{ 'dark-mode': isDarkMode }">
        <div class="settings-header">
            <h3>Settings</h3>
            <button @click="showSettings = false" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="settings-content">
            <div class="setting-item">
                <label>
                    <input type="checkbox" v-model="userSettings.dark_mode" @change="updateSettings">
                    Dark Mode
                </label>
            </div>
            
            <div class="setting-item">
                <label>Chat Color</label>
                <input type="color" v-model="userSettings.chat_color" @change="updateSettings">
            </div>
            
            <div class="setting-item">
                <label>
                    <input type="checkbox" v-model="userSettings.show_online_status" @change="updateSettings">
                    Show Online Status
                </label>
            </div>
            
            <div class="setting-item">
                <label>
                    <input type="checkbox" v-model="userSettings.show_typing_indicator" @change="updateSettings">
                    Show Typing Indicator
                </label>
            </div>
            
            <div class="setting-item">
                <label>
                    <input type="checkbox" v-model="userSettings.play_sound" @change="updateSettings">
                    Play Sound
                </label>
            </div>
        </div>
    </div>

    <!-- Saved Messages Panel -->
    <div v-if="showSavedMessages" class="saved-messages-panel" :class="{ 'dark-mode': isDarkMode }">
        <div class="saved-messages-header">
            <h3>Saved Messages</h3>
            <button @click="showSavedMessages = false" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="saved-messages-list">
            <div v-for="saved in savedMessages" :key="saved.id" class="saved-message-item">
                <div class="saved-message-content">
                    <p>@{{ saved.message.message }}</p>
                    <small>From: @{{ saved.message.sender.name }}</small>
                </div>
                <button @click="unsaveMessage(saved.message_id)" class="unsave-btn">
                    <i class="fas fa-bookmark"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Emoji Picker -->
<div v-if="showEmojiPicker" class="emoji-picker">
    <div class="emoji-grid">
        <span v-for="emoji in emojis" :key="emoji" @click="addEmoji(emoji)" class="emoji-item">
            @{{ emoji }}
        </span>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/chat.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script src="{{ asset('assets/js/chat.js') }}"></script>
@endpush
