<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FARMCHAT')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    @stack('styles')
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 0; height: 100vh; overflow: hidden;">
    <!-- Navigation Bar -->
    <nav class="navbar" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="navbar-brand" style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('dashboard') }}" style="text-decoration: none; color: #1a202c; font-weight: 700; font-size: 18px;">
                <i class="fas fa-seedling" style="color: #10b981; margin-right: 8px;"></i>
                FARMCHAT
            </a>
        </div>
        
        <div class="navbar-actions" style="display: flex; align-items: center; gap: 16px;">
            <a href="{{ route('dashboard') }}" class="nav-link" style="text-decoration: none; color: #64748b; font-weight: 500; padding: 8px 12px; border-radius: 6px; transition: all 0.2s ease;">
                <i class="fas fa-home" style="margin-right: 6px;"></i>
                Dashboard
            </a>
            
            <a href="{{ route('friends') }}" class="nav-link" style="text-decoration: none; color: #64748b; font-weight: 500; padding: 8px 12px; border-radius: 6px; transition: all 0.2s ease;">
                <i class="fas fa-users" style="margin-right: 6px;"></i>
                Friends
            </a>
            
            <div class="user-menu" style="position: relative;">
                <button class="user-button" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; transition: all 0.2s ease;">
                    <img src="{{ Auth::user()->profile_picture ? Storage::url(Auth::user()->profile_picture) : '/assets/img/default-avatar.svg' }}" 
                         alt="{{ Auth::user()->name }}" 
                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <span style="color: #1a202c; font-weight: 500;">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down" style="color: #64748b; font-size: 12px;"></i>
                </button>
                
                <div class="user-dropdown" style="position: absolute; top: 100%; right: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); min-width: 200px; z-index: 1000; display: none;">
                    <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: #1a202c; border-bottom: 1px solid #f1f5f9;">
                        <i class="fas fa-user" style="color: #64748b;"></i>
                        Profile
                    </a>
                    <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: #1a202c; border-bottom: 1px solid #f1f5f9;">
                        <i class="fas fa-cog" style="color: #64748b;"></i>
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: #ef4444; background: none; border: none; width: 100%; text-align: left; cursor: pointer;">
                            <i class="fas fa-sign-out-alt" style="color: #ef4444;"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main style="margin-top: 60px; height: calc(100vh - 60px); overflow: hidden;">
        @yield('content')
    </main>

    <!-- Scripts -->
    @stack('scripts')

    <script>
        // User dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const userButton = document.querySelector('.user-button');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userButton && userDropdown) {
                userButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
                });
                
                document.addEventListener('click', function() {
                    userDropdown.style.display = 'none';
                });
            }
        });

        // Pass user data to JavaScript
        window.currentUser = @json(Auth::user());
        window.userSettings = @json(isset($userSettings) ? $userSettings : [
            'dark_mode' => false,
            'chat_color' => '#6366f1',
            'show_online_status' => true,
            'show_typing_indicator' => true,
            'play_sound' => true
        ]);
    </script>
</body>
</html>
