<x-layout>
    <header class="border-b border-border">
        <div class="mx-auto max-w-5xl px-6 py-4 flex items-center justify-between">
            <nav class="flex w-full justify-between gap-6">
                <a href="{{ url('/')  }}" class="flex items-center gap-2">
                    <x-lucide-link-2 class="h-5 w-5" />
                    <span class="font-medium">Shortly</span>
                </a>
            </nav>
        </div>
    </header>
    {{ $slot }}
</x-layout>
