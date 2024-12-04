<template>
    <div class="card ">
        <div class="card-header"> Registrar compra</div>
        <div class="card-body">
            <div class="linear-stepper">
                <div class="step-container">
                    <div class="step" :class="{ active: step === 1, completed: step > 1 }">
                        <span class="step-number" v-if="step > 1">✔</span>
                        <span class="step-number" v-else>1</span>
                        <span class="step-name">Cliente</span>
                    </div>
                    <div class="step-line" v-if="step >= 2"></div>
                </div>
                <div class="step-container">
                    <div class="step" :class="{ active: step === 2, completed: step > 2 }">
                        <span class="step-number" v-if="step > 2">✔</span>
                        <span class="step-number" v-else>2</span>
                        <span class="step-name">Producto</span>
                    </div>
                    <div class="step-line" v-if="step >= 3"></div>
                </div>
                <div class="step-container">
                    <div class="step" :class="{ active: step === 3, completed: step > 3 }">
                        <span class="step-number" v-if="step > 3">✔</span>
                        <span class="step-number" v-else>3</span>
                        <span class="step-name">Pagos</span>
                    </div>
                </div>
            </div>
            <div v-show="step === 1" class="step-content">
                <div class="card">
                    <div class="container">
                        <div class="row">
                            <!-- Proveedor -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">Proveedor <span
                                            class="text-danger">*</span></label>
                                    <v-select :on-search="selectProveedor" label="nombre" :options="arrayProveedor"
                                        placeholder="Buscar Proveedores..." :onChange="getDatosProveedor"
                                        v-model="proveedorSeleccionado">
                                    </v-select>
                                    <span class="text-danger small" v-show="idproveedor == 0">(!) Seleccione
                                        Proveedor</span>
                                </div>
                            </div>

                            <!-- Tipo comprobante -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">Tipo comprobante <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" v-model="tipo_comprobante">
                                        <option value="0">Seleccione</option>
                                        <option value="BOLETA">Boleta</option>
                                        <option value="FACTURA">Factura</option>
                                        <option value="TICKET">Ticket</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Serie comprobante -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">Serie comprobante <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="serie_comprobante"
                                        placeholder="000x" ref="serieComprobanteRef">
                                    <label class="small" for="serieComprobante">Shift + W</label>
                                </div>
                            </div>

                            <!-- Número comprobante -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">N° Comprobante <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="num_comprobante"
                                        placeholder="000xx" ref="numeroComprobanteRef">
                                    <label class="small" for="numComprobante">Shift + E</label>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div class="row">
                            <div class="col-md-12">
                                <div v-show="errorIngreso" class="form-group div-error">
                                    <div class="text-center text-error">
                                        <div v-for="error in errorMostrarMsjIngreso" :key="error" v-text="error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div v-show="step === 2" class="step-content">
                <div class="card">

                    <div class="row ">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="" class="font-weight-bold">Almacen <span
                                        class="text-danger">*</span></label>

                                <select class="form-control" v-model="AlmacenSeleccionado">
                                    <option value="0" disabled selected>Seleccione</option>
                                    <option v-for="opcion in arrayAlmacenes" :key="opcion.id" :value="opcion.id">{{
                        opcion.nombre_almacen }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="font-weight-bold">Seleccione producto<span
                                        class="text-danger">*</span></label>

                                <div class="form-inline">
                                    <input v-if="idproveedor == 0" disabled type="text" class="form-control"
                                        v-model="codigo" @keyup="buscarArticulo()" placeholder="Escriba el codigo"
                                        ref="articuloRef">
                                    <input v-if="idproveedor != 0" type="text" class="form-control" v-model="codigo"
                                        @keyup="buscarArticulo()" placeholder="Escriba el codigo" ref="articuloRef">
                                    <button @click="abrirArticulos()" class="btn btn-primary" v-if="idproveedor == 0"
                                        disabled>...</button>
                                    <button @click="abrirArticulos()" class="btn btn-primary" v-else>...</button>
                                    <label class="small light-gray-text" for="">Shift + R</label>

                                    <!-- <input type="text" readonly class="form-control" v-model="articulo"> -->
                                </div>
                            </div>
                        </div>
                        <!-- Parte Izquierda con Texto -->
                        <div class="col-md-7" v-if="arrayArticuloSeleccionado.id">
                            <div class="card-body">
                                <h5 class="card-title">{{ this.arrayArticuloSeleccionado.nombre }}</h5>
                                <p class="card-text">
                                    {{ this.arrayArticuloSeleccionado.descripcion }}
                                    <br>
                                    <b>Costo unitario</b>
                                    {{ parseFloat(this.arrayArticuloSeleccionado.precio_costo_unid).toFixed(2) }} {{
                        monedaCompra[1] }}

                                    <br>
                                    <b>Costo paquete</b>
                                    {{ parseFloat(this.arrayArticuloSeleccionado.precio_costo_paq).toFixed(2)
                                    }}
                                    {{ monedaCompra[1] }}

                                    <br>
                                    <b>Unidades por envase</b> {{ this.arrayArticuloSeleccionado.unidad_envase }}

                                </p>
                            </div>
                        </div>

                        <!-- Parte Derecha con la Imagen -->
                        <div class="col-md-2" v-if="arrayArticuloSeleccionado.id">
                            <img :src="'img/articulo/' + this.arrayArticuloSeleccionado.fotografia + '?t=' + new Date().getTime()"
                                v-if="this.arrayArticuloSeleccionado.fotografia" width="50" height="50" ref="imagen"
                                class="card-img">
                            <img v-else src="https://www.bicifan.uy/wp-content/uploads/2016/09/producto-sin-imagen.png"
                                alt="Imagen del Card" class="card-img">

                        </div>
                    </div>

                    <div class="row">
                        <div class="m-1 p-1"></div>



                        <div class="col-md-2">
                            <div class="form-group" v-if="arrayArticuloSeleccionado.id">
                                <label for="campo1">{{ tipoUnidadSeleccionada }}:
                                    <i class="fa fa-exchange small" @click="cambiarTipoUnidad()"></i>


                                </label>



                                <input type="number" class="form-control" v-model="cantidad">
                            </div>
                        </div>
                        <div class="col-md-2" v-if="arrayArticuloSeleccionado.id">
                            <div class="form-group">
                                <label for="campo2">Fecha vencimiento</label>
                                <input v-if="arrayArticuloSeleccionado.vencimiento == 0" type="date"
                                    class="form-control" v-model="fechaPorDefecto" readonly>
                                <input v-if="arrayArticuloSeleccionado.vencimiento == null" type="date"
                                    class="form-control" v-model="fechavencimiento">
                                <input v-if="arrayArticuloSeleccionado.vencimiento == 1" type="date"
                                    class="form-control" v-model="fechavencimiento">

                            </div>
                        </div>

                        <div class="col-md-2" v-if="arrayArticuloSeleccionado.id">
                            <div class="form-group">
                                <label for="campo2">Precio total</label>

                                <div class="input-group">
                                    <input disabled type="number" class="form-control"
                                        :value="(resultadoMultiplicacion * parseFloat(monedaCompra[0])).toFixed(2)">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            {{ monedaCompra[1] }}
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-3"
                            v-if="arrayArticuloSeleccionado.id && valuacionInventario === 'costo_reposicion'">
                            <div class="form-group">
                                <label for="campo2">Editar precio</label>

                                <div class="input-group">
                                    <select class="form-control" v-model="editarPrecios">
                                        <option value="" disabled selected>Elegir opción</option>
                                        <option value="1">Costo unitario</option>
                                        <option value="0">Costo paquete</option>
                                    </select>
                                    <input v-if="editarPrecios == ''" type="number" class="form-control"
                                        v-model="nuevoPrecio" min="0" readonly>
                                    <input v-else type="number" class="form-control" v-model="nuevoPrecio" min="0">
                                    <button @click="editarPrecio" class="btn btn-success"><i
                                            class="bi bi-check2"></i></button>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-2" v-if="arrayArticuloSeleccionado.id">
                            <div class="form-group">
                                <button @click="agregarDetalle()" class="btn btn-success form-control btnagregar"><i
                                        class="icon-plus"></i> Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="form-group row ">
       
            <div class="container mt-4">

</div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Precio </label>
                    <input type="number" value="0" step="any" class="form-control" v-model="precio" ref="precioRef">
                    <label for="">Shift + T</label>
                </div>
            </div>-->
            </div>
            <div v-show="step === 3" class="step-content">
                <div class="form-group row ">
                    <div class="table-responsive col-md-12">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Codigo</th>

                                    <th>Producto</th>
                                    <th>Costo unitario</th>

                                    <th>Unidad por Paquete</th>
                                    <th>Paquetes</th>

                                    <th>Unidades</th>

                                    <th>Fecha vencimiento</th>

                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody v-if="arrayDetallePedido || arrayDetalle">
                                <tr v-for="(detalle, index) in arrayDetalle" :key="detalle.id">
                                    <td>
                                        <button @click="eliminarDetalle(index)" type="button"
                                            class="btn btn-danger btn-sm">
                                            <i class="icon-close"></i>
                                        </button>
                                    </td>
                                    <td v-text="detalle.codigo"></td>
                                    <td v-text="detalle.articulo"></td>

                                    <td>
                                        {{ (detalle.precio * parseFloat(monedaCompra[0])).toFixed(2) }} {{
                        monedaCompra[1]
                    }}

                                    </td>
                                    <td v-text="detalle.unidad_x_paquete"></td>
                                    <td v-text="(detalle.cantidad / detalle.unidad_x_paquete).toFixed(2)"></td>

                                    <td>
                                        <input v-model="detalle.cantidad" type="number" value="2" class="form-control">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" v-model="detalle.fecha_vencimiento">
                                    </td>
                                    <td>
                                        {{ ((detalle.precio * detalle.cantidad) *
                        parseFloat(monedaCompra[0])).toFixed(2) }}
                                        {{
                        monedaCompra[1] }}

                                    </td>
                                </tr>
                                <tr style="background-color: #CEECF5;">
                                    <td colspan="8" align="right"><strong>Total Parcial:</strong></td>
                                    <td>
                                        {{ ((totalParcial = (total - totalImpuesto)) *
                        parseFloat(monedaCompra[0])).toFixed(2)
                                        }} {{
                        monedaCompra[1] }}

                                    </td>
                                </tr>
                                <tr style="background-color: #CEECF5;">
                                    <td colspan="8" align="right"><strong>Total Impuesto:</strong></td>
                                    <td>
                                        {{ ((totalImpuesto = ((total * impuesto) / (1 + impuesto)))
                        * parseFloat(monedaCompra[0])).toFixed(2) }} {{ monedaCompra[1] }}

                                    </td>
                                </tr>
                                <tr style="background-color: #CEECF5;">
                                    <td colspan="8" align="right"><strong>Total Neto:</strong></td>
                                    <td>
                                        {{ ((total = calcularTotal) * parseFloat(monedaCompra[0])).toFixed(2) }} {{
                        monedaCompra[1]
                                        }}

                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="9">
                                        No hay articulos agregados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="button" @click="cerrarFormulario()" class="btn btn-secondary">Cerrar</button>
                        <button type="button" class="btn btn-primary" @click="registrarIngreso()">Registrar
                            Compra</button>
                    </div>
                </div>
            </div>

            <div class="buttons d-flex justify-content-center">
                <button class="btn btn-primary mr-2" @click="prevStep" :disabled="step === 1">Anterior</button>
                <button class="btn btn-primary" @click="validarYAvanzar" :disabled="step === 3">Siguiente</button>
            </div>

        </div>
    </div>
</template>

<script>
import vSelect from 'vue-select';
export default {
    props: {
        arrayArticuloSeleccionado: {
            type: Object, // Asegúrate de que el tipo sea Object si esperas un objeto
            required: false // Si es obligatorio, agrega esta línea
        },
        arrayPedidoSeleccionado: {
            type: Object, // Asegúrate de que el tipo sea Object si esperas un objeto
            required: false // Si es obligatorio, agrega esta línea
        },
        arrayDetallePedido: {
            type: Array,
            required: false
        },
        monedaCompra: {
            type: Array,
            required: true
        }
    },
    created() {
        this.selectAlmacen();
        this.articuloSeleccionadoLocal = { ...this.arrayArticuloSeleccionado };
        if (this.arrayDetallePedido) {

            this.arrayDetalle = [...this.arrayDetallePedido];

            this.AlmacenSeleccionado = this.arrayPedidoSeleccionado.idalmacen
            this.proveedorSeleccionado = this.arrayPedidoSeleccionado.nombre_proveedor
            this.idproveedor = this.arrayPedidoSeleccionado.idproveedor


        }
    },
    data() {
        return {
            editarPrecios: '',
            nuevoPrecio: 0,
            nuevoCostoUnidad: 0,
            nuevoCostoPaquete: 0,
            precios: [],
            precio_uno: 0,
            precio_dos: 0,
            precio_tres: 0,
            precio_cuatro: 0,
            valuacionInventario: '',
            step: 1,
            actualizarIva: null,
            proveedorSeleccionado: "",
            tipoUnidadSeleccionada: "Unidades",
            fechavencimiento: null,
            arrayArticuloSeleccionadoLocal: {},
            AlmacenSeleccionado: '1',
            arrayAlmacenes: [],
            ingreso_id: 0,
            idproveedor: 0,
            proveedor: '',
            nombre: '',
            tipo_comprobante: 'BOLETA',
            serie_comprobante: '',
            num_comprobante: '',
            impuesto: 0.18,
            total: 0.0,
            totalImpuesto: 0.0,
            totalParcial: 0.0,
            arrayIngreso: [],
            arrayProveedor: [],
            arrayDetalle: [],
            listado: 1,
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,
            errorIngreso: 0,
            errorMostrarMsjIngreso: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            criterio: 'num_comprobante',
            buscar: '',
            criterioA: 'nombre',
            buscarA: '',
            arrayArticulo: [],
            idarticulo: 0,
            codigo: '',
            articulo: '',
            precio: 0,
            cantidad: 1,
        }
    },
    components: {
        vSelect
    },
    watch: {
        codigo(newValue) {
            if (newValue) {
                this.buscarArticulo();
            }
        },
        arrayDetalle: {
            deep: true,
            handler: function (newVal) {
                if (Array.isArray(newVal)) {
                    this.total = newVal.reduce((acc, detalle) => {
                        return acc + (detalle.cantidad * detalle.precio);
                    }, 0);
                } else {
                    console.error('arrayDetalle no es un array:', newVal);
                }
            }
        }
    }
    ,
    computed: {
        resultadoMultiplicacion() {
            if (this.arrayArticuloSeleccionado) {
                if (this.tipoUnidadSeleccionada == "Paquetes") {
                    return this.cantidad * (this.arrayArticuloSeleccionado.precio_costo_unid * this.arrayArticuloSeleccionado.unidad_envase);

                } else {
                    return this.cantidad * this.arrayArticuloSeleccionado.precio_costo_unid;
                }
            }

        },
        calcularTotal: function () {
            var resultado = 0.0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                resultado = resultado + (this.arrayDetalle[i].precio * this.arrayDetalle[i].cantidad)
            }
            return resultado;
        },
        fechaPorDefecto() {
            if (this.arrayArticuloSeleccionado.vencimiento == 0) {
                this.fechavencimiento = '2099-12-31';
            } else {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0'); // Los meses son indexados desde 0
                const day = String(today.getDate()).padStart(2, '0');
                this.fechavencimiento = `${year}-${month}-${day}`;
            }
            return this.fechavencimiento;
        }

    },
    mounted() {
        this.obtenerIva();
        this.listarPrecio();
        this.datosConfiguracion();
    },
    methods: {
        validarYAvanzar() {
            const errores = [];

            if (this.step === 1) {
                if (this.idproveedor === 0) errores.push('Seleccione un proveedor');
                if (this.tipo_comprobante === '0') errores.push('Seleccione un tipo de comprobante');
                if (!this.serie_comprobante) errores.push('Ingrese la serie del comprobante');
                if (!this.num_comprobante) errores.push('Ingrese el número de comprobante');
            } else if (this.step === 2) {
                if (this.AlmacenSeleccionado === '0' || this.AlmacenSeleccionado === 0) errores.push('Seleccione un almacén');
                if (!this.arrayArticuloSeleccionado.id) errores.push('Seleccione un producto');
                if (this.cantidad === 0) errores.push('Ingrese una cantidad válida');
            }

            if (errores.length > 0) {
                const mensaje = errores.join('\n');
                swal('Campos incompletos', mensaje, 'warning');
            } else {
                this.nextStep();
            }
        },

        nextStep() {
            if (this.step < 3) {
                this.step++;
            }
        },
        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },
        buscarArticulo() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let me = this;
                var url = '/articulo/listarArticulo?buscar=' + me.codigo + '&criterio=' + 'codigo' + '&idProveedor=' + this.idproveedor
                axios
                    .get(url)
                    .then(function (response) {
                        let respuesta = response.data;
                        me.arrayArticuloSeleccionado = respuesta.articulos.data[0];
                        console.log(me.arrayArticuloSeleccionado);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }, 1000);
        },
        /*buscarArticulo() {
            clearTimeout(this.timer);
            console.log("Buscando")
            console.log(this.idproveedor);
            this.timer = setTimeout(() => {
                let me = this;
                var url = '/articulo/listarArticulo?buscar=' + me.codigo + '&criterio=' + 'codigo' + '&idProveedor=' + this.idproveedor;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayArticuloSeleccionado = respuesta.articulos.data;
                    console.log(me.arrayArticuloSeleccionado);
                })
                    .catch(function (error) {
                        console.log(error);
                    });
            }, 1000);
        },*/
        eliminarDetalle(index) {
            let me = this;
            me.arrayDetalle.splice(index, 1);
        },
        cambiarTipoUnidad() {
            if (this.tipoUnidadSeleccionada == "Paquetes") {
                this.tipoUnidadSeleccionada = "Unidades";
            } else {
                this.tipoUnidadSeleccionada = "Paquetes";

            }

        },
        cerrarFormulario() {
            this.$emit('cerrar');
        },
        editarEstado() {
            const datos = {
                pedido: this.arrayPedidoSeleccionado,
                detalles: this.arrayDetallePedido
            }
            this.$emit('editarEstadoPedido', datos);

        },
        listarIngreso(page, buscar, criterio) {
            const datos = {
                page: page, buscar: buscar, criterio: criterio
            }
            this.$emit('listarIngreso', datos);

        },
        encuentra(id) {
            var sw = 0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                if (this.arrayDetalle[i].idarticulo == id) {
                    sw = true;
                }
            }
            return sw;
        },
        selectAlmacen() {
            let me = this;
            var url = '/almacen/selectAlmacen';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayAlmacenes = respuesta.almacenes;

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        getDatosProveedor(val1) {
            let me = this;
            me.loading = true;
            if (!val1.id && this.arrayPedidoSeleccionado) {
                this.idproveedor = this.arrayPedidoSeleccionado.idproveedor
            } else {
                me.idproveedor = val1.id;

            }
        },
        selectProveedor(search, loading) {
            let me = this;
            if (search.trim() === '') {
                swal("Aviso", "Debe seleccionar un proveedor", "warning");
                loading(false);
                return;
            }
            loading(true)
            var url = '/proveedor/selectProveedor?filtro=' + search;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                q: search
                me.arrayProveedor = respuesta.proveedores;
                loading(false)
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        editarPrecio() {
            console.log("NUEVO PRECIO ", this.nuevoPrecio);
            console.log("ARTICULO SELECCIONADO ", this.arrayArticuloSeleccionado);
            let me = this;
            if (me.editarPrecios == '1') {
                me.nuevoCostoPaquete = (me.nuevoPrecio * me.arrayArticuloSeleccionado.unidad_envase).toFixed(2);
                console.log("NUEVO PRECIO2 ", this.nuevoCostoPaquete);
                me.arrayArticuloSeleccionado.precio_costo_paq = me.nuevoCostoPaquete;
                me.arrayArticuloSeleccionado.precio_costo_unid = me.nuevoPrecio;
                this.cambiarPrecios(me.arrayArticuloSeleccionado.precio_costo_unid, me.nuevoCostoPaquete, 'Costo unitario');


            }
            if (me.editarPrecios == '0') {
                me.nuevoCostoUnidad = (me.nuevoPrecio / me.arrayArticuloSeleccionado.unidad_envase).toFixed(2);
                console.log("NUEVO PRECIO2 ", this.nuevoCostoUnidad);
                me.arrayArticuloSeleccionado.precio_costo_unid = me.nuevoCostoUnidad;
                me.arrayArticuloSeleccionado.precio_costo_paq = me.nuevoPrecio;
                this.cambiarPrecios(me.nuevoCostoUnidad, me.arrayArticuloSeleccionado.precio_costo_paq, 'Costo paquete');

            }
        },
        calcularPrecio(precio, index, preciounidad) {
            const margen_ganancia = parseFloat(preciounidad) * (parseFloat(precio.porcentage) / 100);
            const precio_publico = parseFloat(preciounidad) + margen_ganancia;

            if (index === 0) {
                this.precio_uno = precio_publico.toFixed(2);
            } else if (index === 1) {
                this.precio_dos = precio_publico.toFixed(2);
            } else if (index === 2) {
                this.precio_tres = precio_publico.toFixed(2);
            } else if (index === 3) {
                this.precio_cuatro = precio_publico.toFixed(2);
            }
        },
        cambiarPrecios(precioUnidad, precioPaquete, editarPrecios) {
            let me = this;
            console.log("tipo ", typeof me.precios)
            console.log("tipo ", me.precios)
            me.precios.forEach((precio, index) => {
                me.calcularPrecio(precio, index, precioUnidad);
            });
            swal({
                title: 'Esta seguro de cambiar el ' + editarPrecios,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    axios.post('/articulo/actualizarPrecios', {
                        id: me.arrayArticuloSeleccionado.id,
                        precio_costo_paquete: precioPaquete,
                        precio_costo_unidad: precioUnidad,
                        precio_uno: me.precio_uno,
                        precio_dos: me.precio_dos,
                        precio_tres: me.precio_tres,
                        precio_cuatro: me.precio_cuatro
                    }).then(response => {
                        me.nuevoPrecio = '',
                            me.editarPrecios = ''
                    }).catch(error => {
                        console.error(error);
                    })

                } else if (
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        listarPrecio() {
            let me = this;
            var url = '/precios';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.precios = respuesta.precio.data;
                //me.precioCount = me.arrayBuscador.length;
            }).catch(function (error) {
                console.log(error);
            });
        },
        obtenerIva() {
            let me = this;
            axios.get('/configuracion/iva')
                .then(response => {
                    me.actualizarIva = response.data.actualizarIva;
                    // me.impuesto = response.data.impuesto;
                    console.log('Actualizar Iva:', me.actualizarIva);
                })
                .catch(error => {
                    console.error('Error al obtener iva:', error);
                });
            console.log('pruebaaa', me.actualizarIva);
        },

        abrirArticulos() {
            const datos = { idproveedor: this.idproveedor }
            this.$emit('listarArticuloProveedor', datos);

            this.$emit('abrirModalArticulos');

        },
        registrarIngreso() {
            if (this.validarIngreso()) {
                return;
            }
            if (this.arrayDetalle.length === 0) {
                swal('Error', 'No se ha seleccionado ningún producto. No se puede realizar la compra.', 'error');
                return;
            }
            let me = this;

            axios.post('/ingreso/registrar', {
                'idproveedor': this.idproveedor,
                'tipo_comprobante': this.tipo_comprobante,
                'serie_comprobante': this.serie_comprobante,
                'num_comprobante': this.num_comprobante,
                'impuesto': this.impuesto,
                'total': this.total,
                'data': this.arrayDetalle
            }).then(function (response) {
                console.log("response ", response.data);
                if (response.data.id > 0) {
                    me.guardarInventarios();
                    me.listado = 1;
                    me.listarIngreso(1, '', 'num_comprobante');
                    me.cerrarFormulario();
                    me.idproveedor = 0;
                    me.tipo_comprobante = 'BOLETA';
                    me.serie_comprobante = '';
                    me.num_comprobante = '';
                    me.impuesto = 0.18;
                    me.total = 0.0;
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 1;
                    me.precio = 0;
                    me.arrayDetalle = [];
                    me.id_ingreso = response.data.id;
                    console.log('id ingreso ', me.id_ingreso);

                    // Genera y descarga el PDF
                    axios({
                        url: '/ingreso/generar-pdf-boleta/' + me.id_ingreso,
                        method: 'GET',
                        responseType: 'blob' // Importante para manejar el archivo como blob
                    }).then((response) => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'venta.pdf');
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }).catch((error) => {
                        console.log("Error al generar el PDF: ", error);
                    });
                } else {
                    swal(
                        'Aviso',
                        response.data.caja_validado,
                        'warning'
                    );
                    return;
                }
            }).catch(function (error) {
                console.log(error);
            });
        },
        guardarInventarios() {
            this.editarEstado();

            axios.post('/inventarios/registrar', { inventarios: this.arrayDetalle })
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    console.error(error);
                });
        },
        validarIngreso() {
            this.errorIngreso = 0;
            this.errorMostrarMsjIngreso = [];

            if (this.idproveedor == 0) this.errorMostrarMsjIngreso.push("Seleccione un Proveedor");
            if (this.tipo_comprobante == 0) this.errorMostrarMsjIngreso.push("Sleccione el Comprobante");
            if (!this.num_comprobante) this.errorMostrarMsjIngreso.push("Ingrese el numero de comprobante");
            if (!this.impuesto) this.errorMostrarMsjIngreso.push("Ingrese el impuesto de compra");
            if (this.arrayDetalle.length <= 0) this.errorMostrarMsjIngreso.push("Ingrese detalles");

            if (this.errorMostrarMsjIngreso.length) this.errorIngreso = 1;

            return this.errorIngreso;
        },
        datosConfiguracion() {
            let me = this;
            var url = '/configuracion';

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.valuacionInventario = respuesta.configuracionTrabajo.valuacionInventario;
                console.log(" valuacion ", me.valuacionInventario)

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        agregarDetalle() {
            let me = this;

            if (me.arrayArticuloSeleccionado.length == 0 || me.cantidad == 0 || me.AlmacenSeleccionado == 0) {
                console.log("Seleccione un producto, verifique la cantidad o almacén");
            } else if (me.fechavencimiento == null) {
                swal({
                    type: 'error',
                    title: 'Error...',
                    text: 'No se ingresó fecha de vencimiento!',
                });
            } else {
                if (me.encuentra(me.arrayArticuloSeleccionado.id)) {
                    swal({
                        type: 'error',
                        title: 'Error...',
                        text: 'Este Artículo ya se encuentra agregado!',
                    });
                } else {
                    if (me.tipoUnidadSeleccionada == "Paquetes") {
                        me.arrayDetalle.push({
                            idarticulo: me.arrayArticuloSeleccionado.id,
                            idalmacen: me.AlmacenSeleccionado,
                            codigo: me.arrayArticuloSeleccionado.codigo,
                            articulo: me.arrayArticuloSeleccionado.nombre,
                            precio: me.arrayArticuloSeleccionado.precio_costo_unid,
                            unidad_x_paquete: me.arrayArticuloSeleccionado.unidad_envase,
                            fecha_vencimiento: me.fechavencimiento,
                            cantidad: me.cantidad * me.arrayArticuloSeleccionado.unidad_envase,
                        });
                    } else {
                        me.arrayDetalle.push({
                            idarticulo: me.arrayArticuloSeleccionado.id,
                            idalmacen: me.AlmacenSeleccionado,
                            codigo: me.arrayArticuloSeleccionado.codigo,
                            articulo: me.arrayArticuloSeleccionado.nombre,
                            precio: me.arrayArticuloSeleccionado.precio_costo_unid,
                            unidad_x_paquete: me.arrayArticuloSeleccionado.unidad_envase,
                            fecha_vencimiento: me.fechavencimiento,
                            cantidad: me.cantidad,
                        });
                    }

                    // Mostrar mensaje de éxito
                    swal({
                        type: 'success',
                        title: 'Éxito!',
                        text: 'Artículo agregado correctamente!',
                    });

                    me.arrayArticuloSeleccionadoLocal = {};
                    me.codigo = '';
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 1;
                    me.precio = 0;
                    me.fechavencimiento = null;
                }
            }
        }
    }
};
</script>
<style>
.step-content {
    margin-top: 1.5rem;
}

.linear-stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 2px 0;
    position: relative;
}

.step-container {
    display: flex;
    align-items: center;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 10px;
    opacity: 0.5;
    position: relative;
}

.step.active,
.step.completed {
    opacity: 1;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #ccc;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    z-index: 1;
}

.step.active .step-number {
    background-color: #007bff;
}

.step.completed .step-number {
    background-color: #34bc9b;
}

.step-line {
    height: 3px;
    width: 40px;
    background-color: #ccc;
    transition: background-color 0.3s;
    z-index: 0;
}

.step.completed+.step-line {
    background-color: #34bc9b;
}

.step.active+.step-line {
    background-color: #007bff;
}

.step-name {
    margin-top: 10px;
}
</style>