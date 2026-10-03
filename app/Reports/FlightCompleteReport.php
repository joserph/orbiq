<?php

namespace App\Reports;

use App\Models\Company;
use App\Models\Flight;
use Illuminate\Support\Collection;

class FlightCompleteReport
{
    /**
     * Prepare all data required by the Complete Flight Report.
     */
    public static function data(Flight $flight): array
    {
        $coordinations = $flight->coordinations()
            ->with([
                'client',
                'farm',
            ])
            ->get();

        /*
         * ---------------------------------------------------------
         * ACTIVE BOX TYPES
         * ---------------------------------------------------------
         */

        $activeBoxes = collect([
            'fb' => $flight->fb_status,
            'hb' => $flight->hb_status,
            'qb' => $flight->qb_status,
            'eb' => $flight->eb_status,
            'db' => $flight->db_status,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();


        /*
         * ---------------------------------------------------------
         * PREPARE COORDINATIONS
         * ---------------------------------------------------------
         */

        $coordinations = $coordinations->map(function ($coordination) {

            /*
             * Flower varieties
             *
             * The varieties are stored in the coordination.
             * We convert them into readable names for the report.
             */

            $varietyNames = [];

            if (is_array($coordination->varieties)) {

                $varietyNames = \App\Models\FlowerVariety::query()
                    ->whereIn('id', $coordination->varieties)
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            }

            $coordination->variety_names = implode(', ', $varietyNames);

            return $coordination;
        });


        /*
         * ---------------------------------------------------------
         * GROUP BY COMMERCIALIZER
         * ---------------------------------------------------------
         *
         * A coordination belongs to a client and the client can
         * have commercializers.
         *
         * For the report we use the commercializer assigned to
         * the coordination when available.
         */

        $grouped = $coordinations
            ->sortBy(function ($coordination) {

                return $coordination->client?->name ?? '';

            })
            ->groupBy(function ($coordination) {

                return $coordination->client?->name
                    ?? 'WITHOUT COMMERCIALIZER';

            });


        /*
         * ---------------------------------------------------------
         * GLOBAL TOTALS
         * ---------------------------------------------------------
         */

        $totals = [
            'coordinated' => [],
            'received' => [],
        ];

        foreach ($activeBoxes as $box) {

            $totals['coordinated'][$box] = $coordinations->sum(
                fn ($coordination) => (float) ($coordination->{$box} ?? 0)
            );

            $receivedField = $box . '_r';

            $totals['received'][$box] = $coordinations->sum(
                fn ($coordination) => (float) ($coordination->{$receivedField} ?? 0)
            );
        }


        $totals['coordinated']['fulls'] = $coordinations->sum(
            fn ($coordination) => (float) ($coordination->fulls ?? 0)
        );

        $totals['received']['fulls'] = $coordinations->sum(
            fn ($coordination) => (float) ($coordination->fulls_r ?? 0)
        );

        /*
         * ---------------------------------------------------------
         * LOGO
         * ---------------------------------------------------------
         */
        $company = Company::first();

        /*
         * ---------------------------------------------------------
         * RETURN DATA
         * ---------------------------------------------------------
         */

        return [
            'flight' => $flight,

            'coordinations' => $grouped,

            'activeBoxes' => $activeBoxes,

            'totals' => $totals,

            'company' => $company
        ];
    }
}