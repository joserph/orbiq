<?php

namespace App\Filament\Resources\Loads\Pages;

use App\Filament\Resources\Loads\LoadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ListLoads extends ListRecords
{
    protected static string $resource = LoadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalWidth('7xl')
                ->using(function (array $data): \App\Models\Load {
                    return DB::transaction(function () use ($data) {
                        $date = $data['date'] ?? null;

                        if (! $date) {
                            throw ValidationException::withMessages([
                                'date' => 'La fecha del embarque es obligatoria.',
                            ]);
                        }

                        $year = (int) date('Y', strtotime($date));

                        DB::table('load_shipment_sequences')
                            ->insertOrIgnore([
                                'year' => $year,
                                'last_number' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                        $sequence = DB::table('load_shipment_sequences')
                            ->where('year', $year)
                            ->lockForUpdate()
                            ->first();

                        $nextNumber = $sequence->last_number + 1;

                        DB::table('load_shipment_sequences')
                            ->where('year', $year)
                            ->update([
                                'last_number' => $nextNumber,
                                'updated_at' => now(),
                            ]);

                        $data['shipment_year'] = $year;
                        $data['shipment'] = (string) $nextNumber;

                        return \App\Models\Load::create($data);
                    }, attempts: 5);
                }),
            
        ];
    }
}
