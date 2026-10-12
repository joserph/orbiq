<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Flight;
use App\Models\FlowerVariety;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FlightCompleteExport
{
    public function __construct(
        protected Flight $flight
    ) {}

    public function download()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Complete');

        /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTHS
        |--------------------------------------------------------------------------
        */

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(2);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(43);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(16);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(14);
        $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(25);
        // $spreadsheet->getActiveSheet()->getRowDimension('8')->setRowHeight(30);

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND 
        |--------------------------------------------------------------------------
        */
        
        $coordinations = $this->flight->coordinations()
            ->with([
                'client',
                'farm',
            ])
            ->get();

        $groups = $coordinations->groupBy('client_id');

        $sub_space = count($groups) * 2;
        $total_line = 12;
        $line_final = count($coordinations) + count($groups) + $sub_space + $total_line + 3;
        // dd($line_final);
        $letra = 'A';
        for($letra; $letra <= 'V'; $letra++)
        {
            $spreadsheet->getActiveSheet()->getStyle($letra . '1:' . $letra . $line_final)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFFFF');
        }

        /*
        |--------------------------------------------------------------------------
        | GENERAL STYLES
        |--------------------------------------------------------------------------
        */

        $border = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'B7C0CE'],
                ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        // $logoPath = public_path('images/orbiq-logo.png');

        // if (file_exists($logoPath)) {
        //     $drawing = new Drawing();

        //     $drawing->setName('ORBIQ');
        //     $drawing->setDescription('ORBIQ');
        //     $drawing->setPath($logoPath);
        //     $drawing->setCoordinates('K2');
        //     $drawing->setHeight(55);
        //     $drawing->setOffsetX(10);
        //     $drawing->setOffsetY(5);

        //     $drawing->setWorksheet($sheet);
        // }
        $company = Company::first();

        if ($company?->logo) {

            $logoPath = public_path('storage/' . $company->logo);

            if (file_exists($logoPath)) {

                $drawing = new Drawing();

                $drawing->setName($company->name ?? 'Company Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath($logoPath);

                // Posición del logo
                $drawing->setCoordinates('C2');

                // Tamaño
                $drawing->setHeight(155);

                // Ajuste de posición
                $drawing->setOffsetX(1);
                $drawing->setRotation(0);

                $drawing->setWorksheet($sheet);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('F2:H4');

        $sheet->setCellValue(
            'F2',
            'COORDINACIONES AÉREAS'
        );

        $sheet->getStyle('B2:P2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 22,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ])->getAlignment()->setWrapText(true);

        $sheet->mergeCells('D5:J6');

        $sheet->setCellValue(
            'D5',
            'CONTROL Y SEGUIMIENTO DE CARGA AÉREA'
        );

        $sheet->getStyle('D5:J6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(2)->setRowHeight(15);

        /*
        |--------------------------------------------------------------------------
        | FLIGHT INFORMATION
        |--------------------------------------------------------------------------
        */
        $infoStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '34226A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        

        // AWB
        $sheet->mergeCells('K3:M3');
        $sheet->mergeCells('K4:M4');

        $sheet->setCellValue('K3', 'AWB');
        $sheet->setCellValue('K4', $this->flight->awb ?? '—');

        // AIRLINE
        $sheet->mergeCells('N3:Q3');
        $sheet->mergeCells('N4:Q4');

        $sheet->setCellValue(
            'N3',
            'AIRLINE'
        );

        $sheet->setCellValue(
            'N4',
            $this->flight->airline?->name ?? '—'
        );

        // FLIGHT DATE
        $sheet->mergeCells('R3:S3');
        $sheet->mergeCells('R4:S4');

        $sheet->setCellValue(
            'R3',
            'FLIGHT DATE'
        );

        $sheet->setCellValue(
            'R4',
            $this->flight->date?->format('d/m/Y') ?? '—'
        );

        // ARRIVAL DATE
        // $sheet->mergeCells('H4:I4');
        // $sheet->mergeCells('H5:I5');

        $sheet->setCellValue(
            'T3',
            'ARRIVAL DATE'
        );

        $sheet->setCellValue(
            'T4',
            $this->flight->arrival_date?->format('d/m/Y') ?? '—'
        );

        $sheet->mergeCells('K5:R5'); //#34226a color base
        // $sheet->setCellValue('K2', 'INFORMACIÓN DEL VUELO');
        $sheet->getStyle('K5:R5')
            ->applyFromArray($infoStyle);

        // ORIGIN
        $sheet->mergeCells('K6:N6');
        $sheet->mergeCells('K7:N7');

        $sheet->setCellValue(
            'K6',
            'ORIGIN'
        );

        $sheet->setCellValue(
            'K7',
            $this->flight->originCity?->name ?? '—'
        );

        // DESTINATION
        $sheet->mergeCells('O6:R6');
        $sheet->mergeCells('O7:R7');

        $sheet->setCellValue(
            'O6',
            'DESTINATION'
        );

        $sheet->setCellValue(
            'O7',
            $this->flight->destinationCity?->name ?? '—'
        );

        /*
        |--------------------------------------------------------------------------
        | STYLE FLIGHT INFORMATION
        |--------------------------------------------------------------------------
        */

        $flightInfoRanges = [
            'K2:T2',
            'K3:M4',
            'N3:Q4',
            'R3:S4',
            'T3:T4',
            'K5:R5',
            'K6:N7',
            'O6:R7',
        ];

        foreach ($flightInfoRanges as $range) {
            $sheet->getStyle($range)->applyFromArray($border);
        }

        // Labels
        foreach ([
            'K2',
            'K3',
            'N3',
            'R3',
            'T3',
            'K5',
            'K6',
            'O6',
        ] as $cell) {
            $sheet->getStyle($cell)->getFont()->setSize(10);
            $sheet->getStyle($cell)->getFont()->setBold(false);
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('34226A');
        }

        // Values
        foreach ([
            'K4',
            'N4',
            'R4',
            'T4',
            'K7',
            'O7',
        ] as $cell) {
            $sheet->getStyle($cell)->getFont()->setSize(13);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Vertical alignment
        $sheet->getStyle('K2:R7')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('K2:T2'); //#34226a color base
        $sheet->setCellValue('K2', 'INFORMACIÓN DEL VUELO');
        $sheet->getStyle('K2:T2')
            ->applyFromArray($infoStyle);

        $sheet->mergeCells('D8:J9');
        $sheet->setCellValue('D8', 'REPORTE DE COORDINACIÓN');

        $sheet->mergeCells('S9:T9');
        $sheet->setCellValue('S9', 'DOCUMENTO OPERATIVO');

        $sheet->getStyle('D8:J9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('S9:T9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        

        /*
        |--------------------------------------------------------------------------
        | TEMPORARY TEST
        |--------------------------------------------------------------------------
        |
        | Dejamos una separación para que después coloquemos
        | la tabla de coordinaciones.
        |
        */

        // $sheet->getRowDimension(10)->setRowHeight(10);

        
        /*
        |--------------------------------------------------------------------------
        | COORDINATIONS
        |--------------------------------------------------------------------------
        */

        $row = 10;

        /*
        |--------------------------------------------------------------------------
        | COLUMN HEADERS
        |--------------------------------------------------------------------------
        */

        $activeBoxes = collect([
            'fb' => $this->flight->fb_status,
            'hb' => $this->flight->hb_status,
            'qb' => $this->flight->qb_status,
            'eb' => $this->flight->eb_status,
            'db' => $this->flight->db_status,
        ])
            
            ->all();
        // dd($activeBoxes);

        $nextRow = $row + 1;

         /*
        |--------------------------------------------------------------------------
        | HAWB / FARM / VARIETY
        |--------------------------------------------------------------------------
        */
        $sheet->mergeCells("B{$row}:B{$nextRow}");
        $sheet->setCellValue("B{$row}", 'HAWB');

        $sheet->mergeCells("C{$row}:C{$nextRow}");
        $sheet->setCellValue("C{$row}", 'FARM');

        $sheet->mergeCells("D{$row}:D{$nextRow}");
        $sheet->setCellValue("D{$row}", 'VARIETY');

        // $sheet->mergeCells("B{$row}:D{$nextRow}");
        // $sheet->setCellValue("B{$row}", '');

        $sheet->mergeCells("E{$row}:K{$row}");
        $sheet->setCellValue("E{$row}", 'COORDINATED');

        $sheet->mergeCells("L{$row}:R{$row}");
        $sheet->setCellValue("L{$row}", 'RECEIVED');

        $sheet->mergeCells("S{$row}:S{$nextRow}");
        $sheet->setCellValue("S{$row}", 'RETURNS');

        $sheet->mergeCells("T{$row}:T{$nextRow}");
        $sheet->setCellValue("T{$row}", 'MISSING');

        $sheet->mergeCells("U{$row}:U{$nextRow}");
        $sheet->setCellValue("U{$row}", 'OBSERVATIONS');


        /*
        |--------------------------------------------------------------------------
        | SECOND HEADER ROW
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("E" . ($row + 1), 'FB');
        $sheet->setCellValue("F" . ($row + 1), 'HB');
        $sheet->setCellValue("G" . ($row + 1), 'QB');
        $sheet->setCellValue("H" . ($row + 1), 'EB');
        $sheet->setCellValue("I" . ($row + 1), 'DB');
        $sheet->setCellValue("J" . ($row + 1), 'PIECES');
        $sheet->setCellValue("K" . ($row + 1), 'FULLS');

        $sheet->setCellValue("L" . ($row + 1), 'FB');
        $sheet->setCellValue("M" . ($row + 1), 'HB');
        $sheet->setCellValue("N" . ($row + 1), 'QB');
        $sheet->setCellValue("O" . ($row + 1), 'EB');
        $sheet->setCellValue("P" . ($row + 1), 'DB');
        $sheet->setCellValue("Q" . ($row + 1), 'PIECES');
        $sheet->setCellValue("R" . ($row + 1), 'FULLS');

        $sheet->getColumnDimension('E')->setVisible($this->flight->fb_status);
        $sheet->getColumnDimension('F')->setVisible($this->flight->hb_status);
        $sheet->getColumnDimension('G')->setVisible($this->flight->qb_status);
        $sheet->getColumnDimension('H')->setVisible($this->flight->eb_status);
        $sheet->getColumnDimension('I')->setVisible($this->flight->db_status);

        $sheet->getColumnDimension('L')->setVisible($this->flight->fb_status);
        $sheet->getColumnDimension('M')->setVisible($this->flight->hb_status);
        $sheet->getColumnDimension('N')->setVisible($this->flight->qb_status);
        $sheet->getColumnDimension('O')->setVisible($this->flight->eb_status);
        $sheet->getColumnDimension('P')->setVisible($this->flight->db_status);


       


        /*
        |--------------------------------------------------------------------------
        | HEADER STYLE
        |--------------------------------------------------------------------------
        */

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '34226A'], // 34226A - 172033
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        $sheet->getStyle("B{$row}:U" . ($row + 1))
            ->applyFromArray($headerStyle);

        $sheet->getRowDimension($row)->setRowHeight(25);
        $sheet->getRowDimension($row + 1)->setRowHeight(25);

        $row += 2;


        /*
        |--------------------------------------------------------------------------
        | GLOBAL TOTALS
        |--------------------------------------------------------------------------
        */

        $global = [
            'fb' => 0,
            'hb' => 0,
            'qb' => 0,
            'eb' => 0,
            'db' => 0,
            'pieces' => 0,
            'fulls' => 0,

            'fb_r' => 0,
            'hb_r' => 0,
            'qb_r' => 0,
            'eb_r' => 0,
            'db_r' => 0,
            'pieces_r' => 0,
            'fulls_r' => 0,

            'returns' => 0,
            'missing' => 0,
        ];


        /*
        |--------------------------------------------------------------------------
        | GROUP BY CLIENT
        |--------------------------------------------------------------------------
        */
        $arrSubTotal = array();
        foreach ($groups as $clientId => $clientCoordinations) {

            $client = $clientCoordinations->first()?->client;

            $clientName = $client?->name ?? 'NO CLIENT';


            /*
            |--------------------------------------------------------------------------
            | CLIENT HEADER
            |--------------------------------------------------------------------------
            */
            $clientStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 15,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ];

            $sheet->mergeCells("C{$row}:U{$row}");

            $sheet->setCellValue(
                "B{$row}",
                'CLIENT:'
            );
            $sheet->setCellValue(
                "C{$row}",
                $clientName
            );

            $sheet->getStyle("C{$row}:U{$row}")
                ->applyFromArray($clientStyle);

            $sheet->getStyle("B{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    // 'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            
            // $sheet->getStyle("C{$row}:U{$row}")->applyFromArray([
            //     'font' => [
            //         'bold' => true,
            //         'size' => 15,
            //     ],
            //     'alignment' => [
            //         'horizontal' => Alignment::HORIZONTAL_CENTER,
            //         'vertical' => Alignment::VERTICAL_CENTER,
            //     ],
            //     'fill' => [
            //         'fillType' => Fill::FILL_SOLID,
            //         'color' => ['rgb' => 'E5E7EB'],
            //     ],
            // ]);

            $row++;


            /*
            |--------------------------------------------------------------------------
            | CLIENT SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = [
                'fb' => 0,
                'hb' => 0,
                'qb' => 0,
                'eb' => 0,
                'db' => 0,
                'pieces' => 0,
                'fulls' => 0,

                'fb_r' => 0,
                'hb_r' => 0,
                'qb_r' => 0,
                'eb_r' => 0,
                'db_r' => 0,
                'pieces_r' => 0,
                'fulls_r' => 0,

                'returns' => 0,
                'missing' => 0,
            ];


            /*
            |--------------------------------------------------------------------------
            | COORDINATION ROWS
            |--------------------------------------------------------------------------
            */
            $row_first = $row;
            foreach ($clientCoordinations as $coordination) {

                $varietyIds = $coordination->varieties ?? [];

                $varieties = \App\Models\FlowerVariety::query()
                    ->whereIn('id', $varietyIds)
                    ->pluck('name')
                    ->implode(', ');
                // $varieties = $coordination->varieties;
                
                // if (is_array($varieties)) {
                //     $variety = implode(', ', $varieties);
                // } else {
                //     $variety = $varieties ?? '—';
                // }


                /*
                |--------------------------------------------------------------------------
                | VALUES
                |--------------------------------------------------------------------------
                */

                $fb = (int) ($coordination->fb ?? 0);
                $hb = (int) ($coordination->hb ?? 0);
                $qb = (int) ($coordination->qb ?? 0);
                $eb = (int) ($coordination->eb ?? 0);
                $db = (int) ($coordination->db ?? 0);

                $pieces = (int) ($coordination->pieces ?? 0);
                $fulls = (float) ($coordination->fulls ?? 0);

                $fbR = (int) ($coordination->fb_r ?? 0);
                $hbR = (int) ($coordination->hb_r ?? 0);
                $qbR = (int) ($coordination->qb_r ?? 0);
                $ebR = (int) ($coordination->eb_r ?? 0);
                $dbR = (int) ($coordination->db_r ?? 0);

                $piecesR = (int) ($coordination->pieces_r ?? 0);
                $fullsR = (float) ($coordination->fulls_r ?? 0);

                $returns = (int) ($coordination->returns ?? 0);
                $missing = (int) ($coordination->missing ?? 0);


                /*
                |--------------------------------------------------------------------------
                | WRITE ROW
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue("B{$row}", $coordination->hawb ?? '—');

                $sheet->setCellValue(
                    "C{$row}",
                    $coordination->farm?->name ?? '—'
                );

                $sheet->setCellValue(
                    "D{$row}",
                    strtoupper($varieties)
                );

                // Coordinated
                $sheet->setCellValue("E{$row}", $fb);
                $sheet->setCellValue("F{$row}", $hb);
                $sheet->setCellValue("G{$row}", $qb);
                $sheet->setCellValue("H{$row}", $eb);
                $sheet->setCellValue("I{$row}", $db);
                $sheet->setCellValue("J{$row}", $pieces);
                $sheet->setCellValue("K{$row}", $fulls);

                // Received
                $sheet->setCellValue("L{$row}", $fbR);
                $sheet->setCellValue("M{$row}", $hbR);
                $sheet->setCellValue("N{$row}", $qbR);
                $sheet->setCellValue("O{$row}", $ebR);
                $sheet->setCellValue("P{$row}", $dbR);
                $sheet->setCellValue("Q{$row}", $piecesR);
                $sheet->setCellValue("R{$row}", $fullsR);

                $sheet->setCellValue("S{$row}", $returns);
                $sheet->setCellValue("T{$row}", $missing);
                $sheet->setCellValue("U{$row}", $coordination->observation);


                /*
                |--------------------------------------------------------------------------
                | SUBTOTAL
                |--------------------------------------------------------------------------
                */

                $subtotal['fb'] += $fb;
                $subtotal['hb'] += $hb;
                $subtotal['qb'] += $qb;
                $subtotal['eb'] += $eb;
                $subtotal['db'] += $db;
                $subtotal['pieces'] += $pieces;
                $subtotal['fulls'] += $fulls;

                $subtotal['fb_r'] += $fbR;
                $subtotal['hb_r'] += $hbR;
                $subtotal['qb_r'] += $qbR;
                $subtotal['eb_r'] += $ebR;
                $subtotal['db_r'] += $dbR;
                $subtotal['pieces_r'] += $piecesR;
                $subtotal['fulls_r'] += $fullsR;

                $subtotal['returns'] += $returns;
                $subtotal['missing'] += $missing;

                $row++;
            }


            /*
            |--------------------------------------------------------------------------
            | CLIENT SUBTOTAL ROW
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue("B{$row}", 'SUBTOTAL');

            $sheet->mergeCells("B{$row}:D{$row}");

            $sheet->setCellValue("E{$row}", '=SUM(E' . ($row - 1) . ':E' . $row_first . ')');
            $sheet->setCellValue("F{$row}", '=SUM(F' . ($row - 1) . ':F' . $row_first . ')');
            $sheet->setCellValue("G{$row}", '=SUM(G' . ($row - 1) . ':G' . $row_first . ')');
            $sheet->setCellValue("H{$row}", '=SUM(H' . ($row - 1) . ':H' . $row_first . ')');
            $sheet->setCellValue("I{$row}", '=SUM(I' . ($row - 1) . ':I' . $row_first . ')');
            $sheet->setCellValue("J{$row}", '=SUM(J' . ($row - 1) . ':J' . $row_first . ')');
            $sheet->setCellValue("K{$row}", '=SUM(K' . ($row - 1) . ':K' . $row_first . ')');

            $sheet->setCellValue("L{$row}", '=SUM(L' . ($row - 1) . ':L' . $row_first . ')');
            $sheet->setCellValue("M{$row}", '=SUM(M' . ($row - 1) . ':M' . $row_first . ')');
            $sheet->setCellValue("N{$row}", '=SUM(N' . ($row - 1) . ':N' . $row_first . ')');
            $sheet->setCellValue("O{$row}", '=SUM(O' . ($row - 1) . ':O' . $row_first . ')');
            $sheet->setCellValue("P{$row}", '=SUM(P' . ($row - 1) . ':P' . $row_first . ')');
            $sheet->setCellValue("Q{$row}", '=SUM(Q' . ($row - 1) . ':Q' . $row_first . ')');
            $sheet->setCellValue("R{$row}", '=SUM(R' . ($row - 1) . ':R' . $row_first . ')');

            $sheet->setCellValue("S{$row}", '=SUM(S' . ($row - 1) . ':S' . $row_first . ')');
            $sheet->setCellValue("T{$row}", '=SUM(T' . ($row - 1) . ':T' . $row_first . ')');

            $arrSubTotal[] = $row;
            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL STYLE
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("B{$row}:U{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F1F3F5'],
                ],
            ]);

            $row++;


            /*
            |--------------------------------------------------------------------------
            | ACCUMULATE GLOBAL
            |--------------------------------------------------------------------------
            */

            foreach ($global as $key => $value) {
                $global[$key] += $subtotal[$key];
            }

            // Espacio entre clientes
            $row++;
            
        }
        // dd($arrSubTotal);
        
        $i = 'E';
        for($i; $i <= 'T'; $i++)
        {
            $cadena = '';
            //$cadena2 .= $i;
            foreach($arrSubTotal as $tot)
            {
                $cadena .= '+' . $i . $tot;
            }
            $sheet->setCellValue($i . $row, '=' . $cadena);
        }
        /*
        |--------------------------------------------------------------------------
        | GLOBAL TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells("B{$row}:D{$row}");

        $sheet->setCellValue("B{$row}", 'TOTAL GLOBAL');

        // $sheet->setCellValue("E{$row}", $global['fb']);
        // $sheet->setCellValue("F{$row}", $global['hb']);
        // $sheet->setCellValue("G{$row}", $global['qb']);
        // $sheet->setCellValue("H{$row}", $global['eb']);
        // $sheet->setCellValue("I{$row}", $global['db']);
        // $sheet->setCellValue("J{$row}", $global['pieces']);
        // $sheet->setCellValue("K{$row}", $global['fulls']);

        // $sheet->setCellValue("L{$row}", $global['fb_r']);
        // $sheet->setCellValue("M{$row}", $global['hb_r']);
        // $sheet->setCellValue("N{$row}", $global['qb_r']);
        // $sheet->setCellValue("O{$row}", $global['eb_r']);
        // $sheet->setCellValue("P{$row}", $global['db_r']);
        // $sheet->setCellValue("Q{$row}", $global['pieces_r']);
        // $sheet->setCellValue("R{$row}", $global['fulls_r']);

        // $sheet->setCellValue("S{$row}", $global['returns']);
        // $sheet->setCellValue("T{$row}", $global['missing']);


        /*
        |--------------------------------------------------------------------------
        | GLOBAL TOTAL STYLE
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("B{$row}:U{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'CBD5E1'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '94A3B8'],
                ],
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | TABLE BORDERS & ALIGNMENT
        |--------------------------------------------------------------------------
        */

        $lastRow = $row;

        $sheet->getStyle("B12:U{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("K13:K{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');

        $sheet->getStyle("R13:R{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');


        /*
        |--------------------------------------------------------------------------
        | ALIGN NUMBERS
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("E12:T{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);


        /*
        |--------------------------------------------------------------------------
        | TEXT ALIGNMENT
        |--------------------------------------------------------------------------
        */

        // $sheet->getStyle("B12:D{$lastRow}")
        //     ->getAlignment()
        //     ->setHorizontal(Alignment::HORIZONTAL_LEFT);


        /*
        |--------------------------------------------------------------------------
        | FREEZE PANES
        |--------------------------------------------------------------------------
        */

        //$sheet->freezePane('E14');

        

        /*
        |--------------------------------------------------------------------------
        | PRINT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $pageSetup = $sheet->getPageSetup();

        $pageSetup->setOrientation(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
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

        // Repetir el encabezado de la tabla en cada página impresa.
        // En tu Excel, la tabla comienza en las filas 10 y 11.
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(10, 11);

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            'flight-' . $this->flight->awb . '-complete.xlsx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}