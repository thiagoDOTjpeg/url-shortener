<x-layout>
    <header class="border-b border-border">
        <div class="mx-auto max-w-5xl px-6 py-4 flex items-center justify-between">
            <nav class="flex w-full justify-between gap-6">
                <a href="{{ url('/')  }}" class="flex items-center gap-2">
                    <x-lucide-link-2 class="h-5 w-5" />
                    <span class="font-medium">Shortly</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login.form') }}" class="cursor-pointer text-sm text-muted-foreground hover:text-foreground transition-colors">
                        Entrar
                    </a>
                    <x-button href="{{ route('register.form') }}" size="sm">
                        Criar conta
                    </x-button>
                </div>
            </nav>
        </div>
    </header>

    {{ $slot }}

    <footer class="border-t border-border">
        <div class="mx-auto max-w-5xl px-6 py-6 flex items-center justify-between">
            <p class="text-sm text-muted-foreground">
                Shortly — Projeto de estudo
            </p>
            <p class="text-sm text-muted-foreground">
                PHP 8.4 + Laravel 12 + Redis + PostgreSQL
            </p>
        </div>
    </footer>
</x-layout>
