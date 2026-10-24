<?php

namespace App\Filament\Resources\Flights\Pages;

use App\Filament\Resources\Flights\FlightResource;
use Filament\Resources\Pages\Page;
use App\Models\Farm;
use App\Models\FlowerVariety;
use App\Models\FlightCoordination;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\Flights\Actions\CreateFlightCoordinationAction;
use App\Filament\Resources\Flights\Actions\EditFlightCoordinationAction;
use App\Filament\Resources\Flights\Actions\DeleteFlightCoordinationAction;
use App\Models\Client;
use App\Models\Company;
use App\Reports\FlightCompletePdf;
use Filament\Actions\Action;
use App\Models\MyCompany;
use Filament\Actions\ActionGroup;

class FlightCoordinations extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = FlightResource::class;

    protected string $view = 'filament.resources.flights.pages.flight-coordinations';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        // dd($this->record);
    }

    public string $search = '';

    public function getCoordinations()
    {
        $query = \App\Models\FlightCoordination::query()
            ->with([
                'client',
                'farm',
                'marketer',
            ])
            ->where('flight_id', $this->record->id);

        if (filled($this->search)) {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->whereHas('farm', function ($farmQuery) use ($search) {
                    $farmQuery->where('name', 'like', $search);
                })
                ->orWhere('hawb', 'like', $search)
                ->orWhereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', $search);
                });
            });
        }
        // dd($query);
        return $query
            ->orderBy(
                Farm::select('name')
                    ->whereColumn('farms.id', 'flight_coordinations.farm_id')
            )
            ->get();
    }

    public function getCoordinationsByClient()
    {
        return $this->getCoordinations()
            ->groupBy('client_id')
            ->sortBy(function ($coordinations) {
                return $coordinations->first()->client->name ?? '';
            });
    }

    public function editCoordinationAction(): \Filament\Actions\Action
    {
        return EditFlightCoordinationAction::make();
    }

    public function deleteCoordinationAction(): \Filament\Actions\Action
    {
        return DeleteFlightCoordinationAction::make($this);
    }
    
    public function getCoordinationSummary()
    {
        return $this->getCoordinationsByClient()
            ->map(function ($coordinations) {

                $client = $coordinations->first()?->client;

                return [
                    'client' => $client?->name ?? 'CLIENTE SIN NOMBRE',

                    'coordinated' => [
                        'pieces' => $coordinations->sum('pieces'),
                        'fb'     => $coordinations->sum('fb'),
                        'hb'     => $coordinations->sum('hb'),
                        'qb'     => $coordinations->sum('qb'),
                        'eb'     => $coordinations->sum('eb'),
                        'db'     => $coordinations->sum('db'),
                        'fulls'  => $coordinations->sum('fulls'),
                    ],

                    'received' => [
                        'pieces' => $coordinations->sum('pieces_r'),
                        'fb'     => $coordinations->sum('fb_r'),
                        'hb'     => $coordinations->sum('hb_r'),
                        'qb'     => $coordinations->sum('qb_r'),
                        'eb'     => $coordinations->sum('eb_r'),
                        'db'     => $coordinations->sum('db_r'),
                        'fulls'  => $coordinations->sum('fulls_r'),
                    ],
                ];
            })
            ->values();
    }

    public function table(Table $table): Table
    {
        $boxTypes = [
            'fb' => ['status' => 'fb_status', 'label' => 'FB'],
            'hb' => ['status' => 'hb_status', 'label' => 'HB'],
            'qb' => ['status' => 'qb_status', 'label' => 'QB'],
            'eb' => ['status' => 'eb_status', 'label' => 'EB'],
            'db' => ['status' => 'db_status', 'label' => 'DB'],
        ];

        $coordinatedColumns = [];

        foreach ($boxTypes as $field => $config) {
            if ((bool) $this->record->{$config['status']}) {
                $coordinatedColumns[] = TextColumn::make($field)
                    ->label($config['label'])
                    ->alignCenter()
                    ->summarize(Sum::make()->hiddenLabel());
            }
        }

        $coordinatedColumns[] = \Filament\Tables\Columns\TextColumn::make('fulls')
            ->label('FULLS')
            ->alignCenter()
            ->summarize(Sum::make()->hiddenLabel());

        $coordinatedColumns[] = \Filament\Tables\Columns\TextColumn::make('pieces')
            ->label('PIECES')
            ->alignCenter()
            ->summarize(Sum::make()->hiddenLabel());


        $receivedColumns = [];

        foreach ($boxTypes as $field => $config) {
            if ((bool) $this->record->{$config['status']}) {
                $receivedColumns[] = \Filament\Tables\Columns\TextColumn::make($field . '_r')
                    ->label($config['label'])
                    ->alignCenter()
                    ->summarize(Sum::make()->hiddenLabel());
            }
        }

        $receivedColumns[] = \Filament\Tables\Columns\TextColumn::make('fulls_r')
            ->label('FULLS')
            ->alignCenter()
            ->summarize(Sum::make()->hiddenLabel());

        $receivedColumns[] = \Filament\Tables\Columns\TextColumn::make('pieces_r')
            ->label('PIECES')
            ->alignCenter()
            ->summarize(Sum::make()->hiddenLabel());


        return $table
            ->query(
                FlightCoordination::query()
                    ->with(['client', 'farm', 'marketer'])
                    ->where('flight_id', $this->record->id)
            )
            ->defaultGroup(
                Group::make('client.name')
            )
            ->columns([

                // INFORMATION
                ColumnGroup::make('INFORMACIÓN')
                    ->columns([
                        TextColumn::make('farm.name')
                            ->label('FARM')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('hawb')
                            ->label('HAWB')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('varieties')
                            ->label('Variety')
                            ->getStateUsing(function (FlightCoordination $record) {
                                $varietyIds = $record->varieties ?? [];

                                return FlowerVariety::whereIn('id', $varietyIds)
                                    ->pluck('name')
                                    ->implode(', ');
                            })
                            ->wrap(),
                        TextColumn::make('client.name')
                            ->label('Client')
                            ->searchable()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                        TextColumn::make('marketer.name')
                            ->label('Commercializer')
                            ->searchable()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])
                    ->alignCenter(),

                // COORDINATED
                ColumnGroup::make('COORDINADO')
                    ->columns($coordinatedColumns)
                    ->alignCenter(),

                // RECEIVED
                ColumnGroup::make('RECIBIDO')
                    ->columns($receivedColumns)
                    ->alignCenter(),

                // CONTROL
                ColumnGroup::make('CONTROL')
                    ->columns([
                        TextColumn::make('missing')
                            ->label('FALTANTES')
                            ->alignCenter()
                            ->summarize(Sum::make()->hiddenLabel()),

                        TextColumn::make('returns')
                            ->label('DEV')
                            ->alignCenter()
                            ->summarize(Sum::make()->hiddenLabel()),
                        TextColumn::make('observation')
                            ->label('OBSERVACIÓN')
                            ->wrap(),
                    ])
                    ->alignCenter(),
            ]);
    }

    protected ?array $clientsForReportCache = null;

    public function getClientsForReport(): array
    {
        return Client::query()
            ->whereIn(
                'id',
                $this->record->coordinations()
                    ->select('client_id')
                    ->distinct()
            )
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        // $company = Company::first();
        return [ 
            // BOTON DE CREAR NUEVA COORDINACION
            CreateFlightCoordinationAction::make($this)
                ->color('info'), 
            ActionGroup::make([
                // PDF COMPLETO
                Action::make('completePdf')
                    ->label('PDF completo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->url(fn () => route('reports.flight.complete', [
                        'flight' => $this->record->id,
                        // 'company' => $company,
                    ]))
                    ->openUrlInNewTab(),
                // PDF POR UN CLIENTE ESPECIFICO
                Action::make('clientPdf')
                    ->label('PDF por cliente')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->options(fn () => $this->getClientsForReport())
                            ->required()
                            ->searchable(),
                    ])
                    ->visible(fn () => count($this->getClientsForReport()) >= 2)
                    ->action(function (array $data) {
                        $url = route('reports.flight.client.pdf', [
                            'flight' => $this->record->id,
                            'client' => $data['client_id'],
                        ]);

                        $this->js("window.open(" . \Illuminate\Support\Js::from($url) . ", '_blank')");
                    }),
                // PDF SELECCIONANDO VARIOS CLIENTES
                Action::make('selectedClientsPdf')
                    ->label('PDF por clientes seleccionados')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('client_ids')
                            ->label('Clientes')
                            ->options(fn () => $this->getClientsForReport())
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->minItems(2),
                    ])
                    ->visible(fn () => count($this->getClientsForReport()) >= 3)
                    ->action(function (array $data) {
                        $url = route('reports.flight.selected-clients.pdf', [
                            'flight' => $this->record->id,
                            'client_ids' => $data['client_ids'],
                        ]);

                        $this->js(
                            "window.open(" . \Illuminate\Support\Js::from($url) . ", '_blank')"
                        );
                    }),
            ])
                ->label('Reportes PDF')
                ->icon('heroicon-o-document-chart-bar')
                ->button(),

            ActionGroup::make([
                // EXCEL COMPLETO
                Action::make('completeExcel')
                    ->label('Excel completo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn () => route(
                        'reports.flight.complete.excel',
                        ['flight' => $this->record->id]
                    ))
                    ->openUrlInNewTab(),
                // EXCEL POR UN CLIENTE ESPECIFICO
                Action::make('clientExcel')
                    ->label('Excel por cliente')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->options(fn () => $this->getClientsForReport())
                            ->required()
                            ->searchable(),
                    ])
                    ->visible(fn () => count($this->getClientsForReport()) >= 2)
                    ->action(function (array $data) {
                        $url = route('reports.flight.client.excel', [
                            'flight' => $this->record->id,
                            'client' => $data['client_id'],
                        ]);

                        $this->js(
                            "window.open(" . \Illuminate\Support\Js::from($url) . ", '_blank')"
                        );
                    }),
                // EXCEL SELECCIONANDO VARIOS CLIENTES
                Action::make('selectedClientsExcel')
                    ->label('Excel por clientes seleccionados')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('client_ids')
                            ->label('Clientes')
                            ->options(fn () => $this->getClientsForReport())
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->minItems(2),
                    ])
                    ->visible(fn () => count($this->getClientsForReport()) >= 3)
                    ->action(function (array $data) {
                        $url = route('reports.flight.selected-clients.excel', [
                            'flight' => $this->record->id,
                            'client_ids' => $data['client_ids'],
                        ]);

                        $this->js(
                            "window.open(" . \Illuminate\Support\Js::from($url) . ", '_blank')"
                        );
                    }),
            ])
                ->label('Reportes EXCEL')
                ->icon('heroicon-o-document-chart-bar')
                ->color('success')
                ->button(),
            
        ];
    }
}
