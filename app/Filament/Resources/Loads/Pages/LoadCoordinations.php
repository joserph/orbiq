<?php

namespace App\Filament\Resources\Loads\Pages;

use App\Filament\Resources\Loads\Actions\CreateLoadCoordinationAction;
use App\Filament\Resources\Loads\Actions\DeleteLoadCoordinationAction;
use App\Filament\Resources\Loads\Actions\EditLoadCoordinationAction;
use App\Filament\Resources\Loads\LoadResource;
use App\Models\Farm;
use App\Models\Load;
use App\Models\LoadCoordination;
use Filament\Actions\Action;
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


    protected function getHeaderActions(): array
    {
        return [
            // BOTON DE CREAR NUEVA COORDINACION
            CreateLoadCoordinationAction::make($this)
                ->color('info')
        ];
        
    }
}
