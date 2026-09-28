<?php

namespace App\Filament\Resources\FlightCoordinations\Schemas;

use App\Models\Client;
use App\Models\Commercializer;
use App\Models\Farm;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FlightCoordinationForm
{
    public static function configure(Schema $schema): Schema
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
        return $schema
            ->components([
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
                    ->options(function () {
                        return \App\Models\FlowerVariety::query()
                            ->where('status', true)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->columns(3)
                    ->bulkToggleable(),

                // ==========================================
                // COORDINATED
                // ==========================================

                TextInput::make('fb')
                    ->label('FB')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('hb')
                    ->label('HB')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('qb')
                    ->label('QB')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('eb')
                    ->label('EB')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('db')
                    ->label('DB')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
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

                // ==========================================
                // RECEIVED
                // ==========================================

                TextInput::make('fb_r')
                    ->label('FB Received')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('hb_r')
                    ->label('HB Received')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('qb_r')
                    ->label('QB Received')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('eb_r')
                    ->label('EB Received')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('db_r')
                    ->label('DB Received')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->afterStateUpdated($updateCalculations),

                TextInput::make('fulls_r')
                    ->label('FULLS Received')
                    ->readOnly()
                    ->dehydrated(false)
                    ->default('0.000'),

                TextInput::make('pieces_r')
                    ->label('PIECES Received')
                    ->readOnly()
                    ->dehydrated(false)
                    ->default(0),

                // ==========================================
                // RETURNS / MISSING
                // ==========================================

                TextInput::make('returns')
                    ->label('Returns')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0),

                TextInput::make('missing')
                    ->label('MISSING')
                    ->readOnly()
                    ->dehydrated(false)
                    ->default(0),

                Textarea::make('observation')
                    ->label('Observation')
                    ->rows(3)
                    ->columnSpanFull(),
                // Select::make('flight_id')
                //     ->relationship('flight', 'id')
                //     ->required(),
                // TextInput::make('fb')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('hb')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('qb')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('eb')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('db')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('fulls')
                //     ->numeric(),
                // TextInput::make('pieces')
                //     ->numeric(),
                // TextInput::make('fb_r')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('hb_r')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('qb_r')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('eb_r')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('db_r')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('fulls_r')
                //     ->numeric(),
                // TextInput::make('pieces_r')
                //     ->numeric(),
                // TextInput::make('returns')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('missing')
                //     ->numeric(),
                // Select::make('client_id')
                //     ->relationship('client', 'name')
                //     ->required(),
                // Select::make('farm_id')
                //     ->relationship('farm', 'name')
                //     ->required(),
                // Select::make('marketer_id')
                //     ->relationship('marketer', 'name'),
                // TextInput::make('varieties'),
                // Textarea::make('observation')
                //     ->columnSpanFull(),
                // TextInput::make('created_by')
                //     ->numeric(),
                // TextInput::make('updated_by')
                //     ->numeric(),
            ]);
    }
}
