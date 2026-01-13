@php
    $isMoviesActive = request()->routeIs('movies.index') || request()->routeIs('movies.create') || request()->routeIs('movies.edit') || request()->routeIs('movies.show');
    $isMovieAnalyticsActive = request()->routeIs('movies.transactions*');
    $isTelegramUsersActive = request()->routeIs('telegram-users.*');
    $isPaymentsActive = request()->routeIs('payments.*');
    $isBotAdminsActive = request()->routeIs('bot-admins.*');
    $isViewLogsActive = request()->routeIs('view-logs.*');
    $isUsersActive = request()->routeIs('users.*');
    $isSettingsActive = request()->routeIs('settings.*');

    $baseItem = 'nav-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200';
    $activeItem = 'active bg-blue-50 text-blue-700';
    $inactiveItem = 'text-gray-700 hover:bg-blue-50';

    $baseIcon = 'icon-wrapper flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br shadow-md group-hover:shadow-lg transition-all duration-200 flex-shrink-0';
    $activeIconGrad = 'from-indigo-600 to-blue-600';
    $inactiveIconGrad = 'from-blue-500 to-indigo-600';
@endphp

<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-72 bg-white transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300 ease-in-out card-shadow-lg">
    <div class="sidebar-header flex items-center justify-between h-16 px-6 border-b border-gray-100">
        <div class="flex items-center space-x-3 sidebar-full">
            <div class="relative flex-shrink-0">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
            </div>
            <div class="sidebar-text">
                <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent block">Dracin</span>
                <div class="text-xs text-gray-500 font-medium">Film Management</div>
            </div>
        </div>
        <div class="sidebar-icon w-full">
            <div class="relative inline-block">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
            </div>
        </div>
        <button type="button" id="sidebar-close-mobile" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg md:hidden transition-all duration-200 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="px-4 py-6 space-y-6 overflow-y-auto h-[calc(100vh-9rem)] sidebar-scroll">
        <div>
            <div class="section-title px-3 mb-3">
                <span class="section-title-text text-xs font-bold text-gray-400 uppercase tracking-wider">Main</span>
                <span class="section-title-icon"></span>
            </div>
            
            <div class="space-y-1">
                {{-- Film Management - Available for both admin and moderator --}}
                @if(auth()->user()->isStaff())
                    <a href="{{ route('movies.index') }}"
                       class="{{ $baseItem }} {{ $isMoviesActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isMoviesActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isMoviesActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Film Management</span>
                    </a>
                @endif

                {{-- Admin Only Menus --}}
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('telegram-users.index') }}"
                       class="{{ $baseItem }} {{ $isTelegramUsersActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isTelegramUsersActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isTelegramUsersActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Telegram Users</span>
                    </a>

                    <a href="{{ route('bot-admins.index') }}"
                       class="{{ $baseItem }} {{ $isBotAdminsActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isBotAdminsActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isBotAdminsActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Bot Admins</span>
                    </a>

                    <a href="{{ route('payments.index') }}"
                       class="{{ $baseItem }} {{ $isPaymentsActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isPaymentsActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isPaymentsActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Payments</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Analytics Section --}}
        @if(auth()->user()->isAdmin())
            <div>
                <div class="section-title px-3 mb-3">
                    <span class="section-title-text text-xs font-bold text-gray-400 uppercase tracking-wider">Analytics</span>
                    <span class="section-title-icon"></span>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('movies.transactions') }}"
                       class="{{ $baseItem }} {{ $isMovieAnalyticsActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isMovieAnalyticsActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isMovieAnalyticsActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Movie Analytics</span>
                    </a>

                    <a href="{{ route('view-logs.index') }}"
                       class="{{ $baseItem }} {{ $isViewLogsActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isViewLogsActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isViewLogsActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">View Analytics</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- System Section --}}
        @if(auth()->user()->isAdmin())
            <div>
                <div class="section-title px-3 mb-3">
                    <span class="section-title-text text-xs font-bold text-gray-400 uppercase tracking-wider">System</span>
                    <span class="section-title-icon"></span>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('users.index') }}"
                       class="{{ $baseItem }} {{ $isUsersActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isUsersActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isUsersActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Web Users</span>
                    </a>

                    <a href="{{ route('settings.index') }}"
                       class="{{ $baseItem }} {{ $isSettingsActive ? $activeItem : $inactiveItem }}"
                       aria-current="{{ $isSettingsActive ? 'page' : 'false' }}">
                        <div class="{{ $baseIcon }} {{ $isSettingsActive ? $activeIconGrad : $inactiveIconGrad }}">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span class="sidebar-text ml-4 font-semibold">Settings</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</aside>

<div id="sidebar-overlay" class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-sm md:hidden hidden transition-all duration-300"></div>