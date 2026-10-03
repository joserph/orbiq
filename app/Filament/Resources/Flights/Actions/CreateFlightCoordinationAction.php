<?php

namespace App\Filament\Resources\Flights\Actions;

use App\Models\Client;
use App\Models\Commercializer;
use App\Models\Farm;
use App\Models\FlowerVariety;
use App\Models\FlightCoordination;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\Flights\Pages\FlightCoordinations;

class CreateFlightCoordinationAction
{
    public static function make(FlightCoordinations $page): Action
    {
        $updateCalculations = function (callable $get, callable $set): void {
            $fb = (int) ($get('fb') ?? 0);
            $hb = (int) ($get('hb') ?? 0);
            $qb = (int) ($get('qb') ?? 0);
            $eb = (int) ($get('eb') ?? 0);
            $db = (int) ($get('db') ?? 0);

            $fbR = (int) ($get('fb_r') ?? 0);
            $hbR = (int) ($get('hb_r') ?? 0);
            $qbR = (int) ($get('qb_r') ?? 0);
            $ebR = (int) ($get('eb_r') ?? 0);
            $dbR = (int) ($get('db_r') ?? 0);

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

            $fullsR =
                ($fbR * 1) +
                ($hbR * 0.5) +
                ($qbR * 0.25) +
                ($ebR * 0.125) +
                ($dbR * 0.0625);

            $piecesR =
                $fbR +
                $hbR +
                $qbR +
                $ebR +
                $dbR;

            $missing = $pieces - $piecesR;

            $set('fulls', number_format($fulls, 3, '.', ''));
            $set('pieces', $pieces);
            $set('fulls_r', number_format($fullsR, 3, '.', ''));
            $set('pieces_r', $piecesR);
            $set('missing', $missing);
        };


        return Action::make('createCoordination')
            ->label('New Coordination')
            ->icon('heroicon-o-plus')
            ->modalHeading('New Flight Coordination')
            ->modalWidth('7xl')
            ->schema([
                Section::make('Coordinated info')
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(5)
                    ->schema([
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
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('varieties', []);
                            })
                            ->columnSpan(2),
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
                            ->native(false)
                            ->columnSpan(2),
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
                            ->required()
                            ->options(function ($get) {
                                $farmId = $get('farm_id');
                                if (! $farmId) {
                                    return [];
                                }
                                return Farm::find($farmId)
                                    ?->flowerVarieties()
                                    ->where('flower_varieties.status', true)
                                    ->orderBy('flower_varieties.name')
                                    ->pluck(
                                        'flower_varieties.name',
                                        'flower_varieties.id'
                                    )
                                    ->toArray() ?? [];
                            })
                            ->columns()
                            ->bulkToggleable()
                            ->live(),
                        ]),
                // =====================================================
                // COORDINATED BOXES
                // =====================================================
                Section::make('Coordinated Boxes')
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(8)
                    ->schema([
                        TextInput::make('fb')
                            ->label('FB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->fb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('hb')
                            ->label('HB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->hb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('qb')
                            ->label('QB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->qb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('eb')
                            ->label('EB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->eb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('db')
                            ->label('DB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->db_status)
                            ->live()
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
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(9)
                    ->secondary()
                    ->schema([
                        TextInput::make('fb_r')
                            ->label('FB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->fb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('hb_r')
                            ->label('HB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->hb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('qb_r')
                            ->label('QB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->qb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('eb_r')
                            ->label('EB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->eb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
                        TextInput::make('db_r')
                            ->label('DB')
                            ->numeric()
                            ->columnSpan(2)
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn () => (bool) $page->record->db_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),
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
                        TextInput::make('returns')
                            ->label('Returns')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columnSpanFull(),
                Textarea::make('observation')
                    ->label('Observation')
                    ->rows(1)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) use ($page): void {

                $flight = $page->record;

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

                $data['flight_id'] = $flight->id;

                FlightCoordination::create($data);
            });
    }
}
