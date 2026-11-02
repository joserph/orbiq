<?php

namespace App\Filament\Resources\Loads\Pages;

use App\Filament\Resources\Loads\Actions\CreateLoadCoordinationAction;
use App\Filament\Resources\Loads\Actions\DeleteLoadCoordinationAction;
use App\Filament\Resources\Loads\Actions\EditLoadCoordinationAction;
use App\Filament\Resources\Loads\LoadResource;
use App\Models\Client;
use App\Models\Farm;
use App\Models\Load;
use App\Models\LoadCoordination;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Database\Eloquent\Builder;

class LoadCoordinations extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LoadResource::class;

    protected string $view =
        'filament.resources.loads.pages.load-coordinations';

    public string $search = '';

    public array $selectedCoordinations = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Coordinaciones marítimas';
    }

    public function getCoordinations()
    {
        // return LoadCoordination::query()
        //     ->with(['client', 'farm', 'marketer'])
        //     ->where('load_id', $this->record->id)
        //     ->when(trim($this->search) !== '', function (Builder $query) {
        //         $search = trim($this->search);

        //         $query->where(function (Builder $query) use ($search) {
        //             $query->where('hawb', 'like', "%{$search}%")
        //                 ->orWhereHas('client', function (Builder $query) use ($search) {
        //                     $query->where('name', 'like', "%{$search}%");
        //                 })
        //                 ->orWhereHas('farm', function (Builder $query) use ($search) {
        //                     $query->where('name', 'like', "%{$search}%");
        //                 });
        //         });
        //     })
        //     ->orderBy('hawb');
        $query = \App\Models\LoadCoordination::query()
            ->with([
                'client',
                'farm',
                'marketer',
            ])
            ->where('load_id', $this->record->id);

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
                    ->whereColumn('farms.id', 'load_coordinations.farm_id')
            )
            ->get();
    }

    public function getCoordinationSummary()
    {
        // dd('algo');
        // $query = LoadCoordination::query()
        //     ->where('load_id', $this->record->id);

        // return [
        //     'coordinations' => (clone $query)->count(),
        //     'pieces' => (clone $query)->sum('pieces'),
        //     'pieces_r' => (clone $query)->sum('pieces_r'),
        //     'fulls' => (clone $query)->sum('fulls'),
        //     'fulls_r' => (clone $query)->sum('fulls_r'),
        //     'missing' => (clone $query)->sum('missing'),
        //     'returns' => (clone $query)->sum('returns'),
        // ];
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

    public function createCoordinationAction(): Action
    {
        return \App\Filament\Resources\Loads\Actions\CreateLoadCoordinationAction::make($this);
    }


    public function editCoordinationAction(): Action
    {
        return EditLoadCoordinationAction::make();
    }

    public function deleteCoordinationAction(): Action
    {
        return DeleteLoadCoordinationAction::make();
    }


    public function getCoordinationsByClient()
    {
        // return $this->getCoordinations()
        //     ->get()
        //     ->groupBy('client_id')
        //     ->sortBy(function ($coordinations) {
        //         return $coordinations->first()?->client?->name ?? '';
        //     });
        return $this->getCoordinations()
            ->groupBy('client_id')
            ->sortBy(function ($coordinations) {
                return $coordinations->first()->client->name ?? '';
            });
    }

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

    public function transferSelectedCoordinations(array $data): void
    {
        $selectedIds = collect($this->selectedCoordinations)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        
        if ($selectedIds->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('No hay coordinaciones seleccionadas')
                ->send();

            return;
        }

        $destinationLoadId = (int) ($data['destination_load_id'] ?? 0);

        if (
            $destinationLoadId === (int) $this->record->id ||
            ! \App\Models\Load::query()->whereKey($destinationLoadId)->exists()
        ) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('BL de destino no válido')
                ->body('Selecciona un marítimo diferente al actual.')
                ->send();

            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $selectedIds,
                $destinationLoadId
            ) {
                $coordinations = \App\Models\LoadCoordination::query()
                    ->where('load_id', $this->record->id)
                    ->whereIn('id', $selectedIds)
                    ->lockForUpdate()
                    ->get();

                if ($coordinations->count() !== $selectedIds->count()) {
                    throw new \RuntimeException(
                        'Una o más coordinaciones ya no pertenecen al BL de origen.'
                    );
                }

                \App\Models\LoadCoordination::query()
                    ->where('load_id', $this->record->id)
                    ->whereIn('id', $selectedIds)
                    ->update([
                        'load_id' => $destinationLoadId,
                    ]);
            });

            $this->selectedCoordinations = [];
            
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Transferencia completada')
                ->body('Las coordinaciones se trasladaron al BL de destino.')
                ->send();

        } catch (\Throwable $exception) {
            report($exception);

            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('No se pudo completar la transferencia')
                ->body('No se modificaron las coordinaciones. Verifica los datos e inténtalo nuevamente.')
                ->send();
        }
    }
    
    public function getAvailableLoadsForTransfer(): array
    {
        return \App\Models\Load::query()
            ->where('id', '!=', $this->record->id)
            ->orderBy('date', 'desc')
            ->get()
            ->mapWithKeys(function ($load) {
                $label = $load->bl;

                if ($load->date) {
                    $label .= ' - ' . $load->date->format('d/m/Y');
                }

                return [$load->id => $label];
            })
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            // BOTON DE CREAR NUEVA COORDINACION
            CreateLoadCoordinationAction::make($this)
                ->color('info'),
            ActionGroup::make([
                // PDF COMPLETO
                Action::make('completePdf')
                    ->label('PDF completo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->url(fn () => route('reports.load.complete', [
                        'load' => $this->record->id,
                    ]))
                    ->visible(fn () => count($this->getClientsForReport()) >= 1)
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
                        $url = route('reports.load.client.pdf', [
                            'load' => $this->record->id,
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
                        $url = route('reports.load.selected-clients.pdf', [
                            'load' => $this->record->id,
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
                        'reports.load.complete.excel',
                        ['load' => $this->record->id]
                    ))
                    ->visible(fn () => count($this->getClientsForReport()) >= 1)
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
                        $url = route('reports.load.client.excel', [
                            'load' => $this->record->id,
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
                        $url = route('reports.load.selected-clients.excel', [
                            'load' => $this->record->id,
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
            // TRANSFERENCIA DE COORDINACION
            Action::make('transferCoordinations')
                ->label('Transferir coordinaciones')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Select::make('destination_load_id')
                        ->label('BL de destino')
                        ->options(fn () => $this->getAvailableLoadsForTransfer())
                        ->searchable()
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Confirmar transferencia')
                ->modalDescription(
                    'Las coordinaciones seleccionadas se moverán al BL de destino. Desaparecerán del vuelo actual y conservarán sus demás datos.'
                )
                ->modalSubmitActionLabel('Sí, transferir')
                ->action(fn (array $data) => $this->transferSelectedCoordinations($data))
                ->visible(fn () => count($this->selectedCoordinations) > 0),
        ];
        
    }
}
