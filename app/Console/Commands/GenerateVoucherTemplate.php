<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GenerateVoucherTemplate extends Command
{
    protected $signature = 'voucher:generate-template';
    protected $description = 'Generate sample voucher import Excel template';

    public function handle()
    {
        $this->info('Generating voucher import template...');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set sheet title
        $sheet->setTitle('Voucher Import');

        // Set headers
        $headers = ['NAME', 'DENO', 'PERCENTAGE', 'UNIQUE_KEY'];
        $sheet->fromArray($headers, null, 'A1');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Set row height for headers
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Add sample data
        $sampleData = [
            ['Petronas RM50', '50.00', '50.00', 'ABC01DEF321'],
            ['Petronas RM50', '50.00', '50.00', 'ABC02DEF322'],
            ['Petronas RM50', '50.00', '50.00', 'ABC03DEF323'],
            ['Petronas RM100', '100.00', '45.00', 'ABC04DEF324'],
            ['Shell RM30', '30.00', '60.00', 'ABC05DEF325'],
            ['Grab RM20', '20.00', '55.00', 'ABC06DEF326'],
            ['Lazada RM50', '50.00', '40.00', 'ABC07DEF327'],
            ['Shopee RM25', '25.00', '50.00', 'ABC08DEF328'],
            ['Touch n Go RM30', '30.00', '48.00', 'ABC09DEF329'],
            ['Boost RM50', '50.00', '52.00', 'ABC10DEF330'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Style data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:D11')->applyFromArray($dataStyle);

        // Center align numeric columns
        $sheet->getStyle('B2:C11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set minimum column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(18);

        // Add notes sheet
        $notesSheet = $spreadsheet->createSheet(1);
        $notesSheet->setTitle('Instructions');
        
        $instructions = [
            ['VOUCHER IMPORT TEMPLATE - INSTRUCTIONS'],
            [''],
            ['Column Details:'],
            [''],
            ['Column Name', 'Description', 'Format', 'Example', 'Required'],
            ['NAME', 'Voucher description/name', 'Text', 'Petronas RM50', 'Yes'],
            ['DENO', 'Denomination/Retail price', 'Number (2 decimal places)', '50.00', 'Yes'],
            ['PERCENTAGE', 'Discount percentage', 'Number (no % sign)', '50.00 for 50%', 'Yes'],
            ['UNIQUE_KEY', 'Unique voucher code', 'Text (must be unique)', 'ABC01DEF321', 'Yes'],
            [''],
            ['Important Notes:'],
            ['1. The first row MUST contain the column headers exactly: NAME, DENO, PERCENTAGE, UNIQUE_KEY'],
            ['2. Column headers are case-insensitive (name, NAME, Name all work)'],
            ['3. All columns are REQUIRED - do not leave any cell empty'],
            ['4. UNIQUE_KEY must be unique for each row - no duplicates allowed'],
            ['5. DENO and PERCENTAGE must be numeric values'],
            ['6. PERCENTAGE is just the number (e.g., 50 for 50%, not 0.5)'],
            ['7. Maximum file size: 20MB'],
            ['8. Supported formats: .xlsx, .xls, .csv'],
            ['9. The system can process up to 50,000 rows efficiently'],
            [''],
            ['How It Works:'],
            ['- NAME will be saved as voucher description'],
            ['- DENO will be saved as retail price and denomination'],
            ['- PERCENTAGE will be saved as discount percentage'],
            ['- UNIQUE_KEY will be saved as voucher code'],
            ['- Cost Price will be auto-calculated: DENO - (DENO × PERCENTAGE / 100)'],
            ['- SKU will be auto-generated based on merchant and denomination'],
            ['- Status will be set to "active" by default'],
            ['- Expiry date will be set to 1 year from import date'],
            [''],
            ['Example Calculation:'],
            ['If DENO = 50.00 and PERCENTAGE = 50.00:'],
            ['Cost Price = 50.00 - (50.00 × 50 / 100) = RM 25.00'],
            ['You save = 50% discount'],
            [''],
            ['Generated on: ' . date('Y-m-d H:i:s') . ' UTC by AriffAzmi'],
        ];
        
        $notesSheet->fromArray($instructions, null, 'A1');
        
        // Style instructions
        $notesSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4472C4']],
        ]);
        
        $notesSheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);
        
        $notesSheet->getStyle('A5:E5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ],
        ]);
        
        $notesSheet->getStyle('A11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);
        
        $notesSheet->getStyle('A21')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);
        
        $notesSheet->getStyle('A32')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);
        
        // Auto-size columns in notes sheet
        foreach (range('A', 'E') as $col) {
            $notesSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $filePath = public_path('templates/voucher-import-sample.xlsx');
        
        // Create directory if not exists
        if (!file_exists(public_path('templates'))) {
            mkdir(public_path('templates'), 0755, true);
        }
        
        $writer->save($filePath);

        $this->info('✅ Sample template generated successfully!');
        $this->info('📁 Location: ' . $filePath);
        $this->info('🌐 URL: ' . url('templates/voucher-import-sample.xlsx'));
        $this->line('');
        $this->info('You can now download this file from your application.');

        return 0;
    }
}