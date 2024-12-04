<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBajaproductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bajaproductos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('numeroFactura');
            $table->string('almacen');
            $table->integer('numeroComprobante');
            $table->integer('idtipobajas')->unsigned();
            $table->foreign('idtipobajas')->references('id')->on('tipobajas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bajaproductos');
    }
}
