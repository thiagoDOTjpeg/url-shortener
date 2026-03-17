<x-layouts.auth>
    <x-slot:title>
        Redefinir senha - Shortly
    </x-slot:title>

    <div class="min-h-screen bg-background flex flex-col">
        <main class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-2xl font-medium mb-2">Redefinir senha</h1>
                    <p class="text-sm text-muted-foreground">
                        Digite sua nova senha abaixo para redefinir o acesso à sua conta.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

                    <div class="space-y-2">
                        <x-label for="email">Email</x-label>
                        <x-input
                            id="email"
                            name="email"
                            type="email"
                            placeholder="seu@email.com"
                            :value="old('email', $email ?? request('email'))"
                            required
                            autofocus
                        />
                        @error('email')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-label for="password">Nova senha</x-label>
                        <x-input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                            required
                        />
                        <p class="text-xs text-muted-foreground">Mínimo de 8 caracteres</p>
                        @error('password')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-label for="password_confirmation">Confirmar nova senha</x-label>
                        <x-input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            required
                        />
                        @error('password_confirmation')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit" class="w-full">
                        Redefinir senha
                    </x-button>
                </form>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    Lembrou da senha?
                    <a href="{{ route('login.form') }}" class="text-foreground hover:underline font-medium">
                        Entrar
                    </a>
                </p>
            </div>
        </main>
    </div>
</x-layouts.auth>

