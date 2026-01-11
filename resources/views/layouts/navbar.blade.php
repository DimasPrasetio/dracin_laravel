<header class="glass-effect flex items-center justify-between h-16 px-4 md:px-6 border-b border-gray-100 sticky top-0 z-10">
    <div class="flex items-center space-x-4">
        <button type="button" id="sidebar-toggle" class="p-2.5 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl md:hidden transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <button type="button" id="sidebar-minimize" class="hidden p-2.5 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl md:flex items-center transition-all duration-200 group">
            <svg class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
        <div class="flex items-center space-x-3">
            <h1 class="text-xl font-bold text-gray-900">@yield('title', 'Dashboard')</h1>
        </div>
    </div>
    
    <div class="flex items-center space-x-3">
        <div class="relative group">
            <button class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 rounded-xl transition-all duration-200">
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                        <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <svg class="hidden sm:block w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div class="absolute right-0 mt-2 w-56 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right">
                <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>