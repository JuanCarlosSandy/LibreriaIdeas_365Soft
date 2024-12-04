<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use Exception; // Importa la clase Exception
use App\Almacen;
use App\Articulo;
use App\Inventario;

class InventarioImport implements ToCollection
{
    private $almacenMapping;
    private $articuloMapping;

    private $errors = [];

    public function __construct()
    {
        $this->almacenMapping = $this->createAlmacenMapping();
        $this->articuloMapping = $this->createArticuloMapping();

    }

    private function createAlmacenMapping()
    {
        return Almacen::pluck('nombre_almacen', 'id')->toArray();
    }
    private function createArticuloMapping()
    {
        return Articulo::pluck('codigo', 'id')->toArray();
    }  
    public function collection(Collection $rows)
    {
        $rowNumber = 1;
        $importacionExitosa = true;
        try {
            \DB::beginTransaction();
    
            foreach ($rows as $row) {
                $idAlmacen = $this->getAlmacenId($row[0]);
                $idArticulo = $this->getArticuloId($row[1]);
                try {
                    $fechaVencimiento = $row[2] ?? '2099-01-01'; // Asignar fecha por defecto si no se proporciona
                    Inventario::create([
                        'idalmacen' => $idAlmacen,
                        'idarticulo' => $idArticulo,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'saldo_stock' => $row[3],
                        'cantidad'=> $row[3],
                    ]); 
                } catch (Exception $e) {
                    if (!$idAlmacen) {
                        $this->errors[] = "Error fila $rowNumber: No existe 'El almacen $row[0]'";
                    } else if (!$idArticulo) {
                        $this->errors[] = "Error fila $rowNumber: No se encontró 'Articulo $row[1]' en la base de datos";
                    } else {
                        $this->errors[] = "Error al procesar fila: " . $e->getMessage();
                    }
    
                    $importacionExitosa = false;
                }
    
                $rowNumber++;
            }
    
            if ($importacionExitosa) {
                \DB::commit(); // Confirmar la transacción si no hay errores
            } else {
                \DB::rollBack(); // Revertir la transacción en caso de error
            }
        } catch (Exception $e) {
            \DB::rollBack(); // Revertir la transacción en caso de error
            $importacionExitosa = false;
            $this->errors[] = "Error al procesar fila: " . $e->getMessage();
        }
        return $this->getErrorsResponse($importacionExitosa);
    }
    

    
    private function getArticuloId($nombreArticulo)
    {
        $idArticulo = array_search($nombreArticulo, $this->articuloMapping);

        return $idArticulo !== false ? $idArticulo : null;
    }

    private function getAlmacenId($nombreAlmacen)
    {
        $idAlmacen = array_search($nombreAlmacen, $this->almacenMapping);

        return $idAlmacen !== false ? $idAlmacen : null;
    }

    public function getErrors()
    {
        return $this->errors ?? [];
    }

    private function getErrorsResponse($importacionExitosa)
    {
        if (!$importacionExitosa) {
            return response()->json(['errors' => $this->errors], 422);
        } else {
            return response()->json(['mensaje' => 'Importación exitosa'], 200);
        }
    }

}