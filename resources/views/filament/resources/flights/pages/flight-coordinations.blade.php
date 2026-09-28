<x-filament-panels::page>

    {{-- ==========================================
         FLIGHT INFORMATION
    =========================================== --}}

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

        {{-- AWB --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                AWB
            </div>

            <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                {{ $this->record->awb }}
            </div>
        </div>

        {{-- Airline --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Airline
            </div>

            <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                {{ $this->record->airline?->name ?? '—' }}
            </div>
        </div>

        {{-- Flight Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Flight Date
            </div>

            <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                {{ $this->record->date?->format('d/m/Y') ?? '—' }}
            </div>
        </div>

        {{-- Arrival Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Arrival Date
            </div>

            <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                {{ $this->record->arrival_date?->format('d/m/Y') ?? '—' }}
            </div>
        </div>

    </div>


    {{-- ==========================================
         ROUTE
    =========================================== --}}

    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">

        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
            Flight Route
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- Origin --}}
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Origin
                </div>

                <div class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                    {{ $this->record->originCity?->name ?? '—' }}
                </div>
            </div>

            {{-- Destination --}}
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Destination
                </div>

                <div class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                    {{ $this->record->destinationCity?->name ?? '—' }}
                </div>
            </div>

        </div>

    </div>


    {{-- ==========================================
         BOX TYPES
    =========================================== --}}

    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">

        <div>
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                Box Types
            </h2>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Box types enabled for this flight.
            </p>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-5">

            {{-- FB --}}
            <div class="rounded-lg border p-4 text-center
                {{ $this->record->fb_status
                    ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">

                <div class="text-lg font-bold text-gray-950 dark:text-white">
                    FB
                </div>

                <div class="mt-1 text-sm font-medium
                    {{ $this->record->fb_status
                        ? 'text-success-600 dark:text-success-400'
                        : 'text-gray-500 dark:text-gray-400' }}">

                    {{ $this->record->fb_status ? 'Active' : 'Inactive' }}

                </div>
            </div>

            {{-- HB --}}
            <div class="rounded-lg border p-4 text-center
                {{ $this->record->hb_status
                    ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">

                <div class="text-lg font-bold text-gray-950 dark:text-white">
                    HB
                </div>

                <div class="mt-1 text-sm font-medium
                    {{ $this->record->hb_status
                        ? 'text-success-600 dark:text-success-400'
                        : 'text-gray-500 dark:text-gray-400' }}">

                    {{ $this->record->hb_status ? 'Active' : 'Inactive' }}

                </div>
            </div>

            {{-- QB --}}
            <div class="rounded-lg border p-4 text-center
                {{ $this->record->qb_status
                    ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">

                <div class="text-lg font-bold text-gray-950 dark:text-white">
                    QB
                </div>

                <div class="mt-1 text-sm font-medium
                    {{ $this->record->qb_status
                        ? 'text-success-600 dark:text-success-400'
                        : 'text-gray-500 dark:text-gray-400' }}">

                    {{ $this->record->qb_status ? 'Active' : 'Inactive' }}

                </div>
            </div>

            {{-- EB --}}
            <div class="rounded-lg border p-4 text-center
                {{ $this->record->eb_status
                    ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">

                <div class="text-lg font-bold text-gray-950 dark:text-white">
                    EB
                </div>

                <div class="mt-1 text-sm font-medium
                    {{ $this->record->eb_status
                        ? 'text-success-600 dark:text-success-400'
                        : 'text-gray-500 dark:text-gray-400' }}">

                    {{ $this->record->eb_status ? 'Active' : 'Inactive' }}

                </div>
            </div>

            {{-- DB --}}
            <div class="rounded-lg border p-4 text-center
                {{ $this->record->db_status
                    ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">

                <div class="text-lg font-bold text-gray-950 dark:text-white">
                    DB
                </div>

                <div class="mt-1 text-sm font-medium
                    {{ $this->record->db_status
                        ? 'text-success-600 dark:text-success-400'
                        : 'text-gray-500 dark:text-gray-400' }}">

                    {{ $this->record->db_status ? 'Active' : 'Inactive' }}

                </div>
            </div>

        </div>

    </div>

    <div class="mt-6">
        {{-- Tabla actual de Filament --}}
        {{ $this->table }}

        {{-- Reporte V2 --}}
        @include('filament.resources.flights.partials.flight-coordinations-report-v2')
    </div>

    {{-- <div class="mt-4 min-w-0 max-w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                Flight Coordinations
            </h2>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Coordinations associated with this flight.
            </p>
        </div>

        <div class="min-w-0 max-w-full overflow-x-auto">
            @php
                $coordinatedBoxes = collect([
                    'fb' => $this->record->fb_status,
                    'hb' => $this->record->hb_status,
                    'qb' => $this->record->qb_status,
                    'eb' => $this->record->eb_status,
                    'db' => $this->record->db_status,
                ])->filter()->count();

                $receivedBoxes = $coordinatedBoxes;
            @endphp

<table
    class="text-left text-sm"
    style="width: max-content; table-layout: fixed;"
>

    <colgroup>
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 100px;">

        @if ($this->record->fb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->hb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->qb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->eb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->db_status)
            <col style="width: 10px;">
        @endif

        <col style="width: 40px;">
        <col style="width: 50px;">
        @if ($this->record->fb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->hb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->qb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->eb_status)
            <col style="width: 10px;">
        @endif

        @if ($this->record->db_status)
            <col style="width: 10px;">
        @endif

        <col style="width: 110px;">
        <col style="width: 110px;">
        <col style="width: 100px;">
    </colgroup>

    <thead>

        
        <tr class="border-b border-gray-200 dark:border-gray-700">

            <th
                rowspan="2"
                class="whitespace-nowrap bg-gray-50 px-6 py-3 text-left font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                HAWB
            </th>

            <th
                rowspan="2"
                class="whitespace-nowrap bg-gray-50 px-6 py-3 text-left font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                Client
            </th>

            <th
                rowspan="2"
                class="whitespace-nowrap bg-gray-50 px-6 py-3 text-left font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                Farm
            </th>

            <th
                rowspan="2"
                class="whitespace-nowrap bg-gray-50 px-6 py-3 text-left font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                Commercializer
            </th>

            <th
                colspan="{{ $coordinatedBoxes + 2 }}"
                class="bg-gray-100 px-6 py-3 text-center font-bold text-gray-800 dark:bg-gray-700 dark:text-gray-100"
            >
                COORDINATED
            </th>
            <th
                colspan="{{ $receivedBoxes + 2 }}"
                class="bg-gray-100 px-6 py-3 text-center font-bold text-gray-800 dark:bg-gray-700 dark:text-gray-100"
            >
                RECEIVED
            </th>
            <th
                colspan="2"
                class="bg-gray-100 px-6 py-3 text-center font-bold text-gray-800 dark:bg-gray-700 dark:text-gray-100"
            >
                CONTROL
            </th>

        </tr>

        
        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">

            @if ($this->record->fb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    FB
                </th>
            @endif

            @if ($this->record->hb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    HB
                </th>
            @endif

            @if ($this->record->qb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    QB
                </th>
            @endif

            @if ($this->record->eb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    EB
                </th>
            @endif

            @if ($this->record->db_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    DB
                </th>
            @endif

            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                FULLS
            </th>

            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                PIECES
            </th>
            @if ($this->record->fb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    FB
                </th>
            @endif

            @if ($this->record->hb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    HB
                </th>
            @endif

            @if ($this->record->qb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    QB
                </th>
            @endif

            @if ($this->record->eb_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    EB
                </th>
            @endif

            @if ($this->record->db_status)
                <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                    DB
                </th>
            @endif

            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                FULLS
            </th>

            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                PIECES
            </th>
            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                MISSING
            </th>

            <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                RETURNS
            </th>
            <th
                rowspan="2"
                class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide"
            >
                ACTIONS
            </th>
        </tr>

    </thead>

    <tbody>

        @forelse ($this->getCoordinations() as $coordination)

            <tr class="border-b border-gray-100 dark:border-gray-800">

                <td class="whitespace-nowrap px-6 py-3 font-medium text-gray-950 dark:text-white">
                    {{ $coordination->hawb }}
                </td>

                <td class="whitespace-nowrap px-6 py-3 text-gray-700 dark:text-gray-300">
                    {{ $coordination->client?->name ?? '—' }}
                </td>

                <td class="whitespace-nowrap px-6 py-3 text-gray-700 dark:text-gray-300">
                    {{ $coordination->farm?->name ?? '—' }}
                </td>

                <td class="whitespace-nowrap px-6 py-3 text-gray-700 dark:text-gray-300">
                    {{ $coordination->marketer?->name ?? '—' }}
                </td>

                @if ($this->record->fb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->fb }}
                    </td>
                @endif

                @if ($this->record->hb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->hb }}
                    </td>
                @endif

                @if ($this->record->qb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->qb }}
                    </td>
                @endif

                @if ($this->record->eb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->eb }}
                    </td>
                @endif

                @if ($this->record->db_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->db }}
                    </td>
                @endif

                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ number_format((float) $coordination->fulls, 3) }}
                </td>

                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ $coordination->pieces }}
                </td>
                @if ($this->record->fb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->fb_r }}
                    </td>
                @endif

                @if ($this->record->hb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->hb_r }}
                    </td>
                @endif

                @if ($this->record->qb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->qb_r }}
                    </td>
                @endif

                @if ($this->record->eb_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->eb_r }}
                    </td>
                @endif

                @if ($this->record->db_status)
                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                        {{ $coordination->db_r }}
                    </td>
                @endif

                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ number_format((float) $coordination->fulls_r, 3) }}
                </td>

                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ $coordination->pieces_r }}
                </td>
                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ $coordination->missing }}
                </td>

                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                    {{ $coordination->returns }}
                </td>
                
                <td class="whitespace-nowrap px-4 py-3 text-center">
                    {{ ($this->editCoordinationAction)([
                        'coordination' => $coordination->id,
                    ]) }}
                </td>
            </tr>
            

        @empty

            <tr>
                <td
                    colspan="{{ 4 + $coordinatedBoxes + 2 + $receivedBoxes + 2 + 2 + 1 }}"
                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                >
                    No flight coordinations yet.
                </td>
            </tr>

        @endforelse

    </tbody>

</table>
        </div>
    </div> --}}
<x-filament-actions::modals />




</x-filament-panels::page>