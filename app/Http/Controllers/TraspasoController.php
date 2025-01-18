<?php

namespace App\Http\Controllers;

use App\DetalleTraspaso;
use App\Inventario;
use App\Traspaso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TraspasoController extends Controller
{
    //----lisTado or filtro de fecha--
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;

        $traspasos = Traspaso::select('id', 'almacen_origen', 'almacen_destino', 'fecha_traspaso', 'created_at')
        ->whereBetween('fecha_traspaso', [$fechaInicio, $fechaFin])
            ->paginate(100); // Mover el paginate(4) aquí

        return [
            'pagination' => [
                'total'        => $traspasos->total(),
                'current_page' => $traspasos->currentPage(),
                'per_page'     => $traspasos->perPage(),
                'last_page'    => $traspasos->lastPage(),
                'from'         => $traspasos->firstItem(),
                'to'           => $traspasos->lastItem(),
            ],
            'traspasos' => $traspasos // Corregir 'tras$traspasos' por 'traspasos'
        ];
    }
    //--registrando datos de Trapaso luego pasando datosa  inventario para registrar---
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
    
            // Crear el traspaso
            $traspasos = new Traspaso();
            $traspasos->tipo_traspaso = $request->tipo_traspaso;
            $traspasos->idusuario = \Auth::user()->id;
            $traspasos->almacen_origen = $request->almacen_origen;
            $traspasos->almacen_destino = $request->almacen_destino;
            $traspasos->fecha_traspaso = $request->fecha_traspaso;
            $traspasos->save();
    
            // Obtener los detalles del traspaso
            $detalles = $request->data;
    
            // Recorrer los detalles de los productos
            foreach ($detalles as $detalle) {
                // Encuentra el inventario correspondiente al artículo y almacén de origen
                $inventarioOrigen = Inventario::where('idalmacen', $detalle['idalmacen'])
                    ->where('idarticulo', $detalle['idarticulo'])
                    ->first();
    
                if ($inventarioOrigen) {
                    // Actualiza el saldo_stock restando la cantidad transferida
                    $inventarioOrigen->saldo_stock -= $detalle['cantidad_traspaso'];
                    $inventarioOrigen->cantidad -= $detalle['cantidad_traspaso'];
                    $inventarioOrigen->save();
                }
    
                // Buscar si el artículo ya existe en el almacén de destino
                $inventarioDestino = Inventario::where('idalmacen', $detalle['idalmacendes'])
                    ->where('idarticulo', $detalle['idarticulo'])
                    ->first();
    
                if ($inventarioDestino) {
                    // Si existe, sumar la cantidad transferida
                    $inventarioDestino->saldo_stock += $detalle['cantidad_traspaso'];
                    $inventarioDestino->cantidad += $detalle['cantidad_traspaso'];
                    $inventarioDestino->save();
    
                    // Asignar el idinventario del inventario de destino
                    $idinventarioDestino = $inventarioDestino->id;
                } else {
                    // Si no existe, registrar como un nuevo inventario
                    $inventario = new Inventario();
                    $inventario->idalmacen = $detalle['idalmacendes']; // almacén de destino
                    $inventario->idarticulo = $detalle['idarticulo'];
                    $inventario->saldo_stock = $detalle['cantidad_traspaso'];
                    $inventario->cantidad = $detalle['cantidad_traspaso'];
                    $inventario->save();
    
                    // Asignar el idinventario del inventario de destino
                    $idinventarioDestino = $inventario->id;
                }
    
                // Registrar el detalle del traspaso
                $detalletraspaso = new DetalleTraspaso();
                $detalletraspaso->idtraspaso = $traspasos->id;
                $detalletraspaso->idinventario = $idinventarioDestino; // id del inventario de destino
                $detalletraspaso->cantidad_traspaso = $detalle['cantidad_traspaso'];
                $detalletraspaso->save();
            }
    
            // Confirmar los cambios en la base de datos
            DB::commit();
    
        } catch (Exception $e) {
            DB::rollBack();
            // Manejo del error
            Log::error('Error al registrar traspaso', ['exception' => $e->getMessage()]);
        }
    }
    

    //---listado por id lo que se traspaso--
    public function indexPorID(Request $request){
        if (!$request->ajax()) return redirect('/');
 
        $idtraspaso = $request->idtraspaso;
        $detalletrasp = DetalleTraspaso::join('inventarios', 'detalle_traspasos.idinventario', '=', 'inventarios.id') 
            ->join('traspasos as t', 'detalle_traspasos.idtraspaso', '=', 't.id')
            ->join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
        ->select(
            'detalle_traspasos.id',
            'detalle_traspasos.cantidad_traspaso',
            'inventarios.saldo_stock',
            'inventarios.fecha_vencimiento',
            'articulos.nombre as nombre_producto',
            'articulos.unidad_envase',
            'articulos.precio_costo_unid',
        )
        ->where('detalle_traspasos.idtraspaso','=',$idtraspaso)->get();   
        return [ 'detalletrasp' => $detalletrasp];
    }
    
}
