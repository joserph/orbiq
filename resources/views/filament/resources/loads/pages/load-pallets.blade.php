{{-- Estilos externos: resources/css/load-pallets.css (cargar mediante Vite) --}}
<x-filament-panels::page>
    
    @php
        $pallets = $this->getPallets();
        $allDetails = $pallets->flatMap(fn ($pallet) => $this->getPalletDetails($pallet));

        $summaryByClient = $allDetails
            ->groupBy('client_id')
            ->map(function ($clientDetails) {
                $client = $clientDetails->first()->client;

                $farms = $clientDetails
                    ->groupBy('farm_id')
                    ->map(function ($farmDetails) {
                        $farm = $farmDetails->first()->farm;

                        return [
                            'name' => $farm?->name ?? 'Sin finca',
                            'fb' => $farmDetails->sum('fb'),
                            'hb' => $farmDetails->sum('hb'),
                            'qb' => $farmDetails->sum('qb'),
                            'eb' => $farmDetails->sum('eb'),
                            'db' => $farmDetails->sum('db'),
                            'quantity' => $farmDetails->sum('quantity'),
                        ];
                    })
                    ->sortBy('name');

                return [
                    'name' => $client?->name ?? 'Sin cliente',
                    'farms' => $farms,
                    'fb' => $clientDetails->sum('fb'),
                    'hb' => $clientDetails->sum('hb'),
                    'qb' => $clientDetails->sum('qb'),
                    'eb' => $clientDetails->sum('eb'),
                    'db' => $clientDetails->sum('db'),
                    'quantity' => $clientDetails->sum('quantity'),
                ];
            })
            ->sortBy('name');

        $grandTotals = [
            'fb' => $allDetails->sum('fb'),
            'hb' => $allDetails->sum('hb'),
            'qb' => $allDetails->sum('qb'),
            'eb' => $allDetails->sum('eb'),
            'db' => $allDetails->sum('db'),
            'quantity' => $allDetails->sum('quantity'),
        ];
    @endphp
    @vite('resources/css/pallets.css')
    <div class="lp-style-1">
        {{-- Información de la carga --}}
        <x-filament::section>
            <x-slot name="heading">Información de la carga</x-slot>
            <div class="lp-style-2">
                <div>
                    <span class="lp-style-3">Shipment</span>
                    <div class="lp-style-4">{{ $this->record->shipment }}</div>
                </div>
                <div>
                    <span class="lp-style-3">BL</span>
                    <div class="lp-style-5">{{ $this->record->bl ?? 'Sin BL' }}</div>
                </div>
                <div>
                    <span class="lp-style-3">Shipping Company</span>
                    <div class="lp-style-5">{{ $this->record->carrier ?? 'Sin contenedor' }}</div>
                </div>
            </div>
        </x-filament::section>

        {{-- Paletas + resumen acumulado --}}
        <div class="lp-style-6">
            {{-- PALETAS --}}
            <div class="lp-style-7">
                <x-filament::section>
                    <x-slot name="heading">Paletas</x-slot>

                    @forelse ($pallets as $pallet)
                        @php
                            $details = $this->getPalletDetails($pallet);
                            $palletTotals = [
                                'fb' => $details->sum('fb'),
                                'hb' => $details->sum('hb'),
                                'qb' => $details->sum('qb'),
                                'eb' => $details->sum('eb'),
                                'db' => $details->sum('db'),
                                'quantity' => $details->sum('quantity'),
                            ];
                        @endphp

                        <div class="lp-style-8">
                            <div class="lp-style-9">
                                <div>
                                    <div class="lp-style-10">
                                        <x-filament::icon icon="heroicon-o-archive-box" class="lp-style-11" />
                                        <span>Paleta #{{ $pallet->number }}</span>
                                    </div>
                                    <div class="lp-style-12">{{ $palletTotals['quantity'] }} cajas</div>
                                </div>

                                <div class="lp-style-13">
                                    <x-filament::button size="sm" color="success" icon="heroicon-o-plus-circle"
                                        wire:click="mountAction('createPalletDetail', { palletId: {{ $pallet->id }} })">
                                        Agregar
                                    </x-filament::button>

                                    @if ($pallet->id === $this->record->pallets()->max('id'))
                                        <x-filament::button size="sm" color="danger" icon="heroicon-o-trash"
                                            wire:click="mountAction('deletePallet', { palletId: {{ $pallet->id }} })">
                                            Eliminar paleta
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>

                            <div class="lp-style-14">
                                @if ($details->isNotEmpty())
                                    <div class="lp-style-15">
                                        <table class="lp-style-16">
                                            <thead>
                                                <tr class="lp-style-17">
                                                    <th class="lp-style-18">Finca</th>
                                                    <th class="lp-style-18">Cliente</th>
                                                    <th class="lp-style-19">Piso</th>
                                                    @if ($this->record->fb_status) <th class="lp-style-19">FB</th> @endif
                                                    @if ($this->record->hb_status) <th class="lp-style-19">HB</th> @endif
                                                    @if ($this->record->qb_status) <th class="lp-style-19">QB</th> @endif
                                                    @if ($this->record->eb_status) <th class="lp-style-19">EB</th> @endif
                                                    @if ($this->record->db_status) <th class="lp-style-19">DB</th> @endif
                                                    <th class="lp-style-19">Total</th>
                                                    <th class="lp-style-19">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="lp-style-20">
                                                @foreach ($details as $detail)
                                                    <tr class="lp-style-21">
                                                        <td class="lp-style-22">{{ strtoupper(Str::limit($detail->farm?->name, 20)) ?? 'Sin finca' }}</td>
                                                        <td class="px-3 py-3">{{ strtoupper(Str::limit($detail->client?->name, 8)) ?? 'Sin cliente' }}</td>
                                                        <td class="lp-style-19">{{ $detail->piso ? 'Sí' : 'No' }}</td>
                                                        @if ($this->record->fb_status) <td class="lp-style-19">{{ $detail->fb }}</td> @endif
                                                        @if ($this->record->hb_status) <td class="lp-style-19">{{ $detail->hb }}</td> @endif
                                                        @if ($this->record->qb_status) <td class="lp-style-19">{{ $detail->qb }}</td> @endif
                                                        @if ($this->record->eb_status) <td class="lp-style-19">{{ $detail->eb }}</td> @endif
                                                        @if ($this->record->db_status) <td class="lp-style-19">{{ $detail->db }}</td> @endif
                                                        <td class="lp-style-23">{{ $detail->quantity }}</td>
                                                        {{-- <td class="lp-style-19">
                                                            <x-filament::button size="xs" color="warning" icon="heroicon-o-pencil"
                                                                wire:click="mountAction('editPalletDetail', { detailId: {{ $detail->id }} })">
                                                                
                                                            </x-filament::button>
                                                            <x-filament::button
                                                                color="danger"
                                                                size="sm"
                                                                icon="heroicon-o-trash"
                                                                tooltip="Eliminar coordinación"
                                                                wire:click="mountAction('deletePalletDetail', {
                                                                    detailId: {{ $detail->id }}
                                                                })"
                                                            />
                                                        </td> --}}
                                                        <td class="lp-actions-cell">
                                                            <div class="lp-actions-group">
                                                                {{-- Editar --}}
                                                                <x-filament::button
                                                                    color="warning"
                                                                    size="sm"
                                                                    icon="heroicon-o-pencil"
                                                                    tooltip="Editar item"
                                                                    wire:click="mountAction('editPalletDetail', {
                                                                        detailId: {{ $detail->id }}
                                                                    })"
                                                                />

                                                                {{-- Eliminar --}}
                                                                <x-filament::button
                                                                    color="danger"
                                                                    size="sm"
                                                                    icon="heroicon-o-trash"
                                                                    tooltip="Eliminar item"
                                                                    wire:click="mountAction('deletePalletDetail', {
                                                                        detailId: {{ $detail->id }}
                                                                    })"
                                                                />

                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="lp-style-24">
                                                    <td colspan="3" class="px-3 py-3 text-right">Total paleta</td>
                                                    @if ($this->record->fb_status) <td class="lp-style-19">{{ $palletTotals['fb'] }}</td> @endif
                                                    @if ($this->record->hb_status) <td class="lp-style-19">{{ $palletTotals['hb'] }}</td> @endif
                                                    @if ($this->record->qb_status) <td class="lp-style-19">{{ $palletTotals['qb'] }}</td> @endif
                                                    @if ($this->record->eb_status) <td class="lp-style-19">{{ $palletTotals['eb'] }}</td> @endif
                                                    @if ($this->record->db_status) <td class="lp-style-19">{{ $palletTotals['db'] }}</td> @endif
                                                    <td class="lp-style-19">{{ $palletTotals['quantity'] }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="lp-style-25">Esta paleta todavía no tiene cajas.</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="lp-style-26">
                            No existen paletas registradas en esta carga.
                        </div>
                    @endforelse
                </x-filament::section>
            </div>

            {{-- RESUMEN --}}
            <div class="lp-style-27">
                <x-filament::section>
                    <x-slot name="heading">Resumen acumulado</x-slot>

                    {{-- <div class="lp-style-28">
                        <div class="lp-style-29">Total general de cajas</div>
                        <div class="lp-style-30">{{ number_format($grandTotals['quantity']) }}</div>
                    </div> --}}
                    <div class="lp-total-boxes">
                        <div class="lp-total-boxes-title">
                            Total de cajas
                        </div>

                        <div class="lp-total-boxes-numbers">
                            <span class="lp-total-palletized">
                                {{ number_format($this->getPalletizedBoxes()) }}
                            </span>

                            <span class="lp-total-separator">/</span>

                            <span class="lp-total-coordinated">
                                {{ number_format($this->getCoordinatedBoxes()) }}
                            </span>
                        </div>

                        <div class="lp-progress">
                            <div
                                class="lp-progress-bar"
                                style="width: {{ $this->getPalletizationPercentage() }}%"
                            ></div>
                        </div>

                        <div class="lp-total-boxes-footer">
                            <span>
                                Paletizadas
                            </span>

                            <span>
                                Recibidas
                            </span>
                        </div>
                    </div>

                    @if ($summaryByClient->isNotEmpty())
                        <div class="lp-style-31">
                            @foreach ($summaryByClient as $clientSummary)
                                <div class="lp-style-32">
                                    <div class="lp-style-33">
                                        <div class="lp-style-34">{{ $clientSummary['name'] }}</div>
                                        <div class="lp-style-35">
                                            {{ number_format($clientSummary['quantity']) }} cajas
                                        </div>
                                    </div>

                                    <div class="lp-style-15">
                                        <table class="lp-style-36">
                                            <thead>
                                                <tr class="lp-style-37">
                                                    <th class="lp-style-38">Finca</th>
                                                    @if ($this->record->fb_status) <th class="lp-style-39">FB</th> @endif
                                                    @if ($this->record->hb_status) <th class="lp-style-39">HB</th> @endif
                                                    @if ($this->record->qb_status) <th class="lp-style-39">QB</th> @endif
                                                    @if ($this->record->eb_status) <th class="lp-style-39">EB</th> @endif
                                                    @if ($this->record->db_status) <th class="lp-style-39">DB</th> @endif
                                                    <th class="lp-style-39">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="lp-style-20">
                                                @foreach ($clientSummary['farms'] as $farmSummary)
                                                    <tr>
                                                        <td class="px-2 py-2">{{ $farmSummary['name'] }}</td>
                                                        @if ($this->record->fb_status) <td class="lp-style-39">{{ $farmSummary['fb'] }}</td> @endif
                                                        @if ($this->record->hb_status) <td class="lp-style-39">{{ $farmSummary['hb'] }}</td> @endif
                                                        @if ($this->record->qb_status) <td class="lp-style-39">{{ $farmSummary['qb'] }}</td> @endif
                                                        @if ($this->record->eb_status) <td class="lp-style-39">{{ $farmSummary['eb'] }}</td> @endif
                                                        @if ($this->record->db_status) <td class="lp-style-39">{{ $farmSummary['db'] }}</td> @endif
                                                        <td class="lp-style-40">{{ $farmSummary['quantity'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="lp-style-24">
                                                    <td class="px-2 py-2">Total cliente</td>
                                                    @if ($this->record->fb_status) <td class="lp-style-39">{{ $clientSummary['fb'] }}</td> @endif
                                                    @if ($this->record->hb_status) <td class="lp-style-39">{{ $clientSummary['hb'] }}</td> @endif
                                                    @if ($this->record->qb_status) <td class="lp-style-39">{{ $clientSummary['qb'] }}</td> @endif
                                                    @if ($this->record->eb_status) <td class="lp-style-39">{{ $clientSummary['eb'] }}</td> @endif
                                                    @if ($this->record->db_status) <td class="lp-style-39">{{ $clientSummary['db'] }}</td> @endif
                                                    <td class="lp-style-39">{{ $clientSummary['quantity'] }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="lp-style-41">
                            <table class="lp-style-36">
                                <thead>
                                    <tr class="lp-style-42">
                                        <th class="lp-style-38">TOTAL GENERAL</th>
                                        @if ($this->record->fb_status) <th class="lp-style-39">FB</th> @endif
                                        @if ($this->record->hb_status) <th class="lp-style-39">HB</th> @endif
                                        @if ($this->record->qb_status) <th class="lp-style-39">QB</th> @endif
                                        @if ($this->record->eb_status) <th class="lp-style-39">EB</th> @endif
                                        @if ($this->record->db_status) <th class="lp-style-39">DB</th> @endif
                                        <th class="lp-style-39">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="lp-style-5">
                                        <td class="lp-style-43">Todas las paletas</td>
                                        @if ($this->record->fb_status) <td class="px-2 py-3 text-center">{{ $grandTotals['fb'] }}</td> @endif
                                        @if ($this->record->hb_status) <td class="px-2 py-3 text-center">{{ $grandTotals['hb'] }}</td> @endif
                                        @if ($this->record->qb_status) <td class="px-2 py-3 text-center">{{ $grandTotals['qb'] }}</td> @endif
                                        @if ($this->record->eb_status) <td class="px-2 py-3 text-center">{{ $grandTotals['eb'] }}</td> @endif
                                        @if ($this->record->db_status) <td class="px-2 py-3 text-center">{{ $grandTotals['db'] }}</td> @endif
                                        <td class="px-2 py-3 text-center">{{ $grandTotals['quantity'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="lp-style-44">Aún no hay coordinaciones en las paletas.</div>
                    @endif
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
