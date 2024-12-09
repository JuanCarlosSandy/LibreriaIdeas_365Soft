<?php

namespace App\Http\Controllers;
use NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Venta;
use App\Persona;
use App\Articulo;
use App\Inventario;
use App\DetalleVenta;
use App\User;
use App\Ingreso;
use App\CreditoVenta;
use App\CuotasCredito;
use App\Empresa;
use App\Caja;
use App\Factura;
use App\FacturaFueraLinea;
use App\FacturaInstitucional;
use App\Helpers\CustomHelpers;
use App\Http\Controllers\CifrasEnLetrasController;
use Illuminate\Support\Facades\Log;
use App\Notifications\NotifyAdmin;
use FPDF;
use chillerlan\QRCode\{QRCode, QROptions};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Environment\Console;
use SimpleXMLElement;
use SoapClient;
use TheSeer\Tokenizer\Exception;
use App\Medida;
use App\MotivoAnulacion;
use App\PuntoVenta;
use App\Rol;
use App\Sucursales;
use Illuminate\Support\Facades\File;
use Phar;
use PharData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function Ramsey\Uuid\v1;

class VentaController extends Controller
{
    private $fecha_formato;

    public function __construct()
    {
        session_start();
    }
public function index(Request $request)
{
    if (!$request->ajax()) {
        return redirect('/');
    }

    $buscar = $request->buscar;
    $usuario = \Auth::user();
    $usuario = \Auth::user();
    $idrol = $usuario->idrol;
    $idsucursal = $usuario->idsucursal;

    // Obtener el codigoPuntoVenta
    $codigoPuntoVenta = '';
    if (!empty($usuario->idpuntoventa)) {
        $puntoVenta = PuntoVenta::find($usuario->idpuntoventa);
        if ($puntoVenta) {
            $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
        }
    }

    // Obtener el codigoSucursal
    $codigoSucursal = '';
    $sucursal = Sucursales::find($idsucursal);
    if ($sucursal) {
        $codigoSucursal = $sucursal->codigoSucursal;
    }

    $query = Venta::join('users', 'ventas.idusuario', '=', 'users.id')
    ->join('personas', 'ventas.idcliente', '=', 'personas.id')
    ->leftJoin('facturas', 'ventas.id', '=' , 'facturas.idventa')
    ->select(
        'ventas.tipo_comprobante as tipo_comprobante',
        'ventas.idcliente',
        'ventas.id',
        'ventas.tipo_comprobante',
        'ventas.serie_comprobante',
        'ventas.num_comprobante',
        'ventas.fecha_hora',
        'ventas.impuesto',
        'ventas.total',
        'ventas.estado',
        'users.usuario',
        'personas.nombre as razonSocial',
        'personas.num_documento as documentoid',
        'facturas.id as idFactura',
        'facturas.numeroFactura',
        'facturas.cuf',
        'facturas.cufd',
        'facturas.codigoControl',
        'facturas.correo',
        'facturas.fechaEmision'
    )
    ->orderBy('ventas.id', 'desc');

    // Filtrar por usuario si no es administrador
    if ($usuario->idrol != 1) { // Asumiendo que el rol 1 es el de administrador
        $query->where('ventas.idusuario', $usuario->id);
    }

    if (!empty($buscar)) {
        $query->where(function ($q) use ($buscar) {
            $q->where('ventas.num_comprobante', 'like', '%' . $buscar . '%')
              ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
              ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
              ->orWhere('ventas.fecha_hora', 'like', '%' . $buscar . '%')
              ->orWhere('users.usuario', 'like', '%' . $buscar . '%');
        });
    }

    $ventas = $query->paginate(5);

    return [
        'pagination' => [
            'total' => $ventas->total(),
            'current_page' => $ventas->currentPage(),
            'per_page' => $ventas->perPage(),
            'last_page' => $ventas->lastPage(),
            'from' => $ventas->firstItem(),
            'to' => $ventas->lastItem(),
        ],
        'ventas' => $ventas,
        'usuario' => $usuario,
        'codigoPuntoVenta' => $codigoPuntoVenta,
        'codigoSucursal' => $codigoSucursal,
    ];
}

public function indexFactura(Request $request)
{
    if (!$request->ajax()) {
        return redirect('/');
    }

    $buscar = $request->buscar;
    $usuario = \Auth::user();
    $usuario = \Auth::user();
    $idrol = $usuario->idrol;
    $idsucursal = $usuario->idsucursal;

    // Obtener el codigoPuntoVenta
    $codigoPuntoVenta = '';
    if (!empty($usuario->idpuntoventa)) {
        $puntoVenta = PuntoVenta::find($usuario->idpuntoventa);
        if ($puntoVenta) {
            $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
        }
    }

    // Obtener el codigoSucursal
    $codigoSucursal = '';
    $sucursal = Sucursales::find($idsucursal);
    if ($sucursal) {
        $codigoSucursal = $sucursal->codigoSucursal;
    }

    $query = Factura::join('ventas', 'facturas.idventa', '=', 'ventas.id')
        ->join('users', 'ventas.idusuario', '=', 'users.id')
        ->join('personas', 'ventas.idcliente', '=', 'personas.id')
        //->join('facturas', 'ventas.id', '=' , 'facturas.idventa')
        ->select(
            'ventas.tipo_comprobante as tipo_comprobante',
            'ventas.idcliente',
            'ventas.id',
            'ventas.tipo_comprobante',
            'ventas.serie_comprobante',
            'ventas.num_comprobante',
            'ventas.fecha_hora',
            'ventas.impuesto',
            'ventas.total',
            'ventas.estado',
            'users.usuario',
            'personas.nombre as razonSocial',
            'personas.num_documento as documentoid',
            'facturas.id as idFactura',
            'facturas.numeroFactura',
            'facturas.cuf',
            'facturas.cufd',
            'facturas.codigoControl',
            'facturas.correo',
            'facturas.fechaEmision'
        )
        ->orderBy('ventas.id', 'desc');

    // Filtrar por usuario si no es administrador
    if ($usuario->idrol != 1) { // Asumiendo que el rol 1 es el de administrador
        $query->where('ventas.idusuario', $usuario->id);
    }

    if (!empty($buscar)) {
        $query->where(function ($q) use ($buscar) {
            $q->where('ventas.num_comprobante', 'like', '%' . $buscar . '%')
              ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
              ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
              ->orWhere('ventas.fecha_hora', 'like', '%' . $buscar . '%')
              ->orWhere('users.usuario', 'like', '%' . $buscar . '%');
        });
    }

    $ventas = $query->paginate(5);

    return [
        'pagination' => [
            'total' => $ventas->total(),
            'current_page' => $ventas->currentPage(),
            'per_page' => $ventas->perPage(),
            'last_page' => $ventas->lastPage(),
            'from' => $ventas->firstItem(),
            'to' => $ventas->lastItem(),
        ],
        'ventas' => $ventas,
        'usuario' => $usuario,
        'codigoPuntoVenta' => $codigoPuntoVenta,
        'codigoSucursal' => $codigoSucursal,
    ];
}

public function indexRecibo(Request $request)
{
    if (!$request->ajax()) {
        return redirect('/');
    }

    $buscar = $request->buscar;
    $usuario = \Auth::user();
    $usuario = \Auth::user();
    $idrol = $usuario->idrol;
    $idsucursal = $usuario->idsucursal;

    // Obtener el codigoPuntoVenta
    $codigoPuntoVenta = '';
    if (!empty($usuario->idpuntoventa)) {
        $puntoVenta = PuntoVenta::find($usuario->idpuntoventa);
        if ($puntoVenta) {
            $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
        }
    }

    // Obtener el codigoSucursal
    $codigoSucursal = '';
    $sucursal = Sucursales::find($idsucursal);
    if ($sucursal) {
        $codigoSucursal = $sucursal->codigoSucursal;
    }

    $query = Venta::join('users', 'ventas.idusuario', '=', 'users.id')
        ->join('personas', 'ventas.idcliente', '=', 'personas.id')
        ->select(
            'ventas.tipo_comprobante as tipo_comprobante',
            'ventas.idcliente',
            'ventas.id',
            'ventas.tipo_comprobante',
            'ventas.serie_comprobante',
            'ventas.num_comprobante',
            'ventas.fecha_hora',
            'ventas.impuesto',
            'ventas.total',
            'ventas.estado',
            'users.usuario',
            'personas.nombre as razonSocial',
            'personas.num_documento as documentoid',

        )
        ->where('ventas.tipo_comprobante', '=', 'RESIVO')
        ->orderBy('ventas.id', 'desc');

    // Filtrar por usuario si no es administrador
    if ($usuario->idrol != 1) { // Asumiendo que el rol 1 es el de administrador
        $query->where('ventas.idusuario', $usuario->id);
    }

    if (!empty($buscar)) {
        $query->where(function ($q) use ($buscar) {
            $q->where('ventas.num_comprobante', 'like', '%' . $buscar . '%')
              ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
              ->orWhere('personas.nombre', 'like', '%' . $buscar . '%')
              ->orWhere('ventas.fecha_hora', 'like', '%' . $buscar . '%')
              ->orWhere('users.usuario', 'like', '%' . $buscar . '%');
        });
    }

    $ventas = $query->paginate(5);

    return [
        'pagination' => [
            'total' => $ventas->total(),
            'current_page' => $ventas->currentPage(),
            'per_page' => $ventas->perPage(),
            'last_page' => $ventas->lastPage(),
            'from' => $ventas->firstItem(),
            'to' => $ventas->lastItem(),
        ],
        'ventas' => $ventas,
        'usuario' => $usuario,
        'codigoPuntoVenta' => $codigoPuntoVenta,
        'codigoSucursal' => $codigoSucursal,
    ];
}


    public function ventaOffline(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $idtipoventa = $request->idtipo_venta;

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $usuario = \Auth::user();

        if ($buscar == '') {
            $facturasOffline = FacturaFueraLinea::join('ventas', 'factura_fuera_lineas.idventa', '=', 'ventas.id')
                ->join('personas', 'factura_fuera_lineas.idcliente', '=', 'personas.id')
                ->join('users', 'ventas.idusuario', '=', 'users.id')
                ->select(
                    'factura_fuera_lineas.*',
                    'ventas.tipo_comprobante as tipo_comprobante',
                    'ventas.serie_comprobante',
                    'ventas.num_comprobante as num_comprobante',
                    'ventas.fecha_hora as fecha_hora',
                    'ventas.impuesto as impuesto',
                    'ventas.total as total',
                    'ventas.estado as estado',
                    'personas.nombre as razonSocial',
                    'personas.email as email',
                    'personas.num_documento as documentoid',
                    'personas.complemento_id as complementoid',
                    'users.usuario as usuario'
                )
                ->orderBy('factura_fuera_lineas.id', 'desc')->paginate(3);
        } else {
            $facturasOffline = FacturaFueraLinea::join('ventas', 'factura_fuera_lineas.idventa', '=', 'ventas.id')
                ->join('personas', 'factura_fuera_lineas.idcliente', '=', 'personas.id')
                ->join('users', 'ventas.idusuario', '=', 'users.id')
                ->select(
                    'factura_fuera_lineas.*',
                    'ventas.tipo_comprobante as tipo_comprobante',
                    'ventas.serie_comprobante',
                    'ventas.num_comprobante as num_comprobante',
                    'ventas.fecha_hora as fecha_hora',
                    'ventas.impuesto as impuesto',
                    'ventas.total as total',
                    'ventas.estado as estado',
                    'personas.nombre as razonSocial',
                    'personas.email as email',
                    'personas.num_documento as documentoid',
                    'personas.complemento_id as complementoid',
                    'users.usuario as usuario'
                )
                ->where('factura_fuera_lineas.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('factura_fuera_lineas.id', 'desc')->paginate(3);
        }

        return [
            'pagination' => [
                'total' => $facturasOffline->total(),
                'current_page' => $facturasOffline->currentPage(),
                'per_page' => $facturasOffline->perPage(),
                'last_page' => $facturasOffline->lastPage(),
                'from' => $facturasOffline->firstItem(),
                'to' => $facturasOffline->lastItem(),
            ],
            'facturasOffline' => $facturasOffline,
            'usuario' => $usuario
        ];
    }

    public function indexBuscar(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $usuario = \Auth::user();
        $idRoles = $request->idRoles;
        $idAlmacen = $request->idAlmacen;
        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;
        $idRoles = ($idRoles == 0) ? null : $idRoles;
        $idAlmacen = ($idAlmacen == 0) ? null : $idAlmacen;

        if ($buscar == '') {
            $ventas = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
                ->join('users', 'ventas.idusuario', '=', 'users.id')
                ->join('detalle_ventas', 'ventas.id', '=', 'detalle_ventas.idventa')
                ->join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
                ->join('inventarios', 'articulos.id', '=', 'inventarios.idarticulo')
                ->select(
                    'ventas.id',
                    'ventas.tipo_comprobante',
                    'ventas.serie_comprobante',
                    'ventas.num_comprobante',
                    'ventas.fecha_hora',
                    'ventas.impuesto',
                    'ventas.total',
                    'ventas.estado',
                    'personas.nombre as cliente',
                    'users.usuario',
                    'users.idrol',
                    'detalle_ventas.idarticulo'
                )
                ->distinct()
                ->where(function ($query) use ($idRoles) {
                    if ($idRoles !== null) {
                        $query->where('users.idrol', $idRoles);
                    }
                })
                ->where(function ($query) use ($idAlmacen) {
                    if ($idAlmacen !== null) {
                        $query->where('inventarios.idalmacen', $idAlmacen);
                    }
                });

            // Filtrar por fechas solo si se proporcionan fechas distintas de la actual
            if ($fechaInicio !== now()->toDateString() || $fechaFin !== now()->addDay()->toDateString()) {
                $ventas->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin]);
            }

            $ventas = $ventas->orderBy('ventas.id', 'desc')->paginate(6);
        } else {
            $ventas = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
                ->join('users', 'ventas.idusuario', '=', 'users.id')
                ->join('detalle_ventas', 'ventas.id', '=', 'detalle_ventas.idventa')
                ->join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
                ->join('inventarios', 'articulos.id', '=', 'inventarios.idarticulo')
                ->select(
                    'ventas.id',
                    'ventas.tipo_comprobante',
                    'ventas.serie_comprobante',
                    'ventas.num_comprobante',
                    'ventas.fecha_hora',
                    'ventas.impuesto',
                    'ventas.total',
                    'ventas.estado',
                    'personas.nombre as cliente',
                    'users.usuario',
                    'users.idrol',
                    'detalle_ventas.idarticulo'
                )
                ->distinct()
                ->where(function ($query) use ($idRoles) {
                    if ($idRoles !== null) {
                        $query->where('users.idrol', $idRoles);
                    }
                })
                ->where(function ($query) use ($idAlmacen) {
                    if ($idAlmacen !== null) {
                        $query->where('inventarios.idalmacen', $idAlmacen);
                    }
                })
                ->where('personas.' . $criterio, 'like', '%' . $buscar . '%');

            // Filtrar por fechas
            if ($fechaInicio !== now()->toDateString() || $fechaFin !== now()->addDay()->toDateString()) {
                $ventas->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin]);
            }

            $ventas = $ventas->orderBy('ventas.id', 'desc')->paginate(6);
        }

        return [
            'pagination' => [
                'total' => $ventas->total(),
                'current_page' => $ventas->currentPage(),
                'per_page' => $ventas->perPage(),
                'last_page' => $ventas->lastPage(),
                'from' => $ventas->firstItem(),
                'to' => $ventas->lastItem(),
            ],
            'ventas' => $ventas,
            'usuario' => $usuario
        ];
    }



    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $id = $request->id;
        $venta = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->join('users', 'ventas.idusuario', '=', 'users.id')
            ->select(
                'ventas.id',
                'ventas.tipo_comprobante',
                'ventas.serie_comprobante',
                'ventas.num_comprobante',
                'ventas.fecha_hora',
                'ventas.impuesto',
                'ventas.total',
                'ventas.estado',
                'personas.nombre',
                'users.usuario'
            )
            ->where('ventas.id', '=', $id)
            ->orderBy('ventas.id', 'desc')->take(1)->get();

        return ['venta' => $venta];
    }
    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $id = $request->id;
        $detalles = DetalleVenta::join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ventas.cantidad',
                'detalle_ventas.precio',
                'detalle_ventas.descuento',
                'articulos.nombre as articulo',
                'articulos.unidad_envase'
            )
            ->where('detalle_ventas.idventa', '=', $id)
            ->orderBy('detalle_ventas.id', 'desc')->get();

        return ['detalles' => $detalles];
    }
    public function pdf(Request $request, $id)
    {
        $venta = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->join('users', 'ventas.idusuario', '=', 'users.id')
            ->select(
                'ventas.id',
                'ventas.tipo_comprobante',
                'ventas.serie_comprobante',
                'ventas.num_comprobante',
                'ventas.created_at',
                'ventas.impuesto',
                'ventas.total',
                'ventas.estado',
                'personas.nombre',
                'personas.tipo_documento',
                'personas.num_documento',
                'personas.direccion',
                'personas.email',
                'personas.telefono',
                'users.usuario'
            )
            ->where('ventas.id', '=', $id)
            ->orderBy('ventas.id', 'desc')->take(1)->get();

        $detalles = DetalleVenta::join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ventas.cantidad',
                'detalle_ventas.precio',
                'detalle_ventas.descuento',
                'articulos.nombre as articulo'
            )
            ->where('detalle_ventas.idventa', '=', $id)
            ->orderBy('detalle_ventas.id', 'desc')->get();

        $numventa = Venta::select('num_comprobante')->where('id', $id)->get();

        $pdf = \PDF::loadView('pdf.venta', ['venta' => $venta, 'detalles' => $detalles]);
        return $pdf->setPaper('a4', 'landscape')->download('venta-' . $numventa[0]->num_comprobante . '.pdf');
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $idtipoventa = $request->idtipo_venta;

        try {
            DB::beginTransaction();

            if (!$this->validarCajaAbierta()) {
                return ['id' => -1, 'caja_validado' => 'Debe tener una caja abierta'];
            }

            if ($request->tipo_comprobante === "RESIVO") {
                // Crear venta con RESIVO
                $venta = $this->crearVentaResivo($request);
            } else {
                // Crear venta regular (con factura)
                $venta = $this->crearVenta($request);
            }

            $this->actualizarCaja($request);
            $this->registrarDetallesVenta($venta, $request->data, $request->idAlmacen);
            $this->notificarAdministradores();

            DB::commit();
            return ['id' => $venta->id];
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    public function store2(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $idtipoventa = $request->idtipo_venta;

        try {
            DB::beginTransaction();

            if (!$this->validarCajaAbierta()) {
                return ['id' => -1, 'caja_validado' => 'Debe tener una caja abierta'];
            }

            if ($request->tipo_comprobante === "RESIVO") {
                // Crear venta con RESIVO
                $venta = $this->crearVentaResivo2($request);
            } else {
                // Crear venta regular (con factura)
                $venta = $this->crearVenta2($request);
            }

            //$this->actualizarCaja($request);
            $this->registrarDetallesVenta($venta, $request->data, $request->idAlmacen);
            $this->notificarAdministradores();

            DB::commit();
            return ['id' => $venta->id];
        } catch (Exception $e) {
            DB::rollBack();
        }
    }


    private function validarCajaAbierta()
    {
        $ultimaCaja = Caja::latest()->first();
        return $ultimaCaja && $ultimaCaja->estado == '1';
    }

    private function calcularDescuentoMaximo($detalles)
    {
        $descuento = 0;
        foreach ($detalles as $ep => $det) {
            $descuento = $det['descuento'];
        }
        return $descuento;
    }

    private function crearVenta($request)
    {
        $venta = new Venta();
        $venta->fill($request->only([
            'idcliente',
            'idtipo_pago',
            'idtipo_venta',
            'tipo_comprobante',
            'serie_comprobante',
            'num_comprobante',
            'impuesto',
            'total'
        ]));
        $venta->idusuario = \Auth::user()->id;
        $venta->fecha_hora = now()->setTimezone('America/La_Paz');
        if ($request->idtipo_venta == 2) {
            $venta->estado = 'Pendiente';
        } else {
            $venta->estado = 'Registrado';
        }

        $venta->idcaja = Caja::latest()->first()->id;
        $venta->save();

        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($venta, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }

        return $venta;
    }

    private function crearVenta2($request)
    {
        $venta = new Venta();
        $venta->fill($request->only([
            'idcliente',
            'idtipo_pago',
            'idtipo_venta',
            'tipo_comprobante',
            'serie_comprobante',
            'num_comprobante',
            'impuesto',
            'total'
        ]));
        $venta->idusuario = \Auth::user()->id;
        $venta->fecha_hora = now()->setTimezone('America/La_Paz');
        $venta->estado = 'Pendiente';

        if ($request->idtipo_venta == 2) {
            $venta->estado = 'Pendiente';
        }

        $venta->idcaja = Caja::latest()->first()->id;
        $venta->save();

        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($venta, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }

        return $venta;
    }


    private function actualizarCaja($request)
    {

        $ultimaCaja = Caja::latest()->first();

        if ($request->idtipo_pago == 1) {
            // Actualizar caja en ventas y ventas efectivo
            // Código para ventas en efectivo

            if ($request->idtipo_venta == 2) {
                // Sumar a ventas crédito
                // Código para ventas a crédito
                $ultimaCaja->ventas += $request->primer_precio_cuota;
                $ultimaCaja->pagosEfectivoVentas += $request->primer_precio_cuota;
                $ultimaCaja->ventasCredito += $request->primer_precio_cuota;
                $ultimaCaja->saldoCaja += $request->primer_precio_cuota;
            } else {
                // Sumar a ventas contado
                // Código para ventas a contado
                $ultimaCaja->ventasContado += $request->total;
                $ultimaCaja->ventas += $request->total;
                $ultimaCaja->pagosEfectivoVentas += $request->total;
                $ultimaCaja->saldoCaja += $request->total;
                $ultimaCaja->saldototalventas += $request->total;
            }
        } elseif ($request->idtipo_pago == 7) {
            $ultimaCaja->ventasQR = ($request->total) + ($ultimaCaja->ventasQR);
            $ultimaCaja->saldototalventas = ($ultimaCaja->saldototalventas + $request->total) ;
            $ultimaCaja->ventas += $request->total;


        } elseif ($request->idtipo_pago == 2){
            $ultimaCaja->ventasTarjeta = ($request->total) + ($ultimaCaja->ventasTarjeta);
            $ultimaCaja->saldototalventas = ($ultimaCaja->saldototalventas + $request->total) ;
            $ultimaCaja->ventas += $request->total;

        }


        $ultimaCaja->save();
    }


    private function registrarDetallesVenta($venta, $detalles, $idAlmacen)
    {
        foreach ($detalles as $ep => $det) {
            $this->actualizarInventario($idAlmacen, $det);
            $detalleVenta = new DetalleVenta();
            $detalleVenta->idventa = $venta->id;
            $detalleVenta->idarticulo = $det['idarticulo'];
            $detalleVenta->cantidad = $det['cantidad'];
            $detalleVenta->precio = $det['precioseleccionado'];
            $detalleVenta->descuento = $det['descuento'];
            $detalleVenta->save();
        }
        $_SESSION['sidAlmacen'] = $idAlmacen;
        $_SESSION['sdetalle'] = $detalles;
    }

    private function actualizarInventario($idAlmacen, $detalle)
    {
        $cantidadRestante = $detalle['cantidad'];
        $fechaActual = now();
        $inventarios = Inventario::where('idalmacen', $idAlmacen)
            ->where('idarticulo', $detalle['idarticulo'])
            ->where('fecha_vencimiento','>',$fechaActual)
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

            $inventario->save();
        }
    }
    private function revertirInventario()
    {
        $idAlmacen = $_SESSION['sidAlmacen'];
        $detalles = $_SESSION['sdetalle'];

        foreach ($detalles as $detalle) {
            $inventario = Inventario::where('idalmacen', $idAlmacen)
                ->where('idarticulo', $detalle['idarticulo'])
                ->firstOrFail();
            $inventario->saldo_stock += $detalle['cantidad'];
            $inventario->save();
        }
    }


    public function eliminarVenta($id)
    {
        $ultimaCaja = Caja::latest()->first();

        try {
            $venta = Venta::findOrFail($id);
            $idTipoPago = $venta->idtipo_pago;

             // Disminuir el saldo de la caja dependiendo del tipo de pago
             switch ($idTipoPago) {
                case 1: // Efectivo
                    $ultimaCaja->ventasContado -= $venta->total;
                    $ultimaCaja->saldototalventas -= ($venta->total);
                    $ultimaCaja->saldoCaja -= $venta->total;
                    break;
                case 7: // QR
                    $ultimaCaja->ventasQR -= ($venta->total);
                    $ultimaCaja->saldototalventas -= ($venta->total);
                    break;
                case 2: // Tarjeta
                    $ultimaCaja->ventasTarjeta -= ($venta->total);
                    $ultimaCaja->saldototalventas -= ($venta->total);
                    break;
                default:
                    // Manejo de otros tipos de pago si es necesario
                    break;
            }

            $ultimaCaja->save();
            $venta->delete();
            $this->revertirInventario();
            return response()->json('Venta eliminada correctamente', 200);
        } catch (\Exception $e) {
            return response()->json('Error al eliminar la venta: ' . $e->getMessage(), 500);
        }
    }

    public function eliminarVentaFalloSiat($id)
{
    $ultimaCaja = Caja::latest()->first();
    
    // Verificar que las variables de sesión estén definidas
    if (!isset($_SESSION['sidAlmacen']) || !isset($_SESSION['sdetalle'])) {
        return response()->json('Error: Variables de sesión no definidas.', 400);
    }

    // Obtener el ID del almacén y los detalles de la venta de la sesión
    $idAlmacen = $_SESSION['sidAlmacen'];
    $detalles = $_SESSION['sdetalle'];

    try {
        DB::beginTransaction();

        // Revertir el inventario
        foreach ($detalles as $det) {
            // Si el código de comida está en la tabla Inventario, aumentar el stock
            $disminuirStock = Inventario::join('articulos', 'inventarios.idarticulo', '=', 'articulos.id')
                                        ->where('inventarios.idalmacen', $idAlmacen)
                                        ->where('articulos.id', $det['idarticulo'])
                                        ->firstOrFail();
            $disminuirStock->saldo_stock += $det['cantidad'];
            $disminuirStock->save();
        }

        // Eliminar todas las facturas relacionadas con la venta
        $facturas = Factura::where('idventa', $id)->get();
        foreach ($facturas as $factura) {
            $factura->delete();
        }

        // Eliminar todos los detalles de la venta
        $detallesVenta = DetalleVenta::where('idventa', $id)->get();
        foreach ($detallesVenta as $detalle) {
            $detalle->delete();
        }

        // Obtener el tipo de pago de la venta
        $venta = Venta::findOrFail($id);
        $idTipoPago = $venta->idtipo_pago;

        // Disminuir el saldo de la caja dependiendo del tipo de pago
        switch ($idTipoPago) {
            case 1: // Efectivo
                $ultimaCaja->ventasContado -= $venta->total;
                $ultimaCaja->saldototalventas -= ($venta->total - $venta->tarifaDelivery);
                $ultimaCaja->saldoCaja -= $venta->total;
                break;
            case 7: // QR
                $ultimaCaja->ventasQR -= ($venta->total - $venta->tarifaDelivery);
                $ultimaCaja->saldototalventas -= ($venta->total - $venta->tarifaDelivery);
                break;
            case 2: // Tarjeta
                $ultimaCaja->ventasTarjeta -= ($venta->total - $venta->tarifaDelivery);
                $ultimaCaja->saldototalventas -= ($venta->total - $venta->tarifaDelivery);
                break;
            default:
                // Manejo de otros tipos de pago si es necesario
                break;
        }

        $ultimaCaja->save();
        $venta->delete();

        DB::commit();
        return response()->json('Venta eliminada correctamente', 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json('Error al eliminar la venta: ' . $e->getMessage(), 500);
    }
}

    private function notificarAdministradores()
    {
        $fechaActual = date('Y-m-d');
        $numVentas = Venta::whereDate('created_at', $fechaActual)->count();
        $numIngresos = Ingreso::whereDate('created_at', $fechaActual)->count();

        $arreglosDatos = [
            'ventas' => ['numero' => $numVentas, 'msj' => 'Ventas'],
            'ingresos' => ['numero' => $numIngresos, 'msj' => 'Ingresos']
        ];

        $allUsers = User::all();

        foreach ($allUsers as $notificar) {
            $user = User::findOrFail($notificar->id);
            $user->notify(new NotifyAdmin($arreglosDatos));
        }
    }





  public function desactivar(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        // Obtener el rol del usuario autenticado
        $rolUsuario = Auth::user()->idrol;

        // Verificar si el usuario es administrador
        if ($rolUsuario !== 1) {
            return response()->json([
                'error' => 'Sólo los administradores pueden anular ventas.'
            ], 403);
        }

        // Buscar la venta a anular
        $venta = Venta::findOrFail($request->id);

        // Anular la venta
        $venta->estado = 'Anulado';
        $venta->save();

        return response()->json([
            'mensaje' => 'Venta anulada correctamente.'
        ]);
    }

    public function verificarComunicacion()
    {
        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->verificarComunicacion();
        if ($res->RespuestaComunicacion->transaccion == true) {
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        } else {
            $msg = "Falló la comunicación";
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
    }

    public function cuis()
    {
        $user = Auth::user();   

        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }
        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;


        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->cuis($puntoVenta, $codSucursal, $codnit);
        $res->RespuestaCuis->codigo;
        $_SESSION['scuis'] = $res->RespuestaCuis->codigo;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function nuevoCufd()
    {
        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }
        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->cufd($puntoVenta, $codSucursal, $codnit);
        //dd($res);
        
        if ($res->RespuestaCufd->transaccion == true) {
            $cufd = $res->RespuestaCufd->codigo;
            $codigoControl = $res->RespuestaCufd->codigoControl;
            $direccion = $res->RespuestaCufd->direccion;
            $fechaVigencia = $res->RespuestaCufd->fechaVigencia;
            
            $_SESSION['scufd'] = $cufd;
            $_SESSION['scodigoControl'] = $codigoControl;
            $_SESSION['sdireccion'] = $direccion;
            $_SESSION['sfechaVigenciaCufd'] = $fechaVigencia;

            $res = $res->RespuestaCufd;

            /*$res['transaccion'] = $res->RespuestaCufd->transaccion;
            $res['codigo'] = $_SESSION['scufd'];
            $res['fechaVigencia'] = $_SESSION['sfechaVigenciaCufd'];
            $res['direccion'] = $_SESSION['sdireccion'];
            $res['codigoControl'] = $_SESSION['scodigoControl'];*/
        } else {
            $res = false;
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function cufd()
    {
        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }
        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;

        if (!isset ($_SESSION['scufd'])) {
            require "SiatController.php";
            $siat = new SiatController();
            $res = $siat->cufd($puntoVenta, $codSucursal, $codnit);
            if ($res->RespuestaCufd->transaccion == true) {
                $cufd = $res->RespuestaCufd->codigo;
                $codigoControl = $res->RespuestaCufd->codigoControl;
                $direccion = $res->RespuestaCufd->direccion;
                $fechaVigencia = $res->RespuestaCufd->fechaVigencia;
                $_SESSION['scufd'] = $cufd;
                $_SESSION['scodigoControl'] = $codigoControl;
                $_SESSION['sdireccion'] = $direccion;
                $_SESSION['sfechaVigenciaCufd'] = $fechaVigencia;
            } else {
                $res = false;
            }
        } else {
            $fechaVigencia = substr($_SESSION['sfechaVigenciaCufd'], 0, 16);
            $fechaVigencia = str_replace("T", " ", $fechaVigencia);
            if ($fechaVigencia < date('Y-m-d H:i')) {
                require "SiatController.php";
                $siat = new SiatController();
                $res = $siat->cufd($puntoVenta, $codSucursal);
                if ($res->RespuestaCufd->transaccion == true) {
                    $cufd = $res->RespuestaCufd->codigo;
                    $codigoControl = $res->RespuestaCufd->codigoControl;
                    $direccion = $res->RespuestaCufd->direccion;
                    $fechaVigencia = $res->RespuestaCufd->fechaVigencia;
                    $_SESSION['scufd'] = $cufd;
                    $_SESSION['scodigoControl'] = $codigoControl;
                    $_SESSION['sdireccion'] = $direccion;
                    $_SESSION['sfechaVigenciaCufd'] = $fechaVigencia;
                } else {
                    $res = false;
                }
            } else {
                $res['transaccion'] = true;
                $res['codigo'] = $_SESSION['scufd'];
                $res['fechaVigencia'] = $_SESSION['sfechaVigenciaCufd'];
                $res['direccion'] = $_SESSION['sdireccion'];
                $res['codigoControl'] = $_SESSION['scodigoControl'];
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }


    public function sincronizarActividades()
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->sincronizarActividades($puntoVenta, $codSucursal);
        //$mensaje = $res->RespuestaListaActividades;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }



    public function sincronizarListaProductosServicios()
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->sincronizarListaProductosServicios($puntoVenta, $codSucursal);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaMotivoAnulacion()
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->sincronizarParametricaMotivoAnulacion($puntoVenta, $codSucursal);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaEventosSignificativos()
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->sincronizarParametricaEventosSignificativos($puntoVenta, $codSucursal);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }



    public function sincronizarParametricaUnidadMedida()
    {
        $user = Auth::user();
        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = 0;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->sincronizarParametricaUnidadMedida($puntoVenta, $codSucursal);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function verificarNit($numeroDocumento)
    {
        $user = Auth::user();
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->verificarNit($codSucursal, $numeroDocumento, $codnit);
        if ($res->RespuestaVerificarNit->transaccion === true) {
            $mensaje = $res->RespuestaVerificarNit->mensajesList->descripcion;
        } else if ($res->RespuestaVerificarNit->transaccion === false) {
            $mensaje = $res->RespuestaVerificarNit->transaccion;
        }

        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
    }

    public function anulacionFactura($cuf, $motivoSeleccionado)
    {
        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }

        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->anulacionFactura($cuf, $motivoSeleccionado, $puntoVenta, $codSucursal, $codnit);
        if ($res->RespuestaServicioFacturacion->transaccion === true) {
            $mensaje = $res->RespuestaServicioFacturacion->codigoDescripcion;
        } else {
            $mensaje = $res->RespuestaServicioFacturacion->mensajesList->descripcion;
        }
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        //var_dump($res);

        /*if ($res->RespuestaServicioFacturacion->transaccion === true) {
            $pdfPath = public_path('docs/facturaRollo.pdf');
            $descripcionMotivo = MotivoAnulacion::where('codigo', $motivoSeleccionado)->value('descripcion');
            $numeroFactura = Factura::where('cuf', $cuf)->value('numeroFactura');
            $correo = Factura::where('cuf', $cuf)->value('correo');
            $numFactura = str_pad($numeroFactura, 5, "0", STR_PAD_LEFT);
            \Mail::to($correo)->send(new \App\Mail\AnulaciónFactura($pdfPath, $descripcionMotivo, $numFactura));
        }*/

    }


    public function emitirFactura(Request $request){

        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }

        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $empresa = $sucursal->empresas;
        $codnit = $empresa->nit;
        $codSucursal = $sucursal->codigoSucursal;

        $datos = $request->input('factura');
        $id_cliente = $request->input('id_cliente');
        $idventa = $request->input('idventa');
        $correo = $request->input('correo');
        $cufd = $request->input('cufd');
            
        $valores = $datos['factura'][0]['cabecera'];
        $nitEmisor = str_pad($valores['nitEmisor'], 13, "0", STR_PAD_LEFT);
            
        $fechaEmision = $valores['fechaEmision'];
        $fecha_formato = str_replace("T", "", $fechaEmision);
        $fecha_formato = str_replace("-", "", $fecha_formato);
        $fecha_formato = str_replace(":", "", $fecha_formato);
        $fecha_formato = str_replace(".", "", $fecha_formato);
        $sucursal = str_pad($codSucursal, 4, "0", STR_PAD_LEFT);
        $modalidad = 2;
        $tipoEmision = 1;
        $tipoFactura = 1;
        $tipoDocSector = str_pad(1, 2, "0", STR_PAD_LEFT);
        $numeroFactura = str_pad($valores['numeroFactura'], 10, "0", STR_PAD_LEFT);
        $puntoVentaCuf = str_pad($puntoVenta, 4, "0", STR_PAD_LEFT);
        $codigoControl = $_SESSION['scodigoControl'];
        $cadena = $nitEmisor . $fecha_formato . $sucursal . $modalidad . $tipoEmision . $tipoFactura . $tipoDocSector . $numeroFactura . $puntoVentaCuf;
        $numDig = 1;
        $limMult = 9;
        $x10 = false;
        $mod11 = CustomHelpers::calculaDigitoMod11($cadena, $numDig, $limMult, $x10);
        $cadena2 = $cadena . $mod11;
        
        $pString = $cadena2;
        $bas16 = CustomHelpers::base16($pString);
        
        $cuf = strtoupper($bas16) . $codigoControl;
            
        $datos['factura'][0]['cabecera']['cuf'] = $cuf;
            
        $temporal = $datos['factura'];
        //dd($temporal);
        $xml_temporal = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><facturaComputarizadaCompraVenta xsi:noNamespaceSchemaLocation=\"facturaComputarizadaCompraVenta.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"></facturaComputarizadaCompraVenta>");

        $this->formato_xml($temporal, $xml_temporal);
        $xml_temporal->asXML(public_path("docs/facturaxml.xml"));
        $gzdata = gzencode(file_get_contents(public_path("docs/facturaxml.xml")), 9);
        $fp = fopen(public_path("docs/facturaxml.xml.zip"), "w");
        fwrite($fp, $gzdata);
        fclose($fp);
        $archivo = $gzdata;
        $hashArchivo = hash("sha256", file_get_contents(public_path("docs/facturaxml.xml")));
            
        $numeroFactura = $valores['numeroFactura'];
        $codigoMetodoPago = $valores['codigoMetodoPago'];
        $montoTotal = $valores['montoTotal'];
        $montoTotalSujetoIva = $valores['montoTotalSujetoIva'];
        $descuentoAdicional = $valores['descuentoAdicional'];
        $productos = file_get_contents(public_path("docs/facturaxml.xml"));

        require "SiatController.php";
        $siat = new SiatController();
        $resFactura = $siat->recepcionFactura($archivo, $fechaEmision, $hashArchivo, $puntoVenta, $codSucursal, $codnit);
        //dd($resFactura);

        $idFactura = null;

        if ($resFactura->RespuestaServicioFacturacion->codigoDescripcion === "VALIDADA") {
            $mensaje = $resFactura->RespuestaServicioFacturacion->codigoDescripcion;
            $facturaResponse = $this->insertarFactura(
                $request, 
                $id_cliente, 
                $idventa, 
                $numeroFactura, 
                $cuf, 
                $cufd, 
                $codigoControl, 
                $correo, 
                $fechaEmision, 
                $codigoMetodoPago, 
                $montoTotal, 
                $montoTotalSujetoIva, 
                $descuentoAdicional, 
                $productos
            );
            
            // Verificar si la factura se insertó correctamente y obtener el ID
            if ($facturaResponse->getData()->success) {
                $idFactura = $facturaResponse->getData()->id;
            } else {
                $mensaje = "Error al insertar la factura";
            }
        } else if ($resFactura->RespuestaServicioFacturacion->codigoDescripcion === "RECHAZADA") {
            $mensaje = $resFactura->RespuestaServicioFacturacion->mensajesList->descripcion;
        }
        
        echo json_encode(['mensaje' => $mensaje, 'idFactura' => $idFactura], JSON_UNESCAPED_UNICODE);
            
    }

    public function insertarFactura(Request $request, $id_cliente, $idventa, $numeroFactura, $cuf, $cufd, $codigoControl, $correo, $fechaEmision, $codigoMetodoPago, $montoTotal, $montoTotalSujetoIva, $descuentoAdicional, $productos){
        if (!$request->ajax()) {
            return response()->json(['error' => 'Acceso no autorizado'], 401);
        }
    
        $factura = new Factura();
        $factura->idventa = $idventa;
        $factura->idcliente = $id_cliente;
        $factura->numeroFactura = $numeroFactura;
        $factura->cuf = $cuf;
        $factura->cufd = $cufd;
        $factura->codigoControl = $codigoControl;
        $factura->correo = $correo;
        $factura->fechaEmision = $fechaEmision;
        $factura->codigoMetodoPago = $codigoMetodoPago;
        $factura->montoTotal = $montoTotal;
        $factura->montoTotalSujetoIva = $montoTotalSujetoIva;
        $factura->descuentoAdicional = $descuentoAdicional;
        $factura->productos = $productos;
        $factura->estado = 1;
    
        $success = $factura->save();
    
        if ($success) {
            return response()->json(['success' => true, 'id' => $factura->id]);
        } else {
            return response()->json(['success' => false, 'error' => 'No se pudo guardar la factura']);
        }
    }

    public function enviarPaquete(Request $request)
    {
        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }
        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;
        // Ruta al directorio que deseas comprimir en el archivo TAR
        $carpetaFuente = public_path("docs/temporal");
        // Nombre del archivo TAR resultante
        $nombreArchivoTAR = 'docs/temporal.tar';

        try {
            // Obtener la lista de archivos en el directorio
            $archivosEnDirectorio = scandir($carpetaFuente);

            $archivos = array_diff($archivosEnDirectorio, array('.', '..'));

            // Obtener el número de archivos en la carpeta
            $numeroFacturas = count($archivos);

            // Verificar si la cantidad de archivos excede 500
            if ($numeroFacturas > 500) {
                // Si supera el límite, muestra un mensaje de error
                echo 'La cantidad de archivos excede el límite de 500.';
                return;
            }

            // Crear un objeto PharData para el archivo TAR
            $tar = new PharData($nombreArchivoTAR);

            // Agregar el contenido del directorio al archivo TAR
            $tar->buildFromDirectory($carpetaFuente);

            // Comprimir el archivo TAR utilizando Gzip
            $gzdata = gzencode(file_get_contents(public_path($nombreArchivoTAR)), 9);
            $fp = fopen(public_path("docs/temporal.tar.zip"), "w");
            fwrite($fp, $gzdata);
            fclose($fp);
            $archivo = $gzdata;
            $hashArchivo = hash("sha256", file_get_contents(public_path("docs/temporal.tar.zip")));
            $nombreArchivoZIP = public_path("docs/temporal.tar.zip");

            require "SiatController.php";
            $siat = new SiatController();
            $res = $siat->recepcionPaqueteFactura($archivo, $request->fechaEmision, $hashArchivo, $numeroFacturas, $puntoVenta, $codSucursal);
            // Verificar el valor de transacción y asignar el mensaje correspondiente
            if ($res->RespuestaServicioFacturacion->codigoDescripcion === "PENDIENTE") {
                $mensaje = $res->RespuestaServicioFacturacion->codigoDescripcion;
                $_SESSION['scodigorecepcion'] = $res->RespuestaServicioFacturacion->codigoRecepcion;

                // Eliminar el archivo TAR si existe
                if (file_exists($nombreArchivoTAR)) {
                    unlink($nombreArchivoTAR);
                }
                // Eliminar el archivo ZIP si existe
                if (file_exists($nombreArchivoZIP)) {
                    unlink($nombreArchivoZIP);
                }
                // Eliminar la carpeta temporal si existe y está vacía
                if (is_dir($carpetaFuente)) {
                    $this->eliminarDirectorio($carpetaFuente);
                }
            } else if ($res->RespuestaServicioFacturacion->codigoDescripcion === "RECHAZADA") {
                $mensajes = $res->RespuestaServicioFacturacion->mensajesList;

                if (is_array($mensajes)) {
                    $descripciones = array_map(function ($mensaje) {
                        return $mensaje->descripcion;
                    }, $mensajes);
                    $mensaje = $descripciones;
                }
            }
            echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
            //var_dump($res);

        } catch (Exception $e) {
            echo "Error al crear el archivo TAR comprimido o al enviarlo al servicio: " . $e->getMessage();
        }
    }



    public function eliminarDirectorio($directorio)
    {
        if (!is_dir($directorio)) {
            return;
        }

        $archivos = glob($directorio . '/*');
        foreach ($archivos as $archivo) {
            is_dir($archivo) ? $this->eliminarDirectorio($archivo) : unlink($archivo);
        }

        rmdir($directorio);
    }





    public function formato_xml($temporal, $xml_temporal)
    {
        $ns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
        foreach ($temporal as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnodo = $xml_temporal->addChild("$key");
                    $this->formato_xml($value, $subnodo);
                } else {
                    $this->formato_xml($value, $xml_temporal);
                }
            } else {
                if ($value == null && $value <> '0') {
                    $hijo = $xml_temporal->addChild("$key", "$value");
                    $hijo->addAttribute('xsi:nil', 'true', $ns_xsi);
                } else {
                    $xml_temporal->addChild("$key", "$value");
                }
            }
        }
    }


    public function registroEventoSignificativo(Request $request)
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        $descripcion = $request->descripcion;
        $cufdEvento = $request->cufdEvento;
        $codigoMotivoEvento = $request->codigoMotivoEvento;
        $inicioEvento = $request->inicioEvento;
        $finEvento = $request->finEvento;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->registroEventoSignificativo($descripcion, $cufdEvento, $codigoMotivoEvento, $inicioEvento, $finEvento, $puntoVenta, $codSucursal);
        // Verificar el valor de transacción y asignar el mensaje correspondiente
        if ($res->RespuestaListaEventos->transaccion === true) {
            $mensaje = $res->RespuestaListaEventos->codigoRecepcionEventoSignificativo;
            $_SESSION['scodigoevento'] = $res->RespuestaListaEventos->codigoRecepcionEventoSignificativo;
        } else {
            $mensaje = $res->RespuestaListaEventos->mensajesList->descripcion;
        }

        // Imprimir o retornar el mensaje, o realizar otras acciones según tu necesidad
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        //var_dump($res);
    }

    public function registroPuntoVenta(Request $request)
    {
        $user = Auth::user();
        $puntoVenta = $user->idpuntoventa;
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;

        $nombre = $request->nombre;
        $descripcion = $request->descripcion;
        $nit = $request->nit;
        $idtipopuntoventa = $request->idtipopuntoventa;
        $idsucursal = $request->idsucursal;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->registroPuntoVenta($nombre, $descripcion, $nit, $idtipopuntoventa, $idsucursal, $puntoVenta, $codSucursal);
        // Verificar el valor de transacción y asignar el mensaje correspondiente
        if ($res->RespuestaRegistroPuntoVenta->transaccion === true) {
            $mensaje = $res->RespuestaRegistroPuntoVenta->codigoPuntoVenta;
        } else {
            $mensaje = $res->RespuestaRegistroPuntoVenta->mensajesList->descripcion;
        }

        // Imprimir o retornar el mensaje, o realizar otras acciones según tu necesidad
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        //var_dump($res);
    }

    public function cierrePuntoVenta(Request $request)
    {
        $user = Auth::user();
        $sucursal = $user->sucursal;
        $codSucursal = $sucursal->codigoSucursal;
        $codigoPuntoVenta = $request->codigoPuntoVenta;
        $codigoSucursal = $request->codigoSucursal;
        $nit = $request->nit;

        require "SiatController.php";
        $siat = new SiatController();
        $res = $siat->cierrePuntoVenta($codigoPuntoVenta, $nit, $codSucursal);
        // Verificar el valor de transacción y asignar el mensaje correspondiente
        if ($res->RespuestaCierrePuntoVenta->transaccion === true) {
            $mensaje = $res->RespuestaCierrePuntoVenta->codigoPuntoVenta;
        } else {
            $mensaje = $res->RespuestaCierrePuntoVenta->mensajesList->descripcion;
        }

        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        //var_dump($res);
    }



    private function crearVentaResivo($request)
    {
        $ventaResivo = new Venta();
        $ventaResivo->fill($request->only([
            'idcliente',
            'idtipo_pago',
            'idtipo_venta',
            'tipo_comprobante', // Asumiendo que el tipo de comprobante es 'RESIVO'
            'serie_comprobante', // Agregado para prueba
            'num_comprobante', // Agregado para prueba
            'impuesto',
            'total'
        ]));
        $ventaResivo->idusuario = \Auth::user()->id;
        $ventaResivo->fecha_hora = now()->setTimezone('America/La_Paz');

        if ($request->idtipo_venta == 2) {
            $ventaResivo->estado = 'Pendiente';
        } else {
            $ventaResivo->estado = 'Registrado';
        }

        $ventaResivo->idcaja = Caja::latest()->first()->id;
        $ventaResivo->save();

        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($ventaResivo, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }

        return $ventaResivo;
    }

    private function crearVentaResivo2($request)
    {
        $ventaResivo = new Venta();
        $ventaResivo->fill($request->only([
            'idcliente',
            'idtipo_pago',
            'idtipo_venta',
            'tipo_comprobante', // Asumiendo que el tipo de comprobante es 'RESIVO'
            'serie_comprobante', // Agregado para prueba
            'num_comprobante', // Agregado para prueba
            'impuesto',
            'total'
        ]));
        $ventaResivo->idusuario = \Auth::user()->id;
        $ventaResivo->fecha_hora = now()->setTimezone('America/La_Paz');
        $ventaResivo->estado = 'Pendiente';

        if ($request->idtipo_venta == 2) {
            $ventaResivo->estado = 'Pendiente';
        }

        $ventaResivo->idcaja = Caja::latest()->first()->id;
        $ventaResivo->save();

        if ($request->idtipo_venta == 2) {
            $creditoventa = $this->crearCreditoVenta($ventaResivo, $request);
            $this->registrarCuotasCredito($creditoventa, $request->cuotaspago);
        }

        return $ventaResivo;
    }

    public function imprimirFacturaRollo($id)
    {
        $user = Auth::user();
        $codigoPuntoVenta = '';
        if (!empty($user->idpuntoventa)) {
            $puntoVenta = PuntoVenta::find($user->idpuntoventa);
            if ($puntoVenta) {
                $codigoPuntoVenta = $puntoVenta->codigoPuntoVenta;
            }
        }

        //$puntoVenta = $user->idpuntoventa;
        $puntoVenta = $codigoPuntoVenta;

        $facturas = Factura::join('ventas', 'facturas.idventa', '=', 'ventas.id')
        ->join('personas', 'facturas.idcliente', '=', 'personas.id')
        ->select('facturas.*','personas.nombre as razonSocial', 'personas.num_documento as documentoid')
        ->where('facturas.id', '=', $id)
        ->orderBy('facturas.id', 'desc')->paginate(3);
        
        Log::info('Resultado', [
            //'facturas' => $facturas,
            'idFactura' => $id,
        ]);

        //dd($facturas);

        $xml = $facturas[0]->productos;
        $archivoXML = new SimpleXMLElement($xml);
        $nitEmisor = $archivoXML->cabecera[0]->nitEmisor;
        $numeroFactura = str_pad($archivoXML->cabecera[0]->numeroFactura, 5, "0", STR_PAD_LEFT);
        $cuf = $archivoXML->cabecera[0]->cuf;
        $direccion = $archivoXML->cabecera[0]->direccion;
        $telefono = $archivoXML->cabecera[0]->telefono;
        $municipio = $archivoXML->cabecera[0]->municipio;
        $fechaEmision = $archivoXML->cabecera[0]->fechaEmision;
        $fechaFormateada = date("d/m/Y h:i A", strtotime($fechaEmision));
        $documentoid = $archivoXML->cabecera[0]->numeroDocumento;
        $razonSocial = $archivoXML->cabecera[0]->nombreRazonSocial;
        $codigoCliente = $archivoXML->cabecera[0]->codigoCliente;
        $montoTotal1 = $archivoXML->cabecera[0]->montoTotal;
        $montoGiftCard = $archivoXML->cabecera[0]->montoGiftCard;
        $descuentoAdicional = $archivoXML->cabecera[0]->descuentoAdicional;
        $leyenda = $archivoXML->cabecera[0]->leyenda;
        $complementoid = $archivoXML->cabecera[0]->complemento;

        $montoTotal = ($montoTotal1-$montoGiftCard);
        $totalpagar = number_format(floatval($montoTotal), 2);
        $totalpagar = str_replace(',', '', $totalpagar);
        $totalpagar = str_replace('.', ',', $totalpagar);
        $cifrasEnLetras = new CifrasEnLetrasController();
        $letra = ($cifrasEnLetras->convertirBolivianosEnLetras($totalpagar));


        $url = 'https://siat.impuestos.gob.bo/consulta/QR?nit=' . $nitEmisor . '&cuf=' . $cuf . '&numero=' . $numeroFactura . '&t=2';
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => 10,
        ]);
        $qrCode = new QRCode($options);
        $qrCode->render($url, public_path('qr/qrcode.png'));

        //$pdf = new FPDF('P', 'mm', array(80, 0));
        $pdf = new FPDF('P', 'mm', array(80, 250));
        //$pdf = new FPDF();

        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetMargins(10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, 'FACTURA', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, utf8_decode('CON DERECHO A CRÉDITO FISCAL'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, utf8_decode('JHENRY EDSON CASTRO CAMACHO'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('Casa Matriz'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('No. Punto de Venta '.$puntoVenta), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(0, 3, utf8_decode($direccion), 0, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, utf8_decode('Tel. ' . $telefono), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode($municipio), 0, 1, 'C');

        $y = $pdf->GetY();
        $pdf->SetY($y + 2);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(0, 3, '', 'T', 1, 'C');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, 'NIT', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 6);
        //$pdf->Cell(0, 3, utf8_decode($documentoid."-".$complementoid), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode($nitEmisor), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, utf8_decode('FACTURA N°'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, utf8_decode($numeroFactura), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, utf8_decode('CÓD. AUTORIZACIÓN'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(0, 3, utf8_decode($cuf), 0, 'C');

        $y = $pdf->GetY();
        $pdf->SetY($y + 2);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(0, 3, '', 'T', 1, 'C');

        $spacing = 2;

        $pdf->SetX(($pdf->GetPageWidth() - $pdf->GetStringWidth('NOMBRE/RAZON SOCIAL:') - $pdf->GetStringWidth($razonSocial)) / 2);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(10, 3, 'NOMBRE/RAZON SOCIAL:', 0, 0, 'C');
        $pdf->SetX($pdf->GetX() + $spacing);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, utf8_decode($razonSocial), 0, 1, 'C');

        $spacingBetweenColumns = 10;
        $pdf->SetX(($pdf->GetPageWidth() - $pdf->GetStringWidth('NIT/CI/CEX:') - $pdf->GetStringWidth($documentoid)) / 2);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(2.5, 3, 'NIT/CI/CEX:', 0, 0, 'C');
        $pdf->SetX($pdf->GetX() + $spacingBetweenColumns);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(5.5, 3, utf8_decode($documentoid), 0, 1, 'C');

        $spacingBetweenColumns = 10;
        $pdf->SetX(($pdf->GetPageWidth() - $pdf->GetStringWidth('COD. CLIENTE:') - $pdf->GetStringWidth($codigoCliente)) / 2);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(2.5, 3, 'COD. CLIENTE:', 0, 0, 'C');
        $pdf->SetX($pdf->GetX() + $spacingBetweenColumns);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(9, 3, utf8_decode($codigoCliente), 0, 1, 'C');

        $spacingBetweenColumns = 10;
        $pdf->SetX(($pdf->GetPageWidth() - $pdf->GetStringWidth('FECHA DE EMISIÓN:') - $pdf->GetStringWidth($fechaEmision)) / 2);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(21.5, 3, utf8_decode('FECHA DE EMISIÓN:'), 0, 0, 'C');
        $pdf->SetX($pdf->GetX() + $spacingBetweenColumns);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(10, 3, utf8_decode($fechaFormateada), 0, 1, 'C');

        $y = $pdf->GetY();
        $pdf->SetY($y + 2);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(0, 3, '', 'T', 1, 'C');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, 'DETALLE', 0, 1, 'C');

        $detalle = $archivoXML->detalle;
        $sumaSubTotales = 0.0;
        foreach ($detalle as $p) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(0, 3, $p->codigoProducto . " - " . $p->descripcion, 0, 1, 'L');

            $medida = $p->unidadMedida;
            $nombreMedida = Medida::where('codigoClasificador', $medida)->value('descripcion_medida');

            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(0, 3, "Unidad de Medida: " . $nombreMedida, 0, 1, 'L');
            $pdf->Cell(0, 3, number_format(floatval($p->cantidad), 2) . " X " . number_format(floatval($p->precioUnitario), 2) . " - " . number_format(floatval($p->montoDescuento), 2), 0, 0, 'L');
            $pdf->Cell(0, 3, number_format(floatval($p->subTotal), 2), 0, 1, 'R');

            $sumaSubTotales += floatval($p->subTotal);
        }

        $y = $pdf->GetY();
        $pdf->SetY($y + 2);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(0, 3, '', 'T', 1, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, 'SUBTOTAL Bs', 0, 0, 'C');
        $pdf->Cell(0, 3, number_format(floatval($sumaSubTotales), 2), 0, 1, 'R');
        $pdf->Cell(0, 3, 'DESCUENTO Bs', 0, 0, 'C');
        $pdf->Cell(0, 3, number_format(floatval($descuentoAdicional), 2), 0, 1, 'R');
        $pdf->Cell(0, 3, 'TOTAL Bs', 0, 0, 'C');
        $pdf->Cell(0, 3, number_format(floatval($montoTotal), 2), 0, 1, 'R');
        $pdf->Cell(0, 3, 'MONTO GIFT CARD Bs', 0, 0, 'C');
        $pdf->Cell(0, 3, number_format(floatval($montoGiftCard), 2), 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, 'MONTO A PAGAR Bs', 0, 0, 'C');
        $pdf->Cell(0, 3, number_format(floatval($montoTotal), 2), 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(0, 3, utf8_decode('IMPORTE BASE CRÉDITO FISCAL Bs'), 0, 0, 'C');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(0, 3, number_format(floatval($montoTotal), 2), 0, 1, 'R');
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, 'Son: ' . $letra, 0, 1, 'L');

        $y = $pdf->GetY();
        $pdf->SetY($y + 2);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(0, 3, '', 'T', 1, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 3, utf8_decode('ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('ACUERDO A LA LEY'), 0, 1, 'C');
        $pdf->Ln(3);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(0, 3, utf8_decode($leyenda), 0, 'C');
        $pdf->Ln(3);
        $pdf->Cell(0, 3, utf8_decode('Este documento es la Representación Gráfica de un'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('Documento Fiscal Digital emitido en una modalidad de'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('facturación en línea'), 0, 1, 'C');
        $pdf->Ln(3);

        $textY = $pdf->GetY();

        $imageWidth = 25;
        $pageWidth = $pdf->GetPageWidth();
        $imageX = ($pageWidth - $imageWidth) / 2;
        $pdf->Image(public_path('qr/qrcode.png'), $imageX, $textY + 3, $imageWidth, 0, 'PNG');



        $pdf->Output(public_path('docs/facturaRollo.pdf'), 'F');

        $pdfPath = public_path('docs/facturaRollo.pdf');
        $xmlPath = public_path("docs/facturaxml.xml");
    
        //\Mail::to($correo)->send(new \App\Mail\FacturaElectrónica($xmlPath, $pdfPath));
    
        return response()->json(['url' => url('docs/facturaRollo.pdf')]);
    }
    
public function imprimirResivoRollo($id) {
    try {
        $venta = Venta::with('detalles.producto')->find($id);
        if (!$venta) {
            return response()->json(['error' => 'NO SE ENCONTRÓ LA VENTA'], 404);
        }

        $persona = Persona::find($venta->idcliente);
        if (!$persona) {
            return response()->json(['error' => 'NO SE ENCONTRÓ EL CLIENTE'], 404);
        }

        $empresa = Empresa::first();
        if (!$empresa) {
            return response()->json(['error' => 'NO SE ENCONTRÓ LA EMPRESA'], 404);
        }

        if ($venta->detalles->isNotEmpty()) {
            // Configuración para recibo de rollo
            $pdf = new FPDF('P', 'mm', array(80, 297)); // Ancho de 80mm, alto variable
            $pdf->SetAutoPageBreak(true, 10);
            $pdf->SetMargins(5, 10, 5);
            $pdf->AddPage();

            if ($empresa->logo) {
                $logoPath = storage_path('app/public/logos/' . $empresa->logo);
                if (file_exists($logoPath)) {
                    $logoWidth = 30; // Ancho del logo en mm
                    $xPosition = (80 - $logoWidth) / 2; // Calcular posición X para centrar
                    $pdf->Image($logoPath, $xPosition, 2, $logoWidth); // Ajusta las coordenadas y el tamaño según sea necesario
                    $pdf->Ln(20); // Mueve la posición hacia abajo después de insertar la imagen
                }
            }

            // Establecer fuente monoespaciada
            $pdf->SetFont('Courier', 'B', 12);

            // Encabezado
            $pdf->Cell(0, 10, utf8_decode(strtoupper('RECIBO DE VENTA')), 0, 1, 'C');
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('No. ' . $id)), 0, 1, 'C');

            // Información de la empresa
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->nombre)), 0, 1, 'C');
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->direccion)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('TELÉFONO: ' . $empresa->telefono)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('EMAIL: ' . $empresa->email)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('NIT: ' . $empresa->nit)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('LICENCIA: ' . $empresa->licencia)), 0, 1, 'C');

            $pdf->Ln(5);

            // Fecha y hora
            $pdf->Cell(0, 5, utf8_decode(strtoupper('FECHA: ' . date('d/m/Y', strtotime($venta->created_at)))), 0, 1);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('HORA: ' . date('H:i:s', strtotime($venta->created_at)))), 0, 1);

            // Detalles del cliente
            $pdf->Ln(2);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('CLIENTE: ' . $persona->nombre)), 0, 1);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('DOC: ' . $persona->num_documento)), 0, 1);

            // Línea separadora
            $pdf->Cell(0, 2, '', 'T', 1);

            // Encabezados de la tabla
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(40, 5, utf8_decode(strtoupper('PRODUCTO')), 0, 0);
            $pdf->Cell(10, 5, utf8_decode(strtoupper('CANT')), 0, 0, 'R');
            $pdf->Cell(20, 5, utf8_decode(strtoupper('PRECIO')), 0, 1, 'R');

            $pdf->SetFont('Courier', '', 8);

            // Detalles de los productos
            $total = 0;
            foreach ($venta->detalles as $detalle) {
                $precioVenta = $detalle->cantidad * $detalle->precio;
                $total += $precioVenta;

                $pdf->Cell(40, 5, utf8_decode(strtoupper(substr($detalle->producto->nombre, 0, 20))), 0, 0);
                $pdf->Cell(10, 5, utf8_decode($detalle->cantidad), 0, 0, 'R');
                $pdf->Cell(20, 5, utf8_decode(number_format($precioVenta, 2)), 0, 1, 'R');
            }

            // Línea separadora
            $pdf->Cell(0, 2, '', 'T', 1);

            // Total
            $pdf->SetFont('Courier', 'B', 10);
            $pdf->Cell(50, 6, utf8_decode(strtoupper('TOTAL')), 0, 0);
            $pdf->Cell(20, 6, utf8_decode(number_format($total, 2)), 0, 1, 'R');

            $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
            $totalTexto = strtoupper($formatter->format($total)) . ' BOLIVIANOS';
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(0, 5, 'SON: ' . $totalTexto, 0, 1);
            
            
            // Tipo de pago
            /*$tipoPago = $venta->tipoPago;
            $nombreTipoPago = $tipoPago ? $tipoPago->nombre_tipo_pago : 'N/A';
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('TIPO DE PAGO: ' . $nombreTipoPago)), 0, 1);*/

            // Mensaje de agradecimiento
            $pdf->Ln(5);
            $pdf->SetFont('Courier', 'I', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper('¡GRACIAS POR SU COMPRA!')), 0, 1, 'C');

            // Guardar el archivo PDF generado
            $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
            $pdfPath = public_path('docs/recibo_rollo_' . $nombreLimpio . '_' . $id . '.pdf');
            $pdf->Output($pdfPath, 'F');

            // Descargar el archivo PDF generado
            return response()->download($pdfPath);
        } else {
            return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 404);
        }
    } catch (\Exception $e) {
        \Log::error('Error al imprimir el recibo en rollo: ' . $e->getMessage());
        return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN ROLLO'], 500);
    }
}




public function imprimirResivoCarta($id) {
    try {
        $venta = Venta::with('detalles.producto')->find($id);
        if (!$venta) {
            return response()->json(['error' => 'NO SE ENCONTRÓ LA VENTA'], 500);
        }

        $persona = Persona::find($venta->idcliente);
        if (!$persona) {
            return response()->json(['error' => 'NO SE ENCONTRÓ EL CLIENTE'], 500);
        }

        $empresa = Empresa::first();
        if (!$empresa) {
            return response()->json(['error' => 'NO SE ENCONTRÓ LA EMPRESA'], 404);
        }

        if ($venta->detalles->isNotEmpty()) {
            $pdf = new FPDF('P', 'mm', 'Letter');
            $pdf->SetMargins(20, 10, 20);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->AddPage();

            // Logos a los costados
            $logoPathLeft = storage_path('app/public/logos/' . $empresa->logo);
            $logoPathRight = storage_path('app/public/logos/' . $empresa->logo); // Puedes definir un logo diferente si lo necesitas
            $logoWidth = 40; // Ancho del logo en mm

        

            // Logo derecho
            if (file_exists($logoPathRight)) {
                $pdf->Image($logoPathRight, 160, 10, $logoWidth); // Logo a la derecha
            }

            // Título RECIBO DE PAGO
            $pdf->SetFont('Courier', 'B', 14);
            $pdf->Cell(0, 10, 'RECIBO DE PAGO', 0, 1, 'C');
            $pdf->SetFont('Courier', '', 10);

            // Información de la empresa
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->nombre)), 0, 1, 'C');
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper($empresa->direccion)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('TELÉFONO: ' . $empresa->telefono)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('EMAIL: ' . $empresa->email)), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_decode(strtoupper('NIT: ' . $empresa->nit)), 0, 1, 'C');
            $pdf->Ln(5);

            $fecha = date('d/m/Y', strtotime($venta->created_at));
            $hora = date('H:i:s', strtotime($venta->created_at));
            $pdf->Cell(50, 6, utf8_decode('FECHA: ' . strtoupper($fecha)), 0, 0, 'L');
            $pdf->Cell(50, 6, utf8_decode('HORA: ' . strtoupper($hora)), 0, 0, 'L');
            $pdf->Cell(50, 6, utf8_decode('NUM RECIBO: ' . strtoupper($id)), 0, 1, 'L');

            $clienteInfo = utf8_decode('CLIENTE: ' . strtoupper($persona->nombre) . '                 DOCUMENTO: ' . strtoupper($persona->num_documento));
            $pdf->Cell(0, 6, $clienteInfo, 0, 1, 'L');

            // Tabla de productos
            $pdf->SetFont('Courier', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(90, 7, utf8_decode('PRODUCTO'), 1, 0, 'C', true);
            $pdf->Cell(25, 7, utf8_decode('CANTIDAD'), 1, 0, 'C', true);
            $pdf->Cell(35, 7, utf8_decode('PRECIO UNIT.'), 1, 0, 'C', true);
            $pdf->Cell(35, 7, utf8_decode('SUBTOTAL'), 1, 1, 'C', true);

            $pdf->SetFont('Courier', '', 9);
            $total = 0;
            foreach ($venta->detalles as $detalle) {
                $subtotal = $detalle->cantidad * $detalle->precio;
                $total += $subtotal;
                $pdf->Cell(90, 6, utf8_decode(strtoupper($detalle->producto->nombre)), 1, 0);
                $pdf->Cell(25, 6, utf8_decode(strtoupper($detalle->cantidad)), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode(strtoupper(number_format($detalle->precio, 2))), 1, 0, 'R');
                $pdf->Cell(35, 6, utf8_decode(strtoupper(number_format($subtotal, 2))), 1, 1, 'R');
            }

            $pdf->SetFont('Courier', 'B', 10);
            $pdf->Cell(150, 7, utf8_decode('TOTAL'), 1, 0, 'R');
            $pdf->Cell(35, 7, utf8_decode(strtoupper(number_format($total, 2))), 1, 1, 'R');

            $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
            $totalTexto = strtoupper($formatter->format($total)) . ' BOLIVIANOS';
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(0, 5, 'SON: ' . $totalTexto, 0, 1);
            
            $pdf->Ln(5);
            $pdf->SetFont('Courier', 'B', 8);
            $pdf->Cell(0, 5, utf8_decode('FORMA DE PAGO:'), 0, 1);
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 5, utf8_decode(strtoupper($venta->tipoPago ? $venta->tipoPago->nombre_tipo_pago : 'N/A')), 0, 1, '', true);

            // Firma
            $pdf->SetFont('Courier', '', 10);
            $anchoFirma = $pdf->GetPageWidth() / 2 - 20;
            $pdf->Cell($anchoFirma, 6, '_________________________', 0, 0, 'C');
            $pdf->Cell($anchoFirma, 6, '_________________________', 0, 1, 'C');
            $pdf->Cell($anchoFirma, 6, 'FIRMA DEL CLIENTE', 0, 0, 'C');
            $pdf->Cell($anchoFirma, 6, 'FIRMA AUTORIZADA', 0, 1, 'C');
            $pdf->Ln(10);

            // Nota de agradecimiento
            $pdf->SetFont('Courier', 'I', 10);
            $pdf->Cell(0, 7, utf8_decode('¡GRACIAS POR SU COMPRA!'), 0, 1, 'C');

            // Dibujar el margen
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(1);
            $margenVertical = 10;
            $margenHorizontal = 10;
            $pdf->Rect($margenHorizontal, $margenVertical, $pdf->GetPageWidth() - ($margenHorizontal * 2), $pdf->GetY() + $margenVertical);

            $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $persona->nombre);
            $pdfPath = public_path('docs/recibo_carta_' . $nombreLimpio . '_' . $id . '.pdf');
            $pdf->Output($pdfPath, 'F');

            return response()->download($pdfPath);
        } else {
            return response()->json(['error' => 'NO HAY DETALLES PARA ESTA VENTA'], 500);
        }
    } catch (\Exception $e) {
        \Log::error('Error al imprimir el recibo en carta: ' . $e->getMessage());
        return response()->json(['error' => 'OCURRIÓ UN ERROR AL IMPRIMIR EL RECIBO EN CARTA'], 500);
    }
}


    public function selectRoles(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $roles = Rol::where('condicion', '=', '1')
            ->select('id', 'nombre')->orderBy('nombre', 'asc')->get();
        return ['roles' => $roles];
    }

    public function reporteVentasDiarias(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
        ]);

        $idUsuario = $request->input('idUsuario');

        $query = DetalleVenta::join('ventas', 'detalle_ventas.idventa', '=', 'ventas.id')
            ->join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->join('users', 'ventas.idusuario', '=', 'users.id')
            ->select(
                'ventas.id',

                'ventas.num_comprobante',
                DB::raw('GROUP_CONCAT(DISTINCT articulos.nombre SEPARATOR ", ") as articulo'),
                DB::raw('SUM(detalle_ventas.cantidad) as cantidad'),
                DB::raw('SUM(detalle_ventas.precio * detalle_ventas.cantidad) as total'),
                DB::raw('MAX(detalle_ventas.precio) as precio')
            )
            ->whereDate('ventas.created_at', $request->input('fecha'))
            ->groupBy('ventas.id',  'ventas.num_comprobante');

        if ($request->has('idCategoria') && $request->input('idCategoria') !== 'all') {
            $query->where('articulos.idcategoria', $request->input('idCategoria'));
        }

        if ($idUsuario !== 'all') {
            $query->where('ventas.idusuario', $idUsuario);
        }

        $ventas = $query->get();

        if ($ventas->isEmpty()) {
            return response()->json(['mensaje' => 'Ninguna Venta Realizada en la Fecha Indicada']);
        }

        return response()->json(['ventas' => $ventas]);
    }
    public function selectUsuarios()
    {
        $usuarios = User::select('id', 'usuario')->get();
        return response()->json(['usuarios' => $usuarios]);
    }

    public function topVendedores(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topVendedores = Venta::join('personas', 'ventas.idusuario', '=', 'personas.id')
            ->select(
                'ventas.idusuario',
                'personas.nombre as nombreUsuario',
                DB::raw('COUNT(*) as cantidadVentas'),
                DB::raw('SUM(ventas.total) as totalVentas')
            )
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('ventas.idusuario', 'personas.nombre')
            ->orderByDesc('cantidadVentas')
            ->limit(10)
            ->get();

        return response()->json(['topVendedores' => $topVendedores]);
    }
    public function topClientes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topVendedores = Venta::join('personas', 'ventas.idcliente', '=', 'personas.id')
            ->select(
                'ventas.idcliente',
                'personas.nombre as nombreCliente',
                DB::raw('COUNT(*) as cantidadCompras'),
                DB::raw('SUM(ventas.total) as totalGastado')
            )
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('ventas.idcliente', 'personas.nombre')
            ->orderByDesc('cantidadCompras')
            ->limit(10)
            ->get();

        return response()->json(['topClientes' => $topVendedores]);
    }

    public function topProductos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $topProductos = DetalleVenta::join('articulos', 'detalle_ventas.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ventas.idarticulo',
                'articulos.nombre as nombreArticulo',
                DB::raw('SUM(detalle_ventas.cantidad) as cantidadTotal'),
                DB::raw('COUNT(*) as vecesVendido')
            )
            ->join('ventas', 'detalle_ventas.idventa', '=', 'ventas.id')
            ->whereBetween('ventas.fecha_hora', [$fechaInicio, $fechaFin])
            ->groupBy('detalle_ventas.idarticulo', 'articulos.nombre')
            ->orderByDesc('cantidadTotal')
            ->limit(10)
            ->get();

        return response()->json(['topProductos' => $topProductos]);
    }
    
    public function obtenerUltimoComprobante(Request $request) {
        $idsucursal = $request->idsucursal;
    
        $ultimoComprobanteRecibo = Venta::join('users', 'ventas.idusuario', '=', 'users.id')
            ->select('ventas.num_comprobante')
            ->where('users.idsucursal', $idsucursal)
            ->where('ventas.tipo_comprobante', 'RESIVO')
            ->orderBy('ventas.id', 'desc')
            ->limit(1)
            ->first();

        $lastComprobanteRecibo = $ultimoComprobanteRecibo ? $ultimoComprobanteRecibo->num_comprobante : 0;
    
        return response()->json(['last_comprobante' => $lastComprobanteRecibo]);
    }
    
    
    private function crearCreditoVenta($venta, $request)
    {
        $creditoventa = new CreditoVenta();
        $creditoventa->idventa = $venta->id;
        $creditoventa->idcliente = $request->idcliente;
        $creditoventa->numero_cuotas = $request->numero_cuotasCredito;
        $creditoventa->tiempo_dias_cuota = $request->tiempo_dias_cuotaCredito;
        $creditoventa->total = $request->totalCredito;
        $creditoventa->estado = $request->estadoCredito;

        $primerCuotaNoPagada = null;
        foreach ($request->cuotaspago as $cuota) {
            if ($cuota['estado'] !== 'Pagado') {
                $primerCuotaNoPagada = $cuota;
                break;
            }
        }
        $creditoventa->proximo_pago = $primerCuotaNoPagada['fecha_pago'];

        $creditoventa->save();

        return $creditoventa;
    }

    private function registrarCuotasCredito($creditoventa, $cuotas)
    {
        $numeroCuota = 1; // Inicializamos el número de cuota en 1

        foreach ($cuotas as $detalle) {
            $cuota = new CuotasCredito();
            $cuota->idcredito = $creditoventa->id;
            if ($detalle['estado'] == "Pagado") {
                $cuota->idcobrador = \Auth::user()->id;
                $cuota->fecha_cancelado = $detalle['fecha_cancelado']; // Podrías ajustar esto según tus necesidades

            } else {
                $cuota->idcobrador = null;
                $cuota->fecha_cancelado = null; // Podrías ajustar esto según tus necesidades


            }

            $cuota->numero_cuota = $numeroCuota++; // Asignamos el número de cuota y luego incrementamos
            $cuota->fecha_pago = $detalle['fecha_pago'];
            $cuota->precio_cuota = $detalle['precio_cuota'];
            $cuota->saldo_restante = $detalle['saldo_restante'];
            $cuota->estado = $detalle['estado'];
            $cuota->save();
        }
    }

    public function ventaSelecionada($id)
    {
        // Encuentra la venta por su ID
        $venta = Venta::find($id);

        // Verifica si la venta existe
        if ($venta) {
            // Devuelve la respuesta JSON con los datos requeridos
            return response()->json([
                'id' => $venta->id,
                'num_comprobante' => $venta->num_comprobante,
                'total' => $venta->total,
            ]);
        } else {
            // Devuelve un error 404 si la venta no se encuentra
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
    }

    public function cerrarVenta(Request $request)
{
    if (!$request->ajax()) return redirect('/');

    try {
        DB::beginTransaction();

        $venta = Venta::findOrFail($request->id);
        $venta->idtipo_pago = $request->idtipo_pago;
        $venta->estado = $request->estado;

        // Buscar la caja asociada a la venta
        $ultimaCaja = Caja::find($venta->idcaja);

        if ($ultimaCaja) {
            // Actualizar las ventas y el saldo de la caja dependiendo del tipo de pago
            if ($venta->idtipo_pago == 1) {
                $ultimaCaja->ventasContado += $venta->total;
                $ultimaCaja->saldoCaja += $venta->total;
                $ultimaCaja->saldototalventas += $venta->total;
            } elseif ($venta->idtipo_pago == 7) {
                $ultimaCaja->ventasQR += $venta->total;
                $ultimaCaja->saldototalventas += $venta->total;
            } elseif ($venta->idtipo_pago == 2) {
                $ultimaCaja->ventasTarjeta += $venta->total;
                $ultimaCaja->saldototalventas += $venta->total;
            }

            // Guardar la caja
            $ultimaCaja->save();
        } else {
            return response()->json([
                'id' => -1,
                'error' => 'No se encontró la caja asociada a esta venta'
            ]);
        }

        $venta->save();

        DB::commit();

        return response()->json(['id' => $venta->id]);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'id' => -1,
            'error' => 'Ha ocurrido un error al cerrar la venta'
        ]);
    }
}

}