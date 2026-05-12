@php use Carbon\Carbon; @endphp
@php
    $baseUrl = config('app.url');
    $periodLabels = [
        'today' => 'Hoje',
        '7d' => 'Últimos 7 dias',
        '30d' => 'Últimos 30 dias',
        'total' => 'Todo o período',
    ];
    $selectedPeriod = $selectedPeriod ?? '7d';
    $selectedPeriodLabel = $periodLabels[$selectedPeriod] ?? $periodLabels['7d'];
    $includeBots = $includeBots ?? false;
    $botFilterQuery = ['include_bots' => $includeBots ? 1 : 0];
    $showGlobalEmptyState = $totalClicks === 0;
    $expiresAt = $link->expires_at;
    $hasExpiration = !is_null($expiresAt);
    $isExpired = $hasExpiration && $expiresAt->isPast();
    $expirationHumanized = $hasExpiration ? $expiresAt->locale('pt_BR')->diffForHumans() : null;
    $remainingExpirationLabel = !$hasExpiration
        ? 'Sem expiração'
        : ($isExpired ? 'Expirado ' . $expirationHumanized : $expirationHumanized);
    $expirationDateLabel = $hasExpiration ? $expiresAt->format('d/m/Y H:i') : 'Sem expiração';
    $expirationStatusLabel = !$hasExpiration ? 'Sem expiração' : ($isExpired ? 'Expirado' : 'Ativo');
    $expirationStatusClasses = !$hasExpiration
        ? 'bg-secondary text-muted-foreground border-border/60'
        : ($isExpired
            ? 'bg-destructive/10 text-destructive border-destructive/30'
            : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30');
@endphp

<x-layouts.app>
    <x-slot:title>Analytics - {{ $link->id }}</x-slot:title>

    <div class="flex flex-col max-w-7xl m-auto my-12">
        <div x-data="{
            copied: false,
            echoListenerAttached: false,
            userId: {{ \Illuminate\Support\Js::from(auth()->id()) }},
            totalClicks: {{ \Illuminate\Support\Js::from($totalClicks) }},
            chartInstances: { clicksChart: null, hoursChart: null },
            clicksOverTime: {{ \Illuminate\Support\Js::from($clicksOverTime->toArray()) }},
            clicksByHour: {{ \Illuminate\Support\Js::from($clicksByHour->toArray()) }},
            recentClicks: {{ \Illuminate\Support\Js::from($recentClicks) }},
            topCountries: {{ \Illuminate\Support\Js::from($topCountries) }},
            heatmap: {{ \Illuminate\Support\Js::from($heatmap) }},
            includeBots: {{ \Illuminate\Support\Js::from($includeBots) }},
            copy() {
                navigator.clipboard.writeText('{{ $baseUrl }}/r/{{ $link->id }}');
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            },
            getDateKey(date) {
                return new Date(date).toLocaleDateString('pt-BR');
            },
            getHourKey(date) {
                return String(new Date(date).getHours()).padStart(2, '0') + ':00';
            },
            updateChartsData() {
                if (this.chartInstances.clicksChart && this.clicksOverTime) {
                    const labels = Object.keys(this.clicksOverTime);
                    const data = Object.values(this.clicksOverTime).map(v => Number(v) || 0);
                    this.chartInstances.clicksChart.data.labels = labels.length ? labels : ['Sem dados'];
                    this.chartInstances.clicksChart.data.datasets[0].data = data.length ? data : [0];
                    this.chartInstances.clicksChart.update();
                }
                if (this.chartInstances.hoursChart && this.clicksByHour) {
                    const labels = Object.keys(this.clicksByHour);
                    const data = Object.values(this.clicksByHour).map(v => Number(v) || 0);
                    this.chartInstances.hoursChart.data.labels = labels;
                    this.chartInstances.hoursChart.data.datasets[0].data = data;
                    this.chartInstances.hoursChart.update();
                }
            },
            handleClickEvent(click) {
                this.totalClicks++;

                const dateKey = this.getDateKey(click.clicked_at);
                this.clicksOverTime[dateKey] = (this.clicksOverTime[dateKey] || 0) + 1;

                const hourKey = this.getHourKey(click.clicked_at);
                this.clicksByHour[hourKey] = (this.clicksByHour[hourKey] || 0) + 1;

                this.recentClicks.unshift({
                    country: click.country || 'Desconhecido',
                    clicked_at: click.clicked_at,
                    browser: click.browser || 'N/A',
                    from: click.from || 'Direct'
                });
                if (this.recentClicks.length > 10) {
                    this.recentClicks.pop();
                }

                if (click.country) {
                    const countryIndex = this.topCountries.findIndex(c => c.country === click.country);
                    if (countryIndex !== -1) {
                        this.topCountries[countryIndex].clicks++;
                    } else {
                        this.topCountries.push({
                            country: click.country,
                            clicks: 1,
                            percentage: 0
                        });
                    }

                    const totalCountryClicks = this.topCountries.reduce((sum, c) => sum + c.clicks, 0);
                    this.topCountries.forEach(c => {
                        c.percentage = Math.round((c.clicks / totalCountryClicks) * 100);
                    });

                    const countryCode = click.country;
                    if (countryCode) {
                        const maxClicks = Math.max(...this.topCountries.map(c => c.clicks));
                        this.heatmap[countryCode] = (this.topCountries.find(c => c.country === countryCode)?.clicks || 0) / maxClicks;
                        this.updateHeatmap();
                    }
                }

                this.$nextTick(() => this.updateChartsData());
            },
            updateHeatmap() {
                const mapContainer = this.$refs.mapContainer;
                if (!mapContainer) return;

                Object.keys(this.heatmap).forEach(countryCode => {
                    if (!countryCode || !/^[A-Za-z0-9\-_]+$/.test(countryCode)) return;
                    const countryPath = mapContainer.querySelector(`#${countryCode}`);
                    if (countryPath) {
                        const intensity = this.heatmap[countryCode];
                        countryPath.style.fill = `rgba(220, 38, 38, ${0.2 + (intensity * 0.8)})`;
                        countryPath.style.transition = 'fill 0.6s ease';
                    }
                });
            },
            listenForUrlClicks(retryCount = 0) {
                if (!this.userId) {
                    return;
                }

                if (!window.Echo) {
                    if (retryCount < 20) {
                        setTimeout(() => this.listenForUrlClicks(retryCount + 1), 250);
                    }

                    return;
                }

                if (this.echoListenerAttached) {
                    return;
                }

                this.echoListenerAttached = true;
                window.Echo.private(`App.Models.UrlClick.${ {{ $link->id }} }`)
                    .listen('.link-clicked', (event) => {
                        const click = event.linkClicked;
                        if (click && click.url_id === '{{ $link->id }}') {
                            if (!this.includeBots && click.is_bot) {
                                return;
                            }
                            this.handleClickEvent(click);
                        }
                    });
            },
            initCharts() {
                document.addEventListener('chartsReady', (event) => {
                    if (event.detail && event.detail.chartsInstance) {
                        this.chartInstances = event.detail.chartsInstance;
                    }
                });
            },
            initHeatmap() {
                this.$nextTick(() => {
                    if (this.$refs.mapContainer) {
                        this.updateHeatmap();
                    }
                });
            }

             }"
             x-init="listenForUrlClicks(); initCharts(); initHeatmap();">
            <a href="{{ route('dashboard.home') }}"
               class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
                <x-lucide-arrow-left class="h-4 w-4"/>
                Voltar para dashboard
            </a>

            <div class="flex gap-3 mb-6">
                <div class="inline-flex items-center gap-2 bg-secondary/60 border border-border rounded-lg p-1 w-fit">
                <a href="{{ route('dashboard.analytics', array_merge(['slug' => $link->id, 'period' => 'today'], $botFilterQuery)) }}"
                   class="px-3 py-1.5 text-sm rounded-md transition-colors {{ $selectedPeriod === 'today' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    Hoje
                </a>
                <a href="{{ route('dashboard.analytics', array_merge(['slug' => $link->id, 'period' => '7d'], $botFilterQuery)) }}"
                   class="px-3 py-1.5 text-sm rounded-md transition-colors {{ $selectedPeriod === '7d' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    7 dias
                </a>
                <a href="{{ route('dashboard.analytics', array_merge(['slug' => $link->id, 'period' => '30d'], $botFilterQuery)) }}"
                   class="px-3 py-1.5 text-sm rounded-md transition-colors {{ $selectedPeriod === '30d' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    30 dias
                </a>
                <a href="{{ route('dashboard.analytics', array_merge(['slug' => $link->id, 'period' => 'total'], $botFilterQuery)) }}"
                   class="px-3 py-1.5 text-sm rounded-md transition-colors {{ $selectedPeriod === 'total' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    Total
                </a>
                </div>

                <div class="inline-flex items-center gap-2 bg-secondary/60 border border-border rounded-lg p-1 w-fit">
                    <span class="px-2 text-xs text-muted-foreground">Bots</span>
                    <a href="{{ route('dashboard.analytics', ['slug' => $link->id, 'period' => $selectedPeriod, 'include_bots' => 0]) }}"
                       class="px-3 py-1.5 text-sm rounded-md transition-colors {{ !$includeBots ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                        Excluir
                    </a>
                    <a href="{{ route('dashboard.analytics', ['slug' => $link->id, 'period' => $selectedPeriod, 'include_bots' => 1]) }}"
                       class="px-3 py-1.5 text-sm rounded-md transition-colors {{ $includeBots ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                        Incluir
                    </a>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-medium">{{ $baseUrl }}/r/{{ $link->id }}</h1>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $expirationStatusClasses }}">
                            {{ $expirationStatusLabel }}
                        </span>
                        <button
                            @click="copy()"
                            class="text-muted-foreground hover:text-foreground transition-colors"
                        >
                            <template x-if="copied">
                                <x-lucide-check class="h-4 w-4 text-green-600"/>
                            </template>
                            <template x-if="!copied">
                                <x-lucide-copy class="h-4 w-4"/>
                            </template>
                        </button>
                    </div>
                    <p class="text-sm text-muted-foreground truncate max-w-md">{{ $link->original_url }}</p>
                    <p class="text-xs text-muted-foreground mt-1">
                        Tempo restante: {{ $remainingExpirationLabel }}
                    </p>
                </div>
                <x-button variant="outline" href="{{ $link->original_url }}" target="_blank">
                    <x-lucide-external-link class="h-4 w-4 mr-2"/>
                    Abrir link original
                </x-button>
            </div>

            <template x-if="totalClicks === 0">
                <div class="border border-border rounded-lg p-8 bg-card text-center">
                    <div class="max-w-xl mx-auto">
                        <h2 class="text-xl font-medium mb-2">Nenhum clique registrado em {{ strtolower($selectedPeriodLabel) }}</h2>
                        <p class="text-sm text-muted-foreground mb-6">
                            Compartilhe seu link encurtado para começar a coletar dados e visualizar gráficos de desempenho.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <button
                                @click="copy()"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm rounded-md bg-foreground text-background hover:opacity-90 transition-opacity"
                            >
                                <x-lucide-copy class="h-4 w-4"/>
                                Copiar link curto
                            </button>
                            <x-button variant="outline" href="{{ $link->original_url }}" target="_blank">
                                <x-lucide-external-link class="h-4 w-4 mr-2"/>
                                Abrir link original
                            </x-button>
                        </div>
                    </div>
                </div>
            </template>

            <div :style="totalClicks === 0 ? 'display: none' : ''">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                        <div class="flex items-center gap-2 mb-2">
                            <x-lucide-mouse-pointer class="h-4 w-4 text-muted-foreground"/>
                            <p class="text-sm text-muted-foreground">Total de cliques</p>
                        </div>
                        <p class="text-3xl font-medium" x-text="totalClicks"></p>
                    </div>
                    <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                        <div class="flex items-center gap-2 mb-2">
                            <x-lucide-clock class="h-4 w-4 text-muted-foreground"/>
                            <p class="text-sm text-muted-foreground">Criado em</p>
                        </div>
                        <p class="text-3xl font-medium">{{ Carbon::parse($link->created_at)->format('d/m/Y') }}</p>
                    </div>
                    <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                        <div class="flex items-center gap-2 mb-2">
                            <x-lucide-globe class="h-4 w-4 text-muted-foreground"/>
                            <p class="text-sm text-muted-foreground">Países</p>
                        </div>
                        <p class="text-3xl font-medium" x-text="topCountries.length"></p>
                    </div>
                    <div class="bg-secondary/50 rounded-lg p-4 border border-border/50">
                        <div class="flex items-center gap-2 mb-2">
                            <x-lucide-clock class="h-4 w-4 text-muted-foreground"/>
                            <p class="text-sm text-muted-foreground">Tempo restante</p>
                        </div>
                        <p class="text-2xl font-medium leading-tight">
                            {{ $remainingExpirationLabel }}
                        </p>
                        @if($hasExpiration)
                            <p class="text-xs text-muted-foreground mt-1">Data: {{ $expirationDateLabel }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="border border-border rounded-lg p-5 bg-card">
                        <h2 class="font-medium mb-1">Cliques por dia</h2>
                        <p class="text-sm text-muted-foreground mb-4">{{ $selectedPeriodLabel }}</p>
                        <div class="h-48">
                            <canvas id="clicksChart"></canvas>
                        </div>
                    </div>

                    <div class="border border-border rounded-lg p-5 bg-card">
                        <h2 class="font-medium mb-1">Cliques por horário</h2>
                        <p class="text-sm text-muted-foreground mb-4">Distribuição em {{ strtolower($selectedPeriodLabel) }}</p>
                        <div class="h-48">
                            <canvas id="hoursChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="border border-border rounded-lg p-5 bg-card">
                        <h2 class="font-medium mb-1">Países</h2>
                        <p class="text-sm text-muted-foreground mb-4">De onde vêm seus cliques</p>
                        <div class="space-y-4">
                            <template x-for="item in topCountries" :key="item.country">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm" x-text="item.country"></span>
                                        <span class="text-sm text-muted-foreground">
                                            <span x-text="item.clicks"></span>
                                            (<span x-text="item.percentage"></span>%)
                                        </span>
                                    </div>
                                    <div class="h-1.5 bg-secondary rounded-full overflow-hidden">
                                        <div class="h-full bg-foreground rounded-full"
                                             :style="`width: ${item.percentage}%`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border border-border rounded-lg p-5 bg-card">
                        <h2 class="font-medium mb-1">Cliques recentes</h2>
                        <p class="text-sm text-muted-foreground mb-4">Acessos em {{ strtolower($selectedPeriodLabel) }}</p>
                        <div class="divide-y divide-border">
                            <template x-for="click in recentClicks" :key="click.clicked_at">
                                <div class="py-2.5 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium" x-text="click.country"></p>
                                        <p class="text-xs text-muted-foreground">
                                            <span x-text="new Date(click.clicked_at).toLocaleDateString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' })"></span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground bg-secondary px-2 py-0.5 rounded"
                                            x-text="click.browser"
                                        ></span>
                                        <span
                                            class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground bg-secondary px-2 py-0.5 rounded"
                                            x-text="click.from"
                                        ></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="border border-border rounded-lg p-5 bg-card">
                    <h2 class="font-medium mb-4">Cliques por País</h2>

                    <div class="relative w-full rounded-md overflow-hidden">
                        <div x-ref="mapContainer" class="flex justify-center w-full h-full">
                            {!! file_get_contents(public_path('storage/world.svg')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;

                const clicksCanvas = document.getElementById('clicksChart');
                const hoursCanvas = document.getElementById('hoursChart');

                const fg = '#111827';
                const mutedFg = '#6b7280';
                const border = '#e5e7eb';
                const bg = '#ffffff';
                const secondary = '#f3f4f6';

                const labelsRaw = {{ \Illuminate\Support\Js::from($clicksOverTime->keys()) }};
                const clicksRaw = {{ \Illuminate\Support\Js::from($clicksOverTime->values()) }};

                const labels = labelsRaw.length ? labelsRaw : ['Sem dados'];
                const clicks = clicksRaw.length ? clicksRaw.map(v => Number(v) || 0) : [0];

                window.chartsInstance = window.chartsInstance || {};

                if (clicksCanvas) {
                    window.chartsInstance.clicksChart = new Chart(clicksCanvas, {
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
                                pointBackgroundColor: fg
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {display: false},
                                tooltip: {
                                    backgroundColor: bg,
                                    borderColor: border,
                                    borderWidth: 1,
                                    titleColor: fg,
                                    bodyColor: fg,
                                    displayColors: true
                                }
                            },
                            scales: {
                                x: {
                                    grid: {display: true},
                                    border: {display: true},
                                    ticks: {color: mutedFg, font: {size: 11}}
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {display: true},
                                    border: {display: true},
                                    ticks: {color: mutedFg, font: {size: 11}}
                                }
                            }
                        }
                    });
                }

                if (hoursCanvas) {
                    window.chartsInstance.hoursChart = new Chart(hoursCanvas, {
                        type: 'bar',
                        data: {
                            labels: {{ \Illuminate\Support\Js::from($clicksByHour->keys()) }},
                            datasets: [{
                                data: {{ \Illuminate\Support\Js::from($clicksByHour->values()) }},
                                backgroundColor: '#111827',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {legend: {display: false}},
                            scales: {
                                x: {
                                    grid: {display: false},
                                    border: {display: false},
                                    ticks: {color: mutedFg, font: {size: 11}}
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {display: false},
                                    border: {display: false},
                                    ticks: {color: mutedFg, font: {size: 11}}
                                }
                            }
                        }
                    });
                }

                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent('chartsReady', {
                        detail: { chartsInstance: window.chartsInstance }
                    }));
                }, 100);
            });
        </script>
    @endpush
</x-layouts.app>
