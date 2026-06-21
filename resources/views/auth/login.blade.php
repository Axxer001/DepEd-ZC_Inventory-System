<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">

    <div class="flex flex-col items-center w-full max-w-2xl">

        <div class="flex flex-col items-center mb-6 text-center -ml-8 animate-fade-up">
            <div class="flex items-center gap-4 mb-2">
                <img src="{{ asset('images/deped_logo.png') }}" alt="DepEd Logo" class="h-12 md:h-14 w-auto object-contain">
                <img src="{{ asset('images/deped_zc_logo.png') }}" alt="DepEd ZC Logo" class="h-12 md:h-14 w-auto object-contain">
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                    DepEd Zamboanga City
                </h1>
            </div>
            <p class="text-white/70 font-bold tracking-[0.2em] text-[10px] ml-8 uppercase">Inventory Management System</p>
        </div>

        <div class="w-full max-w-md bg-white rounded-[2rem] shadow-none overflow-hidden animate-fade-up" style="animation-delay: 0.1s;">

            <div class="h-1.5 bg-deped-red w-full"></div>

            <div class="p-8 md:p-10 pb-6">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome Back</h2>
                        <p class="text-slate-500 text-sm mt-1">Enter your credentials to access your dashboard.</p>
                    </div>

                    <form action="{{ route('login') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm"
                                   placeholder="username@deped.gov.ph">
                        </div>

                        <div class="space-y-2" x-data="{ showPw: false }">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Password</label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="password" required
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm pr-10"
                                       placeholder="••••••••">
                                <button type="button" @click="showPw = !showPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg x-show="!showPw" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showPw" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end mt-1">
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#c00000] hover:underline">Forgot Password?</a>
                        </div>

                        <button type="submit"
                                class="btn-hover-effect w-full bg-deped-red text-white py-3 rounded-2xl font-bold text-lg active:scale-[0.98] shadow-md mt-2">
                            Sign In
                        </button>

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold mt-2">
                                {{ session('error') }}
                            </div>
                        @endif
                    </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-[#c00000] font-bold hover:underline transition-colors">Register here</a>
                    </p>
                </div>
            </div>

            <div class="bg-slate-50/80 px-10 py-4 border-t border-slate-100 text-center">
                <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">
                   Region IX Division of Zamboanga City • Inventory
                </p>
            </div>
        </div>
    </div>

</body>
</html>