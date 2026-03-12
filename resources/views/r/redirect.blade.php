<x-layout>
    <div class="min-h-screen bg-background flex items-center justify-center p-4">
        @if(!$destination)
            <div class="max-w-md w-full text-center">
                <div class="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl font-light text-destructive">!</span>
                </div>
                <h1 class="text-2xl font-medium mb-2">Link não encontrado</h1>
                <p class="text-muted-foreground mb-6">
                    O link que você está tentando acessar não existe ou foi removido.
                </p>
                <a href="{{ url('/') }}" class="text-sm text-foreground underline underline-offset-4 hover:text-foreground/80">
                    Voltar para a página inicial
                </a>
            </div>
        @else
            <div class="max-w-md w-full text-center"
                 x-data="{
                    countdown: 3,
                    total: 3,
                    progress: 1,
                    redirectUrl: '{{ $destination  }}',
                    init() {
                        let startTime = Date.now();
                        let duration = this.total * 1000;
                        let interval = setInterval(() => {
                            let elapsed = Date.now() - startTime;
                            this.progress = Math.max(0, 1 - (elapsed / duration));
                            this.countdown = Math.ceil((duration - elapsed) / 1000);
                            if (elapsed >= duration) {
                                clearInterval(interval);
                                this.countdown = 0;
                                this.progress = 0;
                                window.location.href = this.redirectUrl;
                            }
                        }, 5);
                    }
                 }">

                <a href="{{ url('/') }}" class="inline-block mb-8">
                    <span class="text-xl font-semibold tracking-tight">Shortly</span>
                </a>

                <div class="relative w-32 h-32 mx-auto mb-8">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle
                            cx="64" cy="64" r="58"
                            fill="none"
                            stroke="currentColor"
                            class="text-secondary"
                            stroke-width="4"
                        />
                        <circle
                            cx="64" cy="64" r="58"
                            fill="none"
                            stroke="currentColor"
                            class="text-foreground"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-dasharray="364.4"
                            :stroke-dashoffset="364.4 - (364.4 * progress)"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-4xl font-light" x-text="countdown"></span>
                    </div>
                </div>

                <h1 class="text-xl font-medium mb-2">Redirecionando...</h1>
                <p class="text-muted-foreground text-sm mb-6">
                    Você será redirecionado em <span x-text="countdown"></span> segundo<span x-show="countdown !== 1">s</span>
                </p>

                <div class="bg-secondary/50 rounded-lg p-4 mb-6">
                    <p class="text-xs text-muted-foreground mb-1">Destino</p>
                    <p class="text-sm truncate">{{ $destination }}</p>
                </div>

                <button
                    @click="window.location.href = redirectUrl"
                    class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
                >
                    <x-lucide-external-link class="h-4 w-4" />
                    Pular e ir direto
                </button>

                <div class="mt-12 pt-6 border-t border-border">
                    <p class="text-xs text-muted-foreground">
                        Este é um projeto de estudo.
                        <a href="{{ url('/') }}" class="underline underline-offset-2 hover:text-foreground">
                            Saiba mais
                        </a>
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
