<?php

namespace App\Reports;

use App\Models\Flight;
use App\Models\Load;
use Barryvdh\DomPDF\Facade\Pdf;

class LoadCompletePdf
{
    public static function generate(Load $load)
    {
        $data = LoadCompleteReport::data($load);
        // dd($data);
        return Pdf::loadView(
            'reports.loads.complete',
            $data
        )
            ->setPaper('a4', 'landscape');
    }

    // public static function download(Flight $flight)
    // {
    //     $pdf = self::generate($flight);

    //     return $pdf->download(
    //         'flight-coordination-' . $flight->awb . '.pdf'
    //     );
    // }

    public static function stream(Load $load)
    {
        $pdf = self::generate($load);

        return $pdf->stream(
            'load-coordination-' . $load->awb . '.pdf'
        );
    }
}