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
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4"
      x-data="{
          submitted: {{ session('success') ? 'true' : 'false' }},
          email: '{{ old('email', '') }}',
          otp: '',
          otpSent: false,
          otpVerified: false,
          otpLoading: false,
          verifyLoading: false,
          otpMessage: '',
          otpMessageType: '',

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

            <div class="p-8 md:p-10">

                {{-- Registration Form --}}
                <div x-show="!submitted">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Create Account</h2>
                        <p class="text-slate-500 text-sm mt-1">Register your email to request access.</p>
                    </div>

                    <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Email input --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                            {{-- Hidden input always submits the email value --}}
                            <input type="hidden" name="email" :value="email">
                            {{-- Visible input is display-only after verification --}}
                            <input type="email" required
                                   x-model="email"
                                   :readonly="otpVerified"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm"
                                   :class="otpVerified ? 'opacity-50 cursor-not-allowed' : ''"
                                   placeholder="username@deped.gov.ph">
                        </div>

                        {{-- Verify Email button --}}
                        <div x-show="!otpVerified">
                            <button type="button"
                                    @click="sendOtp()"
                                    :disabled="!email || otpLoading"
                                    class="w-full py-3 rounded-2xl font-bold text-sm uppercase tracking-widest transition-all duration-200 border-2"
                                    :class="otpLoading ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-wait' : 'bg-white text-[#c00000] border-[#c00000] hover:bg-red-50 active:scale-[0.98]'">
                                <span x-show="!otpLoading">
                                    <span x-show="!otpSent">✉ Verify Email</span>
                                    <span x-show="otpSent">↻ Resend Code</span>
                                </span>
                                <span x-show="otpLoading">Sending...</span>
                            </button>
                        </div>

                        {{-- Verified badge --}}
                        <div x-show="otpVerified" x-transition class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 p-3 rounded-2xl text-xs font-semibold justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Email Verified
                        </div>

                        {{-- OTP input --}}
                        <div x-show="otpSent && !otpVerified" x-transition class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Verification Code</label>
                            <div class="flex gap-2">
                                <input type="text"
                                       x-model="otp"
                                       maxlength="6"
                                       inputmode="numeric"
                                       pattern="[0-9]*"
                                       class="flex-1 px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus-ring-red transition-all duration-200 text-sm text-center tracking-[0.5em] font-bold text-lg"
                                       placeholder="000000">
                                <button type="button"
                                        @click="verifyOtp()"
                                        :disabled="otp.length !== 6 || verifyLoading"
                                        class="px-6 py-4 rounded-2xl font-bold text-sm transition-all duration-200"
                                        :class="otp.length === 6 && !verifyLoading ? 'bg-[#c00000] text-white hover:brightness-110 active:scale-[0.98]' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                                    <span x-show="!verifyLoading">✓</span>
                                    <span x-show="verifyLoading">...</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 ml-1">Enter the 6-digit code sent to your email. Expires in 10 minutes.</p>
                        </div>

                        {{-- Status messages --}}
                        <div x-show="otpMessage" x-transition>
                            <div x-show="otpMessageType === 'error'" class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-2xl text-xs text-center font-semibold" x-text="otpMessage"></div>
                            <div x-show="otpMessageType === 'success'" class="bg-green-50 border border-green-100 text-green-600 p-3 rounded-2xl text-xs text-center font-semibold" x-text="otpMessage"></div>
                        </div>

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

                        {{-- Register button: grey when not verified, red when verified --}}
                        <button type="submit"
                                :disabled="!otpVerified"
                                class="w-full py-4 rounded-2xl font-bold text-lg transition-all duration-300"
                                :class="otpVerified ? 'btn-hover-effect bg-deped-red text-white active:scale-[0.98]' : 'bg-slate-300 text-slate-500 cursor-not-allowed shadow-none'">
                            Register Now
                        </button>
                    </form>

                    <div class="mt-6 text-center">
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