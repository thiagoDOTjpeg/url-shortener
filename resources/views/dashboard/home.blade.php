@php
    $baseUrl = config('app.url');
    $totalClicks = $links->sum('click_count');
    $maxClicks = $links->max('click_count') ?? 0;
    $avgClicks = $links->count() > 0 ? round($totalClicks / $links->count()) : 0;
@endphp

<x-layouts.app>
    <x-slot:title>
        Dashboard - Shortly
    </x-slot:title>

    <div class="mx-auto max-w-5xl px-6 py-8">
        <div x-data="{
            isDialogOpen: false,
            qrCodeUrl: null,
            copiedId: null,
            clicks: {{ json_encode($links->pluck('click_count', 'id')) }},
            copy(text, id) {
                navigator.clipboard.writeText(text);
                this.copiedId = id;
                setTimeout(() => this.copiedId = null, 2000);
            },
            openLink(id, url) {
                this.clicks[id]++;
                window.open(url, '_blank');
            }
        }">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-medium mb-1">Seus links</h1>
                    <p class="text-sm text-muted-foreground">
                        Gerencie e acompanhe seus links encurtados.
                    </p>
                </div>
                <x-button @click="isDialogOpen = true">
                    <x-lucide-plus class="h-4 w-4 mr-2" />
                    Novo link
                </x-button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Total de links</p>
                    <p class="text-2xl font-medium">{{ $links->count() }}</p>
                </div>
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Total de cliques</p>
                    <p class="text-2xl font-medium" x-text="Object.values(clicks).reduce((a, b) => a + b, 0)"></p>
                </div>
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Média por link</p>
                    <p class="text-2xl font-medium" x-text="Object.keys(clicks).length > 0 ? Math.round(Object.values(clicks).reduce((a, b) => a + b, 0) / Object.keys(clicks).length) : 0"></p>
                </div>
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Mais popular</p>
                    <p class="text-2xl font-medium" x-text="Math.max(...Object.values(clicks), 0)"></p>
                </div>
            </div>

            <div class="border border-border rounded-lg overflow-hidden bg-card">
                @if($links->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-muted-foreground mb-4">Você ainda não tem links encurtados.</p>
                        <x-button @click="isDialogOpen = true">
                            <x-lucide-plus class="h-4 w-4 mr-2" />
                            Criar seu primeiro link
                        </x-button>
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach($links as $link)
                            <div class="p-4 flex items-center justify-between gap-4 hover:bg-secondary/30 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-sm">{{ $baseUrl }}/r/{{ $link->id }}</span>
                                        <button
                                            @click="copy('{{ $baseUrl }}/r/{{ $link->id }}', '{{ $link->id }}')"
                                            class="text-muted-foreground hover:text-foreground transition-all"
                                        >
                                            <template x-if="copiedId === '{{ $link->id }}'">
                                                <x-lucide-check class="h-3.5 w-3.5 text-green-600" />
                                            </template>
                                            <template x-if="copiedId !== '{{ $link->id }}'">
                                                <x-lucide-copy class="h-3.5 w-3.5" />
                                            </template>
                                        </button>
                                    </div>
                                    <p class="text-sm text-muted-foreground truncate">{{ $link->original_url }}</p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div class="text-right hidden md:block">
                                        <p class="text-sm font-medium">
                                            <span x-text="clicks['{{ $link->id }}']"></span> cliques
                                        </p>
                                        <p class="text-xs text-muted-foreground">Criado em {{ $link->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <x-button variant="ghost" size="icon" @click="qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $baseUrl }}/r/{{ $link->id }}?from=qrcode'">
                                            <x-lucide-qr-code class="h-4 w-4" />
                                        </x-button>

                                        <x-button variant="ghost" size="icon" @click="openLink('{{ $link->id }}', '{{ $baseUrl }}/r/{{ $link->id }}')">
                                            <x-lucide-external-link class="h-4 w-4" />
                                        </x-button>

                                        <form action="#" method="POST" onsubmit="return confirm('Excluir este link?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-muted-foreground hover:text-destructive transition-colors">
                                                <x-lucide-trash-2 class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                    </div>
                @endif
            </div>

            <div x-show="isDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
                <div class="bg-background border border-border rounded-lg p-6 max-w-md w-full shadow-lg" @click.outside="isDialogOpen = false">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium">Criar novo link</h3>
                        <p class="text-sm text-muted-foreground">Cole a URL que deseja encurtar.</p>
                    </div>
                    <form @submit.prevent="
                            fetch('/shorten', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ original_url: document.getElementById('url').value })
                            })
                            .then(r => {
                                if (!r.ok) return r.json().then(e => { console.error(e); throw e; });
                                return r.json();
                            })
                            .then(data => {
                                console.log('Criado:', data);
                                isDialogOpen = false;
                                window.location.reload();
                            })
                            .catch(e => console.error('Erro:', e))
                        " class="space-y-4">
                        <div class="space-y-2">
                            <x-label for="url">URL original</x-label>
                            <x-input id="url" name="original_url" type="url" placeholder="https://exemplo.com/pagina-longa" required />
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <x-button variant="outline" type="button" @click="isDialogOpen = false">Cancelar</x-button>
                            <x-button type="submit">Criar link</x-button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="qrCodeUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
                <div class="bg-background border border-border rounded-lg p-6 max-w-sm w-full relative" @click.outside="qrCodeUrl = null">
                    <button @click="qrCodeUrl = null" class="absolute top-4 right-4 text-muted-foreground hover:text-foreground">
                        <x-lucide-x class="h-5 w-5" />
                    </button>
                    <h3 class="text-lg font-medium mb-2 text-center">QR Code</h3>
                    <div class="bg-white p-4 rounded-lg flex items-center justify-center border border-border mt-4">
                        <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
