<?php

namespace App\Reports;

use App\Models\Flight;
use Barryvdh\DomPDF\Facade\Pdf;

class FlightCompletePdf
{
    public static function generate(Flight $flight)
    {
        $data = FlightCompleteReport::data($flight);
        // dd($data);
        return Pdf::loadView(
            'reports.flights.complete',
            $data
        )
            ->setPaper('a4', 'landscape');
    }

    public static function download(Flight $flight)
    {
        $pdf = self::generate($flight);

        return $pdf->download(
            'flight-coordination-' . $flight->awb . '.pdf'
        );
    }

    public static function stream(Flight $flight)
    {
        $pdf = self::generate($flight);

        return $pdf->stream(
            'flight-coordination-' . $flight->awb . '.pdf'
        );
    }
}