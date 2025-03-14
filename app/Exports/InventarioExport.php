<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithStyles
{
    protected $inventario;

    public function __construct($inventario)
    {
        $this->inventario = $inventario;
    }

    public function collection()
    {
        return $this->inventario;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Artículo',
            'Stock Actual',
            'Proveedor',
            'Ajustado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}