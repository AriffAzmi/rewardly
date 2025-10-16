<?php

namespace App\Exports;

use App\Models\Voucher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class RedemptionLinksExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithColumnWidths,
    WithTitle
{
    protected $importId;
    protected $baseUrl;

    public function __construct(string $importId, string $baseUrl = 'https://app.rewardly.com/redeem-voucher')
    {
        $this->importId = $importId;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get vouchers collection
     */
    public function collection()
    {
        return Voucher::where('import_id', $this->importId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Set headings
     */
    public function headings(): array
    {
        return [
            'Redemption Link',
        ];
    }

    /**
     * Map each voucher to export format
     */
    public function map($voucher): array
    {
        return [
            "{$this->baseUrl}/{$voucher->code}",
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Make links blue and underlined
            'A' => [
                'font' => [
                    'color' => ['rgb' => '0563C1'],
                    'underline' => Font::UNDERLINE_SINGLE,
                ],
            ],
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 60,
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Redemption Links';
    }
}