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


<div class="flight-report">
    <div class="flight-report-scroll">
    {{-- ========================================================= --}}
    {{-- DESKTOP / TABLET --}}
    {{-- ========================================================= --}}

        <div class="flight-report-desktop">
            

            @foreach ($this->getCoordinationsByClient() as $coordinations)

                @php
                    $client = $coordinations->first()?->client;

                    $gridColumns = [
                        '220px',
                        '100px',
                        'minmax(130px, 1fr)',
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


                <div class="flight-client">

                    {{-- CLIENT --}}
                    <div class="flight-client-title">
                        {{ $client?->name ?? 'CLIENTE SIN NOMBRE' }}
                    </div>


                    

                        <div
                            class="flight-grid"
                            style="grid-template-columns: {{ $gridTemplate }};"
                        >

                            {{-- ================================================= --}}
                            {{-- GROUP HEADERS --}}
                            {{-- ================================================= --}}

                            <div
                                class="flight-group-header information"
                                style="grid-column: span 3;"
                            >
                                INFORMATION
                            </div>

                            <div
                                class="flight-group-header coordinated"
                                style="grid-column: span {{ $quantityGroupColumns }};"
                            >
                                COORDINATED
                            </div>

                            <div
                                class="flight-group-header received"
                                style="grid-column: span {{ $quantityGroupColumns }};"
                            >
                                RECEIVED
                            </div>

                            <div
                                class="flight-group-header control"
                                style="grid-column: span 2;"
                            >
                                CONTROL
                            </div>

                            <div class="flight-group-header empty"></div>
                            <div class="flight-group-header empty"></div>


                            {{-- ================================================= --}}
                            {{-- HEADERS --}}
                            {{-- ================================================= --}}

                            <div class="flight-header-cell">FARM</div>
                            <div class="flight-header-cell">HAWB</div>
                            <div class="flight-header-cell">VARIETY</div>


                            {{-- COORDINATED --}}

                            <div class="flight-header-cell coordinated">
                                PCS
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="flight-header-cell coordinated">
                                    {{ $box['label'] }}
                                </div>
                            @endforeach

                            <div class="flight-header-cell coordinated">
                                FULL
                            </div>


                            {{-- RECEIVED --}}

                            <div class="flight-header-cell received">
                                PCS
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="flight-header-cell received">
                                    {{ $box['label'] }}
                                </div>
                            @endforeach

                            <div class="flight-header-cell received">
                                FULL
                            </div>


                            {{-- CONTROL --}}

                            <div class="flight-header-cell">
                                FALTANTES
                            </div>

                            <div class="flight-header-cell returns">
                                DEV
                            </div>

                            <div class="flight-header-cell">
                                OBSERVACIÓN
                            </div>

                            <div class="flight-header-cell">
                                ACCIONES
                            </div>


                            {{-- ================================================= --}}
                            {{-- ROWS --}}
                            {{-- ================================================= --}}

                            @foreach ($coordinations as $coordination)

                                @php
                                    $varietyIds = $coordination->varieties ?? [];

                                    $varieties = \App\Models\FlowerVariety::query()
                                        ->whereIn('id', $varietyIds)
                                        ->pluck('name')
                                        ->implode(', ');
                                @endphp


                                {{-- INFORMATION --}}

                                <div class="flight-cell text">
                                    {{ $coordination->farm?->name }}
                                </div>

                                <div class="flight-cell text">
                                    {{ $coordination->hawb }}
                                </div>

                                <div class="flight-cell text">
                                    {{ $varieties }}
                                </div>


                                {{-- COORDINATED --}}

                                <div class="flight-cell number coordinated">
                                    {{ $coordination->pieces }}
                                </div>

                                @foreach ($activeBoxes as $box)
                                    <div class="flight-cell number coordinated">
                                        {{ $coordination->{$box['field']} }}
                                    </div>
                                @endforeach

                                <div class="flight-cell number coordinated">
                                    {{ number_format((float) $coordination->fulls, 3) }}
                                </div>


                                {{-- RECEIVED --}}

                                <div class="flight-cell number received">
                                    {{ $coordination->pieces_r }}
                                </div>

                                @foreach ($activeBoxes as $box)
                                    <div class="flight-cell number received">
                                        {{ $coordination->{$box['received']} }}
                                    </div>
                                @endforeach

                                <div class="flight-cell number received">
                                    {{ number_format((float) $coordination->fulls_r, 3) }}
                                </div>


                                {{-- CONTROL --}}

                                <div class="flight-cell number">
                                    {{ $coordination->missing }}
                                </div>

                                <div class="flight-cell number returns">
                                    {{ $coordination->returns }}
                                </div>


                                {{-- OBSERVATION --}}

                                <div class="flight-cell text">
                                    {{ $coordination->observation ?: '—' }}
                                </div>


                                {{-- ACTIONS --}}

                                <div class="flight-cell number">

                                    <button
                                        type="button"
                                        wire:click="mountAction('editCoordination', { coordination: {{ $coordination->id }} })"
                                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-primary-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-primary-400"
                                        title="Editar coordinación"
                                    >
                                        <svg wire:loading.remove.delay.default="1" wire:target="mountAction('editCoordination', {}, JSON.parse('{\u0022recordKey\u0022:\u00228\u0022,\u0022table\u0022:true}'))" class="fi-icon fi-size-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                        </svg>
                                    </button>

                                </div>

                            @endforeach


                            {{-- ================================================= --}}
                            {{-- CLIENT TOTAL --}}
                            {{-- ================================================= --}}

                            <div
                                class="flight-total-label"
                                style="grid-column: span 3;"
                            >
                                TOTAL {{ $client?->name }}
                            </div>


                            <div class="flight-total-cell">
                                {{ $coordinations->sum('pieces') }}
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="flight-total-cell">
                                    {{ $coordinations->sum($box['field']) }}
                                </div>
                            @endforeach

                            <div class="flight-total-cell">
                                {{ number_format((float) $coordinations->sum('fulls'), 3) }}
                            </div>


                            <div class="flight-total-cell">
                                {{ $coordinations->sum('pieces_r') }}
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="flight-total-cell">
                                    {{ $coordinations->sum($box['received']) }}
                                </div>
                            @endforeach

                            <div class="flight-total-cell">
                                {{ number_format((float) $coordinations->sum('fulls_r'), 3) }}
                            </div>


                            <div class="flight-total-cell">
                                {{ $coordinations->sum('missing') }}
                            </div>

                            <div class="flight-total-cell">
                                {{ $coordinations->sum('returns') }}
                            </div>

                            <div class="flight-total-cell"></div>
                            <div class="flight-total-cell"></div>

                        </div>

                    {{-- </div> --}}

                </div>

            @endforeach
            

            {{-- ========================================================= --}}
            {{-- GLOBAL TOTAL --}}
            {{-- ========================================================= --}}

            @if ($allCoordinations->isNotEmpty())

                <div class="flight-global-total">

                    <div
                        class="flight-global-grid"
                        style="grid-template-columns: {{ $gridTemplate ?? '220px 130px 160px 70px 60px 60px 60px 80px 70px 60px 60px 60px 80px 85px 60px 180px 90px' }};"
                    >

                        <div
                            class="flight-global-label"
                            style="grid-column: span 3;"
                        >
                            TOTAL GLOBAL
                        </div>


                        <div class="flight-global-cell">
                            {{ $allCoordinations->sum('pieces') }}
                        </div>

                        @foreach ($activeBoxes as $box)
                            <div class="flight-global-cell">
                                {{ $allCoordinations->sum($box['field']) }}
                            </div>
                        @endforeach

                        <div class="flight-global-cell">
                            {{ number_format((float) $allCoordinations->sum('fulls'), 3) }}
                        </div>


                        <div class="flight-global-cell">
                            {{ $allCoordinations->sum('pieces_r') }}
                        </div>

                        @foreach ($activeBoxes as $box)
                            <div class="flight-global-cell">
                                {{ $allCoordinations->sum($box['received']) }}
                            </div>
                        @endforeach

                        <div class="flight-global-cell">
                            {{ number_format((float) $allCoordinations->sum('fulls_r'), 3) }}
                        </div>


                        <div class="flight-global-cell">
                            {{ $allCoordinations->sum('missing') }}
                        </div>

                        <div class="flight-global-cell">
                            {{ $allCoordinations->sum('returns') }}
                        </div>

                        <div class="flight-global-cell"></div>
                        <div class="flight-global-cell"></div>

                    </div>

                </div>

            @endif

        </div>


        {{-- ========================================================= --}}
        {{-- MOBILE --}}
        {{-- ========================================================= --}}

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

                            {{-- INFORMATION --}}

                            <div class="mobile-section-title">
                                INFORMATION
                            </div>

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


                            {{-- COORDINATED --}}

                            <div class="mobile-section-title coordinated">
                                COORDINATED
                            </div>

                            <div class="mobile-row">
                                <span>PCS</span>
                                <strong>{{ $coordination->pieces }}</strong>
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="mobile-row">
                                    <span>{{ $box['label'] }}</span>
                                    <strong>
                                        {{ $coordination->{$box['field']} }}
                                    </strong>
                                </div>
                            @endforeach

                            <div class="mobile-row">
                                <span>FULL</span>
                                <strong>
                                    {{ number_format((float) $coordination->fulls, 3) }}
                                </strong>
                            </div>


                            {{-- RECEIVED --}}

                            <div class="mobile-section-title received">
                                RECEIVED
                            </div>

                            <div class="mobile-row">
                                <span>PCS</span>
                                <strong>{{ $coordination->pieces_r }}</strong>
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div class="mobile-row">
                                    <span>{{ $box['label'] }}</span>
                                    <strong>
                                        {{ $coordination->{$box['received']} }}
                                    </strong>
                                </div>
                            @endforeach

                            <div class="mobile-row">
                                <span>FULL</span>
                                <strong>
                                    {{ number_format((float) $coordination->fulls_r, 3) }}
                                </strong>
                            </div>


                            {{-- CONTROL --}}

                            <div class="mobile-section-title control">
                                CONTROL
                            </div>

                            <div class="mobile-row">
                                <span>FALTANTES</span>
                                <strong>{{ $coordination->missing }}</strong>
                            </div>

                            <div class="mobile-row">
                                <span>DEV</span>
                                <strong>{{ $coordination->returns }}</strong>
                            </div>


                            {{-- OBSERVATION --}}

                            <div class="mobile-row observation">
                                <span>OBSERVACIÓN</span>

                                <strong>
                                    {{ $coordination->observation ?: '—' }}
                                </strong>
                            </div>


                            {{-- ACTION --}}

                            <div class="mobile-actions">
                                <button
                                    type="button"
                                    wire:click="mountAction('editCoordination', { coordination: {{ $coordination->id }} })"
                                    title="Editar coordinación"
                                >
                                    EDITAR
                                </button>
                            </div>

                        </div>

                    @endforeach


                    {{-- MOBILE CLIENT TOTAL --}}

                    <div class="mobile-total">

                        <div class="mobile-section-title">
                            TOTAL {{ $client?->name }}
                        </div>

                        <div class="mobile-total-grid">

                            <div>
                                <span>PCS</span>
                                <strong>{{ $coordinations->sum('pieces') }}</strong>
                            </div>

                            @foreach ($activeBoxes as $box)
                                <div>
                                    <span>{{ $box['label'] }}</span>
                                    <strong>
                                        {{ $coordinations->sum($box['field']) }}
                                    </strong>
                                </div>
                            @endforeach

                            <div>
                                <span>FULL</span>
                                <strong>
                                    {{ number_format((float) $coordinations->sum('fulls'), 3) }}
                                </strong>
                            </div>

                            <div>
                                <span>REC. PCS</span>
                                <strong>
                                    {{ $coordinations->sum('pieces_r') }}
                                </strong>
                            </div>

                            <div>
                                <span>FALTANTES</span>
                                <strong>
                                    {{ $coordinations->sum('missing') }}
                                </strong>
                            </div>

                            <div>
                                <span>DEV</span>
                                <strong>
                                    {{ $coordinations->sum('returns') }}
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>

            @endforeach


            {{-- MOBILE GLOBAL TOTAL --}}

            @if ($allCoordinations->isNotEmpty())

                <div class="mobile-global-total">

                    <div class="mobile-section-title">
                        TOTAL GLOBAL
                    </div>

                    <div class="mobile-total-grid">

                        <div>
                            <span>PCS</span>
                            <strong>
                                {{ $allCoordinations->sum('pieces') }}
                            </strong>
                        </div>

                        @foreach ($activeBoxes as $box)
                            <div>
                                <span>{{ $box['label'] }}</span>
                                <strong>
                                    {{ $allCoordinations->sum($box['field']) }}
                                </strong>
                            </div>
                        @endforeach

                        <div>
                            <span>FULL</span>
                            <strong>
                                {{ number_format((float) $allCoordinations->sum('fulls'), 3) }}
                            </strong>
                        </div>

                        <div>
                            <span>REC. PCS</span>
                            <strong>
                                {{ $allCoordinations->sum('pieces_r') }}
                            </strong>
                        </div>

                        <div>
                            <span>FALTANTES</span>
                            <strong>
                                {{ $allCoordinations->sum('missing') }}
                            </strong>
                        </div>

                        <div>
                            <span>DEV</span>
                            <strong>
                                {{ $allCoordinations->sum('returns') }}
                            </strong>
                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>

</div>


<style>

    /* ============================================================
       DESKTOP
       ============================================================ */

    .flight-report {
        width: 100%;
    }

    .flight-report-desktop {
        display: block;
    }

    .flight-report-mobile {
        display: none;
    }

    .flight-client {
        margin-bottom: 0;

        overflow: visible;

        /* border: 1px solid rgb(209 213 219); */
        border-radius: 12px;

        background: white;

        box-shadow:
            0 1px 2px rgb(0 0 0 / 0.05);
    }

    .dark .flight-client {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }

    .flight-client-title {
        padding: 12px 16px;

        background: rgb(249 250 251);

        border-bottom: 1px solid rgb(209 213 219);

        font-size: 15px;
        font-weight: 700;
        text-align: center;
    }

    .dark .flight-client-title {
        background: rgb(31 41 55);
        border-color: rgb(55 65 81);
        color: white;
    }

    .flight-report-scroll {
        overflow-x: auto;
        overflow-y: hidden;

        width: 100%;
    }

    .flight-grid,
    .flight-global-grid {
        display: grid;

        /* width: max-content; */
        min-width: 100%;
    }

    .flight-group-header {
        min-height: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 8px;

        border-right: 1px solid rgb(209 213 219);
        border-bottom: 1px solid rgb(209 213 219);

        font-size: 12px;
        font-weight: 800;

        white-space: nowrap;
    }

    .flight-group-header.information {
        background: rgb(243 244 246);
    }

    .flight-group-header.coordinated {
        background: rgb(229 231 235);
    }

    .flight-group-header.received {
        background: rgb(220 252 231);
    }

    .flight-group-header.control {
        background: rgb(243 244 246);
    }

    .flight-group-header.empty {
        background: rgb(243 244 246);
    }

    .flight-header-cell {
        min-height: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 8px;

        border-right: 1px solid rgb(209 213 219);
        border-bottom: 1px solid rgb(209 213 219);

        background: rgb(249 250 251);

        font-size: 11px;
        font-weight: 700;

        text-align: center;
        white-space: nowrap;
    }

    .flight-header-cell.coordinated {
        background: rgb(243 244 246);
    }

    .flight-header-cell.received {
        background: rgb(240 253 244);
    }

    .flight-header-cell.returns {
        background: rgb(254 249 195);
    }

    .flight-cell {
        /* min-height: 44px; */

        display: flex;
        align-items: center;

        padding: 8px;

        border-right: 1px solid rgb(229 231 235);
        border-bottom: 1px solid rgb(229 231 235);

        background: white;

        font-size: 12px;
    }

    .dark .flight-cell {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
        color: rgb(229 231 235);
    }

    .flight-cell.text {
        overflow-wrap: anywhere;
    }

    .flight-cell.number {
        justify-content: center;

        text-align: center;
        white-space: nowrap;
    }

    .flight-cell.coordinated {
        background: rgb(249 250 251);
    }

    .flight-cell.received {
        background: rgb(240 253 244);
    }

    .flight-cell.returns {
        background: rgb(254 249 195);
    }

    .flight-total-label,
    .flight-total-cell {
        min-height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 8px;

        border-top: 2px solid rgb(156 163 175);
        border-right: 1px solid rgb(209 213 219);

        background: rgb(249 250 251);

        font-size: 12px;
        font-weight: 800;
    }

    .flight-total-label {
        justify-content: flex-end;
    }

    .dark .flight-total-label,
    .dark .flight-total-cell {
        background: rgb(31 41 55);
        border-color: rgb(75 85 99);
        color: white;
    }

    .flight-global-total {
        margin-top: 0;

        /* overflow-x: auto; */
        overflow: visible;

        /* border: 1px solid rgb(156 163 175); */
        border-radius: 10px;
    }

    .flight-global-label,
    .flight-global-cell {
        min-height: 50px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 8px;

        border-right: 1px solid rgb(156 163 175);

        background: rgb(229 231 235);

        font-size: 13px;
        font-weight: 800;
    }

    .flight-global-label {
        justify-content: flex-end;
    }


    /* ============================================================
       TABLET
       ============================================================ */

    @media (max-width: 1100px) {

        .flight-report-scroll {
            overflow-x: auto;
        }

        .flight-grid,
        .flight-global-grid {
            min-width: 1500px;
        }

    }


    /* ============================================================
       MOBILE
       ============================================================ */

    @media (max-width: 767px) {

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        |
        | On mobile we KEEP the desktop table.
        | The table gets its own internal horizontal scroll.
        |
        */

        .flight-report-desktop {
            display: block;
        }

        .flight-report-mobile {
            display: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Client table
        |--------------------------------------------------------------------------
        */

        .flight-client {
            margin-bottom: 0;

            border-radius: 0;
            border-left: 0;
            border-right: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Internal horizontal scroll
        |--------------------------------------------------------------------------
        */

        .flight-report-scroll {
            width: 100%;

            overflow-x: auto;
            overflow-y: hidden;

            -webkit-overflow-scrolling: touch;
        }


        /*
        |--------------------------------------------------------------------------
        | Keep the report wide enough to remain readable
        |--------------------------------------------------------------------------
        */

        .flight-grid {
            width: max-content;

            min-width: 1500px;
        }


        /*
        |--------------------------------------------------------------------------
        | Global total
        |--------------------------------------------------------------------------
        */

        .flight-global-total {
            margin-top: 0;

            border-radius: 0;

            border-left: 0;
            border-right: 0;
        }

        .flight-global-grid {
            width: max-content;

            min-width: 1500px;
        }

    }

</style>