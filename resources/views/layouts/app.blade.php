<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Modern Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>

<body class="min-h-screen antialiased">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.sidebar')

        <div class="flex flex-col flex-1 overflow-hidden">
            @include('layouts.navbar')

            <main class="flex-1 overflow-y-auto">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>