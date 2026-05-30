<x-layouts.guest title="Login SSO - Helpdesk UPA TIK">

    <div class="flex items-center justify-center min-h-[calc(100vh-150px)] px-4 py-12 sm:px-6 lg:px-8">

        <div
            class="w-full max-w-md p-8 space-y-8 bg-surface-light dark:bg-surface-dark rounded-xl shadow-lg border border-border-light dark:border-border-dark transition-colors duration-200">

            <div class="text-center">
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-text-light dark:text-text-dark">
                    Login Sistem
                </h2>
                <p class="mt-2 text-sm text-muted-light dark:text-muted-dark">
                    Gunakan akun SSO Universitas Lampung Anda
                </p>
            </div>

            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400"
                    role="alert">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="username"
                            class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">
                            Username
                        </label>
                        <input id="username" name="username" type="text" autocomplete="username" required
                            value="{{ old('username') }}"
                            class="block w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark 
                                   bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark 
                                   focus:ring-2 focus:ring-brand focus:border-brand focus:outline-none 
                                   transition-colors duration-200 sm:text-sm"
                            placeholder="Masukkan username SSO">
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password"
                            class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                class="block w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark 
                                       bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark 
                                       focus:ring-2 focus:ring-brand focus:border-brand focus:outline-none 
                                       transition-colors duration-200 sm:text-sm"
                                placeholder="••••••••">

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-light dark:text-muted-dark hover:text-text-light dark:hover:text-text-dark focus:outline-none">
                                <span class="material-icons-round icon-sm"
                                    x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white transition-colors duration-200 bg-brand border border-transparent rounded-lg hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand dark:focus:ring-offset-surface-dark shadow-sm">
                        Masuk
                    </button>
                </div>
            </form>

        </div>
    </div>

</x-layouts.guest>
