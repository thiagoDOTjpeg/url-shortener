<x-layouts.auth>
    <x-slot:title>
        Login - Shortly
    </x-slot:title>

    <div class="min-h-screen bg-background flex flex-col">


        <main class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-2xl font-medium mb-2">Entrar</h1>
                    <p class="text-sm text-muted-foreground">
                        Digite suas credenciais para acessar sua conta.
                    </p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-2">
                        <x-label for="email">Email</x-label>
                        <x-input
                            id="email"
                            name="email"
                            type="email"
                            placeholder="seu@email.com"
                            :value="old('email')"
                            required
                            autofocus
                        />
                        @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <x-label for="password">Senha</x-label>
                        </div>
                        <x-input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                            required
                        />
                        @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center justify-end">
                            <a class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                                Esqueceu a senha?
                            </a>
                        </div>
                    </div>

                    <x-button type="submit" class="w-full">
                        Entrar
                    </x-button>
                </form>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    Não tem uma conta?
                    <a href="{{ route('register.form')  }}" class="text-foreground hover:underline font-medium">
                        Criar conta
                    </a>
                </p>
            </div>
        </main>
    </div>
</x-layouts.auth>
