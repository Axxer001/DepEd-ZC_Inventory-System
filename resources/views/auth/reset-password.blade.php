<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | DepEd Zamboanga City</title>

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

        <div class="flex flex-col items-center w-full max-w-md bg-white rounded-[2rem] shadow-none overflow-hidden animate-fade-up">

            <div class="h-1.5 bg-deped-red w-full"></div>

            <div class="p-8 md:p-10 pb-6 w-full">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Reset Password</h2>
                        <p class="text-slate-500 text-sm mt-1">Please enter your new password below.</p>
                    </div>

                    <form action="{{ route('password.update') }}" method="POST" class="space-y-4 w-full"
                          x-data="{
                              password: '',
                              passwordConf: '',
                              showPw: false,
                              showConfPw: false,
                              get isPasswordValid() {
                                  return this.password.length >= 8 && 
                                         /(?=.*[a-z])/.test(this.password) && 
                                         /(?=.*[A-Z])/.test(this.password) && 
                                         /(?=.*\d)/.test(this.password) && 
                                         /^[a-zA-Z\d]+$/.test(this.password);
                              },
                              get passwordsMatch() {
                                  return this.password !== '' && this.password === this.passwordConf;
                              }
                          }">
                        @csrf

                        @if(session('success'))
                            <div class="bg-green-50 border border-green-100 text-green-600 p-3 rounded-2xl text-xs text-center font-semibold mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">New Password</label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="password" required
                                       x-model="password"
                                       class="w-full px-4 py-3 bg-slate-50 border rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm pr-10"
                                       :class="password.length > 0 ? (isPasswordValid ? 'border-green-300' : 'border-red-300') : 'border-slate-200'"
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

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Confirm Password</label>
                            <div class="relative">
                                <input :type="showConfPw ? 'text' : 'password'" name="password_confirmation" required
                                       x-model="passwordConf"
                                       class="w-full px-4 py-3 bg-slate-50 border rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm pr-10"
                                       :class="passwordConf.length > 0 ? (passwordsMatch ? 'border-green-300' : 'border-red-300') : 'border-slate-200'"
                                       placeholder="••••••••">
                                <button type="button" @click="showPwConfirm = !showPwConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg x-show="!showPwConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showPwConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p x-show="passwordConf.length > 0 && !passwordsMatch" class="text-[10px] text-red-500 font-bold ml-1 text-center sm:text-left">Passwords do not match</p>

                        {{-- Dynamic Helper Rules (Grid layout for compactness) --}}
                        <ul class="text-[9px] grid grid-cols-2 gap-x-2 gap-y-1 ml-1 font-semibold">
                            <li :class="password.length >= 8 ? 'text-green-500' : 'text-slate-400'">✓ Minimum 8 characters</li>
                            <li :class="/(?=.*[a-z])/.test(password) && /(?=.*[A-Z])/.test(password) ? 'text-green-500' : 'text-slate-400'">✓ Uppercase & Lowercase</li>
                            <li :class="/(?=.*\d)/.test(password) ? 'text-green-500' : 'text-slate-400'">✓ Contains a number</li>
                            <li :class="/^[a-zA-Z\d]+$/.test(password) && password.length > 0 ? 'text-green-500' : 'text-slate-400'">✓ No special characters</li>
                        </ul>

                        <button type="submit"
                                :disabled="!isPasswordValid || !passwordsMatch"
                                class="btn-hover-effect w-full py-3 rounded-2xl font-bold text-lg transition-all duration-200 mt-4"
                                :class="(isPasswordValid && passwordsMatch) ? 'bg-deped-red text-white active:scale-[0.98] shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                            Reset Password
                        </button>

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold mt-2">
                                {{ session('error') }}
                            </div>
                        @endif
                        @error('password')
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">
                        <a href="{{ route('password.request') }}" class="text-[#c00000] font-bold hover:underline transition-colors">Cancel</a>
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
