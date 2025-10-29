# FARMCHAT - Real-time Messaging System

FARMCHAT is a comprehensive, feature-rich, real-time messaging system built with Laravel and Vue.js. It provides a modern, responsive chat interface with advanced features for agricultural communities.

## Features

### Core Chat Functionality
- **One-to-One Chat**: Private messaging between individual users
- **Real-time Updates**: Live contact list and message updates
- **Active Status**: Real-time online/offline status display
- **Typing Indicator**: Shows when users are typing
- **Message Status**: Read receipts and delivery status
- **Last Message Indicator**: Shows preview of last message in conversations
- **Connection Status**: Internet connection status indicator

### Advanced Features & Media
- **Saved Messages**: Personal message storage (like Telegram's Saved Messages)
- **Favorite Users**: Instagram/Facebook Stories-style favorites section
- **Attachments**: Support for photos, files, and documents
- **Emojis**: Full emoji support with picker
- **Search**: Find contacts and search through messages

### User Experience & Customization
- **Details Panel**: View shared photos and conversation management
- **Settings Panel**: Comprehensive user customization options
  - Profile photo management
  - Dark/Light mode toggle
  - Chat color selection
  - Notification preferences
- **Responsive Design**: Works on all devices
- **Modern UI**: Clean, intuitive interface inspired by popular chat apps

## Installation

1. **Database Setup**: Run the migrations to create the required tables:
   ```bash
   php artisan migrate
   ```

2. **Storage Link**: Create a symbolic link for file storage:
   ```bash
   php artisan storage:link
   ```

3. **Access the Chat**: Navigate to `/chat` in your application

## Database Structure

The system uses the following main tables:
- `messages` - Stores chat messages with metadata
- `favorites` - User favorite relationships
- `saved_messages` - User-saved messages
- `user_settings` - User preferences and settings
- `attachments` - File attachments for messages

## API Endpoints

### Chat Routes
- `GET /chat` - Main chat interface
- `GET /chat/conversations` - Get user conversations
- `GET /chat/messages/{userId}` - Get messages with specific user
- `POST /chat/send-message` - Send a new message
- `GET /chat/search-users` - Search for users
- `POST /chat/add-favorite` - Add user to favorites
- `POST /chat/remove-favorite` - Remove user from favorites
- `GET /chat/favorites` - Get user's favorites
- `GET /chat/saved-messages` - Get saved messages
- `POST /chat/save-message` - Save a message
- `POST /chat/unsave-message` - Unsave a message
- `POST /chat/update-settings` - Update user settings
- `DELETE /chat/delete-conversation/{userId}` - Delete conversation
- `GET /chat/shared-photos/{userId}` - Get shared photos
- `POST /chat/set-typing` - Set typing status

## Frontend Architecture

The chat interface is built with:
- **Vue.js 3** - Reactive frontend framework
- **Custom CSS** - Modern, responsive styling
- **Font Awesome** - Icon library
- **Real-time Updates** - Polling-based updates (can be enhanced with WebSockets)

## Customization

### Themes
The system supports both light and dark modes with customizable chat colors. Users can:
- Toggle between light and dark themes
- Choose custom chat bubble colors
- Adjust notification preferences

### File Uploads
Supports various file types:
- Images (JPG, PNG, GIF, WebP)
- Documents (PDF, DOC, DOCX)
- Videos and audio files

## Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Authentication**: Requires user authentication
- **File Validation**: Secure file upload handling
- **Input Sanitization**: All user inputs are sanitized

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Future Enhancements

- WebSocket integration for true real-time updates
- Push notifications
- Group chat functionality
- Message encryption
- Voice messages
- Video calls integration

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure your database is running and properly configured
2. **File Uploads**: Check that the storage directory is writable
3. **Real-time Updates**: The system uses polling as a fallback; consider implementing WebSockets for better performance

### Debug Mode

Enable debug mode in your `.env` file to see detailed error messages:
```
APP_DEBUG=true
```

## Contributing

To contribute to FARMCHAT:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is part of the Farm Guide application and follows the same licensing terms.
