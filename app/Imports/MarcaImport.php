<?php

namespace App\Imports;

use App\Marca;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
//use Maatwebsite\Excel\Concerns\WithHeadings;

// class MarcaImport implements ToCollection
// {
//     /**
//     * @param Collection $collection
//     */
//     public function collection(Collection $collection)
//     {
//         //
//     }
// }

//--------------------------------------------------------------------
// class MarcaImport implements ToCollection, WithHeadingRow
// {
//     public function collection(Collection $rows)
//     {
//         foreach ($rows as $row) {
//             // Verifica si el valor de 'nombre' no es nulo o en blanco
//             if (!empty($row['nombre'])) {
//                 Marca::create([
//                     'nombre' => $row['nombre'],
//                     'condicion' => $row['condicion'],
//                 ]);
//             }
//         }
//     }
// }
//-----------------------------------------------------------------
/*
class MarcaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Verifica si el valor de 'nombre' no es nulo o en blanco
            if (!empty($row['nombre'])) {
           // Busca si ya existe una marca con el mismo nombre
                $existingMarca = Marca::where('nombre', $row['nombre'])->first();
        
                // Si no existe, inserta un nuevo registro
                if (!$existingMarca) {
                    Marca::create([
                        'nombre' => $row['nombre'],
                        'condicion' => $row['condicion'],
                    ]);
                }
                // Puedes agregar un manejo especial si deseas hacer algo cuando ya existe el registro
            }
        }
    }
}
*/

//nuevo codigo implementado 11/03/2025. 
class MarcaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        Log::info('Iniciando importación de ' . count($rows) . ' filas');
        
        foreach ($rows as $index => $row) {
            Log::info('Procesando fila ' . ($index + 1), ['row' => json_encode($row)]);
            
            $nombre = null;
            
            // Intenta encontrar el nombre en diferentes formatos posibles
            if (isset($row['nombre'])) {
                $nombre = $row['nombre'];
                Log::info('Encontrado nombre en columna "nombre": ' . $nombre);
            } 
            elseif (isset($row['nombrecondicion'])) {
                $nombre = trim(str_replace(';', '', $row['nombrecondicion']));
                Log::info('Encontrado nombre en columna "nombrecondicion": ' . $nombre);
            }
            elseif (isset($row[0])) {
                $nombre = $row[0];
                Log::info('Encontrado nombre en primera columna: ' . $nombre);
            }
            
            if (!empty($nombre)) {
                // Busca si ya existe una marca con el mismo nombre
                $existingMarca = Marca::where('nombre', $nombre)->first();
                
                // Si no existe, inserta un nuevo registro
                if (!$existingMarca) {
                    try {
                        Log::info('Creando marca: ' . $nombre);
                        
                        $marca = Marca::create([
                            'nombre' => $nombre,
                            'condicion' => '1',  // Valor predeterminado
                        ]);
                        
                        if ($marca) {
                            Log::info('Marca creada exitosamente con ID: ' . $marca->id);
                        } else {
                            Log::error('No se pudo crear la marca');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al crear marca: ' . $e->getMessage());
                    }
                } else {
                    Log::info('Marca ya existe: ' . $nombre);
                }
            } else {
                Log::warning('Fila ' . ($index + 1) . ' ignorada: nombre no encontrado');
            }
        }
        
        Log::info('Finalizada importación de marcas');
    }
}