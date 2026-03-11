<x-layout>
    <x-slot:title>Criar conta - Shortly</x-slot:title>

    <div class="min-h-screen bg-background flex flex-col">
        <header class="border-b border-border">
            <div class="mx-auto max-w-5xl px-6 py-4">
                <a href="{{ url('/') }}" class="flex items-center gap-2 w-fit">
                    <x-lucide-link-2 class="h-5 w-5" />
                    <span class="font-medium">Shortify</span>
                </a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-2xl font-medium mb-2">Criar conta</h1>
                    <p class="text-sm text-muted-foreground">
                        Preencha os dados abaixo para criar sua conta.
                    </p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-2">
                        <x-label for="name">Nome</x-label>
                        <x-input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Seu nome"
                            :value="old('name')"
                            required
                            autofocus
                        />
                        @error('name')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-label for="email">Email</x-label>
                        <x-input
                            id="email"
                            name="email"
                            type="email"
                            placeholder="seu@email.com"
                            :value="old('email')"
                            required
                        />
                        @error('email')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-label for="password">Senha</x-label>
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
                        <x-label for="password_confirmation">Confirmar senha</x-label>
                        <x-input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            required
                            @class(['border-destructive' => $errors->has('password_confirmation')])
                        />
                        @error('password_confirmation')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit" class="w-full">
                        Criar conta
                    </x-button>
                </form>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    Já tem uma conta?
                    <a href="{{ route('login') }}" class="text-foreground hover:underline font-medium">
                        Entrar
                    </a>
                </p>
            </div>
        </main>
    </div>
</x-layout>
