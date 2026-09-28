<div class="space-y-8">

    @foreach ($this->getCoordinationsByClient() as $coordinations)

        @php
            $client = $coordinations->first()->client;
        @endphp

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">

            {{-- CLIENTE --}}
            <div class="bg-gray-100 px-4 py-3 font-bold dark:bg-gray-800">
                CLIENTE: {{ $client?->name }}
            </div>

            {{-- TABLA --}}
            <table
                class="text-sm"
                style="
                    width: 100%;
                    min-width: 1500px;
                    table-layout: fixed;
                    border-collapse: collapse;
                "
            >
            <colgroup>
    {{-- INFORMATION --}}
    <col style="width: 240px;"> {{-- FARM --}}
    <col style="width: 125px;"> {{-- HAWB --}}
    <col style="width: 145px;"> {{-- VARIETY --}}

    {{-- COORDINATED --}}
    <col style="width: 55px;"> {{-- HB --}}
    <col style="width: 55px;"> {{-- QB --}}
    <col style="width: 55px;"> {{-- EB --}}
    <col style="width: 75px;"> {{-- FULLS --}}
    <col style="width: 70px;"> {{-- PIECES --}}

    {{-- RECEIVED --}}
    <col style="width: 55px;"> {{-- HB --}}
    <col style="width: 55px;"> {{-- QB --}}
    <col style="width: 55px;"> {{-- EB --}}
    <col style="width: 75px;"> {{-- FULLS --}}
    <col style="width: 70px;"> {{-- PIECES --}}

    {{-- CONTROL --}}
    <col style="width: 90px;"> {{-- FALTANTES --}}
    <col style="width: 55px;"> {{-- DEV --}}

    {{-- OTHER --}}
    <col style="width: 150px;"> {{-- OBSERVACIÓN --}}
    <col style="width: 90px;"> {{-- ACCIONES --}}
</colgroup>

                {{-- ENCABEZADO --}}
                <thead>
                    <tr>
                        <th colspan="3" class="border px-3 py-2 text-center">
                            INFORMATION
                        </th>

                        <th colspan="5" class="border px-3 py-2 text-center">
                            COORDINATED
                        </th>

                        <th colspan="5" class="border px-3 py-2 text-center">
                            RECEIVED
                        </th>

                        <th colspan="2" class="border px-3 py-2 text-center">
                            CONTROL
                        </th>

                        <th class="border px-3 py-2"></th> {{-- OBSERVACIÓN --}}
                        <th class="border px-3 py-2"></th> {{-- ACCIONES --}}
                    </tr>

                    <tr>
                        <th class="border px-3 py-2">FARM</th>
                        <th class="border px-3 py-2">HAWB</th>
                        <th class="border px-3 py-2">VARIETY</th>

                        <th class="border px-3 py-2">HB</th>
                        <th class="border px-3 py-2">QB</th>
                        <th class="border px-3 py-2">EB</th>
                        <th class="border px-3 py-2">FULLS</th>
                        <th class="border px-3 py-2">PIECES</th>

                        <th class="border px-3 py-2">HB</th>
                        <th class="border px-3 py-2">QB</th>
                        <th class="border px-3 py-2">EB</th>
                        <th class="border px-3 py-2">FULLS</th>
                        <th class="border px-3 py-2">PIECES</th>

                        <th class="border px-3 py-2">FALTANTES</th>
                        <th class="border px-3 py-2">DEV</th>
                        <th class="border px-3 py-2">OBSERVACIÓN</th>
                        <th class="border px-3 py-2">ACCIONES</th>
                    </tr>
                </thead>

                {{-- AQUÍ PONDREMOS LAS COORDINACIONES --}}
                <tbody>
                    @foreach ($coordinations as $coordination)

                        @php
                            $varietyIds = $coordination->varieties ?? [];

                            $varieties = \App\Models\FlowerVariety::whereIn('id', $varietyIds)
                                ->pluck('name')
                                ->implode(', ');
                        @endphp

                        <tr>
                            {{-- INFORMATION --}}
                            <td class="border px-3 py-2">
                                {{ $coordination->farm?->name }}
                            </td>

                            <td class="border px-3 py-2">
                                {{ $coordination->hawb }}
                            </td>

                            <td class="border px-3 py-2">
                                {{ $varieties }}
                            </td>

                            {{-- COORDINATED --}}
                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->hb }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->qb }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->eb }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->fulls }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->pieces }}
                            </td>

                            {{-- RECEIVED --}}
                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->hb_r }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->qb_r }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->eb_r }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->fulls_r }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->pieces_r }}
                            </td>

                            {{-- CONTROL --}}
                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->missing }}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                {{ $coordination->returns }}
                            </td>
                            <td class="border px-3 py-2">
                                {{ $coordination->observation }}
                            </td>
                            {{-- ACTIONS --}}
                            <td class="border px-3 py-2 text-center">
                                -
                            </td>
                        </tr>

                    @endforeach
                </tbody>

            </table>
        </div>

    @endforeach

</div>




<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
    <table class="w-full text-sm text-left rtl:text-right text-body">
        <thead class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-default">
            <tr>
                <th scope="col" class="px-6 py-3 font-medium">
                    Product name
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Color
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Category
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Price
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Stock
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-neutral-primary border-b border-default">
                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                    Apple MacBook Pro 17"
                </th>
                <td class="px-6 py-4">
                    Silver
                </td>
                <td class="px-6 py-4">
                    Laptop
                </td>
                <td class="px-6 py-4">
                    $2999
                </td>
                <td class="px-6 py-4">
                    231
                </td>
            </tr>
            <tr class="bg-neutral-primary border-b border-default">
                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                    Microsoft Surface Pro
                </th>
                <td class="px-6 py-4">
                    White
                </td>
                <td class="px-6 py-4">
                    Laptop PC
                </td>
                <td class="px-6 py-4">
                    $1999
                </td>
                <td class="px-6 py-4">
                    423
                </td>
            </tr>
            <tr class="bg-neutral-primary">
                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                    Magic Mouse 2
                </th>
                <td class="px-6 py-4">
                    Black
                </td>
                <td class="px-6 py-4">
                    Accessories
                </td>
                <td class="px-6 py-4">
                    $99
                </td>
                <td class="px-6 py-4">
                    121
                </td>
            </tr>
        </tbody>
    </table>
</div>
