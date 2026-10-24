<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Flight Coordination Report</title>

    <style>
        @page {
            margin: 30px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
        }

        /*.header {
            width: 100%;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }*/
        .header {
            width: 100%;
            display: table;
            margin-bottom: 15px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header-grid {
            width: 100%;
            margin-right: 155px;
        }

        .header-grid td {
            padding: 3px 3px 3px 3px;
            vertical-align: top;
        }

        .label {
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .value {
            font-size: 9px;
            font-weight: bold;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 6px;
            padding: 5px 7px;
            background: #f3f4f6;
            border-left: 3px solid #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #24435F;
            color: white;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #FFFFFF;
        }

        th.coordinated {
            background: #247FB8;
            color: white;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #FFFFFF;
        }

        th.received {
            background: #FFB800;
            color: #000000;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #FFFFFF;
        }

        th.subtotal {
            background: #E5E7EB;
            color: #000000;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #d1d5db;
        }

        th.subtotal_c {
            background: #EDF5FE;
            color: #000000;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #d1d5db;
        }

        th.subtotal_r {
            background: #FFF4CC;
            color: #000000;
            font-size: 7px;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #d1d5db;
        }

        th.total {
            background: #BBD2EA;
            color: #000000;
            font-size: 8px;
            padding: 4px 1px;
            text-align: center;
            border: 1px solid #B7C7D8;
            /* padding-top: 50px; */
        }

        th.total_c {
            background: #EDF5FE;
            color: #000000;
            font-size: 8px;
            padding: 4px 1px;
            text-align: center;
            border: 1px solid #B7C7D8;
            /* padding-top: 50px; */
        }

        th.total_r {
            background: #FFF4CC;
            color: #000000;
            font-size: 8px;
            padding: 4px 1px;
            text-align: center;
            border: 1px solid #B7C7D8;
            /* padding-top: 50px; */
        }

        td {
            padding: 4px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .group-header {
            background: #e5e7eb;
            font-weight: bold;
            font-size: 8px;
            padding: 5px;
            border: 1px solid #d1d5db;
        }

        /* .subtotal {
            background: #f9fafb;
            font-weight: bold;
        } */

        /* .total {
            margin-top: 15px;
            page-break-inside: avoid;
        } */

        .total-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .total-table th {
            background: #374151;
        }

        .number {
            text-align: center;
            white-space: nowrap;
        }

        .empty {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }
        .logo {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
        }

        .logo img {
            max-width: 180px;
            max-height: 70px;
        }

        .title {
            display: table-cell;
            width: 70%;
            text-align: center;
            vertical-align: middle;
        }

        .title h1 {
            margin: 0;
            font-size: 24px;
            color: #172033;
        }
    </style>
</head>

<body>

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="header">
        <div class="logo">
            @if($company?->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo">
            @endif
        </div>

        <div class="title">
            <h1>FLIGHT COORDINATIONS</h1>
        </div>
        {{-- Info del vuelo --}}
        <table class="header-grid">
            <tr>
                <td width="25%">
                    <div class="label">AWB</div>
                    <div class="value">
                        {{ $flight->awb ?? '—' }}
                    </div>
                </td>
                <td width="25%">
                    <div class="label">Airline</div>
                    <div class="value">
                        {{ $flight->airline?->name ?? '—' }}
                    </div>
                </td>
                <td width="20%">
                    <div class="label">Flight Date</div>
                    <div class="value">
                        {{ $flight->date?->format('d/m/Y') ?? '—' }}
                    </div>
                </td>
                <td width="20%">
                    <div class="label">Arrival Date</div>
                    <div class="value">
                        {{ $flight->arrival_date?->format('d/m/Y') ?? '—' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="label">Origin</div>
                    <div class="value">
                        {{ $flight->originCity?->name ?? '—' }}
                    </div>
                </td>

                <td colspan="2">
                    <div class="label">Destination</div>
                    <div class="value">
                        {{ $flight->destinationCity?->name ?? '—' }}
                    </div>
                </td>

                {{-- <td>
                    <div class="label">Consignee</div>
                    <div class="value">
                        {{ $flight->consignee ?? '—' }}
                    </div>
                </td>

                <td>
                    <div class="label">Entry Number</div>
                    <div class="value">
                        {{ $flight->entry_number ?? '—' }}
                    </div>
                </td> --}}
            </tr>
        </table>
    </div>


    {{-- =========================================================
         COORDINATIONS
    ========================================================== --}}

    @if ($coordinations->isEmpty())

        <div class="empty">
            No flight coordinations found.
        </div>

    @else

        @foreach ($coordinations as $commercializerName => $items)
            {{-- NOMBRE CLIENTE --}}
            <div class="section-title">
                CLIENT:
                {{ $commercializerName ?: 'WITHOUT CLIENT' }}
            </div>
            <table>
                {{-- CABECERA DE TABLA --}}
                <thead>
                    <tr>
                        <th rowspan="2">HAWB</th>
                        <th rowspan="2">FARM</th>
                        <th rowspan="2">VARIETY</th>
                        <th colspan="{{ count($activeBoxes) + 2 }}" class="coordinated">
                            COORDINATED
                        </th>
                        <th colspan="{{ count($activeBoxes) + 2 }}" class="received">
                            RECEIVED
                        </th>
                        <th rowspan="2">RETURNS</th>
                        <th rowspan="2">MISSING</th>
                        <th rowspan="2">OBSERVATIONS</th>
                    </tr>
                    <tr>
                        @foreach ($activeBoxes as $box)
                            <th class="coordinated">{{ strtoupper($box) }}</th>
                        @endforeach
                        <th class="coordinated">PIECES</th>
                        <th class="coordinated">FULLS</th>
                        @foreach ($activeBoxes as $box)
                            <th class="received">{{ strtoupper($box) . '_R' }}</th>
                        @endforeach
                        <th class="received">PIECES_R</th>
                        <th class="received">FULLS_R</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- CUEPO DE CADA COOORDINACION POR CLIENTE  --}}
                    @php
                        // Inicializar acumuladores dinámicos
                        $sub_totals = [];
                        $sub_totals_r = [];
                        foreach ($activeBoxes as $box) {
                            $sub_totals[$box] = 0;
                        }
                        $sub_totals['pieces'] = 0;
                        $sub_totals['fulls']  = 0;
                        foreach ($activeBoxes as $box) {
                            $sub_totals_r[$box] = 0;
                        }
                        $sub_totals_r['pieces_r'] = 0;
                        $sub_totals_r['fulls_r']  = 0;
                        $sub_totals['returns']  = 0;
                        $sub_totals['missing']  = 0;
                    @endphp
                    
                    @foreach ($items as $coordination)
                        <tr>
                            <td class="text-center" width="70px">
                                {{ $coordination->hawb ?? '—' }}
                            </td>
                            <td class="text-left" width="250px">
                                {{ $coordination->farm?->name ?? '—' }}
                            </td>
                            <td class="text-center" width="90px">
                                {{ $coordination->variety_names ?? '—' }}
                            </td>
                            {{-- COORDINATED --}}
                            @foreach ($activeBoxes as $box)
                                @php
                                    $sub_totals[$box] += (float) ($coordination->{$box} ?? 0);
                                @endphp
                                <td class="number">
                                    {{ number_format((float) ($coordination->{$box} ?? 0), 0) }}
                                </td>
                            @endforeach
                            @php
                                $sub_totals['pieces'] += (float) ($coordination->pieces ?? 0);
                                $sub_totals['fulls']  += (float) ($coordination->fulls ?? 0);
                            @endphp
                            <td class="number">
                                {{ number_format((float) ($coordination->pieces ?? 0), 0) }}
                            </td>
                            <td class="number">
                                {{ number_format((float) ($coordination->fulls ?? 0), 3) }}
                            </td>
                            {{-- RECEIVED --}}
                            @foreach ($activeBoxes as $box)
                                @php
                                    $receivedField = $box . '_r';
                                @endphp
                                @php
                                    $sub_totals_r[$box] += (float) ($coordination->{$receivedField} ?? 0);
                                @endphp
                                <td class="number">
                                    {{ number_format((float) ($coordination->{$receivedField} ?? 0), 0) }}
                                </td>
                            @endforeach
                            @php
                                $sub_totals_r['pieces_r'] += (float) ($coordination->pieces_r ?? 0);
                                $sub_totals_r['fulls_r']  += (float) ($coordination->fulls_r ?? 0);
                                $sub_totals['returns']  += (float) ($coordination->returns ?? 0);
                                $sub_totals['missing']  += (float) ($coordination->missing ?? 0);
                            @endphp
                            <td class="number">
                                {{ number_format((float) ($coordination->pieces_r ?? 0), 0) }}
                            </td>
                            <td class="number">
                                {{ number_format((float) ($coordination->fulls_r ?? 0), 3) }}
                            </td>
                            <td class="number" width="40px">
                                {{ number_format((float) ($coordination->returns ?? 0), 0) }}
                            </td>

                            <td class="number" width="40px">
                                {{ number_format((float) ($coordination->missing ?? 0), 0) }}
                            </td>
                            <td class="number" width="90px">
                                {{ ($coordination->observation ?? '-') }}
                            </td>
                        </tr>
                        
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="subtotal" colspan="3">SUB TOTAL</th>
                        {{-- AQUI QUIERO LOS SUB TOTALES DE CADA CLIENTE  --}}
                        {{-- SUBTOTALES COORDINATED (dinámicos por $activeBoxes) --}}
                        @foreach ($activeBoxes as $box)
                            <th class="number subtotal_c">{{ number_format($sub_totals[$box], 0) }}</th>
                        @endforeach
                        <th class="number subtotal_c">{{ number_format($sub_totals['pieces'], 0) }}</th>
                        <th class="number subtotal_c">{{ number_format($sub_totals['fulls'], 3) }}</th>
                        @foreach ($activeBoxes as $box)
                            <th class="number subtotal_r">{{ number_format($sub_totals_r[$box], 0) }}</th>
                        @endforeach
                        <th class="number subtotal_r">{{ number_format($sub_totals_r['pieces_r'], 0) }}</th>
                        <th class="number subtotal_r">{{ number_format($sub_totals_r['fulls_r'], 3) }}</th>
                        <th class="number subtotal">{{ number_format($sub_totals['returns'], 0) }}</th>
                        <th class="number subtotal">{{ number_format($sub_totals['missing'], 0) }}</th>
                        <th class="subtotal"></th>
                    </tr>
                </tfoot>
                
            </table>
        @endforeach
        <hr>
        @if ($totals)
            <table>
                {{-- CABECERA DE TABLA --}}
                <thead>
                    
                    <tr>
                        <th colspan="3"></th>
                        @foreach ($activeBoxes as $box)
                            <th class="coordinated">{{ strtoupper($box) }}</th>
                        @endforeach
                        <th class="coordinated">PIECES</th>
                        <th class="coordinated">FULLS</th>
                        @foreach ($activeBoxes as $box)
                            <th class="received">{{ strtoupper($box) . '_R' }}</th>
                        @endforeach
                        <th class="received">PIECES_R</th>
                        <th class="received">FULLS_R</th>
                        <th>RETURNS</th>
                        <th>MISSING</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="total" width="290px" colspan="3">GLOBAL TOTAL</th>
                        @foreach ($activeBoxes as $box)
                            <th class="number total_c">
                                {{ number_format((float) ($totals['coordinated'][$box] ?? 0), 0) }}
                            </th>
                        @endforeach
                        <th class="number total_c"> {{ number_format((float) ($totals['coordinated']['pieces'] ?? 0), 0) }}</th>
                        <th class="number total_c"> {{ number_format((float) ($totals['coordinated']['fulls'] ?? 0), 3) }}</th>
                        @foreach ($activeBoxes as $box)
                            <th class="number total_r">
                                {{ number_format((float) ($totals['received'][$box] ?? 0), 0) }}
                            </th>
                        @endforeach
                        <th class="number total_r"> {{ number_format((float) ($totals['received']['pieces'] ?? 0), 0) }}</th>
                        <th class="number total_r"> {{ number_format((float) ($totals['received']['fulls'] ?? 0), 3) }}</th>
                        <th class="number total" width="40px"> {{ number_format((float) ($totals['returns'] ?? 0), 0) }}</th>
                        <th class="number total" width="40px"> {{ number_format((float) ($totals['missing'] ?? 0), 0) }}</th>
                        <th class="total" width="90px"></th>
                    </tr>
                </tbody>
            </table>
        @endif
                
        


        {{-- =====================================================
             GLOBAL TOTAL
        ====================================================== --}}

        {{-- @if ($totals)

            <div class="total">

                <div class="total-title">
                    GLOBAL TOTAL
                </div>
                <table class="total-table">
                    <thead>
                        <tr>
                            <th>TYPE</th>

                            @foreach ($activeBoxes as $box)
                                <th>{{ strtoupper($box) }}</th>
                            @endforeach

                            <th>FULLS</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td><strong>COORDINATED</strong></td>

                            @foreach ($activeBoxes as $box)
                                <td class="number">
                                    {{ number_format((float) ($totals['coordinated'][$box] ?? 0), 0) }}
                                </td>
                            @endforeach

                            <td class="number">
                                {{ number_format((float) ($totals['coordinated']['fulls'] ?? 0), 3) }}
                            </td>
                        </tr>

                        <tr>
                            <td><strong>RECEIVED</strong></td>

                            @foreach ($activeBoxes as $box)

                                <td class="number">
                                    {{ number_format((float) ($totals['received'][$box] ?? 0), 0) }}
                                </td>

                            @endforeach

                            <td class="number">
                                {{ number_format((float) ($totals['received']['fulls'] ?? 0), 3) }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        @endif --}}

    @endif

</body>
</html>