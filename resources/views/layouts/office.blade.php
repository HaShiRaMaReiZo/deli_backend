<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Office Panel') - Delivery Express</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white min-h-screen">
            <div class="p-4">
                <h1 class="text-xl font-bold">Delivery Express</h1>
                <p class="text-gray-400 text-sm">Office Panel</p>
            </div>
            <nav class="mt-6">
                <a href="{{ route('office.dashboard') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.dashboard') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-home mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('office.packages') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.packages') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-box mr-3"></i>
                    Packages
                </a>
                <a href="{{ route('office.registered_packages_by_merchant') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.registered_packages_by_merchant') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-store mr-3"></i>
                    Packages by Merchant
                </a>
                <a href="{{ route('office.picked_up_packages') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.picked_up_packages') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-box-open mr-3"></i>
                    Picked Up Packages
                </a>
                <a href="{{ route('office.riders') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.riders') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-motorcycle mr-3"></i>
                    Riders
                </a>
                <a href="{{ route('office.map') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.map') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-map-marked-alt mr-3"></i>
                    Live Map
                </a>
                <a href="{{ route('office.register_user') }}" class="flex items-center px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('office.register_user*') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-user-plus mr-3"></i>
                    Register User
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <span class="text-gray-400">|</span>
                        <span class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                        <form action="{{ route('office.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Laravel Echo & Pusher for WebSocket -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher for WebSocket connections
        const pusherConfig = {
            key: '{{ env("PUSHER_APP_KEY", "") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Authorization': 'Bearer {{ $apiToken ?? "" }}'
                }
            }
        };
        
        // Only initialize if Pusher key is configured
        let pusher = null;
        
        if (pusherConfig.key && pusherConfig.key !== '') {
            try {
                pusher = new Pusher(pusherConfig.key, {
                    cluster: pusherConfig.cluster,
                    encrypted: pusherConfig.encrypted,
                    authEndpoint: pusherConfig.authEndpoint,
                    auth: pusherConfig.auth
                });
                
                // Connection status logging
                pusher.connection.bind('connected', () => {
                    console.log('WebSocket connected');
                });
                
                pusher.connection.bind('error', (err) => {
                    console.warn('WebSocket connection error:', err);
                });
            } catch (e) {
                console.warn('Failed to initialize Pusher:', e);
            }
        } else {
            console.info('Pusher not configured. WebSocket features disabled. Add PUSHER_APP_KEY to .env to enable.');
        }
        
        // Global WebSocket helper functions
        window.WebSocketHelper = {
            connect: function(channelName, eventName, callback) {
                if (!pusher) {
                    console.warn('Pusher not configured. WebSocket features disabled.');
                    return null;
                }
                
                const channel = pusher.subscribe(channelName);
                channel.bind(eventName, callback);
                return channel;
            },
            
            disconnect: function(channel) {
                if (channel && pusher) {
                    pusher.unsubscribe(channel.name);
                }
            },
            
            isConnected: function() {
                return pusher !== null && pusher.connection.state === 'connected';
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>

