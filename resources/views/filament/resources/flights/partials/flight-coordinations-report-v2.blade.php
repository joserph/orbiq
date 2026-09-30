@php
    $boxTypes = collect([
        [
            'field' => 'fb',
            'received' => 'fb_r',
            'status' => 'fb_status',
            'label' => 'FB',
        ],
        [
            'field' => 'hb',
            'received' => 'hb_r',
            'status' => 'hb_status',
            'label' => 'HB',
        ],
        [
            'field' => 'qb',
            'received' => 'qb_r',
            'status' => 'qb_status',
            'label' => 'QB',
        ],
        [
            'field' => 'eb',
            'received' => 'eb_r',
            'status' => 'eb_status',
            'label' => 'EB',
        ],
        [
            'field' => 'db',
            'received' => 'db_r',
            'status' => 'db_status',
            'label' => 'DB',
        ],
    ]);

    $activeBoxes = $boxTypes
        ->filter(fn ($box) => (bool) $this->record->{$box['status']})
        ->values();

    $allCoordinations = $this->getCoordinations();
@endphp

@vite('resources/css/flight-coordinations.css')

<div class="flight-coordinations">

    <!-- Header -->
    <div class="flight-report">
        <div class="flight-search">
            <div class="flight-search-wrapper">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..." class="flight-search-input">

                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="flight-search-clear" title="Limpiar búsqueda">
                        ×
                    </button>
                @endif
            </div>
        </div>
    </div> <!-- END flight-report -->

    <!-- SECTION COODINATION -->
    <div class="flight-report-scroll">
        <!-- DESKTOP / TABLET -->
        <div class="flight-report-desktop">
            @if ($allCoordinations->isEmpty())
                <div class="flight-empty-state">
                    <div class="flight-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>

                    <div class="flight-empty-text">
                        No flight coordinations
                    </div>
                </div>
            @else
                <!-- Coordination by Client -->
                <div class="flight-client">
                @foreach ($this->getCoordinationsByClient() as $coordinations)
                    <!-- Grid Columns -->
                    @php
                        $client = $coordinations->first()?->client;

                        $gridColumns = [
                            '190px',
                            '105px',
                            'minmax(150px, 1fr)',
                            '50px',
                        ];

                        foreach ($activeBoxes as $box) {
                            $gridColumns[] = '35px';
                        }

                        $gridColumns[] = '50px';
                        $gridColumns[] = '50px';

                        foreach ($activeBoxes as $box) {
                            $gridColumns[] = '35px';
                        }

                        $gridColumns[] = '50px';
                        $gridColumns[] = '75px';
                        $gridColumns[] = '50px';
                        $gridColumns[] = '140px';
                        $gridColumns[] = '90px';

                        $gridTemplate = implode(' ', $gridColumns);

                        $quantityGroupColumns = $activeBoxes->count() + 2;
                    @endphp
                        <!-- CLIENT -->
                        <div class="flight-client-title">
                            {{ $client?->name ?? 'CLIENTE SIN NOMBRE' }}
                        </div>
                        <div class="flight-grid" style="grid-template-columns: {{ $gridTemplate }};">
                            <!-- GROUP HEADERS -->
                            <div class="flight-group-header information" style="grid-column: span 3;"> INFORMATION </div>
                            <div class="flight-group-header coordinated" style="grid-column: span {{ $quantityGroupColumns }};"> COORDINATED </div>
                            <div class="flight-group-header received" style="grid-column: span {{ $quantityGroupColumns }};"> RECEIVED </div>
                            <div class="flight-group-header control" style="grid-column: span 2;">CONTROL</div>
                            <div class="flight-group-header empty"></div>
                            <div class="flight-group-header empty"></div>
                            <!-- HEADERS -->
                            <div class="flight-header-cell">FARM</div>
                            <div class="flight-header-cell">HAWB</div>
                            <div class="flight-header-cell">VARIETY</div>
                            <!-- COORDINATED -->
                            <div class="flight-header-cell coordinated">PCS</div>
                                @foreach ($activeBoxes as $box) 
                                    <div class="flight-header-cell coordinated">{{ $box['label'] }}</div>
                                @endforeach
                            <div class="flight-header-cell coordinated">FULL</div>
                            <!-- RECEIVED -->
                            <div class="flight-header-cell received">PCS</div>
                                @foreach ($activeBoxes as $box)
                                    <div class="flight-header-cell received">{{ $box['label'] }}</div>
                                @endforeach
                            <div class="flight-header-cell received">FULL</div>
                            <!-- CONTROL -->
                            <div class="flight-header-cell">FALTANTES</div>
                            <div class="flight-header-cell returns">DEV</div>
                            <div class="flight-header-cell">OBSERVACIÓN</div>
                            <div class="flight-header-cell">ACCIONES</div>
                            <!-- ROWS -->
                            @foreach ($coordinations as $coordination)
                                @php
                                    $varietyIds = $coordination->varieties ?? [];

                                    $varieties = \App\Models\FlowerVariety::query()
                                        ->whereIn('id', $varietyIds)
                                        ->pluck('name')
                                        ->implode(', ');
                                @endphp
                                <!-- INFORMATION -->
                                <div class="flight-cell text">{{ $coordination->farm?->name }}</div>
                                <div class="flight-cell text">{{ $coordination->hawb }}</div>
                                <div class="flight-cell text">{{ $varieties }}</div>
                                <!-- COORDINATED -->
                                <div class="flight-cell number coordinated">{{ $coordination->pieces }}</div>
                                @foreach ($activeBoxes as $box)
                                    <div class="flight-cell number coordinated">{{ $coordination->{$box['field']} }}</div>
                                @endforeach
                                <div class="flight-cell number coordinated">{{ number_format((float) $coordination->fulls, 3) }}</div>
                                <!-- RECEIVED -->
                                <div class="flight-cell number received">{{ $coordination->pieces_r }}</div>
                                @foreach ($activeBoxes as $box)
                                    <div class="flight-cell number received">{{ $coordination->{$box['received']} }}</div>
                                @endforeach
                                <div class="flight-cell number received">{{ number_format((float) $coordination->fulls_r, 3) }}</div>
                                <!-- CONTROL -->
                                <div class="flight-cell number">{{ $coordination->missing }}</div>
                                <div class="flight-cell number returns">{{ $coordination->returns }}</div>
                                <!-- OBSERVATION -->
                                <div class="flight-cell text">{{ $coordination->observation ?: '—' }}</div>
                                <!-- ACTIONS -->
                                <div class="flight-cell number">
                                    <!-- EDITAR -->
                                    <button type="button" wire:click="mountAction('editCoordination', { coordination: {{ $coordination->id }} })" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-primary-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-primary-400" title="Editar coordinación">
                                        <svg wire:loading.remove.delay.default="1" wire:target="mountAction('editCoordination', {}, JSON.parse('{\u0022recordKey\u0022:\u00228\u0022,\u0022table\u0022:true}'))" class="fi-icon fi-size-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                        </svg>
                                    </button>
                                    <!-- ELIMINAR -->
                                    <button type="button" wire:click="mountAction('deleteCoordination', { coordination: {{ $coordination->id }} })" class="coordination-action delete" title="Eliminar coordinación" > 
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"> 
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7m3 4v6m4-6v6" /> 
                                        </svg> 
                                    </button>
                                </div>
                            @endforeach
                            <!-- CLIENT TOTAL -->
                            <div class="flight-total-label" style="grid-column: span 3;">TOTAL {{ $client?->name }}</div>
                            <div class="flight-total-cell">{{ $coordinations->sum('pieces') }}</div>
                            @foreach ($activeBoxes as $box)
                                <div class="flight-total-cell">{{ $coordinations->sum($box['field']) }}</div>
                            @endforeach
                            <div class="flight-total-cell">{{ number_format((float) $coordinations->sum('fulls'), 3) }}</div>
                            <div class="flight-total-cell">{{ $coordinations->sum('pieces_r') }}</div>
                            @foreach ($activeBoxes as $box)
                                <div class="flight-total-cell">{{ $coordinations->sum($box['received']) }}</div>
                            @endforeach
                            <div class="flight-total-cell">{{ number_format((float) $coordinations->sum('fulls_r'), 3) }}</div>
                            <div class="flight-total-cell">{{ $coordinations->sum('missing') }}</div>
                            <div class="flight-total-cell">{{ $coordinations->sum('returns') }}</div>
                            <div class="flight-total-cell"></div>
                            <div class="flight-total-cell"></div>
                        </div>
                @endforeach
                </div>
            @endif
            <!-- GLOBAL TOTAL -->
            @if ($allCoordinations->isNotEmpty())
                <div class="flight-global-total">
                    <div class="flight-global-grid" style="grid-template-columns: {{ $gridTemplate ?? '220px 130px 160px 70px 60px 60px 60px 80px 70px 60px 60px 60px 80px 85px 60px 180px 90px' }};">
                        <div class="flight-global-label" style="grid-column: span 3;">TOTAL GLOBAL</div>
                        <div class="flight-global-cell">{{ $allCoordinations->sum('pieces') }}</div>
                        @foreach ($activeBoxes as $box)
                            <div class="flight-global-cell">{{ $allCoordinations->sum($box['field']) }}</div>
                        @endforeach
                        <div class="flight-global-cell">{{ number_format((float) $allCoordinations->sum('fulls'), 3) }}</div>
                        <div class="flight-global-cell">{{ $allCoordinations->sum('pieces_r') }}</div>
                        @foreach ($activeBoxes as $box)
                            <div class="flight-global-cell">{{ $allCoordinations->sum($box['received']) }}</div>
                        @endforeach
                        <div class="flight-global-cell">{{ number_format((float) $allCoordinations->sum('fulls_r'), 3) }}</div>
                        <div class="flight-global-cell">{{ $allCoordinations->sum('missing') }}</div>
                        <div class="flight-global-cell">{{ $allCoordinations->sum('returns') }}</div>
                        <div class="flight-global-cell"></div>
                        <div class="flight-global-cell-e"></div>
                    </div>
                </div>
            @endif
        </div>
        <!-- MOBILE -->
        <div class="flight-report-mobile">
            @foreach ($this->getCoordinationsByClient() as $coordinations)
                @php
                    $client = $coordinations->first()?->client;
                @endphp
                <div class="mobile-client">
                    <div class="mobile-client-title">
                        {{ $client?->name ?? 'CLIENTE SIN NOMBRE' }}
                    </div>
                    @foreach ($coordinations as $coordination)
                        @php
                            $varietyIds = $coordination->varieties ?? [];
                            $varieties = \App\Models\FlowerVariety::query()
                                ->whereIn('id', $varietyIds)
                                ->pluck('name')
                                ->implode(', ');
                        @endphp
                        <div class="mobile-card">
                            <!-- INFORMATION -->
                            <div class="mobile-section-title">INFORMATION</div>
                            <div class="mobile-row">
                                <span>FARM</span>
                                <strong>{{ $coordination->farm?->name }}</strong>
                            </div>
                            <div class="mobile-row">
                                <span>HAWB</span>
                                <strong>{{ $coordination->hawb }}</strong>
                            </div>
                            <div class="mobile-row">
                                <span>VARIETY</span>
                                <strong>{{ $varieties ?: '—' }}</strong>
                            </div>
                            <!-- COORDINATED -->
                            <div class="mobile-section-title coordinated">COORDINATED</div>
                            <div class="mobile-row">
                                <span>PCS</span>
                                <strong>{{ $coordination->pieces }}</strong>
                            </div>
                            @foreach ($activeBoxes as $box)
                                <div class="mobile-row">
                                    <span>{{ $box['label'] }}</span>
                                    <strong>{{ $coordination->{$box['field']} }}</strong>
                                </div>
                            @endforeach
                            <div class="mobile-row">
                                <span>FULL</span>
                                <strong>{{ number_format((float) $coordination->fulls, 3) }}</strong>
                            </div>
                            <!-- RECEIVED -->
                            <div class="mobile-section-title received">RECEIVED</div>
                            <div class="mobile-row">
                                <span>PCS</span>
                                <strong>{{ $coordination->pieces_r }}</strong>
                            </div>
                            @foreach ($activeBoxes as $box)
                                <div class="mobile-row">
                                    <span>{{ $box['label'] }}</span>
                                    <strong>{{ $coordination->{$box['received']} }}</strong>
                                </div>
                            @endforeach
                            <div class="mobile-row">
                                <span>FULL</span>
                                <strong>{{ number_format((float) $coordination->fulls_r, 3) }}</strong>
                            </div>
                            <!-- CONTROL -->
                            <div class="mobile-section-title control">CONTROL</div>
                            <div class="mobile-row">
                                <span>FALTANTES</span>
                                <strong>{{ $coordination->missing }}</strong>
                            </div>
                            <div class="mobile-row">
                                <span>DEV</span>
                                <strong>{{ $coordination->returns }}</strong>
                            </div>
                            <!-- OBSERVATION -->
                            <div class="mobile-row observation">
                                <span>OBSERVACIÓN</span>
                                <strong>{{ $coordination->observation ?: '—' }}</strong>
                            </div>
                            <!-- ACTION -->
                            <div class="mobile-actions">
                                <button type="button" wire:click="mountAction('editCoordination', { coordination: {{ $coordination->id }} })" title="Editar coordinación">EDITAR</button>
                            </div>
                        </div>
                    @endforeach
                    <!-- MOBILE CLIENT TOTAL -->
                    <div class="mobile-total">
                        <div class="mobile-section-title">TOTAL {{ $client?->name }}</div>
                    </div>
                    <div class="mobile-total-grid">
                        <div>
                            <span>PCS</span>
                            <strong>{{ $coordinations->sum('pieces') }}</strong>
                        </div>
                        @foreach ($activeBoxes as $box)
                            <div>
                                <span>{{ $box['label'] }}</span>
                                <strong>{{ $coordinations->sum($box['field']) }}</strong>
                            </div>
                        @endforeach
                        <div>
                            <span>FULL</span>
                            <strong>{{ number_format((float) $coordinations->sum('fulls'), 3) }}</strong>
                        </div>
                        <div>
                            <span>REC. PCS</span>
                            <strong>{{ $coordinations->sum('pieces_r') }}</strong>
                        </div>
                        <div>
                            <span>FALTANTES</span>
                            <strong>{{ $coordinations->sum('missing') }}</strong>
                        </div>
                        <div>
                            <span>DEV</span>
                            <strong>{{ $coordinations->sum('returns') }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
            <!-- MOBILE GLOBAL TOTAL -->
            @if ($allCoordinations->isNotEmpty())
                <div class="mobile-global-total">
                    <div class="mobile-section-title">
                        TOTAL GLOBAL
                    </div>
                    <div class="mobile-total-grid">
                        <div>
                            <span>PCS</span>
                            <strong>{{ $allCoordinations->sum('pieces') }}</strong>
                        </div>
                        @foreach ($activeBoxes as $box)
                            <div>
                                <span>{{ $box['label'] }}</span>
                                <strong>{{ $allCoordinations->sum($box['field']) }}</strong>
                            </div>
                        @endforeach
                        <div>
                            <span>FULL</span>
                            <strong>{{ number_format((float) $allCoordinations->sum('fulls'), 3) }}</strong>
                        </div>
                        <div>
                            <span>REC. PCS</span>
                            <strong>{{ $allCoordinations->sum('pieces_r') }}</strong>
                        </div>
                        <div>
                            <span>FALTANTES</span>
                            <strong>{{ $allCoordinations->sum('missing') }}</strong>
                        </div>
                        <div>
                            <span>DEV</span>
                            <strong>{{ $allCoordinations->sum('returns') }}</strong>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div> <!-- END flight-report-scroll -->
</div> <!-- END flight-coordinations -->

