<?php

namespace App\Filament\Resources\Flights\Pages;

use App\Filament\Resources\Flights\FlightResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Models\Client;
use App\Models\Commercializer;
use App\Models\Farm;
use App\Models\FlowerVariety;
use App\Models\FlightCoordination;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Actions\Contracts\HasActions;
// use Filament\Schemas\Concerns\InteractsWithSchemas;
// use Filament\Schemas\Contracts\HasSchemas;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Models\Flight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;

class FlightCoordinations extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = FlightResource::class;

    protected string $view = 'filament.resources.flights.pages.flight-coordinations';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    // public function afterActionCalled(\Filament\Actions\Action $action): void
    // {
    //     $this->afterActionCalledFromActions($action);

    //     $this->afterActionCalledFromRecord($action);
    // }
    

    public function getCoordinations()
    {
        return \App\Models\FlightCoordination::query()
            ->with([
                'client',
                'farm',
                'marketer',
            ])
            ->where('flight_id', $this->record->id)
            ->orderBy(
                Farm::select('name')
                    ->whereColumn('farms.id', 'flight_coordinations.farm_id')
            )
            ->get();
    }

    public function getCoordinationsByClient()
    {
        return $this->getCoordinations()
            ->groupBy('client_id');
    }

    // protected function getEditCoordinationAction(): \Filament\Actions\Action
    public function editCoordinationAction(): \Filament\Actions\Action
    {
        return Action::make('editCoordination')
            ->label('Edit')
            ->icon('heroicon-o-pencil-square')
            ->modalHeading('Edit Flight Coordination')
            ->modalWidth('4xl')

            ->schema([
                TextInput::make('hawb')
                    ->label('HAWB')
                    ->required()
                    ->maxLength(255),

                Select::make('client_id')
                    ->label('Client')
                    ->options(
                        Client::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                Select::make('farm_id')
                    ->label('Farm')
                    ->options(
                        Farm::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                Select::make('marketer_id')
                    ->label('Commercializer')
                    ->options(
                        Commercializer::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                CheckboxList::make('varieties')
                    ->label('Flower Varieties')
                    ->options(
                        FlowerVariety::query()
                            ->where('status', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->columns(3)
                    ->bulkToggleable(),

                Section::make('Coordinated Boxes')
                    ->columns(5)
                    ->schema([
                        TextInput::make('fb')
                            ->label('FB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->fb_status),

                        TextInput::make('hb')
                            ->label('HB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->hb_status),

                        TextInput::make('qb')
                            ->label('QB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->qb_status),

                        TextInput::make('eb')
                            ->label('EB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->eb_status),

                        TextInput::make('db')
                            ->label('DB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->db_status),
                    ])
                    ->columnSpanFull(),

                Section::make('Received Boxes')
                    ->columns(5)
                    ->schema([
                        TextInput::make('fb_r')
                            ->label('FB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->fb_status),

                        TextInput::make('hb_r')
                            ->label('HB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->hb_status),

                        TextInput::make('qb_r')
                            ->label('QB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->qb_status),

                        TextInput::make('eb_r')
                            ->label('EB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->eb_status),

                        TextInput::make('db_r')
                            ->label('DB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $this->record->db_status),

                        TextInput::make('returns')
                            ->label('Returns')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columnSpanFull(),

                Textarea::make('observation')
                    ->label('Observation')
                    ->rows(3)
                    ->columnSpanFull(),
            ])

            ->fillForm(function (array $arguments): array {

                $coordination = FlightCoordination::findOrFail(
                    $arguments['coordination']
                );

                return [
                    'hawb' => $coordination->hawb,
                    'client_id' => $coordination->client_id,
                    'farm_id' => $coordination->farm_id,
                    'marketer_id' => $coordination->marketer_id,
                    'varieties' => $coordination->varieties ?? [],

                    'fb' => $coordination->fb,
                    'hb' => $coordination->hb,
                    'qb' => $coordination->qb,
                    'eb' => $coordination->eb,
                    'db' => $coordination->db,

                    'fb_r' => $coordination->fb_r,
                    'hb_r' => $coordination->hb_r,
                    'qb_r' => $coordination->qb_r,
                    'eb_r' => $coordination->eb_r,
                    'db_r' => $coordination->db_r,

                    'returns' => $coordination->returns,
                    'observation' => $coordination->observation,
                ];
            })

            ->action(function (array $data, array $arguments): void {

                $coordination = FlightCoordination::findOrFail(
                    $arguments['coordination']
                );

                $flight = $this->record;

                $allowedBoxes = [
                    'fb'   => (bool) $flight->fb_status,
                    'hb'   => (bool) $flight->hb_status,
                    'qb'   => (bool) $flight->qb_status,
                    'eb'   => (bool) $flight->eb_status,
                    'db'   => (bool) $flight->db_status,

                    'fb_r' => (bool) $flight->fb_status,
                    'hb_r' => (bool) $flight->hb_status,
                    'qb_r' => (bool) $flight->qb_status,
                    'eb_r' => (bool) $flight->eb_status,
                    'db_r' => (bool) $flight->db_status,
                ];

                foreach ($allowedBoxes as $field => $allowed) {
                    if (! $allowed) {
                        $data[$field] = 0;
                    }
                }

                $coordination->update($data);
            });
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
            ])
            ->recordActions([
                $this->editCoordinationAction(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $updateCalculations = function (callable $get, callable $set): void {
            $fb = (int) ($get('fb') ?? 0);
            $hb = (int) ($get('hb') ?? 0);
            $qb = (int) ($get('qb') ?? 0);
            $eb = (int) ($get('eb') ?? 0);
            $db = (int) ($get('db') ?? 0);

            $fulls =
                ($fb * 1) +
                ($hb * 0.5) +
                ($qb * 0.25) +
                ($eb * 0.125) +
                ($db * 0.0625);

            $pieces =
                $fb +
                $hb +
                $qb +
                $eb +
                $db;

            $set('fulls', number_format($fulls, 3, '.', ''));
            $set('pieces', $pieces);
        };

        $updateReceivedCalculations = function (callable $get, callable $set): void {
            $fb = (int) ($get('fb_r') ?? 0);
            $hb = (int) ($get('hb_r') ?? 0);
            $qb = (int) ($get('qb_r') ?? 0);
            $eb = (int) ($get('eb_r') ?? 0);
            $db = (int) ($get('db_r') ?? 0);

            $fulls =
                ($fb * 1) +
                ($hb * 0.5) +
                ($qb * 0.25) +
                ($eb * 0.125) +
                ($db * 0.0625);

            $pieces =
                $fb +
                $hb +
                $qb +
                $eb +
                $db;

            $set('fulls_r', number_format($fulls, 3, '.', ''));
            $set('pieces_r', $pieces);
        };

        $updateMissing = function (callable $get, callable $set): void {
            $coordinated =
                (int) ($get('fb') ?? 0) +
                (int) ($get('hb') ?? 0) +
                (int) ($get('qb') ?? 0) +
                (int) ($get('eb') ?? 0) +
                (int) ($get('db') ?? 0);

            $received =
                (int) ($get('fb_r') ?? 0) +
                (int) ($get('hb_r') ?? 0) +
                (int) ($get('qb_r') ?? 0) +
                (int) ($get('eb_r') ?? 0) +
                (int) ($get('db_r') ?? 0);

            $set('missing', $coordinated - $received);
        };

        return [
            \Filament\Actions\Action::make('newCoordination')
                ->label('New Coordination')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('New Flight Coordination')
                ->modalWidth('4xl')
                ->schema([

                    TextInput::make('hawb')
                        ->label('HAWB')
                        ->required()
                        ->maxLength(255),

                    Select::make('client_id')
                        ->label('Client')
                        ->options(
                            Client::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Select::make('farm_id')
                        ->label('Farm')
                        ->options(
                            Farm::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Select::make('marketer_id')
                        ->label('Commercializer')
                        ->options(
                            Commercializer::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->native(false),

                    CheckboxList::make('varieties')
                        ->label('Flower Varieties')
                        ->options(
                            FlowerVariety::query()
                                ->where('status', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->columns(3)
                        ->bulkToggleable(),
                    Section::make('Coordinated Boxes')
                        ->description('Enter the quantity of each box type for this flight.')
                        ->columns(5)
                        ->schema([
                            TextInput::make('fb')
                                ->label('FB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->fb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateCalculations, $updateMissing): void {
                                    $updateCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateCalculations),

                            TextInput::make('hb')
                                ->label('HB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->hb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateCalculations, $updateMissing): void {
                                    $updateCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateCalculations),

                            TextInput::make('qb')
                                ->label('QB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->qb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateCalculations, $updateMissing): void {
                                    $updateCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateCalculations),

                            TextInput::make('eb')
                                ->label('EB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->eb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateCalculations, $updateMissing): void {
                                    $updateCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateCalculations),

                            TextInput::make('db')
                                ->label('DB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->db_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateCalculations, $updateMissing): void {
                                    $updateCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateCalculations),
                            TextInput::make('fulls')
                                ->label('FULLS')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default('0.000'),

                            TextInput::make('pieces')
                                ->label('PIECES')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default(0),
                        ])
                        ->columnSpanFull(),
                    Section::make('Received Boxes')
                        ->description('Enter the quantities actually received.')
                        ->columns(5)
                        ->schema([
                            TextInput::make('fb_r')
                                ->label('FB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->fb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateReceivedCalculations, $updateMissing): void {
                                    $updateReceivedCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateReceivedCalculations),

                            TextInput::make('hb_r')
                                ->label('HB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->hb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateReceivedCalculations, $updateMissing): void {
                                    $updateReceivedCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateReceivedCalculations),

                            TextInput::make('qb_r')
                                ->label('QB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->qb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateReceivedCalculations, $updateMissing): void {
                                    $updateReceivedCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateReceivedCalculations),

                            TextInput::make('eb_r')
                                ->label('EB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->eb_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateReceivedCalculations, $updateMissing): void {
                                    $updateReceivedCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateReceivedCalculations),

                            TextInput::make('db_r')
                                ->label('DB')
                                ->numeric()
                                ->rules(['regex:/^\d+$/'])
                                ->default(0)
                                ->minValue(0)
                                ->visible(fn () => (bool) $this->record->db_status)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set) use ($updateReceivedCalculations, $updateMissing): void {
                                    $updateReceivedCalculations($get, $set);
                                    $updateMissing($get, $set);
                                })
                                ->afterStateUpdated($updateReceivedCalculations),

                            TextInput::make('fulls_r')
                                ->label('FULLS')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default('0.000'),

                            TextInput::make('pieces_r')
                                ->label('PIECES')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default(0),
                        ])
                        ->columnSpanFull(),
                        TextInput::make('missing')
                            ->label('MISSING')
                            ->readOnly()
                            ->dehydrated(false)
                            ->default(0),

                        TextInput::make('returns')
                            ->label('RETURNS')
                            ->numeric()
                            ->rules(['regex:/^\d+$/'])
                            ->default(0)
                            ->minValue(0),

                ])
                ->action(function (array $data): void {

                    $flight = $this->record;

                    $allowedBoxes = [
                        'fb' => (bool) $flight->fb_status,
                        'hb' => (bool) $flight->hb_status,
                        'qb' => (bool) $flight->qb_status,
                        'eb' => (bool) $flight->eb_status,
                        'db' => (bool) $flight->db_status,

                        'fb_r' => (bool) $flight->fb_status,
                        'hb_r' => (bool) $flight->hb_status,
                        'qb_r' => (bool) $flight->qb_status,
                        'eb_r' => (bool) $flight->eb_status,
                        'db_r' => (bool) $flight->db_status,
                    ];

                    foreach ($allowedBoxes as $field => $allowed) {
                        if (! $allowed) {
                            $data[$field] = 0;
                        }
                    }

                    $data['flight_id'] = $flight->id;

                    FlightCoordination::create($data);
                }),
        ];
    }

    protected function getActions(): array
    {
        return [
            $this->editCoordinationAction(),
        ];
    }
}
