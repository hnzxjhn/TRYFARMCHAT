// FARMCHAT Complete JavaScript (Vue.js 3)
const { createApp } = Vue;

createApp({
    data() {
        return {
            // User data
            currentUser: window.currentUser || {},
            
            // UI state
            isDarkMode: false,
            selectedUser: null,
            showSettings: false,
            showUserDetails: false,
            showSavedMessages: false,
            showEmojiPicker: false,
            
            // Messages
            messages: [],
            conversations: [],
            favorites: [],
            savedMessages: [],
            sharedPhotos: [],
            
            // Search
            searchQuery: '',
            searchResults: [],
            
            // Message input
            newMessage: '',
            isTyping: false,
            typingTimeout: null,
            selectedFile: null,
            
            // Settings
            userSettings: {
                dark_mode: false,
                chat_color: '#6366f1',
                show_online_status: true,
                show_typing_indicator: true,
                play_sound: true
            },
            
            // Emojis
            emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'],
            
            // Real-time updates
            connectionStatus: 'connected',
            lastMessageId: null
        }
    },
    
    computed: {
        filteredConversations() {
            if (!this.searchQuery) {
                return this.conversations;
            }
            
            return this.conversations.filter(conversation => 
                conversation.user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                conversation.last_message.message.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        }
    },
    
    mounted() {
        this.initializeChat();
        this.setupEventListeners();
        this.loadUserSettings();
    },
    
    methods: {
        async initializeChat() {
            try {
                // Load initial data
                await this.loadConversations();
                await this.loadFavorites();
                await this.loadSavedMessages();
                
                // Set up real-time updates
                this.setupRealTimeUpdates();
                
            } catch (error) {
                console.error('Error initializing chat:', error);
            }
        },
        
        async loadConversations() {
            try {
                const response = await fetch('/chat/conversations');
                const data = await response.json();
                this.conversations = data.conversations || [];
            } catch (error) {
                console.error('Error loading conversations:', error);
                this.conversations = [];
            }
        },
        
        async loadFavorites() {
            try {
                const response = await fetch('/chat/favorites');
                const data = await response.json();
                this.favorites = data.favorites || [];
            } catch (error) {
                console.error('Error loading favorites:', error);
                this.favorites = [];
            }
        },
        
        async loadSavedMessages() {
            try {
                const response = await fetch('/chat/saved-messages');
                const data = await response.json();
                this.savedMessages = data.savedMessages || [];
            } catch (error) {
                console.error('Error loading saved messages:', error);
                this.savedMessages = [];
            }
        },
        
        async selectUser(user) {
            this.selectedUser = user;
            this.showUserDetails = false;
            this.showSettings = false;
            this.showSavedMessages = false;
            
            // Load messages for this user
            await this.loadMessages(user.id);
            
            // Load shared photos
            await this.loadSharedPhotos(user.id);
        },
        
        async loadMessages(userId) {
            try {
                const response = await fetch(`/chat/messages/${userId}`);
                const data = await response.json();
                this.messages = data.messages || data || [];
                
                // Scroll to bottom
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            } catch (error) {
                console.error('Error loading messages:', error);
                this.messages = [];
            }
        },
        
        async loadSharedPhotos(userId) {
            try {
                const response = await fetch(`/chat/shared-photos/${userId}`);
                const data = await response.json();
                this.sharedPhotos = data.photos || data || [];
            } catch (error) {
                console.error('Error loading shared photos:', error);
                this.sharedPhotos = [];
            }
        },
        
        async sendMessage() {
            if (!this.newMessage.trim() && !this.selectedFile) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('receiver_id', this.selectedUser.id);
                formData.append('message', this.newMessage);
                formData.append('message_type', 'text');
                
                if (this.selectedFile) {
                    formData.append('attachments[]', this.selectedFile);
                }
                
                const response = await fetch('/chat/send-message', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success !== false) {
                    this.messages.push(data.message || data);
                    this.newMessage = '';
                    this.selectedFile = null;
                    
                    // Scroll to bottom
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                    
                    // Play sound if enabled
                    if (this.userSettings.play_sound) {
                        this.playNotificationSound();
                    }
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        },
        
        async searchUsers() {
            if (!this.searchQuery.trim()) {
                this.searchResults = [];
                return;
            }
            
            try {
                const response = await fetch(`/chat/search-users?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.searchResults = data.users || data || [];
            } catch (error) {
                console.error('Error searching users:', error);
                this.searchResults = [];
            }
        },
        
        async toggleFavorite(userId) {
            try {
                const isFavorited = this.isFavorited(userId);
                const endpoint = isFavorited ? '/chat/remove-favorite' : '/chat/add-favorite';
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (isFavorited) {
                        this.favorites = this.favorites.filter(fav => fav.favorite_user_id !== userId);
                    } else {
                        // Add to favorites (you might need to fetch the user data)
                        this.favorites.push({
                            favorite_user_id: userId,
                            favorite_user: this.selectedUser
                        });
                    }
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        },
        
        isFavorited(userId) {
            return this.favorites.some(fav => fav.favorite_user_id === userId);
        },
        
        async saveMessage(messageId) {
            try {
                const response = await fetch('/chat/save-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message_id: messageId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add to saved messages
                    this.savedMessages.push({
                        message_id: messageId,
                        message: this.messages.find(m => m.id === messageId)
                    });
                }
            } catch (error) {
                console.error('Error saving message:', error);
            }
        },
        
        async unsaveMessage(messageId) {
            try {
                const response = await fetch('/chat/unsave-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message_id: messageId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.savedMessages = this.savedMessages.filter(sm => sm.message_id !== messageId);
                }
            } catch (error) {
                console.error('Error unsaving message:', error);
            }
        },
        
        async updateSettings() {
            try {
                const response = await fetch('/chat/update-settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.userSettings)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.userSettings = data.settings || this.userSettings;
                    this.isDarkMode = this.userSettings.dark_mode;
                    this.applyTheme();
                }
            } catch (error) {
                console.error('Error updating settings:', error);
            }
        },
        
        async deleteConversation(userId) {
            if (!confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/chat/delete-conversation/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.conversations = this.conversations.filter(conv => conv.user.id !== userId);
                    if (this.selectedUser && this.selectedUser.id === userId) {
                        this.selectedUser = null;
                        this.messages = [];
                    }
                }
            } catch (error) {
                console.error('Error deleting conversation:', error);
            }
        },
        
        handleTyping() {
            if (!this.selectedUser) return;
            
            // Clear existing timeout
            if (this.typingTimeout) {
                clearTimeout(this.typingTimeout);
            }
            
            // Set typing status
            this.setTypingStatus(true);
            
            // Set timeout to stop typing
            this.typingTimeout = setTimeout(() => {
                this.setTypingStatus(false);
            }, 1000);
        },
        
        async setTypingStatus(isTyping) {
            try {
                await fetch('/chat/set-typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        receiver_id: this.selectedUser.id,
                        is_typing: isTyping
                    })
                });
            } catch (error) {
                console.error('Error setting typing status:', error);
            }
        },
        
        addEmoji(emoji) {
            this.newMessage += emoji;
            this.showEmojiPicker = false;
        },
        
        handleFileSelect(event) {
            this.selectedFile = event.target.files[0];
            if (this.selectedFile) {
                this.sendMessage();
            }
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) { // Less than 1 minute
                return 'Just now';
            } else if (diff < 3600000) { // Less than 1 hour
                return Math.floor(diff / 60000) + 'm ago';
            } else if (diff < 86400000) { // Less than 1 day
                return Math.floor(diff / 3600000) + 'h ago';
            } else if (diff < 604800000) { // Less than 1 week
                return Math.floor(diff / 86400000) + 'd ago';
            } else {
                return date.toLocaleDateString();
            }
        },
        
        scrollToBottom() {
            const messagesArea = this.$refs.messagesArea;
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        },
        
        playNotificationSound() {
            // Create audio context for notification sound
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (error) {
                console.log('Audio not supported');
            }
        },
        
        setupEventListeners() {
            // Listen for real-time updates
            window.addEventListener('message', (event) => {
                if (event.data.type === 'new_message') {
                    this.handleNewMessage(event.data.message);
                } else if (event.data.type === 'typing') {
                    this.handleTypingUpdate(event.data);
                } else if (event.data.type === 'message_read') {
                    this.handleMessageRead(event.data);
                }
            });
            
            // Listen for connection status changes
            window.addEventListener('online', () => {
                this.connectionStatus = 'connected';
            });
            
            window.addEventListener('offline', () => {
                this.connectionStatus = 'disconnected';
            });
        },
        
        setupRealTimeUpdates() {
            // Set up polling for real-time updates (fallback for when WebSockets aren't available)
            setInterval(() => {
                this.checkForNewMessages();
            }, 2000);
        },
        
        async checkForNewMessages() {
            if (!this.selectedUser) return;
            
            try {
                const response = await fetch(`/chat/messages/${this.selectedUser.id}?since=${this.lastMessageId || 0}`);
                const data = await response.json();
                
                if (data.messages && data.messages.length > 0) {
                    const newMessages = data.messages.filter(msg => 
                        !this.messages.some(existing => existing.id === msg.id)
                    );
                    
                    if (newMessages.length > 0) {
                        this.messages.push(...newMessages);
                        this.lastMessageId = Math.max(...newMessages.map(msg => msg.id));
                        
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                        
                        if (this.userSettings.play_sound) {
                            this.playNotificationSound();
                        }
                    }
                }
            } catch (error) {
                console.error('Error checking for new messages:', error);
            }
        },
        
        handleNewMessage(message) {
            if (message.sender_id === this.currentUser.id) return;
            
            this.messages.push(message);
            
            // Update conversation list
            const conversation = this.conversations.find(conv => conv.user.id === message.sender_id);
            if (conversation) {
                conversation.last_message = message;
                conversation.unread_count++;
            }
            
            this.$nextTick(() => {
                this.scrollToBottom();
            });
            
            if (this.userSettings.play_sound) {
                this.playNotificationSound();
            }
        },
        
        handleTypingUpdate(data) {
            if (data.user_id === this.selectedUser?.id) {
                this.isTyping = data.is_typing;
            }
        },
        
        handleMessageRead(data) {
            // Update message read status
            this.messages.forEach(message => {
                if (message.id === data.message_id) {
                    message.is_read = true;
                    message.read_at = data.read_at;
                }
            });
        },
        
        loadUserSettings() {
            // Load user settings from the server
            this.userSettings = window.userSettings || this.userSettings;
            this.isDarkMode = this.userSettings.dark_mode;
            this.applyTheme();
        },
        
        applyTheme() {
            if (this.isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
            
            // Apply chat color
            document.documentElement.style.setProperty('--chat-color', this.userSettings.chat_color);
        }
    }
}).mount('#chatApp');
