<?php

namespace App\Http\Controllers;

use App\DetalleVenta;
use App\Moneda;
use App\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesVentas extends Controller
{
    public function ResumenVentasPorDocumento(Request $request){
        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;
        $fechaInicio = $fechaInicio . ' 00:00:00';
        $fechaFin = $fechaFin . ' 23:59:59';
        $moneda = $request->moneda;
        $ventas = Venta::join('personas','ventas.idcliente','=','personas.id')
        ->join('users','ventas.idusuario','=','users.id')
        ->join('tipo_ventas','ventas.idtipo_venta','=','tipo_ventas.id')
        ->join('roles','users.idrol','=','roles.id')
        ->join('sucursales','users.idsucursal','=','sucursales.id')
        ->select('ventas.num_comprobante as Factura',
                'ventas.id',
                'sucursales.nombre as Nombre_sucursal',
                'ventas.fecha_hora',
                DB::raw("'$moneda' as Tipo_Cambio"),
                'tipo_ventas.nombre_tipo_ventas as Tipo_venta',
                'roles.nombre AS nombre_rol',
                'users.usuario',
                'personas.nombre',
                'ventas.total AS importe_BS',
                DB::raw("ROUND((ventas.total / $moneda), 2) AS importe_usd"))
        ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
        ->orderBy('ventas.fecha_hora', 'asc');

        if ($request->has('estadoVenta')) {
            $estado_venta = $request->estadoVenta;
            if ($estado_venta !== 'Todos') {
                $ventas->where('ventas.estado', '=', $estado_venta);
            }
        }
        
        if ($request->has('sucursal') && $request->sucursal !== 'undefined') {
            $sucursal = $request->sucursal;
            $ventas->where('sucursales.id', $sucursal);
        }

        if ($request->has('ejecutivoCuentas') && $request->ejecutivoCuentas !== 'undefined') {
            $ejecutivoCuentas = $request->ejecutivoCuentas;
            $ventas->where('ventas.idusuario' , $ejecutivoCuentas);
        }

        if ($request->has('idcliente') && $request->idcliente !== 'undefined') {
            $cliente = $request->idcliente;
            $ventas->where('ventas.idcliente' , $cliente);
        }


        $ventas = $ventas->get();

        $total_importeBs =0;
        $total_importeUSD = 0;
        foreach ($ventas as &$venta){
            $total_importeBs += $venta->importe_BS;
            $total_importeUSD += $venta->importe_usd;
        }
        return['ventas' => $ventas,
                'total_BS'=>$total_importeBs,
                'total_USD'=>$total_importeUSD];

    }
    public function ventasPorProducto(Request $request){
        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;
        $fechaInicio = $fechaInicio . ' 00:00:00';
        $fechaFin = $fechaFin . ' 23:59:59';
        $ventas = Venta::join('detalle_ventas','ventas.id','detalle_ventas.idventa')
        ->join('personas','personas.id','=','ventas.idcliente')
        ->join('articulos','detalle_ventas.idarticulo','=','articulos.id')
        ->join('categorias','articulos.idcategoria','=','categorias.id')
        ->join('marcas','articulos.idmarca','=','marcas.id')
        ->join('industrias','articulos.idindustria','=','industrias.id')
        ->join('medidas','articulos.idmedida','=','medidas.id')
        ->join('users','ventas.idusuario','=','users.id')
        ->join('sucursales','users.idsucursal','=','sucursales.id')
        ->select('ventas.fecha_hora',
                'personas.nombre',
            'detalle_ventas.*',
            'articulos.codigo',
            'articulos.descripcion',
            'categorias.nombre as nombre_categoria',
            'marcas.nombre as nombre_marca',
            'industrias.nombre as nombre_industria',
            'medidas.descripcion_medida as medida')
        ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);

        if ($request->has('sucursal') && $request->sucursal !== 'undefined') {
                $sucursal = $request->sucursal;
                $ventas->where('sucursales.id', $sucursal);
            }

        if ($request->has('idcliente') && $request->idcliente !== 'undefined') {
                $cliente = $request->idcliente;
                $ventas->where('ventas.idcliente' , $cliente);
            }
        if ($request->has('articulo') && $request->articulo !== 'undefined') {
                $articulo = $request->articulo;
                $ventas->where('detalle_ventas.idarticulo' , $articulo);
            }
        if ($request->has('marca') && $request->marca !== 'undefined') {
                $idmarca = $request->marca;
                $ventas->where('articulos.idmarca' , $idmarca);
                
            }
        if ($request->has('linea') && $request->linea !== 'undefined') {
                $idlinea = $request->linea;
                $ventas->where('articulos.idcategoria' , $idlinea);
                
            }
        if ($request->has('industria') && $request->industria !== 'undefined') {
                $idindustria = $request->industria;
                $ventas->where('articulos.idindustria' , $idindustria);
                
            }
        $ventas = $ventas->get();
        return ['resultados' =>$ventas];
    }
    
    public function ResumenVentasPorDocumentoDetallado(Request $request){
        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;
        $fechaInicio = $fechaInicio . ' 00:00:00';
        $fechaFin = $fechaFin . ' 23:59:59';
        $moneda = $request->moneda;
        $ventas = DetalleVenta::select(
            'articulos.codigo as codigo_item',
            'articulos.nombre as nombre_articulo',
            'ventas.num_comprobante as Factura',
            'ventas.id',
            'ventas.total as Importe Bs',
            'ventas.fecha_hora as Fecha',
            'personas.id as id_cliente',
            'personas.nombre as Cliente',
            'users.usuario as Vendedor',
            'tipo_ventas.nombre_tipo_ventas as Tipo de venta',
            'roles.nombre as Ejecutivo de Venta',
            'sucursales.nombre as Sucursal',
            'articulos.nombre',
            'detalle_ventas.cantidad',
            'detalle_ventas.precio',
            'categorias.nombre as nombre_categoria',
            'marcas.nombre as nombre_marca',
            'industrias.nombre as nombre_industria',
            'medidas.descripcion_medida as medida',
            'personas.num_documento as nit',
            
            DB::raw("ROUND((detalle_ventas.precio / detalle_ventas.cantidad), 2) AS precio_unitario"),
            DB::raw("'$moneda' as Tipo_cambio"),
            DB::raw("ROUND((detalle_ventas.precio / $moneda), 2) AS importe_usd")
             )
            ->join('ventas', 'detalle_ventas.idventa', '=', 'ventas.id')
            ->join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->join('users', 'ventas.idusuario', '=', 'users.id')
            ->join('tipo_ventas', 'ventas.idtipo_venta', '=', 'tipo_ventas.id')
            ->join('roles', 'users.idrol', '=', 'roles.id')
            ->join('sucursales', 'users.idsucursal', '=', 'sucursales.id')
            ->join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')

            ->join('categorias','articulos.idcategoria','=','categorias.id')
            ->join('marcas','articulos.idmarca','=','marcas.id')
            ->join('industrias','articulos.idindustria','=','industrias.id')
            ->join('medidas','articulos.idmedida','=','medidas.id')
            ->orderBy('personas.nombre')
            ->orderBy('ventas.fecha_hora')
            ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
        if ($request->has('estadoVenta')) {
            $estado_venta = $request->estadoVenta;
            if ($estado_venta !== 'Todos') {
                $ventas->where('ventas.estado', '=', $estado_venta);
            }
        }
        
        if ($request->has('sucursal') && $request->sucursal !== 'undefined') {
            $sucursal = $request->sucursal;
            $ventas->where('sucursales.id', $sucursal);
        }

        if ($request->has('ejecutivoCuentas') && $request->ejecutivoCuentas !== 'undefined') {
            $ejecutivoCuentas = $request->ejecutivoCuentas;
            $ventas->where('ventas.idusuario' , $ejecutivoCuentas);
        }

        if ($request->has('idcliente') && $request->idcliente !== 'undefined') {
            $cliente = $request->idcliente;
            $ventas->where('ventas.idcliente' , $cliente);
        }
        $ventas = $ventas->get();
    
    $totalVentasPorCliente = [];
    
    foreach ($ventas as $venta) {
        $idCliente = $venta->id_cliente;
        $cantidadVenta = $venta->cantidad;
        $precioVenta = $venta->precio;
    
        if (!isset($totalVentasPorCliente[$idCliente])) {
            $totalVentasPorCliente[$idCliente] = [
                'total_cantidad' => 0,
                'total_precio' => 0,
                'index' => null,];
        }
    
        $totalVentasPorCliente[$idCliente]['total_cantidad'] += $cantidadVenta;
        $totalVentasPorCliente[$idCliente]['total_precio'] += $precioVenta;
        $totalVentasPorCliente[$idCliente]['index'] = $venta->id; 
    }
    foreach ($ventas as $venta) {
        $idCliente = $venta->id_cliente;

        if (isset($totalVentasPorCliente[$idCliente]) && $venta->id == $totalVentasPorCliente[$idCliente]['index']) {
            $venta->total_cantidad_cliente = $totalVentasPorCliente[$idCliente]['total_cantidad'];
            $venta->total_precio_cliente = $totalVentasPorCliente[$idCliente]['total_precio'];
        }
    }

    return [
        'ventas' => $ventas,
    ];
    }

}