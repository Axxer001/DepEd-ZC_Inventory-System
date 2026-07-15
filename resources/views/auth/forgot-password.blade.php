<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | DepEd Zamboanga City</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">

    <div class="flex flex-col items-center w-full max-w-2xl">

        <div class="flex flex-col items-center w-full max-w-md bg-white rounded-[2rem] shadow-none overflow-hidden animate-fade-up">

            <div class="h-1.5 bg-deped-red w-full"></div>

            <div class="p-8 md:p-10 pb-6 w-full">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Forgot Password</h2>
                        <p class="text-slate-500 text-sm mt-1">Enter your account email address to receive a verification PIN.</p>
                    </div>

                    <form action="{{ route('password.email') }}" method="POST" class="space-y-4 w-full">
                        @csrf

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm"
                                   placeholder="username@deped.gov.ph">
                        </div>

                        <button type="submit"
                                class="btn-hover-effect w-full bg-deped-red text-white py-3 rounded-2xl font-bold text-lg active:scale-[0.98] shadow-md mt-2">
                            Send PIN
                        </button>

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold mt-2">
                                {{ session('error') }}
                            </div>
                        @endif
                    </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">
                        Remember your password?
                        <a href="{{ route('login.form') }}" class="text-[#c00000] font-bold hover:underline transition-colors">Back to Login</a>
                    </p>
                </div>
            </div>

            <div class="bg-slate-50/80 px-10 py-4 border-t border-slate-100 text-center w-full">
                <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">
                   Region IX Division of Zamboanga City • Inventory
                </p>
            </div>
        </div>
    </div>

</body>
</html>
