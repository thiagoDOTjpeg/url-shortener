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
            isDeleteDialogOpen: false,
            isQrCodeDialogOpen: false,
            deleteId: null,
            qrCodeSvg: null,
            activeQrCodeId: null,
            qrCodeStatus: null,
            copiedId: null,
            echoListenerAttached: false,
            userId: {{ \Illuminate\Support\Js::from(auth()->id()) }},
            qrCodes: {{ \Illuminate\Support\Js::from($links->mapWithKeys(fn ($link) => [$link->id => $link->qr_code])) }},
            clicks: {{ \Illuminate\Support\Js::from($links->pluck('click_count', 'id')) }},
            copy(text, id) {
                navigator.clipboard.writeText(text);
                this.copiedId = id;
                setTimeout(() => this.copiedId = null, 2000);
            },
            openLink(id, url) {
                this.clicks[id] = Number(this.clicks[id] || 0) + 1;
                window.open(url, '_blank');
            },
            openQrCode(id) {
                this.activeQrCodeId = id;
                this.qrCodeSvg = this.qrCodes[id] || null;
                this.qrCodeStatus = this.qrCodeSvg ? 'QR Code pronto' : 'Gerando QR Code...';
                this.isQrCodeDialogOpen = true;
            },
            closeQrCode() {
                this.isQrCodeDialogOpen = false;
                this.activeQrCodeId = null;
                this.qrCodeSvg = null;
                this.qrCodeStatus = null;
            },
            listenForQrCodeUpdates(retryCount = 0) {
                if (!this.userId) {
                    return;
                }

                if (!window.Echo) {
                    if (retryCount < 20) {
                        setTimeout(() => this.listenForQrCodeUpdates(retryCount + 1), 250);
                    }

                    return;
                }

                if (this.echoListenerAttached) {
                    return;
                }

                this.echoListenerAttached = true;
                window.Echo.private(`App.Models.User.${this.userId}`)
                    .listen('.qr-code-event', (event) => {
                        const link = event?.url;

                        if (!link?.id || !link?.qr_code) {
                            return;
                        }

                        this.qrCodes[link.id] = link.qr_code;

                        if (this.activeQrCodeId === link.id) {
                            this.qrCodeSvg = link.qr_code;
                            this.qrCodeStatus = 'QR Code pronto';
                        }
                    });
            }
        }" x-init="listenForQrCodeUpdates()">
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
                    <p class="text-2xl font-medium" x-text="Object.values(clicks).reduce((a, b) => Number(a) + Number(b || 0), 0)"></p>
                </div>
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Média por link</p>
                    <p class="text-2xl font-medium" x-text="Object.keys(clicks).length > 0 ? Math.round(Object.values(clicks).reduce((a, b) => Number(a) + Number(b || 0), 0) / Object.keys(clicks).length) : 0"></p>
                </div>
                <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                    <p class="text-sm text-muted-foreground mb-1">Mais popular</p>
                    <p class="text-2xl font-medium" x-text="Math.max(...Object.values(clicks).map(value => Number(value || 0)), 0)"></p>
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
                                        <span class="hidden lg:inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-muted-foreground bg-secondary">
                                            <span x-text="qrCodes['{{ $link->id }}'] ? 'QR pronto' : 'Gerando QR' "></span>
                                        </span>

                                        <x-button variant="ghost"
                                                  size="icon"
                                                  x-on:click="openQrCode('{{ $link->id }}')"
                                        >
                                            <x-lucide-qr-code class="h-4 w-4" />
                                        </x-button>

                                        <x-button variant="ghost" size="icon" @click="window.location.href = '{{ route('dashboard.analytics', $link->id) }}'">
                                            <x-lucide-bar-chart-3 class="h-4 w-4" />
                                        </x-button>

                                        <x-button variant="ghost" size="icon" @click="openLink('{{ $link->id }}', '{{ $baseUrl }}/r/{{ $link->id }}')">
                                            <x-lucide-external-link class="h-4 w-4" />
                                        </x-button>

                                        <button @click="isDeleteDialogOpen = true; deleteId = '{{ $link->id }}'" class="p-2 text-muted-foreground hover:text-destructive transition-colors">
                                            <x-lucide-trash-2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                    </div>
                @endif
            </div>

            <div x-data="{
                idempotencyKey: crypto.randomUUID(),
                isProcessing: false
            }
            " x-show="isDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
                <div class="bg-background border border-border rounded-lg p-6 max-w-md w-full shadow-lg" @click.outside="isDialogOpen = false">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium">Criar novo link</h3>
                        <p class="text-sm text-muted-foreground">Cole a URL que deseja encurtar.</p>
                    </div>
                    <form @submit.prevent="
                            isProcessing = true;
                            fetch('/urls/shorten', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json',
                                    'Idempotency-Key': idempotencyKey,
                                },
                                body: JSON.stringify({ original_url: document.getElementById('url').value })
                            })
                            .then(r => {
                                if (!r.ok) return r.json().then(e => { console.error(e); isProcessing= false; throw e;  });
                                isProcessing = false
                                window.location.reload();
                            })
                            .catch(e => console.error('Erro:', e))
                            .finally(() => { isProcessing = false; document.getElementById('url').value = '' });
                        " class="space-y-4">
                        <div class="space-y-2">
                            <x-label for="url">URL original</x-label>
                            <x-input id="url" name="original_url" type="url" placeholder="https://exemplo.com/pagina-longa" required />
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <x-button variant="outline" type="button" @click="isDialogOpen = false">Cancelar</x-button>
                            <x-button x-bind="CreateLinkButton">Criar link</x-button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="isQrCodeDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
                <div class="bg-background border border-border rounded-lg p-6 max-w-sm w-full relative" @click.outside="closeQrCode()">
                    <button @click="closeQrCode()" class="absolute top-4 right-4 text-muted-foreground hover:text-foreground">
                        <x-lucide-x class="h-5 w-5" />
                    </button>
                    <h3 class="text-lg font-medium mb-2 text-center">QR Code</h3>
                    <p class="text-xs text-muted-foreground text-center" x-text="qrCodeStatus"></p>

                    <div class="bg-white p-4 rounded-lg border border-border mt-4 flex items-center justify-center min-h-44">
                        <template x-if="qrCodeSvg">
                            <div x-html="qrCodeSvg"></div>
                        </template>

                        <template x-if="!qrCodeSvg">
                            <div class="text-center">
                                <p class="text-sm font-medium mb-1">Gerando QR Code...</p>
                                <p class="text-xs text-muted-foreground">O código aparece automaticamente assim que o processamento terminar.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="isDeleteDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
                <div class="flex flex-col bg-background border border-border rounded-lg p-6 max-w-lg w-full" @click.outside="isDeleteDialogOpen = false">
                    <div class="flex justify-end pb-2">
                        <button @click="isDeleteDialogOpen = false"  class="text-muted-foreground hover:text-foreground">
                            <x-lucide-x class="h-5 w-5" />
                        </button>
                    </div>
                    <div class="flex flex-col">
                        <p class="font-semibold">Deseja realmente excluir este link? Está ação é irreversível</p>
                        <form @submit.prevent="
                         fetch(`/urls/${deleteId}`, {
                         method: 'DELETE',
                         headers: {
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         }
                         })
                         .then(r => {
                             if (!r.ok) throw new Error('Erro ao excluir');
                             isDeleteDialogOpen = false;
                             deleteId = null;
                             window.location.reload();
                         })
                         .catch(() => {})
                        " class="flex justify-between gap-3 mt-6">
                            <x-button variant="outline" @click="isDeleteDialogOpen = false" class=" text-muted-foreground hover:text-foreground">
                                <p>Cancelar</p>
                            </x-button>
                            <x-button variant="destructive" type="submit"  class="text-muted-foreground hover:text-foreground">
                            <p>Excluir</p>
                        </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.bind('CreateLinkButton', () => ({
                type: 'submit',
                ':disabled'() {
                    return this.isProcessing
                }
            }))
        })
    </script>
</x-layouts.app>
