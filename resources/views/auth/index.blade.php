<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-attachment: fixed;">
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <h2 class="text-4xl font-bold tracking-tight text-white">
                    Welcome Back
                </h2>
                <p class="mt-3 text-sm text-white/80">
                    Sign in to your account to continue
                </p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl">
                <div class="px-8 py-10">
                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        {{ $errors->first() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="login" class="block text-sm font-semibold text-slate-700">
                                Email / Username
                            </label>
                            <input 
                                type="text" 
                                name="login" 
                                id="login" 
                                required 
                                value="{{ old('login') }}"
                                class="block w-full px-4 py-3 mt-2 text-slate-900 placeholder-slate-400 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="Enter your username or email"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700">
                                Password
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required
                                class="block w-full px-4 py-3 mt-2 text-slate-900 placeholder-slate-400 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="Enter your password"
                            >
                        </div>

                        <div>
                            <button 
                                type="submit"
                                class="w-full px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-lg"
                            >
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-xs text-center text-white/70">
                © {{ date('Y') }} Admin. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>