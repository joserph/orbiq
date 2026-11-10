<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FlowerVariety;
use App\Models\Load;
use App\Models\LoadCoordination;
use App\Models\Pallet;
use App\Models\PalletDetail;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerLoadReport
{
    public function download(Load $load)
    {
        $coordinated = (int) LoadCoordination::where('load_id', $load->id)
            ->sum('pieces');

        $palletized = (int) PalletDetail::whereHas('pallet', function ($query) use ($load) {
            $query->where('load_id', $load->id);
        })->sum('quantity');

        if ($coordinated <= 0 || $palletized !== $coordinated) {
            abort(403, 'La carga todavía no está completamente palletizada.');
        }

        $spreadsheet = $this->generate($load);

        $filename = 'INFORMES_CLIENTES_(' . $load->shipment . ')_BL ' . $load->bl . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename
        );
    }

    public function generate(Load $load): Spreadsheet
    {
        $load->load([
            'logisticsCompany',
            'coordinations.client',
            'coordinations.farm',
            'pallets.details.client',
            'pallets.details.farm',
            'pallets.details.loadCoordination',
            'pallets.details.pallet',
        ]);
        $myCompany = Company::with('country', 'state', 'city')->first();
        
        // $coordinations = $load->coordinations;
        $coordinations = $load->coordinations()
            ->join('farms', 'farms.id', '=', 'load_coordinations.farm_id')
            ->orderBy('farms.name', 'asc')
            ->select('load_coordinations.*')
            ->get();

        $details = $load->pallets
            ->flatMap(fn ($pallet) => $pallet->details);

        // Obtener los IDs de variedades guardados en las coordinaciones.
        $varietyIds = $coordinations
            ->flatMap(fn ($coordination) => $coordination->varieties ?? [])
            ->unique()
            ->values();

        // Convertir los IDs a nombres de variedades.
        $varieties = FlowerVariety::whereIn('id', $varietyIds)
            ->pluck('name', 'id');

        // Clientes presentes en coordinaciones o pallets.
        $clients = $coordinations
            ->pluck('client')
            ->merge($details->pluck('client'))
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $spreadsheet = new Spreadsheet();

        if ($clients->isEmpty()) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Sin clientes');
            $sheet->setCellValue('A1', 'No existen clientes para esta carga.');
            return $spreadsheet;
        }
        

        foreach ($clients as $index => $client) {
            $sheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $sheet->setTitle($this->uniqueSheetTitle(
                $client->name ?? 'Cliente',
                $spreadsheet
            ));

            $clientCoordinations = $coordinations
                ->where('client_id', $client->id)
                ->values();

            $clientDetails = $details
                ->where('client_id', $client->id)
                ->values();
            
            // $palletsDetails = PalletDetail::whereHas('pallet', function ($query) use ($load) {
            //     $query->where('load_id', $load->id);
            // })
            // ->where('client_id', $client->id)
            // ->with('pallet')
            // ->get();
            $palletsDetails = PalletDetail::whereHas('pallet', function ($query) use ($load) {
                $query->where('load_id', $load->id);
            })
            ->where('client_id', $client->id)
            ->with('pallet')
            ->get()
            ->groupBy('pallet_id')
            ->map(function ($group) {
                $merged = $group->first()->replicate();
                $merged->fb = $group->sum('fb');
                $merged->hb = $group->sum('hb');
                $merged->qb = $group->sum('qb');
                $merged->eb = $group->sum('eb');
                $merged->db = $group->sum('db');
                $merged->quantity = $group->sum('quantity');
                return $merged;
            })
            ->values();
            // dd($palletsDetails);

            $this->buildClientSheet(
                $sheet,
                $load,
                $client,
                $clientCoordinations,
                $clientDetails,
                $varieties,
                $myCompany,
                $palletsDetails
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildClientSheet(
        $sheet,
        Load $load,
        $client,
        Collection $coordinations,
        Collection $details,
        Collection $varieties,
        $myCompany,
        $palletsDetails
    ): void {
        
        $activeBoxes = collect([
            'fb' => $load->fb_status,
            'hb' => $load->hb_status,
            'qb' => $load->qb_status,
            'eb' => $load->eb_status,
            'db' => $load->db_status,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
        // ANCHO COLUMNA 
        $sheet->getColumnDimension('A')->setWidth(33);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $column = 'G';
        foreach ($activeBoxes as $box) {
            $sheet->getColumnDimension("{$column}")->setWidth(10);
            $column++;
        }
        $sheet->getColumnDimension("{$column}")->setWidth(17);

        // STYLES LETRA BLANCA, FONDO AZUL, BORDE MEDIO NEGRO
        $infoStyleBlue = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 18,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '17324D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        // STYLES LETRA NEGRA, ALINEACION CENTER, CENTER
        $infoStyleAlignmentCenter = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 18,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
        // STYLES LETRA NEGRA, FONDO VERDE, BORDER MEDIO NEGRO,  ALINEACION CENTER CENTER.
        $infoStyleGreenClient = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 15,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'EAF3F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        // STYLES LETRA BLANCA, FONDO VERDE, BORDER MEDIO NEGRO,  ALINEACION LEFT CENTER.
        $infoStyleGreen = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '168C9A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        // STYLES BORDE DELGADO NEGRO, FONDO GRIS
        $styleArrayBorderGrey = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'D5E1E8'],
            ],
        ];
        // STYLES BORDE DELGADO NEGRO
        $styleArrayBorderThick = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        // STYLES BORDE MEDIO NEGRO, FONDO BLANCO
        $styleArrayBorderBlack_FillWhite = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // O BORDER_THIN si lo quieres delgado
                    'color' => ['rgb' => '000000'], // Negro puro
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];

        $styleArrayBorderBlack_fillLightBlue = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 12,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // O BORDER_THIN si lo quieres delgado
                    'color' => ['rgb' => '000000'], // Negro puro
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'DCEEF1'],
            ],
        ];
         // STYLES BORDE MEDIO NEGRO
        $styleArrayBorderBlackMedium = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // O BORDER_THIN si lo quieres delgado
                    'color' => ['rgb' => '000000'], // Negro puro
                ],
            ],
        ];

        $textAlignmentCenter = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                // 'wrapText' => true,
            ],
        ];
        // EMPEZAMOS EL EXCEL
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'CUSTOMER LOAD REPORT');
        $sheet->getStyle('A1:J1')
            ->applyFromArray($infoStyleBlue);
        $sheet->getRowDimension(1)->setRowHeight(34);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', $client->name);
        $sheet->getStyle('A2:J2')
            ->applyFromArray($infoStyleGreenClient);
        $sheet->getRowDimension(2)->setRowHeight(25);

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'SHIPPER');
        $sheet->getStyle('A3:E3')
            ->applyFromArray($infoStyleGreen);

        $sheet->mergeCells('F3:J3');
        $sheet->setCellValue('F3', 'BL N°');
        $sheet->getStyle('F3:J3')
            ->applyFromArray($infoStyleGreen);

        for ($i = 3; $i <= 16; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        $sheet->mergeCells('A4:E4');
        $sheet->setCellValue('A4', $load->logisticsCompany?->name);
        $sheet->getStyle('A4:E7')
            ->applyFromArray($styleArrayBorderBlack_FillWhite);

        $sheet->mergeCells('F4:J5');
        $sheet->setCellValue('F4', $load->bl);
        $sheet->getStyle('F4:J5')
            ->applyFromArray($infoStyleAlignmentCenter);
        $sheet->getStyle('F4:J7')
            ->applyFromArray($styleArrayBorderThick);

        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', 'RUC: ' . $load->logisticsCompany?->ruc);
        
        $sheet->mergeCells('A6:E6');
        $sheet->setCellValue('A6', 'DIRECCIÓN: ' . strtoupper($load->logisticsCompany?->address));

        $sheet->mergeCells('F6:I6');
        $sheet->setCellValue('F6', 'PIEZAS');
        $sheet->getStyle('F6:I6')
            ->applyFromArray($styleArrayBorderGrey);

        $sheet->mergeCells('A7:E7');
        $sheet->setCellValue('A7', 'TELÉFONO: ' . $load->logisticsCompany?->contact_phone);

        $sheet->mergeCells('F7:I7');
        $sheet->setCellValue('F7', 'FULLES');
        $sheet->getStyle('F7:I7')
            ->applyFromArray($styleArrayBorderGrey);
        
        $sheet->getStyle('F4:J7')
            ->applyFromArray($styleArrayBorderBlackMedium);

        $sheet->mergeCells('A8:E8');
        $sheet->setCellValue('A8', 'CONSIGNE');
        $sheet->getStyle('A8:E8')
            ->applyFromArray($infoStyleGreen);

        $sheet->mergeCells('F8:J8');
        $sheet->setCellValue('F8', 'EXPORT CARRIER');
        $sheet->getStyle('F8:J8')
            ->applyFromArray($infoStyleGreen);
        

        $sheet->mergeCells('A9:E9');
        $sheet->setCellValue('A9', $myCompany->legal_name);
        $sheet->getStyle('A9:E12')
            ->applyFromArray($styleArrayBorderBlack_FillWhite);

        $sheet->mergeCells('F9:J9');
        $sheet->setCellValue('F9', $load->carrier);

        $sheet->mergeCells('A10:E10');
        $sheet->setCellValue('A10', 'DIRECCIÓN: ' . strtoupper($myCompany->address));

        $sheet->mergeCells('F10:J10');
        $sheet->setCellValue('F10', 'PORT OF LOADING');
        $sheet->getStyle('F10:J10')
            ->applyFromArray($infoStyleGreen);

        $sheet->mergeCells('A11:E11');
        $sheet->setCellValue('A11', strtoupper($myCompany->city->name) . ' ' . strtoupper($myCompany->state->name) . ' ' . strtoupper($myCompany->country->name));

        $sheet->mergeCells('F11:J11');
        $sheet->setCellValue('F11', 'GUAYAQUIL');
        $sheet->getStyle('F11:J13')
            ->applyFromArray($styleArrayBorderThick);

        $sheet->mergeCells('A12:E12');
        $sheet->setCellValue('A12', 'TELÉFONO: ' . $myCompany->phone);

        $sheet->mergeCells('F12:J12');
        $sheet->setCellValue('F12', 'FOR TRANSSHIPMENT TO DESTINO');
        $sheet->getStyle('F12:J12')
            ->applyFromArray($infoStyleGreen);

        $sheet->mergeCells('A13:E13');
        $sheet->setCellValue('A13', 'MARCACIÓN');
        $sheet->getStyle('A13:E13')
            ->applyFromArray($infoStyleGreen);

        $sheet->mergeCells('F13:J13');
        $sheet->setCellValue('F13', 'LOS ANGELES, CA - UNITED STATES');
        $sheet->getStyle('F9:J13')
            ->applyFromArray($styleArrayBorderBlackMedium);

        $sheet->setCellValue('A14', $client->name);
        $sheet->getStyle('A14')
            ->applyFromArray($infoStyleGreenClient);

        $sheet->mergeCells('B14:J14');
        $sheet->setCellValue('B14', 'RESUMEN DE ENTREGA');
        $sheet->getStyle('B14:J14')
            ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));

        $sheet->setCellValue('A15', 'PALLETS NORMAL * 21 PCS');
        $sheet->getStyle('A15:J16')
            ->applyFromArray($styleArrayBorderThick);
        $sheet->getStyle('A15')
            ->applyFromArray($styleArrayBorderGrey);

        $sheet->mergeCells('B15:D15');

        $sheet->mergeCells('E15:G15');
        $sheet->setCellValue('E15', 'PALLETS MIXTAS *17 PCS');
        $sheet->getStyle('E15:G15')
            ->applyFromArray($styleArrayBorderGrey);

        $sheet->mergeCells('H15:J15');

        $sheet->setCellValue('A16', 'PALLETS NORMAL * 18 PCS');
        $sheet->getStyle('A16')
            ->applyFromArray($styleArrayBorderGrey);

        $sheet->mergeCells('B16:D16');

        $sheet->mergeCells('E16:G16');
        $sheet->setCellValue('E16', 'PALLETS GRANDES *15 PCS');
        $sheet->getStyle('E16:G16')
            ->applyFromArray($styleArrayBorderGrey);

        $sheet->mergeCells('H16:J16');
        $sheet->getStyle('A15:J16')
            ->applyFromArray($styleArrayBorderBlackMedium);

        $sheet->mergeCells('A17:J17');
        $sheet->setCellValue('A17', 'DESCRIPCIÓN DE LAS CAJAS ENVIADAS');
        $sheet->getStyle('A17:J17')
            ->applyFromArray($infoStyleBlue);
        $sheet->getRowDimension(17)->setRowHeight(25);

        $sheet->mergeCells('A18:B18');
        $sheet->setCellValue('A18', 'FINCA');
        $sheet->getStyle('A18:B18')
            ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));

        $sheet->setCellValue('C18', 'HAWB');
        $sheet->getStyle('C18')
            ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));

        $sheet->setCellValue('D18', 'DETALLE');
        $sheet->getStyle('D18')
            ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $sheet->getRowDimension(18)->setRowHeight(20);

        $column = 'E';
        $row = 18;
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                strtoupper($box)
            );
            $sheet->getStyle("{$column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));

            $column++;
        }
        $sheet->setCellValue("{$column}{$row}", 'PCS');
        $sheet->getStyle("{$column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $column++;
        $sheet->setCellValue("{$column}{$row}", 'FBX');
        $sheet->getStyle("{$column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $column++;
        $sheet->setCellValue("{$column}{$row}", 'TEMPERATURA');
        $sheet->getStyle("{$column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $row++;

        $coordinationStart = $row;
        $column = 'B';
        $total_fulls = 0;
        $total_pieces = 0;
        foreach ($coordinations as $coordination){
            // TOTALES
            $total_fulls += $coordination->fulls_r;
            $total_pieces += $coordination->pieces;

            $varietyIds = $coordination->varieties ?? [];

            $varieties = \App\Models\FlowerVariety::query()
                ->whereIn('id', $varietyIds)
                ->pluck('name')
                ->implode(', ');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $coordination->farm?->name ?? '—');
            
            $column = 'C';
            $sheet->setCellValue("{$column}{$row}", $coordination->hawb ?? '—');
            
            $column++;
            $sheet->setCellValue("{$column}{$row}", strtoupper($varieties));
            
            $column++;
            $pieza_nemos_uno = chr(ord($column) - 1);
            $pesos = [
                'fb' => 1,
                'hb' => 0.5,
                'qb' => 0.25,
                'eb' => 0.125,
                'db' => 0.0625,
            ];
            $sum_column = $column;
            $sum_column_r = $column;
            foreach ($activeBoxes as $box) {
                $receivedField = $box . '_r';
                $sheet->setCellValue(
                    "{$column}{$row}",
                    $coordination->{$receivedField} ?? 0
                );

                $column++;
            }
            // Calculo para suma horizontal y dependiendo del tipo de caja recibida
            $pieza_nemos_uno_r = chr(ord($sum_column_r));
            $pieza_mas_dos = chr(ord($pieza_nemos_uno) + 3);
            $partes_r = [];
            foreach($activeBoxes as $box){
                if(!isset($pesos[$box])) continue;
                $peso = $pesos[$box];
                $partes_r[] = "({$sum_column_r}{$row}*{$peso})";
                $sum_column_r++;
            }
            $formula_r = '=' . implode('+', $partes_r);
            // Received
            
            $sheet->setCellValue("{$column}{$row}", "=SUM({$pieza_nemos_uno_r}{$row}:{$pieza_mas_dos}{$row})");
            
            $column++;
            $sheet->setCellValue("{$column}{$row}", $formula_r);
            $sheet->getStyle("{$column}{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.000');
            
            $column++;
            $sheet->setCellValue("{$column}{$row}", '1.0°C');
            
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
            
        }

        // MOSTRAMOS ESTO PARA LOS TOTALES DE PIECES Y FULLS
        $sheet->setCellValue('J6', $total_pieces); 
        $sheet->getStyle('J6')
            ->applyFromArray($textAlignmentCenter);
        $sheet->setCellValue('J7', $total_fulls); 
        $sheet->getStyle('J7')
            ->applyFromArray($textAlignmentCenter);
        $sheet->getStyle('J7')
                ->getNumberFormat()
                ->setFormatCode('#,##0.000');

        $sheet->getStyle("C19:{$column}{$row}")
                ->applyFromArray($textAlignmentCenter);
        $sheet->getStyle("A19:{$column}{$row}")
                ->applyFromArray($styleArrayBorderThick);
        $sheet->getStyle("A19:{$column}{$row}")
                ->applyFromArray($styleArrayBorderBlackMedium);
        $column = 'E';
        // Totales de cajas.
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL:');
        $sheet->getStyle("A{$row}")
                ->applyFromArray($textAlignmentCenter);
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                "=SUM({$column}" . ($row - 1) . ":{$column}{$coordinationStart})"
            );

            $column++;
        }
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$coordinationStart})");
        $column++;
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$coordinationStart})");
        $sheet->getStyle("{$column}{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');
        
        $sheet->getStyle("e{$row}:{$column}{$row}")
                ->applyFromArray($styleArrayBorderThick);

        $column = chr(ord($column) + 1);
        $sheet->getStyle("A{$row}:{$column}{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);
        $sheet->getRowDimension($row)->setRowHeight(20);

        $row++;
        $column = 'G';
        $total_column = chr(ord($column) + count($activeBoxes));
        
        $sheet->mergeCells("A{$row}:{$total_column}{$row}");
        $sheet->setCellValue("A{$row}", 'OBSERVACIONES');
        $sheet->getStyle("A{$row}:{$total_column}{$row}")
            ->applyFromArray($infoStyleBlue);
        $sheet->getRowDimension(17)->setRowHeight(25);

        // DETALLE DE PALETAS
        $row++;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'DETALLES DE DESPACHO POR PALETS:');
        $sheet->getStyle("A{$row}:C{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        
        $sheet->getRowDimension($row)->setRowHeight(20);
        $column = "D";
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                strtoupper($box)
            );
            $sheet->getStyle("{$column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));

            $column++;
        }
        $sheet->setCellValue("{$column}{$row}", 'PCS');
        $sheet->getStyle("{$column}{$row}")
            ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $column++;
        $sheet->mergeCells("{$column}{$row}:{$total_column}{$row}");
        $sheet->setCellValue("{$column}{$row}", 'OBSERVACIONES');
        $sheet->getStyle("{$column}{$row}:{$total_column}{$row}")
                ->applyFromArray(array_merge($infoStyleGreen, $textAlignmentCenter));
        $row++;
        $coordinationStart_p = $row;
        foreach($palletsDetails as $detail){
            $acum = 0;
            $column = "D";
            $sum_column_r = $column;
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", 'PALLET #' . $detail->pallet->counter);
            foreach ($activeBoxes as $box) {
                $sheet->setCellValue(
                    "{$column}{$row}",
                    $detail->{$box} ?? 0
                );
                $acum += $detail->{$box};
                $column++;
            }
            $pieza_nemos_uno_r = chr(ord($sum_column_r));
            
            $pieza_mas_dos = chr(ord($pieza_nemos_uno_r) + count($activeBoxes) - 1);
            $partes = [];
            foreach($activeBoxes as $box){
                if(!isset($pesos[$box])) continue;
                $peso = $pesos[$box];
                $partes[] = "({$sum_column_r}{$row}*{$peso})";
                $sum_column_r++;
                
            }
            $formula = '=' . implode('+', $partes);
            // Received
            
            $sheet->setCellValue("{$column}{$row}", "=SUM({$pieza_nemos_uno_r}{$row}:{$pieza_mas_dos}{$row})");
            $column++;
            $sheet->mergeCells("{$column}{$row}:{$total_column}{$row}");
            if($acum <= 20){
                $sheet->setCellValue("{$column}{$row}", 'PALLETS NORMAL * 18 PCS');
            }else{
                $sheet->setCellValue("{$column}{$row}", 'PALLETS NORMAL * 21 PCS');
            }
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }
        $sheet->getStyle("A{$coordinationStart_p}:{$column}{$row}")
                ->applyFromArray($textAlignmentCenter);

        $sheet->getStyle("A{$coordinationStart_p}:{$total_column}{$row}")
                ->applyFromArray($styleArrayBorderThick);
        $sheet->getStyle("A{$coordinationStart_p}:{$total_column}{$row}")
                ->applyFromArray($styleArrayBorderBlackMedium);
        // TOTAL PIEZAS EN PALETAS
        $column = 'D';
        // Totales de cajas.
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL PIEZAS:');
        $sheet->getRowDimension($row)->setRowHeight(20);
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                "=SUM({$column}" . ($row - 1) . ":{$column}{$coordinationStart_p})"
            );

            $column++;
        }
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$coordinationStart_p})");
        $column++;
        $sheet->mergeCells("{$column}{$row}:{$total_column}{$row}");
        $column = chr(ord($column) + 2);
        $sheet->getStyle("A{$row}:{$column}{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);
        $row++;

        // SALIDA Y LLEGADA
        $sheet->mergeCells("A{$row}:{$total_column}{$row}");
        $sheet->setCellValue("{$column}{$row}", '');
        $sheet->getStyle("A{$row}:{$total_column}{$row}")
                ->applyFromArray($infoStyleGreen);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;
        $sheet->setCellValue("A{$row}", 'SALIDA DE ' . strtoupper($load->originCity->name) . ':');
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", date('d/m/Y', strtotime($load->date)));
        $sheet->getStyle("A{$row}:C{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);

        $sheet->mergeCells("D{$row}:{$total_column}{$row}");
        $sheet->setCellValue("D{$row}", 'DEVOLUCIONES');
        $sheet->getStyle("D{$row}:{$total_column}{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $sheet->setCellValue("A{$row}", 'LLEGADA A ' . strtoupper($load->destinationCity->name) . ':');
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", date('d/m/Y', strtotime($load->arrival_date)));
        $sheet->getStyle("A{$row}:C{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);

        $sheet->mergeCells("D{$row}:{$total_column}{$row}");
        $sheet->setCellValue("D{$row}", 'NO HAY DEVOLUCIONES');
        $sheet->getStyle("D{$row}:{$total_column}{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $sheet->mergeCells("A{$row}:{$total_column}{$row}");
        $sheet->setCellValue("{$column}{$row}", '');
        $sheet->getStyle("A{$row}:{$total_column}{$row}")
                ->applyFromArray($infoStyleGreen);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;
        $sheet->mergeCells("{$column}{$row}:{$total_column}{$row}");
        $sheet->setCellValue("A{$row}", 'REALIZADO POR: ' . strtoupper($load->updater->name));
        $sheet->getStyle("A{$row}:{$total_column}{$row}")
                ->applyFromArray($styleArrayBorderBlack_fillLightBlue);
        $sheet->getRowDimension($row)->setRowHeight(20);

        /*
        |--------------------------------------------------------------------------
        | PRINT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $pageSetup = $sheet->getPageSetup();

        $pageSetup->setOrientation(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
        );

        $pageSetup->setPaperSize(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
        );

        // Una página de ancho y tantas páginas de alto como sean necesarias.
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);

        // Definir el área de impresión automáticamente.
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        $sheet->getPageMargins()
            ->setLeft(0.25)
            ->setRight(0.25)
            ->setTop(0.40)
            ->setBottom(0.40);

        $sheet->getPageSetup()->setPrintArea(
            "A1:{$lastColumn}{$lastRow}"
        );
    }

    private function uniqueSheetTitle(string $name, Spreadsheet $spreadsheet): string
    {
        $name = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '', $name);
        $name = trim($name) ?: 'Cliente';
        $name = mb_substr($name, 0, 31);

        $base = $name;
        $counter = 1;

        while ($spreadsheet->getSheetByName($name) !== null) {
            $suffix = '-' . $counter++;
            $name = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
        }

        return $name;
    }
}
