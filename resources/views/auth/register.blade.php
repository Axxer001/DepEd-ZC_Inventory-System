<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4"
      x-data="{
          submitted: {{ session('success') ? 'true' : 'false' }},
          step: 1,
          email: '{{ old('email', '') }}',
          otp: '',
          otpSent: false,
          otpVerified: false,
          otpLoading: false,
          verifyLoading: false,
          otpMessage: '',
          otpMessageType: '',
          
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
          },

          async sendOtp() {
              if (!this.email) return;
              this.otpLoading = true;
              this.otpMessage = '';
              try {
                  const res = await fetch('{{ route('otp.send') }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ email: this.email })
                  });
                  const data = await res.json();
                  this.otpMessage = data.message;
                  if (data.success) {
                      this.otpSent = true;
                      this.otpMessageType = 'success';
                  } else {
                      this.otpMessageType = 'error';
                  }
              } catch (e) {
                  this.otpMessage = 'Network error. Please try again.';
                  this.otpMessageType = 'error';
              }
              this.otpLoading = false;
          },

          async verifyOtp() {
              if (!this.otp || this.otp.length !== 6) return;
              this.verifyLoading = true;
              this.otpMessage = '';
              try {
                  const res = await fetch('{{ route('otp.verify') }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ email: this.email, otp: this.otp })
                  });
                  const data = await res.json();
                  this.otpMessage = data.message;
                  if (data.success) {
                      this.otpVerified = true;
                      this.otpMessageType = 'success';
                      setTimeout(() => { this.step = 2; }, 800);
                  } else {
                      this.otpMessageType = 'error';
                  }
              } catch (e) {
                  this.otpMessage = 'Network error. Please try again.';
                  this.otpMessageType = 'error';
              }
              this.verifyLoading = false;
          }
      }">

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

                {{-- Registration Form --}}
                <div x-show="!submitted">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight" x-text="step === 2 ? 'Create Password' : (otpSent && !otpVerified ? 'Verify PIN' : 'Create Account')">Create Account</h2>
                        <p class="text-slate-500 text-sm mt-1" x-text="step === 2 ? 'Please enter your new password below.' : (otpSent && !otpVerified ? 'Please enter the 6-digit PIN sent to your email.' : 'Register your email to request access.')">Register your email to request access.</p>
                    </div>

                    <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="email" :value="email">

                        <!-- ================= STATUS MESSAGES ================= -->
                        <div x-show="otpMessage" x-transition class="mb-4">
                            <div :class="otpMessageType === 'success' ? 'bg-[#ecfdf5] text-[#059669]' : 'bg-red-50 text-red-600'"
                                 class="p-3 rounded-[1.5rem] text-xs text-center font-semibold">
                                <span x-text="otpMessage"></span>
                            </div>
                        </div>

                        <!-- ================= STEP 1: EMAIL & OTP ================= -->
                        <div x-show="step === 1" x-transition.opacity.duration.300ms class="w-full space-y-4">
                            
                            {{-- Email section --}}
                            <div x-show="!otpSent" class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                                    <input type="email" required
                                           x-model="email"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm"
                                           placeholder="username@deped.gov.ph">
                                </div>

                                <button type="button"
                                        @click="sendOtp()"
                                        :disabled="!email || otpLoading"
                                        class="w-full py-3 rounded-2xl font-bold text-sm uppercase tracking-widest transition-all duration-200 border-2"
                                        :class="otpLoading ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-wait' : 'bg-white text-[#c00000] border-[#c00000] hover:bg-red-50 active:scale-[0.98]'">
                                    <span x-show="!otpLoading">
                                        ✉ Verify Email
                                    </span>
                                    <span x-show="otpLoading">Sending...</span>
                                </button>
                            </div>

                            {{-- OTP input --}}
                            <div x-show="otpSent && !otpVerified" x-transition class="space-y-4 mt-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1 text-center">6-Digit PIN</label>
                                    <input type="text"
                                           x-model="otp"
                                           maxlength="6"
                                           inputmode="numeric"
                                           pattern="[0-9]*"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-2xl tracking-widest text-center font-bold text-slate-800"
                                           placeholder="------">
                                </div>
                                <button type="button"
                                        @click="verifyOtp()"
                                        :disabled="otp.length !== 6 || verifyLoading"
                                        class="btn-hover-effect w-full py-3 rounded-2xl font-bold text-lg transition-all duration-200 mt-2"
                                        :class="otp.length === 6 && !verifyLoading ? 'bg-deped-red text-white active:scale-[0.98] shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                                    <span x-show="!verifyLoading">Verify PIN</span>
                                    <span x-show="verifyLoading">Verifying...</span>
                                </button>
                                
                                <div class="text-center mt-4">
                                    <button type="button" @click="otpSent = false; otpMessage = ''" class="font-bold text-[#c00000] hover:underline transition-colors text-sm">Resend PIN / Back</button>
                                </div>
                            </div>

                            {{-- Verified badge --}}
                            <div x-show="otpVerified" x-transition class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 p-3 rounded-2xl text-xs font-semibold justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Email Verified
                            </div>
                            
                            {{-- Step transition button --}}
                            <div x-show="otpVerified" x-transition class="mt-4">
                                <button type="button" @click="step = 2" class="w-full py-3 rounded-2xl font-bold text-sm bg-slate-800 text-white hover:bg-slate-900 transition-colors">
                                    Continue to Password &#8594;
                                </button>
                            </div>
                        </div>
                        <!-- ================= END STEP 1 ================= -->


                        <!-- ================= STEP 2: PASSWORD ================= -->
                        <div x-show="step === 2" x-transition.opacity.duration.300ms x-cloak class="w-full space-y-4">

                            <div class="space-y-4">
                                {{-- Password Field --}}
                                <div class="space-y-2 relative w-full">
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

                                {{-- Confirm Password Field --}}
                                <div class="space-y-2 relative w-full">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Confirm Password</label>
                                    <div class="relative">
                                        <input :type="showConfPw ? 'text' : 'password'" name="password_confirmation" required
                                               x-model="passwordConf"
                                               class="w-full px-4 py-3 bg-slate-50 border rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm pr-10"
                                               :class="passwordConf.length > 0 ? (passwordsMatch ? 'border-green-300' : 'border-red-300') : 'border-slate-200'"
                                               placeholder="••••••••">
                                        <button type="button" @click="showConfPw = !showConfPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                            <svg x-show="!showConfPw" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <svg x-show="showConfPw" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                            </svg>
                                        </button>
                                    </div>
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

                            {{-- Submit Button --}}
                            <button type="submit"
                                    :disabled="!isPasswordValid || !passwordsMatch"
                                    class="btn-hover-effect w-full py-3 rounded-2xl font-bold text-lg transition-all duration-200 mt-4"
                                    :class="(isPasswordValid && passwordsMatch) ? 'bg-deped-red text-white active:scale-[0.98] shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                                Submit Registration Request
                            </button>
                            
                            <div class="text-center mt-4">
                                <button type="button" @click="step = 1; otpSent = false; otpMessage = ''; otpVerified = false;" class="font-bold text-[#c00000] hover:underline transition-colors text-sm">Cancel</button>
                            </div>
                        </div>
                        <!-- ================= END STEP 2 ================= -->

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="bg-blue-50 border border-blue-100 text-blue-600 p-3 rounded-2xl text-xs text-center font-semibold">
                                {{ session('info') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </form>

                    <div class="mt-6 text-center" x-show="!otpSent">
                        <p class="text-sm text-slate-500">
                            Already have an account?
                            <a href="{{ route('login.form') }}" class="text-[#c00000] font-bold hover:underline transition-colors">Sign in here</a>
                        </p>
                    </div>
                </div>

                {{-- Success state --}}
                <div x-show="submitted" x-transition.duration.500ms class="text-center py-4" x-cloak>
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Request Submitted!</h2>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Your registration request has been sent to the <span class="font-bold text-black">Administrator</span> for review. You will receive an email once a decision has been made.
                    </p>
                    <a href="{{ route('login.form') }}" class="inline-block text-sm font-bold text-deped-red hover:underline uppercase tracking-widest">
                        Back to Login
                    </a>
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