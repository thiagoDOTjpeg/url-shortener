<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot:title>
        {{ $title ?? 'Shortly'  }}
    </x-slot:title>
    <div class="min-h-screen bg-background">
        <header class="border-b border-border sticky top-0 bg-background z-10">
            <div class="mx-auto max-w-5xl px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard.home') }}" class="flex items-center gap-2">
                        <x-lucide-link-2 class="h-5 w-5" />
                        <span class="font-medium">Shortly</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-1">
                        <a
                            href="{{ route('dashboard.home') }}"
                            @class([
                                'text-sm px-3 py-1.5 rounded-md transition-colors',
                                'bg-secondary text-foreground' => request()->routeIs('dashboard.home'),
                                'text-muted-foreground hover:text-foreground' => !request()->routeIs('dashboard.home')
                            ])
                        >
                            <span class="flex items-center gap-2">
                                <x-lucide-layout-dashboard class="h-4 w-4" />
                                Dashboard
                            </span>
                        </a>
                    </nav>
                </div>

                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <x-button
                        variant="ghost"
                        size="sm"
                        class="gap-2"
                        @click="open = !open"
                    >
                        <div class="h-6 w-6 rounded-full bg-secondary flex items-center justify-center">
                            <x-lucide-user class="h-3.5 w-3.5" />
                        </div>
                        <span class="hidden md:inline text-sm">{{ auth()->user()->name }}</span>
                    </x-button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        class="absolute right-0 mt-2 w-48 rounded-md border border-border bg-popover p-1 shadow-md z-50"
                        style="display: none;"
                    >
                        <a class="flex items-center px-2 py-1.5 text-sm rounded-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            <x-lucide-user class="h-4 w-4 mr-2" />
                            Meu perfil
                        </a>

                        <div class="my-1 h-px bg-border"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-2 py-1.5 text-sm rounded-sm text-destructive hover:bg-destructive/10 transition-colors">
                                <x-lucide-log-out class="h-4 w-4 mr-2" />
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{ $slot }}
    </div>
</x-layout>
