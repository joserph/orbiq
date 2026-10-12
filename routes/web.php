<?php

use App\Exports\FlightCompleteExport;
use App\Models\Company;
use App\Models\Flight;

use App\Reports\FlightCompletePdf;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/reports/flights/{flight}/complete', function (Flight $flight) {
//     return FlightCompletePdf::stream($flight);
// })->name('reports.flight.complete');
Route::get('/reports/flights/{flight}/complete', function (\App\Models\Flight $flight) {
    return \App\Reports\FlightCompletePdf::stream($flight);
})->name('reports.flight.complete');
// Route::get('/reports/flights/{flight}/complete', function (Flight $flight) {

//     $company = Company::first();

//     return view('reports.flights.complete', [
//         'flight' => $flight,
//         'company' => $company,
//     ]);

// })->name('reports.flight.complete');

// Route::get('/reports/flights/{flight}/complete-excel', function (Flight $flight) {

//     return Excel::download(
//         new FlightCompleteExport($flight),
//         'flight-' . $flight->awb . '-complete.xlsx'
//     );

// })->name('reports.flight.complete.excel');

Route::get('/reports/flights/{flight}/complete-excel', function (Flight $flight) {

    return (new \App\Exports\FlightCompleteExport($flight))->download();

})->name('reports.flight.complete.excel');