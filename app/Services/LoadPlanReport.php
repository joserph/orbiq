<?php

namespace App\Services;

use App\Models\Load;
use App\Models\Pallet;
use App\Models\PalletDetail;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoadPlanReport
{
    public function download(Load $load): StreamedResponse
    {
        $spreadsheet = $this->generate($load);

        $filename = 'Plano_Carga_' . $load->shipment . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }

    
    public function generate(Load $load): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plano de carga');

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
        
        $pallets = Pallet::where('load_id', '=', $load->id)->get();
        $palletsDetails = PalletDetail::whereHas('pallet', function ($query) use ($load) {
            $query->where('load_id', $load->id);
        })
        ->with('pallet', 'farm', 'client')
        ->join('farms', 'farms.id', '=', 'pallet_details.farm_id')
        ->join('clients', 'clients.id', '=', 'pallet_details.client_id')
        ->orderBy('clients.name', 'asc')
        ->orderBy('farms.name', 'asc')
        ->select('pallet_details.*')
        ->get();

        

        // $palletsInfos = PalletDetail::where('load_id', $load->id)
        //     ->with('farm', 'client')
        //     ->get();
        // $palletsInfos = PalletDetail::whereHas('pallet', function ($query) use ($load) {
        //     $query->where('load_id', $load->id);
        // })
        //     ->join('clients', 'clients.id', '=', 'pallet_details.client_id')
        //     ->orderBy('clients.name')
        //     ->select('pallet_details.*')
        //     ->with('farm', 'client')
        //     ->get();
        $palletsInfos = PalletDetail::whereHas('pallet', function ($query) use ($load) {
            $query->where('load_id', $load->id);
        })
        ->join('clients', 'clients.id', '=', 'pallet_details.client_id')
        ->orderBy('clients.name')
        ->select('pallet_details.*')
        ->with('farm', 'client')
        ->get()
        ->groupBy(fn($item) => $item->farm_id . '-' . $item->client_id)
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
        ->sortBy(fn($item) => $item->client?->name)
        ->values();
        // dd($palletsInfos);
        // STYLES
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $style = [
            'alignment' => array(
                'wrapText' => TRUE,
                'textRotation' => '90',
                'readorder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
        ];

        // STYLES LETRA BLANCA, FONDO AZUL, BORDE MEDIO NEGRO
        $infoStyleBlueDark = [
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
                    'color' => ['rgb' => '17324D'],
                ],
            ],
        ];
        // LETRA AZUL OSCURO Y FONDO AZUL CLARO
        $infoStyleBlue = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '17324D'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'EAF2F8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '17324D'],
                ],
            ],
        ];
        // FONDO VERDE LETRAS BLANCAS Y 
        $infoStyleGreen = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 14,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '168C8C'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5F4F2'],
                ],
            ],
        ];

        $infoStyleGreenLight = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 14,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E5F4F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '168C8C'],
                ],
            ],
        ];

        $infoStyleGreenLightNormal = [
            'font' => [
                // 'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E5F4F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '168C8C'],
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

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(26);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(4);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(2);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(21);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(21);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(7);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(50);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(24);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(45);
        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(5);

        // MARCACIONES
        $sheet->getStyle('A2:B2')->getFont()->setBold(true);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2:B2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:B2')->getFont()->setSize(14);
        $sheet->getStyle('A2:B2')->applyFromArray($infoStyleBlueDark);
        $sheet->setCellValue('A2', 'MARCACIONES');

        $row = 3;
        // $clients = $load->coordinations()
        //     ->with('client')
        //     ->get()
        //     ->pluck('client')
        //     ->filter()
        //     ->unique('id')
        //     ->sortBy('name')
        //     ->values();
        $clients = $load->coordinations()
            ->join('clients', 'clients.id', '=', 'load_coordinations.client_id')
            ->orderBy('clients.name')
            ->select('load_coordinations.*')
            ->with('client')
            ->get()
            ->pluck('client')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
        // dd($load->coordinations());
        foreach($clients as $client)
        {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $client->name);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($infoStyleBlue);
            $row++;
        }
        $rowPlusTwenty = $row + 20;
        $sheet->mergeCells("A{$row}:B{$rowPlusTwenty}");
        $sheet->getStyle("A{$row}:B{$rowPlusTwenty}")->applyFromArray(array_merge($infoStyleBlue, $style));
        $sheet->getStyle("A{$row}:B{$rowPlusTwenty}")->getFont()->setSize(28);
        $sheet->setCellValue("A{$row}", $load->bl);

        // PLANO
        $sheet->getStyle('D2:G3')->getFont()->setBold(true);
        $sheet->getStyle('D4:G3')->getFont()->setSize(14);
        $spreadsheet->getActiveSheet()->getStyle('E4:E69')
            ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $spreadsheet->getActiveSheet()->getStyle('G4:G3')
            ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sheet->mergeCells('D2:E2');
        $sheet->getStyle('D2:G3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D2:G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:G3')->applyFromArray($infoStyleGreen);
        $sheet->setCellValue('D2', 'LADO CHOFER');

        $sheet->mergeCells('F2:G2');
        $sheet->setCellValue('F2', 'LADO CO-PILOTO');

        $sheet->setCellValue('D3', 'PALETA');
        $sheet->setCellValue('E3', 'CLIENTE');
        $sheet->setCellValue('F3', 'PALETA');
        $sheet->setCellValue('G3', 'CLIENTE');

        // ESPACIO DEL PLANO
        $column = 'D';
        for($column; $column <= 'G'; $column++)
        {
            for($row = 4; $row <= 69; $row+=6)
            {
                $rowSpaceFive = $row + 5;
                $sheet->mergeCells("{$column}{$row}:{$column}{$rowSpaceFive}");
                $sheet->getStyle("{$column}{$row}:{$column}{$rowSpaceFive}")->applyFromArray($infoStyleGreenLight);
            }
        }

        // INFORMACION DE PALETAS
        $column = 'I';
        $totalInfoPallet = chr(ord($column) + 2 + count($activeBoxes));
        // dd($totalInfoPallet);
        $sheet->mergeCells("{$column}1:{$totalInfoPallet}1");//'I1:N1'
        $sheet->getStyle("{$column}1:{$totalInfoPallet}3")->getFont()->setBold(true);
        $sheet->getStyle("{$column}1:{$totalInfoPallet}3")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("{$column}1:{$totalInfoPallet}3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$column}1:{$totalInfoPallet}3")->applyFromArray($infoStyleBlue);

        $sheet->getStyle("{$column}1:{$totalInfoPallet}1")->getFont()->setSize(20);
        $spreadsheet->getActiveSheet()->getStyle("{$column}1:{$totalInfoPallet}1")->applyFromArray($infoStyleBlueDark);
        $sheet->setCellValue("{$column}1", $load->bl);

        $sheet->mergeCells("{$column}2:{$totalInfoPallet}2");
        $sheet->setCellValue("{$column}2", 'PIEZAS EMBARCADAS');
        $sheet->setCellValue("{$column}3", 'PALETA');
        $column++;
        $sheet->setCellValue("{$column}3", 'FINCA');
        $column++;
        $sheet->setCellValue("{$column}3", 'CLIENTE');
        $column = 'L';
        $row = 3;
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                strtoupper($box)
            );
            $column++;
        }

        $row = 4; // j = 4
        $count = 0;
        $rowInitialInfoPallet = $row;
        foreach($pallets as $pallet)
        {
            foreach($palletsDetails as $item)
            {
                $column = 'I';
                if($pallet->id == $item->pallet_id)
                {
                    for($column; $column <= $totalInfoPallet; $column++)
                    {
                        if($count != $pallet->counter)
                        {
                            $sheet->getStyle($column . $row)->applyFromArray($infoStyleGreen);
                            $sheet->getStyle($column . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle($column . $row)->getFont()->setBold(true);
                            if(strpos($pallet->number, 'USDA'))
                            {
                                $sheet->setCellValue($column . $row, 'USDA');
                                $spreadsheet->getActiveSheet()->getStyle($column . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('FFFF00');
                            }else{
                                $sheet->setCellValue($column . $row, $pallet->counter);
                            }
                        }
                        $count = $pallet->counter;
                        $column++;
                        $sheet->getStyle($column . $row)->applyFromArray($infoStyleGreenLightNormal);
                        $sheet->setCellValue($column . $row, $item->farm->name);
                        $column++;
                        $sheet->getStyle($column . $row)->applyFromArray(array_merge($infoStyleGreenLightNormal, $textAlignmentCenter));
                        $sheet->setCellValue($column . $row, str_replace('SAG-', '', $item->client->name));
                        $column++;
                        foreach ($activeBoxes as $box) {
                            $sheet->setCellValue(
                                "{$column}{$row}", $item->{$box}
                            );
                            $sheet->getStyle("{$column}{$row}")->applyFromArray(array_merge($infoStyleGreenLightNormal, $textAlignmentCenter));
                            $column++;
                        }
                        // $sheet->getStyle($column . $row)->applyFromArray($styleArray);
                        // if($item->hb != 0)
                        // {
                        //     $sheet->setCellValue($column . $row, $item->hb);
                        // }
                        
                        // $column++;
                        // $sheet->getStyle($column . $row)->applyFromArray($styleArray);
                        // if($item->qb != 0)
                        // {
                        //     $sheet->setCellValue($column . $row, $item->qb);
                        // }
                        
                        // $column++;
                        // $sheet->getStyle($column . $row)->applyFromArray($styleArray);
                        // if($item->eb != 0)
                        // {
                        //     $sheet->setCellValue($column . $row, $item->eb);
                        // }
                    }
                    $row++;
                }
            }
            $space = $row;
            $sheet->setCellValue('I' . $space, '');
            $row = $space + 1;
        }
        // $column = 'J';
        // TOTAL INFORMACION DE PALETAS
        $sheet->getStyle('J' . $row . ':N' . $row)->applyFromArray($infoStyleBlue);
        $sheet->getStyle('J' . $row . ':N' . $row)->getFont()->setBold(true);
        $sheet->getStyle('J' . $row . ':N' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('J' . $row . ':N' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $sheet->setCellValue('J' . $row, 'TOTAL');
        $column = 'L';
        $columnInit = $column;
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                "=SUM({$column}" . ($row - 1) . ":{$column}{$rowInitialInfoPallet})"
            );

            $column++;
        }
        
        $column = $columnInit;
        $row++;
        // $sum_column = $columnInit;
        $rowLessOne = $row - 1;
        // dd($rowLessOne);
        $columnPlusActiBoxs = chr(ord($columnInit) + (count($activeBoxes) - 1));
        // // dd($columnPlusActiBoxs);
        // $pieza_nemos_uno_r = chr(ord($sum_column));
        // // dd($pieza_nemos_uno_r);
        // $pieza_mas_dos = chr(ord($pieza_nemos_uno) + 3);
        // $partes_r = [];
        // foreach($activeBoxes as $box){
        //     // if(!isset($pesos[$box])) continue;
        //     // $peso = $pesos[$box];
        //     // $partes_r[] = "({$sum_column}{$row}*{$peso})";
        //     $sum_column++;
        // }
        // dd($sum_column);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->applyFromArray($infoStyleBlue);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("{$column}{$row}:{$columnPlusActiBoxs}{$row}");
        $sheet->setCellValue("{$column}{$row}", "=SUM({$columnInit}{$rowLessOne}:{$columnPlusActiBoxs}{$rowLessOne})");


        // RESUMEN DE CLIENTES Y FINCAS
        $column = chr(ord($columnPlusActiBoxs) + 2);
        $row = 2;
        $columnLastSecction = chr(ord($column) + count($activeBoxes));
        $columnFourSecction = $column;
        // dd($column);
        $sheet->mergeCells("{$column}{$row}:{$columnLastSecction}{$row}"); //P2:S2
        $sheet->setCellValue("{$column}{$row}", 'TOTAL DE PIEZAS ENVIADAS');
        $row++;
        $sheet->getStyle("{$column}2:{$columnLastSecction}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("{$column}2:{$columnLastSecction}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$column}2:{$columnLastSecction}{$row}")->applyFromArray($infoStyleGreen);
        $sheet->getStyle("{$column}2:{$columnLastSecction}{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("{$column}{$row}", 'FINCA');
        $column++;
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                strtoupper($box)
            );
            $column++;
        }
        $row++;
        
        $columnWildcard = chr(ord($columnFourSecction) + 1);
        // dd($palletsInfos);
        
        foreach($clients as $client){
            $sheet->mergeCells("{$columnFourSecction}{$row}:{$columnLastSecction}{$row}"); // 'P' . $l . ':S' . $l
            $sheet->getStyle("{$columnFourSecction}{$row}:{$columnLastSecction}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$columnFourSecction}{$row}:{$columnLastSecction}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$columnFourSecction}{$row}:{$columnLastSecction}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$columnFourSecction}{$row}:{$columnLastSecction}{$row}")->applyFromArray($infoStyleBlue);
            $sheet->setCellValue("{$columnFourSecction}{$row}", $client->name);
            $row++;
            
            foreach($palletsInfos as $pInfo){
                $columnActive = $columnWildcard;
                if($client->id === $pInfo->client_id){
                    // dd($row);
                    $sheet->getStyle("{$columnFourSecction}{$row}")->applyFromArray($infoStyleGreenLightNormal);
                    $sheet->setCellValue("{$columnFourSecction}{$row}", $pInfo->farm->name);
                    foreach ($activeBoxes as $box) {
                        $sheet->setCellValue(
                            "{$columnActive}{$row}", $pInfo->{$box}
                        );
                        $sheet->getStyle("{$columnActive}{$row}")->applyFromArray(array_merge($infoStyleGreenLightNormal, $textAlignmentCenter));
                        $columnActive++;
                    }
                    $row++;
                }
                
            }
        }
        // $row++;

        $columnInit = $columnWildcard;
        $column = $columnInit;
        
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$columnInit}{$row}",
                "=SUM({$columnInit}" . ($row - 1) . ":{$columnInit}{$rowInitialInfoPallet})"
            );
            $sheet->getStyle("{$columnInit}{$row}")->applyFromArray($infoStyleBlue);
            $sheet->getStyle("{$columnInit}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$columnInit}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$columnInit}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $columnInit++;
        }
        $row++;
        $columnPlusActiBoxs = chr(ord($column) + (count($activeBoxes) - 1));
        // dd($columnPlusActiBoxs);
        $rowLessOne = $row - 1;
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->applyFromArray($infoStyleBlue);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("{$column}{$row}:{$columnPlusActiBoxs}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("{$column}{$row}:{$columnPlusActiBoxs}{$row}");
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}{$rowLessOne}:{$columnPlusActiBoxs}{$rowLessOne})");


        // $sheet->setCellValue('Q3', 'HB');
        // $sheet->setCellValue('R3', 'QB');
        // $sheet->setCellValue('S3', 'EB');

        // $sheet->setCellValue('L' . $j, '=SUM(L4:L' . ($j-1) . ')');
        // $sheet->setCellValue('M' . $j, '=SUM(M4:M' . ($j-1) . ')');
        // $sheet->setCellValue('N' . $j, '=SUM(N4:N' . ($j-1) . ')');
        // $j++;
        
        // $sheet->setCellValue('L' . $j, '=L' . ($j-1) . '+M' . ($j-1) . '+N' . ($j-1));

        // // Configuración general
        // $sheet->getDefaultColumnDimension()->setWidth(12);
        // $sheet->getDefaultRowDimension()->setRowHeight(20);

        // $sheet->getPageSetup()
        //     ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
        //     ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
        //     ->setFitToWidth(1)
        //     ->setFitToHeight(1);

        // $sheet->getPageSetup()->setFitToPage(true);

        // $sheet->setShowGridlines(false);

        // // Título principal
        // $sheet->mergeCells('A1:S1');
        // $sheet->setCellValue('A1', 'PLANO DE CARGA - ' . $load->shipment);

        // $sheet->getStyle('A1:S1')->applyFromArray([
        //     'font' => [
        //         'bold' => true,
        //         'size' => 18,
        //         'color' => ['rgb' => 'FFFFFF'],
        //     ],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '1F2937'],
        //     ],
        //     'alignment' => [
        //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        //         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        //     ],
        // ]);

        // $sheet->getRowDimension(1)->setRowHeight(35);

        // // Información de carga
        // $sheet->setCellValue('A2', 'SHIPMENT');
        // $sheet->setCellValue('B2', $load->shipment);
        // $sheet->setCellValue('D2', 'BL');
        // $sheet->setCellValue('E2', $load->bl);
        // $sheet->setCellValue('G2', 'BOOKING');
        // $sheet->setCellValue('H2', $load->booking);
        // $sheet->setCellValue('J2', 'CONTENEDOR');
        // $sheet->setCellValue('K2', $load->container_number);

        // $sheet->getStyle('A2:K2')->getFont()->setBold(true);


        // // Clientes de la carga
        // $clients = $load->coordinations()
        //     ->with('client')
        //     ->get()
        //     ->pluck('client')
        //     ->filter()
        //     ->unique('id')
        //     ->sortBy('name')
        //     ->values();

        // // Título de clientes
        // $sheet->mergeCells('A4:S4');
        // $sheet->setCellValue('A4', 'MARCACIONES DE CLIENTES');

        // $sheet->getStyle('A4:S4')->applyFromArray([
        //     'font' => [
        //         'bold' => true,
        //         'color' => ['rgb' => 'FFFFFF'],
        //     ],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '374151'],
        //     ],
        //     'alignment' => [
        //         'horizontal' => 'center',
        //     ],
        // ]);

        // // Distribución de clientes en 3 columnas
        // $clientRow = 5;
        // $clientColumn = 1;

        // foreach ($clients as $client) {
        //     $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($clientColumn)
        //         . $clientRow;

        //     $sheet->setCellValue($cell, strtoupper($client->name));

        //     $sheet->getStyle($cell)->applyFromArray([
        //         'font' => ['bold' => true, 'size' => 10],
        //         'alignment' => ['horizontal' => 'center'],
        //         'borders' => [
        //             'allBorders' => [
        //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //                 'color' => ['rgb' => 'D1D5DB'],
        //             ],
        //         ],
        //     ]);

        //     $clientColumn += 3;

        //     if ($clientColumn > 19) {
        //         $clientColumn = 1;
        //         $clientRow++;
        //     }
        // }

        // // Plano de paletas
        // $sheet->mergeCells('A8:I8');
        // $sheet->setCellValue('A8', 'PLANO DE DISTRIBUCIÓN DE PALETAS');

        // $sheet->getStyle('A8:I8')->applyFromArray([
        //     'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '1F2937'],
        //     ],
        //     'alignment' => ['horizontal' => 'center'],
        // ]);

        // // Encabezados
        // $sheet->mergeCells('A9:D9');
        // $sheet->setCellValue('A9', 'LADO CHOFER');

        // $sheet->mergeCells('F9:I9');
        // $sheet->setCellValue('F9', 'LADO COPILOTO');

        // $sheet->getStyle('A9:I9')->getFont()->setBold(true);
        // $sheet->getStyle('A9:I9')->getAlignment()->setHorizontal('center');

        // // Obtener paletas
        // $pallets = $load->pallets()
        //     ->with('details.client', 'details.farm')
        //     ->orderBy('counter')
        //     ->get();

        // // Distribución visual
        // $startRow = 10;
        // $palletsPerSide = (int) ceil($pallets->count() / 2);

        // foreach ($pallets as $index => $pallet) {
        //     $side = $index < $palletsPerSide ? 'driver' : 'copilot';

        //     if ($side === 'driver') {
        //         $column = 1;
        //         $row = $startRow + ($index * 2);
        //     } else {
        //         $column = 6;
        //         $row = $startRow + (($index - $palletsPerSide) * 2);
        //     }

        //     $endColumn = $column + 3;

        //     $startCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
        //     $endCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColumn);

        //     // Número de paleta
        //     $sheet->mergeCells("{$startCell}{$row}:{$endCell}{$row}");
        //     $sheet->setCellValue("{$startCell}{$row}", 'PALETA ' . $pallet->number);

        //     $sheet->getStyle("{$startCell}{$row}:{$endCell}{$row}")->applyFromArray([
        //         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        //         'fill' => [
        //             'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //             'startColor' => ['rgb' => '2563EB'],
        //         ],
        //         'alignment' => ['horizontal' => 'center'],
        //     ]);

        //     // Clientes de la paleta
        //     $clients = $pallet->details
        //         ->pluck('client.name')
        //         ->filter()
        //         ->unique()
        //         ->values();

        //     $sheet->mergeCells("{$startCell}" . ($row + 1) . ":{$endCell}" . ($row + 1));
        //     $sheet->setCellValue(
        //         "{$startCell}" . ($row + 1),
        //         $clients->implode(' / ')
        //     );

        //     $sheet->getStyle("{$startCell}" . ($row + 1) . ":{$endCell}" . ($row + 1))
        //         ->getAlignment()
        //         ->setHorizontal('center');

        //     $sheet->getStyle("{$startCell}{$row}:{$endCell}" . ($row + 1))
        //         ->getBorders()
        //         ->getAllBorders()
        //         ->setBorderStyle(
        //             \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        //         );
        // }

        // // Tabla de piezas embarcadas
        // $piecesRow = max($startRow + ($palletsPerSide * 2) + 2, 25);

        // $sheet->mergeCells("A{$piecesRow}:I{$piecesRow}");
        // $sheet->setCellValue("A{$piecesRow}", 'PIEZAS EMBARCADAS');

        // $sheet->getStyle("A{$piecesRow}:I{$piecesRow}")->applyFromArray([
        //     'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '1F2937'],
        //     ],
        //     'alignment' => ['horizontal' => 'center'],
        // ]);

        // $piecesRow++;

        // $headers = ['PALETA', 'FINCA', 'CLIENTE', 'HB', 'QB', 'EB'];

        // $sheet->fromArray($headers, null, "A{$piecesRow}");

        // $sheet->getStyle("A{$piecesRow}:F{$piecesRow}")->applyFromArray([
        //     'font' => ['bold' => true],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => 'DBEAFE'],
        //     ],
        //     'alignment' => ['horizontal' => 'center'],
        // ]);

        // $piecesRow++;

        // foreach ($pallets as $pallet) {
        //     foreach ($pallet->details as $detail) {
        //         $sheet->fromArray([
        //             $pallet->number,
        //             $detail->farm?->name,
        //             $detail->client?->name,
        //             $detail->hb ?? 0,
        //             $detail->qb ?? 0,
        //             $detail->eb ?? 0,
        //         ], null, "A{$piecesRow}");

        //         $piecesRow++;
        //     }
        // }

        // // Totales de piezas enviadas por finca
        // $totalRow = $piecesRow + 2;

        // $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
        // $sheet->setCellValue("A{$totalRow}", 'TOTAL DE PIEZAS ENVIADAS');

        // $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
        //     'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '1F2937'],
        //     ],
        //     'alignment' => ['horizontal' => 'center'],
        // ]);

        // $totalRow++;

        // $sheet->fromArray([
        //     'FINCA',
        //     'HB',
        //     'QB',
        //     'EB',
        // ], null, "A{$totalRow}");

        // $sheet->getStyle("A{$totalRow}:D{$totalRow}")->getFont()->setBold(true);

        // $totalRow++;

        // // Agrupar detalles por finca
        // $farmTotals = $pallets
        //     ->flatMap(fn ($pallet) => $pallet->details)
        //     ->groupBy('farm_id');

        // foreach ($farmTotals as $farmId => $details) {
        //     $farm = $details->first()->farm;

        //     $sheet->setCellValue("A{$totalRow}", $farm?->name);
        //     $sheet->setCellValue("B{$totalRow}", $details->sum('hb'));
        //     $sheet->setCellValue("C{$totalRow}", $details->sum('qb'));
        //     $sheet->setCellValue("D{$totalRow}", $details->sum('eb'));

        //     $totalRow++;
        // }

        // // Total general
        // $sheet->setCellValue("A{$totalRow}", 'TOTAL GENERAL');
        // $sheet->setCellValue("B{$totalRow}", $farmTotals->sum(fn ($details) => $details->sum('hb')));
        // $sheet->setCellValue("C{$totalRow}", $farmTotals->sum(fn ($details) => $details->sum('qb')));
        // $sheet->setCellValue("D{$totalRow}", $farmTotals->sum(fn ($details) => $details->sum('eb')));

        // $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
        //     'font' => ['bold' => true],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => 'E5E7EB'],
        //     ],
        // ]);

        // // Ajustar ancho de columnas
        // foreach (range('A', 'S') as $column) {
        //     $sheet->getColumnDimension($column)->setAutoSize(true);
        // }

        // // Bordes de las tablas
        // $sheet->getStyle("A{$piecesRow}:F" . ($totalRow - 1))
        //     ->getBorders()
        //     ->getAllBorders()
        //     ->setBorderStyle(
        //         \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        //     );

        // // Congelar encabezado
        // $sheet->freezePane('A4');


        return $spreadsheet;
    }

}
