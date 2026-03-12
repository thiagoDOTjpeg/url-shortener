@php
    $baseUrl = config('app.url')
@endphp

<x-layouts.app>
    <x-slot:title>Analytics - {{ $link->id }}</x-slot:title>

    <div x-data="{
            copied: false,
            copy() {
                navigator.clipboard.writeText('{{ $baseUrl }}/r/{{ $link->id }}');
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
         }"
         class="flex flex-col max-w-7xl m-auto my-12">
        <a href="{{ route('dashboard.home') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
            <x-lucide-arrow-left class="h-4 w-4" />
            Voltar para dashboard
        </a>

        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-medium">{{ $baseUrl }}/r/{{ $link->id }}</h1>
                    <button
                        @click="copy()"
                        class="text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <template x-if="copied">
                            <x-lucide-check class="h-4 w-4 text-green-600" />
                        </template>
                        <template x-if="!copied">
                            <x-lucide-copy class="h-4 w-4" />
                        </template>
                    </button>
                </div>
                <p class="text-sm text-muted-foreground truncate max-w-md">{{ $link->original_url }}</p>
            </div>
            <x-button variant="outline" href="{{ $link->original_url }}" target="_blank">
                <x-lucide-external-link class="h-4 w-4 mr-2" />
                Abrir link original
            </x-button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                <div class="flex items-center gap-2 mb-2">
                    <x-lucide-mouse-pointer class="h-4 w-4 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">Total de cliques</p>
                </div>
                <p class="text-3xl font-medium">{{ $link->clicks->count() }}</p>
            </div>
            <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                <div class="flex items-center gap-2 mb-2">
                    <x-lucide-clock class="h-4 w-4 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">Criado em</p>
                </div>
                <p class="text-3xl font-medium">{{ \Carbon\Carbon::parse($link->created_at)->format('d/m/Y') }}</p>
            </div>
            <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                <div class="flex items-center gap-2 mb-2">
                    <x-lucide-globe class="h-4 w-4 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">Países</p>
                </div>
                <p class="text-3xl font-medium">{{ $topCountries->count() }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="border border-border rounded-lg p-5 bg-card">
                <h2 class="font-medium mb-1">Cliques por dia</h2>
                <p class="text-sm text-muted-foreground mb-4">Últimos 7 dias</p>
                <div class="h-48">
                    <canvas id="clicksChart"></canvas>
                </div>
            </div>

            <div class="border border-border rounded-lg p-5 bg-card">
                <h2 class="font-medium mb-1">Cliques por horário</h2>
                <p class="text-sm text-muted-foreground mb-4">Distribuição ao longo dos últimos 7 dias</p>
                <div class="h-48">
                    <canvas id="hoursChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="border border-border rounded-lg p-5 bg-card">
                <h2 class="font-medium mb-1">Países</h2>
                <p class="text-sm text-muted-foreground mb-4">De onde vêm seus cliques</p>
                <div class="space-y-4">
                    @foreach($topCountries as $item)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm">{{ $item['country'] }}</span>
                                <span class="text-sm text-muted-foreground">{{ $item['clicks'] }} ({{ $item['percentage'] }}%)</span>
                            </div>
                            <div class="h-1.5 bg-secondary rounded-full overflow-hidden">
                                <div class="h-full bg-foreground rounded-full" style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border border-border rounded-lg p-5 bg-card">
                <h2 class="font-medium mb-1">Cliques recentes</h2>
                <p class="text-sm text-muted-foreground mb-4">Últimos acessos ao link</p>
                <div class="divide-y divide-border">
                    @foreach($recentClicks as $click)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">{{ $click['country'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ \Carbon\Carbon::parse($click['clicked_at'])->format('d/m/Y H:m:s') }}</p>
                            </div>
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground bg-secondary px-2 py-0.5 rounded">
                                    {{ $click['user_agent'] }}
                                </span>
                                <span class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground bg-secondary px-2 py-0.5 rounded">
                                    {{ $click['from'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;

                const clicksCanvas = document.getElementById('clicksChart');
                const hoursCanvas = document.getElementById('hoursChart');
                if (!clicksCanvas || !hoursCanvas) return;

                const fg = '#111827';
                const mutedFg ='#6b7280';
                const border = '#e5e7eb';
                const bg = '#ffffff';
                const secondary = '#f3f4f6';

                const labelsRaw = {{ \Illuminate\Support\Js::from($clicksOverTime->pluck('date_label')->values()) }};
                const clicksRaw = {{ \Illuminate\Support\Js::from($clicksOverTime->pluck('clicks')->values()) }};

                const labels = labelsRaw.length ? labelsRaw : ['Sem dados'];
                const clicks = clicksRaw.length ? clicksRaw.map(v => Number(v) ||0) : [0];

                new Chart(clicksCanvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Cliques',
                            data: clicks,
                            borderColor: fg,
                            backgroundColor: secondary,
                            fill: true,
                            borderWidth: 2,
                            pointBackgroundColor: fg }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: bg,
                                borderColor: border,
                                borderWidth:1,
                                titleColor: fg,
                                bodyColor: fg,
                                displayColors: true }
                        },
                        scales: {
                            x: {
                                grid: { display: true },
                                border: { display: true },
                                ticks: { color: mutedFg, font: { size:11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { display: true },
                                border: { display: true },
                                ticks: { color: mutedFg, font: { size:11 } }
                            }
                        }
                    }
                });

                new Chart(hoursCanvas, {
                    type: 'bar',
                    data: {
                        labels: {{ \Illuminate\Support\Js::from($clicksByHour->pluck('hour')->values()) }},
                        datasets: [{
                            data: {{ \Illuminate\Support\Js::from($clicksByHour->pluck('count')->values()) }},
                            backgroundColor: '#111827',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: mutedFg, font: { size:11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: mutedFg, font: { size:11 } }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-layouts.app>
