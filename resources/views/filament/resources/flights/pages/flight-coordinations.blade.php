<x-filament-panels::page>

    {{-- =========================================================
     INFORMACIÓN COMPACTA DEL VUELO
     ========================================================= --}}
    <div class="flight-summary">

        {{-- INFORMACIÓN PRINCIPAL --}}
        <div class="flight-summary-main">

            {{-- AWB --}}
            <div class="flight-summary-item flight-summary-awb">
                <span class="flight-summary-label">AWB</span>
                <span class="flight-summary-value">
                    {{ $this->record->awb }}
                </span>
            </div>

            {{-- AIRLINE --}}
            <div class="flight-summary-item">
                <span class="flight-summary-label">Airline</span>
                <span class="flight-summary-value">
                    {{ $this->record->airline?->name ?? '—' }}
                </span>
            </div>

            {{-- FLIGHT DATE --}}
            <div class="flight-summary-item">
                <span class="flight-summary-label">Flight</span>
                <span class="flight-summary-value">
                    {{ $this->record->date?->format('d/m/Y') ?? '—' }}
                </span>
            </div>

            {{-- ARRIVAL --}}
            <div class="flight-summary-item">
                <span class="flight-summary-label">Arrival</span>
                <span class="flight-summary-value">
                    {{ $this->record->arrival_date?->format('d/m/Y') ?? '—' }}
                </span>
            </div>

            {{-- RUTA --}}
            <div class="flight-summary-route">

                <div>
                    <span class="flight-summary-label">Origin</span>
                    <span class="flight-summary-route-value">
                        {{ $this->record->originCity?->name ?? '—' }}
                    </span>
                </div>

                <span class="flight-summary-arrow">→</span>

                <div>
                    <span class="flight-summary-label">Destination</span>
                    <span class="flight-summary-route-value">
                        {{ $this->record->destinationCity?->name ?? '—' }}
                    </span>
                </div>

            </div>

        </div>


        {{-- BOX TYPES --}}
        <div class="flight-box-types">

            <span class="flight-box-label">
                BOX TYPES
            </span>

            {{-- FB --}}
            <span class="flight-box-type {{ $this->record->fb_status ? 'active' : 'inactive' }}">
                FB
                <span>{{ $this->record->fb_status ? '✓' : '—' }}</span>
            </span>

            {{-- HB --}}
            <span class="flight-box-type {{ $this->record->hb_status ? 'active' : 'inactive' }}">
                HB
                <span>{{ $this->record->hb_status ? '✓' : '—' }}</span>
            </span>

            {{-- QB --}}
            <span class="flight-box-type {{ $this->record->qb_status ? 'active' : 'inactive' }}">
                QB
                <span>{{ $this->record->qb_status ? '✓' : '—' }}</span>
            </span>

            {{-- EB --}}
            <span class="flight-box-type {{ $this->record->eb_status ? 'active' : 'inactive' }}">
                EB
                <span>{{ $this->record->eb_status ? '✓' : '—' }}</span>
            </span>

            {{-- DB --}}
            <span class="flight-box-type {{ $this->record->db_status ? 'active' : 'inactive' }}">
                DB
                <span>{{ $this->record->db_status ? '✓' : '—' }}</span>
            </span>

        </div>

    </div>
  
{{-- ========================================================= --}}
{{-- RESUMEN DE COORDINACIÓN --}}
{{-- ========================================================= --}}

@php
    $summary = $this->getCoordinationSummary();

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

    $activeSummaryBoxes = $boxTypes
        ->filter(fn ($box) => (bool) $this->record->{$box['status']})
        ->values();
@endphp

@if ($summary->isNotEmpty())

    {{-- =========================================================
     RESUMEN DE COORDINACIÓN - COLLAPSED
     ========================================================= --}}

<details class="coordination-summary">

    <summary class="coordination-summary-header">
        <span>Resumen de coordinación</span>

        <span class="coordination-summary-arrow">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m19.5 8.25-7.5 7.5-7.5-7.5"
                />
            </svg>
        </span>
    </summary>

    <div class="coordination-summary-content">

        <table class="coordination-summary-table">

            <thead>

                <tr>
                    <th rowspan="2">CLIENTE</th>

                    <th colspan="5" class="coordinated-header">
                        COORDINADO
                    </th>

                    <th colspan="5" class="received-header">
                        RECIBIDO
                    </th>
                </tr>

                <tr>
                    <th>PCS</th>
                    <th>HB</th>
                    <th>QB</th>
                    <th>EB</th>
                    <th>FULL</th>

                    <th>PCS</th>
                    <th>HB</th>
                    <th>QB</th>
                    <th>EB</th>
                    <th>FULL</th>
                </tr>

            </thead>

            <tbody>

                @php
                    $globalCoordinatedPcs = 0;
                    $globalCoordinatedHb = 0;
                    $globalCoordinatedQb = 0;
                    $globalCoordinatedEb = 0;
                    $globalCoordinatedFull = 0;

                    $globalReceivedPcs = 0;
                    $globalReceivedHb = 0;
                    $globalReceivedQb = 0;
                    $globalReceivedEb = 0;
                    $globalReceivedFull = 0;
                @endphp

                @foreach ($this->getCoordinationsByClient() as $clientId => $coordinations)

                    @php
                        $client = $coordinations->first()?->client;

                        $coordinatedPcs = $coordinations->sum('pieces');
                        $coordinatedHb = $coordinations->sum('hb');
                        $coordinatedQb = $coordinations->sum('qb');
                        $coordinatedEb = $coordinations->sum('eb');
                        $coordinatedFull = $coordinations->sum('fulls');

                        $receivedPcs = $coordinations->sum('pieces_r');
                        $receivedHb = $coordinations->sum('hb_r');
                        $receivedQb = $coordinations->sum('qb_r');
                        $receivedEb = $coordinations->sum('eb_r');
                        $receivedFull = $coordinations->sum('fulls_r');

                        $globalCoordinatedPcs += $coordinatedPcs;
                        $globalCoordinatedHb += $coordinatedHb;
                        $globalCoordinatedQb += $coordinatedQb;
                        $globalCoordinatedEb += $coordinatedEb;
                        $globalCoordinatedFull += $coordinatedFull;

                        $globalReceivedPcs += $receivedPcs;
                        $globalReceivedHb += $receivedHb;
                        $globalReceivedQb += $receivedQb;
                        $globalReceivedEb += $receivedEb;
                        $globalReceivedFull += $receivedFull;
                    @endphp

                    <tr>

                        <td class="client-name">
                            {{ $client?->name ?? 'Sin cliente' }}
                        </td>

                        {{-- COORDINADO --}}
                        <td>{{ $coordinatedPcs }}</td>
                        <td>{{ $coordinatedHb }}</td>
                        <td>{{ $coordinatedQb }}</td>
                        <td>{{ $coordinatedEb }}</td>

                        <td class="full-value">
                            {{ number_format($coordinatedFull, 3) }}
                        </td>

                        {{-- RECIBIDO --}}
                        <td class="received-cell">
                            {{ $receivedPcs }}
                        </td>

                        <td class="received-cell">
                            {{ $receivedHb }}
                        </td>

                        <td class="received-cell">
                            {{ $receivedQb }}
                        </td>

                        <td class="received-cell">
                            {{ $receivedEb }}
                        </td>

                        <td class="received-cell full-value">
                            {{ number_format($receivedFull, 3) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr>

                    <td class="global-total">
                        TOTAL GLOBAL
                    </td>

                    {{-- COORDINADO --}}
                    <td>{{ $globalCoordinatedPcs }}</td>
                    <td>{{ $globalCoordinatedHb }}</td>
                    <td>{{ $globalCoordinatedQb }}</td>
                    <td>{{ $globalCoordinatedEb }}</td>

                    <td>
                        {{ number_format($globalCoordinatedFull, 3) }}
                    </td>

                    {{-- RECIBIDO --}}
                    <td class="received-cell">
                        {{ $globalReceivedPcs }}
                    </td>

                    <td class="received-cell">
                        {{ $globalReceivedHb }}
                    </td>

                    <td class="received-cell">
                        {{ $globalReceivedQb }}
                    </td>

                    <td class="received-cell">
                        {{ $globalReceivedEb }}
                    </td>

                    <td class="received-cell">
                        {{ number_format($globalReceivedFull, 3) }}
                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</details>

@endif


{{-- ========================================================= --}}
{{-- TABLA PRINCIPAL DE COORDINACIONES --}}
{{-- ========================================================= --}}




        {{-- Reporte V2 --}}
        @include('filament.resources.flights.partials.flight-coordinations-report-v2')
    {{-- </div> --}}


<x-filament-actions::modals />

</x-filament-panels::page>