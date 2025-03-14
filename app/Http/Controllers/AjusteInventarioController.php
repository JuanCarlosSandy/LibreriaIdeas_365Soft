<?php

namespace App\Http\Controllers;

use App\AjusteInvetario;
use App\Inventario;
use App\TipoBajas;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\Request;

class AjusteInventarioController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $ajuste = AjusteInvetario::join('articulos', 'ajuste_invetarios.producto', '=', 'articulos.id')
            ->join('tipo_bajas', 'ajuste_invetarios.idtipobajas', '=', 'tipo_bajas.id')
            ->join('almacens', 'ajuste_invetarios.almacen', '=', 'almacens.id')
            ->select(
                'ajuste_invetarios.*',
                'articulos.nombre as nombre',
                'tipo_bajas.nombre as tipo',
                'almacens.nombre_almacen as nombre_almacen'
            )
            ->orderBy('id', 'desc')->paginate(10);
        }
        else{
            $ajuste = AjusteInvetario::join('articulos', 'ajuste_invetarios.producto', '=', 'articulos.id')
            ->join('tipo_bajas', 'ajuste_invetarios.idtipobajas', '=', 'tipo_bajas.id')
            ->join('almacens', 'ajuste_invetarios.almacen', '=', 'almacens.id')
            ->select(
                'ajuste_invetarios.*',
                'articulos.nombre as nombre',
                'tipo_bajas.nombre as tipo',
                'almacens.nombre_almacen as nombre_almacen'
            )
            ->where($criterio, 'like', '%'. $buscar . '%')
            ->orderBy('id', 'desc')->paginate(6);
        }
        

        return [
            'pagination' => [
                'total'        => $ajuste->total(),
                'current_page' => $ajuste->currentPage(),
                'per_page'     => $ajuste->perPage(),
                'last_page'    => $ajuste->lastPage(),
                'from'         => $ajuste->firstItem(),
                'to'           => $ajuste->lastItem(),
            ],
            'ajuste' => $ajuste
        ];
    }

    public function listarMotivo(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar == '') {
            $motivo = TipoBajas::orderBy('id', 'desc')->paginate(6);
        } else {
            $motivo = TipoBajas::where('nombre', 'like', '%' . $buscar . '%')->orderBy('id', 'desc')->paginate(6);
        }
        

        return [
            'pagination' => [
                'total'        => $motivo->total(),
                'current_page' => $motivo->currentPage(),
                'per_page'     => $motivo->perPage(),
                'last_page'    => $motivo->lastPage(),
                'from'         => $motivo->firstItem(),
                'to'           => $motivo->lastItem(),
            ],
            'motivos' => $motivo
        ];
    }
    //nuevo codigo para obtener el stock actual añadido en fecha 13/03/25
    public function obtenerStock(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        // Validamos que se envíe el id del producto y del almacén
        $producto = $request->producto;
        $almacen = $request->almacen;

        // Buscar el stock actual en la tabla de inventario
        $inventario = Inventario::where('idarticulo', $producto)
            ->where('idalmacen', $almacen)
            ->first();

        // Retornar el stock actual o 0 si no existe en el inventario
        return response()->json(['stock_actual' => $inventario ? $inventario->saldo_stock : 0]);
    }


    public function store(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $ajuste = new AjusteInvetario();
        $ajuste->cantidad = $request->cantidad;
        $ajuste->idtipobajas = $request->idtipobaja;
        $ajuste->producto = $request->producto;
        $ajuste->almacen = $request->idAlmacenSeleccionado;

        $ajuste->save();

        $detalle = [
            'cantidad' => $ajuste->cantidad,
            'idarticulo' => $ajuste->producto
        ];
        $this->actualizarInventario($ajuste->almacen, $detalle);

        return ['idArticulo' => $ajuste->id];
    }

    private function actualizarInventario($idAlmacen, $detalle)
{
    $cantidadRestante = $detalle['cantidad'];
    $fechaActual = now();
    $inventarios = Inventario::where('idalmacen', $idAlmacen)
        ->where('idarticulo', $detalle['idarticulo'])
        ->orderBy('id')
        ->get();

    foreach ($inventarios as $inventario) {
        if ($cantidadRestante <= 0) {
            break;
        }

        if ($inventario->saldo_stock >= $cantidadRestante) {
            $inventario->saldo_stock -= $cantidadRestante;
            $cantidadRestante = 0;
        } else {
            $cantidadRestante -= $inventario->saldo_stock;
            $inventario->saldo_stock = 0;
        }

        // Cambiar el campo 'verificado' a 'STOCK VERIFICADO'
        $inventario->verificado = 'STOCK VERIFICADO';

        // Guardar los cambios en el inventario
        $inventario->save();
    }
}


    public function registrarMotivo(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $motivo = new TipoBajas();

        $motivo->nombre = $request->nombre;
        $motivo->save();
    }
}
