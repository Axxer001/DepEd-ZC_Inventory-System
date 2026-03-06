<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="flex flex-col items-center justify-center p-4">

    <div class="flex flex-col items-center w-full max-w-2xl">

        <div class="flex flex-col items-center mb-6 text-center -ml-8 animate-fade-up">
            <div class="flex items-center gap-4 mb-2">
                <img src="{{ asset('images/deped_logo.png') }}" alt="DepEd Logo" class="h-12 md:h-14 w-auto object-contain">
                <img src="{{ asset('images/deped_zc_logo.png') }}" alt="DepEd ZC Logo" class="h-12 md:h-14 w-auto object-contain">
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-black">
                    DepEd Zamboanga City
                </h1>
            </div>
            <p class="text-slate-400 font-bold tracking-[0.2em] text-[10px] ml-8 uppercase">Inventory Management System</p>
        </div>

        <div class="w-full max-w-md bg-white rounded-[2rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden animate-fade-up" style="animation-delay: 0.1s;">

            <div class="h-1.5 bg-deped-red w-full"></div>

            <div class="p-8 md:p-10">
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome Back</h2>
                    <p class="text-slate-500 text-sm mt-1">Enter your email to access your dashboard.</p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                        <input type="email" name="email" required
                               class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm"
                               placeholder="username@deped.gov.ph">
                    </div>

                    <button type="submit"
                            class="btn-hover-effect w-full bg-deped-red text-white py-4 rounded-2xl font-bold text-lg shadow-lg active:scale-[0.98]">
                        Sign In
                    </button>

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold">
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