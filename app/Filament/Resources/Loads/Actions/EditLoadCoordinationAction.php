<?php

namespace App\Filament\Resources\Loads\Actions;

use App\Models\Client;
use App\Models\Commercializer;
use App\Models\Farm;
use App\Models\LoadCoordination;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rule;

class EditLoadCoordinationAction
{
    public static function make(): Action
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

            $fulls = $fb + ($hb * 0.5) + ($qb * 0.25)
                + ($eb * 0.125) + ($db * 0.0625);

            $pieces = $fb + $hb + $qb + $eb + $db;

            $fullsR = $fbR + ($hbR * 0.5) + ($qbR * 0.25)
                + ($ebR * 0.125) + ($dbR * 0.0625);

            $piecesR = $fbR + $hbR + $qbR + $ebR + $dbR;

            $set('fulls', number_format($fulls, 3, '.', ''));
            $set('pieces', $pieces);
            $set('fulls_r', number_format($fullsR, 3, '.', ''));
            $set('pieces_r', $piecesR);
            $set('missing', max($pieces - $piecesR, 0));
        };

        return Action::make('editCoordination')
            ->label('Editar')
            ->icon('heroicon-o-pencil-square')
            ->modalHeading('Editar coordinación marítima')
            ->modalWidth('7xl')
            ->schema([
                Section::make('Información de la coordinación')
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(5)
                    ->schema([
                        Select::make('farm_id')
                            ->label('Finca')
                            ->options(
                                Farm::query()->orderBy('name')->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('varieties', []))
                            ->columnSpan(2),

                        TextInput::make('hawb')
                            ->label('HAWB')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                fn ($record) => Rule::unique(
                                    'load_coordinations',
                                    'hawb'
                                )
                                    ->where('load_id', $record?->load_id)
                                    ->ignore($record?->id),
                            ]),

                        Select::make('client_id')
                            ->label('Cliente')
                            ->options(
                                Client::query()->orderBy('name')->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Select::make('marketer_id')
                            ->label('Comercializadora')
                            ->options(
                                Commercializer::query()->orderBy('name')->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->native(false),

                        CheckboxList::make('varieties')
                            ->label('Variedades de flores')
                            ->required()
                            ->options(function ($get): array {
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
                            ->live()
                            ->disabled(fn ($get): bool => ! $get('farm_id')),
                    ]),

                Section::make('Cajas coordinadas')
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(7)
                    ->schema([
                        TextInput::make('fb')
                            ->label('FB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->fb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('hb')
                            ->label('HB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->hb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('qb')
                            ->label('QB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->qb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('eb')
                            ->label('EB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->eb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('db')
                            ->label('DB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->db_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('fulls')
                            ->label('FULLS')
                            ->readOnly()
                            ->dehydrated(false)
                            ->default('0.000'),

                        TextInput::make('pieces')
                            ->label('PIEZAS')
                            ->readOnly()
                            ->dehydrated(false)
                            ->default(0),
                    ])
                    ->columnSpanFull(),

                Section::make('Cajas recibidas')
                    ->extraAttributes(['class' => 'mi-seccion-compacta'])
                    ->columns(8)
                    ->secondary()
                    ->schema([
                        TextInput::make('fb_r')
                            ->label('FB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->fb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('hb_r')
                            ->label('HB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->hb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('qb_r')
                            ->label('QB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->qb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('eb_r')
                            ->label('EB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->eb_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('db_r')
                            ->label('DB')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn ($livewire) => (bool) $livewire->record->db_status)
                            ->live()
                            ->afterStateUpdated($updateCalculations),

                        TextInput::make('fulls_r')
                            ->label('FULLS')
                            ->readOnly()
                            ->dehydrated(false)
                            ->default('0.000'),

                        TextInput::make('pieces_r')
                            ->label('PIEZAS')
                            ->readOnly()
                            ->dehydrated(false)
                            ->default(0),

                        TextInput::make('returns')
                            ->label('Retornos')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columnSpanFull(),

                Textarea::make('observation')
                    ->label('Observaciones')
                    ->rows(1)
                    ->columnSpanFull(),
            ])
            ->fillForm(function (array $arguments): array {
                $coordination = LoadCoordination::findOrFail(
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

                    'fulls' => $coordination->fulls,
                    'pieces' => $coordination->pieces,

                    'fb_r' => $coordination->fb_r,
                    'hb_r' => $coordination->hb_r,
                    'qb_r' => $coordination->qb_r,
                    'eb_r' => $coordination->eb_r,
                    'db_r' => $coordination->db_r,

                    'fulls_r' => $coordination->fulls_r,
                    'pieces_r' => $coordination->pieces_r,
                    'returns' => $coordination->returns,
                    'observation' => $coordination->observation,
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $coordination = LoadCoordination::findOrFail(
                    $arguments['coordination']
                );

                $load = $coordination->shipmentLoad;

                $allowedBoxes = [
                    'fb' => (bool) $load->fb_status,
                    'hb' => (bool) $load->hb_status,
                    'qb' => (bool) $load->qb_status,
                    'eb' => (bool) $load->eb_status,
                    'db' => (bool) $load->db_status,

                    'fb_r' => (bool) $load->fb_status,
                    'hb_r' => (bool) $load->hb_status,
                    'qb_r' => (bool) $load->qb_status,
                    'eb_r' => (bool) $load->eb_status,
                    'db_r' => (bool) $load->db_status,
                ];

                foreach ($allowedBoxes as $field => $allowed) {
                    if (! $allowed) {
                        $data[$field] = 0;
                    }
                }

                $coordination->update($data);
            });
    }
}
