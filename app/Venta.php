<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'idcliente',
        'idusuario',
        'tipo_comprobante',
        'num_comprobante',
        'fecha_hora',
        'impuesto',
        'total',
        'estado',
        'idcaja',
        'idtipo_venta',
        'idtipo_pago'
    ];

    public function caja()
    {
        return $this->belongsTo('App\Caja', 'id');
    }
    public function detalles()
{
    return $this->hasMany(DetalleVenta::class, 'idventa');
}
public function creditoVenta()
{
    return $this->hasOne(CreditoVenta::class, 'idventa');
}
}