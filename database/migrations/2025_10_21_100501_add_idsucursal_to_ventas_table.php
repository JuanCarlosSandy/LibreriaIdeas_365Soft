<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdsucursalToVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('ventas', function (Blueprint $table) {
            $table->integer('idsucursal')->unsigned()->nullable()->default(1);

            $table->foreign('idsucursal')
                ->references('id')
                ->on('sucursales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar la relación y el campo
            $table->dropForeign(['idsucursal']);
            $table->dropColumn('idsucursal');
        });
    }
}
