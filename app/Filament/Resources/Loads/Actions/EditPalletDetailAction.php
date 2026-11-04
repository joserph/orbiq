<?php

namespace App\Filament\Resources\Loads\Actions;

use App\Models\LoadCoordination;
use App\Models\PalletDetail;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditPalletDetailAction
{
    public static function make($page): Action
    {
        $load = $page->record;

        $boxFields = [];

        foreach (['fb' => 'FB', 'hb' => 'HB', 'qb' => 'QB', 'eb' => 'EB', 'db' => 'DB'] as $box => $label) {
            if ($load->{$box . '_status'}) {
                $boxFields[] = TextInput::make($box)
                    ->label($label)
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $set('total', collect(['fb', 'hb', 'qb', 'eb', 'db'])
                            ->sum(fn ($field) => (int) ($get($field) ?? 0)));
                    });
            }
        }

        return Action::make('editPalletDetail')
            ->label('Editar')
            ->icon('heroicon-o-pencil')
            ->color('warning')
            ->modalHeading('Editar coordinación')
            ->modalWidth('5xl')
            ->fillForm(function (array $arguments): array {
                $detail = PalletDetail::findOrFail($arguments['detailId']);

                return [
                    'farm_id' => $detail->farm_id,
                    'client_id' => $detail->client_id,
                    'fb' => $detail->fb,
                    'hb' => $detail->hb,
                    'qb' => $detail->qb,
                    'eb' => $detail->eb,
                    'db' => $detail->db,
                    'total' => $detail->quantity,
                    'piso' => $detail->piso,
                ];
            })
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('farm_id')
                            ->label('Finca')
                            ->options(
                                LoadCoordination::query()
                                    ->where('load_id', $load->id)
                                    ->with('farm')
                                    ->get()
                                    ->filter(fn ($coordination) => $coordination->farm)
                                    ->unique('farm_id')
                                    ->mapWithKeys(fn ($coordination) => [
                                        $coordination->farm_id => $coordination->farm->name,
                                    ])
                                    ->toArray()
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('client_id', null)),

                        Select::make('client_id')
                            ->label('Cliente')
                            ->options(fn (Get $get) => LoadCoordination::query()
                                ->where('load_id', $load->id)
                                ->where('farm_id', $get('farm_id'))
                                ->with('client')
                                ->get()
                                ->filter(fn ($coordination) => $coordination->client)
                                ->unique('client_id')
                                ->mapWithKeys(fn ($coordination) => [
                                    $coordination->client_id => $coordination->client->name,
                                ])
                                ->toArray())
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get) => blank($get('farm_id')))
                            ->live(),
                    ])
                    ->columns(1),

                Section::make('Cajas')
                    ->schema([
                        ...$boxFields,
                        TextInput::make('total')
                            ->label('Total')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make()
                    ->schema([
                        Checkbox::make('piso')
                            ->label('Piso'),
                    ]),
            ])
            ->action(function (array $data, array $arguments) use ($page): void {

                DB::transaction(function () use ($data, $arguments, $page): void {

                    $detail = PalletDetail::query()
                        ->lockForUpdate()
                        ->findOrFail($arguments['detailId']);

                    $coordination = LoadCoordination::query()
                        ->where('load_id', $page->record->id)
                        ->where('farm_id', $data['farm_id'])
                        ->where('client_id', $data['client_id'])
                        ->lockForUpdate()
                        ->firstOrFail();
                    
                    $activeBoxes = collect(['fb', 'hb', 'qb', 'eb', 'db'])
                        ->filter(fn ($box) => $page->record->{$box . '_status'});

                    $totalReceived = $activeBoxes->sum(
                        fn ($box) => (int) $coordination->{$box . '_r'}
                    );

                    if ($totalReceived <= 0) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede agregar la coordinación')
                            ->body('Esta finca y cliente no tienen cajas recibidas en esta carga.')
                            ->persistent()
                            ->send();

                        return;
                    }

                    if (
                        PalletDetail::query()
                            ->where('pallet_id', $detail->pallet_id)
                            ->where('load_coordination_id', $coordination->id)
                            ->where('id', '!=', $detail->id)
                            ->exists()
                    ) {
                        Notification::make()
                            ->danger()
                            ->title('Coordinación duplicada')
                            ->body('Esta finca y cliente ya están agregados a esta paleta.')
                            ->persistent()
                            ->send();

                        return;
                    }


                    $allocated = PalletDetail::query()
                        ->where('load_coordination_id', $coordination->id)
                        ->where('id', '!=', $detail->id)
                        ->selectRaw('
                            COALESCE(SUM(fb), 0) as fb,
                            COALESCE(SUM(hb), 0) as hb,
                            COALESCE(SUM(qb), 0) as qb,
                            COALESCE(SUM(eb), 0) as eb,
                            COALESCE(SUM(db), 0) as db
                        ')
                        ->first();

                    $boxes = [];

                    foreach (['fb', 'hb', 'qb', 'eb', 'db'] as $box) {
                        if (! $page->record->{$box . '_status'}) {
                            $boxes[$box] = 0;
                            continue;
                        }

                        $received = (int) $coordination->{$box . '_r'};
                        $alreadyAllocated = (int) $allocated->{$box};
                        $requested = (int) ($data[$box] ?? 0);

                        // if ($requested > ($received - $alreadyAllocated)) {
                        //     throw ValidationException::withMessages([
                        //         $box => 'Solo quedan '
                        //             . max(0, $received - $alreadyAllocated)
                        //             . " cajas disponibles de {$box}.",
                        //     ]);
                        // }
                        $available = max(0, $received - $alreadyAllocated);

                        if ($requested > $available) {
                            Notification::make()
                                ->danger()
                                ->title('Cantidad de cajas excedida')
                                ->body("Solo tienes {$available} cajas {$box} disponibles. Has ingresado {$requested}.")
                                ->persistent()
                                ->send();

                            return;
                        }

                        $boxes[$box] = $requested;
                    }

                    $detail->update([
                        'load_coordination_id' => $coordination->id,
                        'farm_id' => $coordination->farm_id,
                        'client_id' => $coordination->client_id,
                        ...$boxes,
                        'piso' => (bool) ($data['piso'] ?? false),
                    ]);
                });
            });
    }
}
