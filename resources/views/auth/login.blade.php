<x-guest-layout>
    <!-- Background: biru/sky/cyan ala sistem bot sosial media -->
    <div class="min-h-screen w-full bg-gradient-to-br from-sky-600 via-blue-700 to-indigo-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <!-- pola grid halus -->
        <div class="absolute inset-0 [background-image:radial-gradient(white/10_1px,transparent_1px)] [background-size:18px_18px] opacity-20"></div>

        <div class="relative min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <!-- Card glass -->
                <div class="relative rounded-2xl border border-white/15 bg-white/60 backdrop-blur-xl shadow-2xl dark:bg-slate-900/60 dark:border-white/10">
                    <!-- Header (tanpa logo) -->
                    <div class="px-8 pt-8 pb-6 text-center">
                        <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Masuk ke Dashboard Bot</h1>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Kelola kanal & otomasi sosial media Anda</p>
                    </div>

                    <!-- Session Status -->
                    <div class="px-8">
                        <x-auth-session-status class="mb-4" :status="session('status')" />
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('login') }}" class="px-8 pb-8">
                        @csrf

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" class="text-slate-700 dark:text-slate-200" />
                            <x-text-input
                                id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                                class="mt-1 block w-full rounded-xl border-slate-300/60 bg-white/80 focus:border-sky-500 focus:ring-sky-500 dark:bg-slate-800/70 dark:border-slate-600/60 dark:text-slate-100"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between">
                                <x-input-label for="password" :value="__('Password')" class="text-slate-700 dark:text-slate-200" />
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                       class="text-sm text-sky-600 hover:text-sky-700 hover:underline dark:text-sky-400">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>

                            <div class="relative mt-1">
                                <x-text-input
                                    id="password" type="password" name="password" required autocomplete="current-password"
                                    class="block w-full rounded-xl border-slate-300/60 bg-white/80 pr-11 focus:border-sky-500 focus:ring-sky-500 dark:bg-slate-800/70 dark:border-slate-600/60 dark:text-slate-100"
                                />
                                <!-- Toggle password -->
                                <button type="button" aria-label="Show password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-700 dark:text-slate-400"
                                    onclick="const i=document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678.07.214.07.45 0 .644C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678z"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember -->
                        <div class="mt-4">
                            <label for="remember_me" class="inline-flex items-center gap-2 select-none">
                                <input id="remember_me" type="checkbox"
                                       class="rounded-md border-slate-300/60 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-600/60">
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <!-- Submit -->
                        <div class="mt-6">
                            <x-primary-button
                                class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-600 px-4 py-3 text-base font-semibold shadow-lg hover:from-sky-600 hover:via-cyan-600 hover:to-blue-700 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-50">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>

                        <!-- Catatan kecil -->
                        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
                            Dengan masuk, Anda menyetujui Ketentuan & Kebijakan Privasi.
                        </p>
                    </form>
                </div>

                <!-- Footer kecil -->
                <div class="mt-6 text-center">
                    <span class="text-xs tracking-wide text-white/80 drop-shadow">© {{ date('Y') }} — Sansein</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
