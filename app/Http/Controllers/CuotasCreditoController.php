<?php

namespace App\Http\Controllers;

use App\CuotasCredito;
use Illuminate\Http\Request;

class CuotasCreditoController extends Controller
{
    // Mostrar una lista de las cuotas de crédito
    public function index()
    {
        // Devolver todas las cuotas de crédito 
        $cuotas = CuotasCredito::all();
        return $cuotas;
    }

    // Almacenar una nueva cuota de crédito en la base de datos
    public function store(Request $request)
    {
        // Validar y guardar una nueva cuota de crédito en la base de datos
        $request->validate([
            'idcredito' => 'required|integer|exists:credito_ventas,id',
            'fecha_pago' => 'required|date',
            'fecha_cancelado' => 'nullable|date',
            'precio_cuota' => 'required|numeric|min:0',
            'total_cancelado' => 'required|numeric|min:0',
            'saldo_restante' => 'required|numeric|min:0', // Se cambió 'saldo' por 'saldo_restante'
            'estado' => 'required|string|in:pendiente,pagado,atrasado'
        ]);

        $cuota = new CuotasCredito();
        $cuota->idcredito = $request->idcredito;
        $cuota->fecha_pago = $request->fecha_pago;
        $cuota->fecha_cancelado = $request->fecha_cancelado;
        $cuota->precio_cuota = $request->precio_cuota;
        $cuota->total_cancelado = $request->total_cancelado;
        $cuota->saldo_restante = $request->saldo_restante; // Se cambió 'saldo' por 'saldo_restante'
        $cuota->estado = $request->estado;
        $cuota->save();

        return $cuota;
    }

    // Mostrar los detalles de una cuota de crédito específica
    public function show(CuotasCredito $cuota)
    {
        // Devolver la cuota de crédito solicitada 
        return $cuota;
    }

    // Actualizar una cuota de crédito existente en la base de datos
    public function update(Request $request, CuotasCredito $cuota)
    {
        // Validar y actualizar la cuota de crédito existente en la base de datos
        $request->validate([
            'idcredito' => 'required|integer|exists:credito_ventas,id',
            'fecha_pago' => 'required|date',
            'fecha_cancelado' => 'nullable|date',
            'precio_cuota' => 'required|numeric|min:0',
            'total_cancelado' => 'required|numeric|min:0',
            'saldo_restante' => 'required|numeric|min:0', // Se cambió 'saldo' por 'saldo_restante'
            'estado' => 'required|string|in:pendiente,pagado,atrasado'
        ]);

        $cuota->idcredito = $request->idcredito;
        $cuota->fecha_pago = $request->fecha_pago;
        $cuota->fecha_cancelado = $request->fecha_cancelado;
        $cuota->precio_cuota = $request->precio_cuota;
        $cuota->total_cancelado = $request->total_cancelado;
        $cuota->saldo_restante = $request->saldo_restante; // Se cambió 'saldo' por 'saldo_restante'
        $cuota->estado = $request->estado;
        $cuota->save();

        return $cuota;
    }

    // Eliminar una cuota de crédito existente de la base de datos
    public function destroy(CuotasCredito $cuota)
    {
        // Eliminar la cuota de crédito existente de la base de datos
        $cuota->delete();
        return $cuota;
    }
}
