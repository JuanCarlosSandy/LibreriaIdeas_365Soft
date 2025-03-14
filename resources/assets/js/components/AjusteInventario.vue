<template>
    <main class="main">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Ajuste de Inventarios
                    <button type="button" @click="abrirModal('articulo', 'registrar'); "
                        class="btn btn-secondary">
                        <i class="icon-plus"></i>&nbsp;Nuevo
                    </button>
                </div>

<!-- Buscador separado -->
<div class="form-group row">
    <div class="col-md-6">
        <div class="input-group">
            <select class="form-control col-md-3" v-model="criterio">
                <option value="articulos.nombre">Nombre</option>
                <option value="almacens.nombre_almacen">Almacén</option>
                <option value="articulos.num_chasis">Capacidad</option>
            </select>
            <input type="text" v-model="buscar" @keyup.enter="listarArticulo(1, buscar, criterio)"
                class="form-control" placeholder="Texto a buscar">
            <button type="submit" @click="listarArticulo(1, buscar, criterio)"
                class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
        </div>
    </div>
</div>

<!-- Tabla -->
<div style="overflow-x: auto;">
    <table class="table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th>ALMACEN</th>
                <th>ARTICULO</th>
                <th>CANTIDAD</th>
                <th>TIPO BAJA</th>
                <th>FECHA Y HORA</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="ajuste in arrayAjuste" :key="ajuste.id">
                <td v-text="ajuste.nombre_almacen"></td>
                <td v-text="ajuste.nombre"></td>
                <td v-text="ajuste.cantidad"></td>
                <td v-text="ajuste.tipo"></td>
                <td v-text="ajuste.created_at"></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<nav>
    <ul class="pagination">
        <li class="page-item" v-if="pagination.current_page > 1">
            <a class="page-link" href="#"
                @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
        </li>
        <li class="page-item" v-for="page in pagesNumber" :key="page"
            :class="[page == isActived ? 'active' : '']">
            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)"
                v-text="page"></a>
        </li>
        <li class="page-item" v-if="pagination.current_page < pagination.last_page">
            <a class="page-link" href="#"
                @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
        </li>
    </ul>
</nav>
                </div>
            </div>
            <!-- registrar ajuste, modificado en fecha 14/03/2025-->
            <div class="modal" tabindex="-1" :class="{ 'mostrar': modal }" role="dialog" aria-labelledby="myModalLabel"
            style="display: ;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form @submit.prevent="enviarFormulario">
                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="almacen" class="font-weight-bold">Almacén</label>
                                    <select id="almacen" class="form-control" v-model="idAlmacenSeleccionado" @change="almacenSeleccionado">
                                        <option value="" disabled>Seleccione un almacén</option>
                                        <option v-for="almacen in arrayAlmacenes" :key="almacen.id" :value="almacen.id">
                                            {{ almacen.nombre_almacen }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Producto <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione un producto"
                                            disabled v-model="productoseleccionado.nombre"
                                            :class="{ 'is-invalid': errores.idproducto }"
                                            @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Productos')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idcategoria">{{ errores.idcategoria }}</p>
                                </div>

                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Motivo de Baja <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione un Motivo"
                                            disabled v-model="motivoseleccionado.nombre"
                                            :class="{ 'is-invalid': errores.idmotivo }" @input="validarCampo('idmotivo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Motivo')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idmarca">{{ errores.idmarca }}</p>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <!-- Stock Actual - Modificado para usar stock_actual -->
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Stock Actual</label>
                                    <input type="text" class="form-control" v-model="stock_actual" readonly />
                                </div>
                                
                                <!-- Cantidad que se dará de baja -->
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Cantidad que se dará de baja <span class="text-danger">*</span></label>
                                    <input type="number" v-model="datosFormulario.cantidad" class="form-control"
                                        placeholder="Cantidad" @input="validarCampo('cantidad')" min="0" :max="stock_actual" />
                                    <p class="text-danger" v-if="datosFormulario.cantidad > stock_actual">
                                        La cantidad no puede ser mayor al stock actual
                                    </p>
                                </div>
                                
                                <!-- Stock Restante - Nuevo campo añadido -->
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Stock Restante</label>
                                    <input type="text" class="form-control" v-model="stock_restante" readonly />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" @click="cerrarModal()">Cerrar</button>
                            <button type="submit" v-if="tipoAccion == 1" class="btn btn-success">Guardar</button>
                            <button type="submit" v-if="tipoAccion == 2" class="btn btn-success">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal2 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal2"></h4>
                        <button type="button" class="close" @click="modal2 = false" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input v-if="tituloModal2 == 'Motivo'" type="text" v-model="buscarA"
                                        @keyup="listarMotivo(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">
                                  
                                    <input v-if="tituloModal2 == 'Productos'" type="text" v-model="buscarA"
                                        @keyup="listarProducto(1, buscarA, criterioA, idAlmacenSeleccionado)" class="form-control"
                                        placeholder="Texto a buscar">
                                                           
                                    <button v-show="tituloModal2 == 'Motivo'" type="button"
                                        @click="abrirModal3('Marca', 'registrarMar')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" v-if="tituloModal2 == 'Productos'">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Codigo de barra</th> 
                                        <th>Nombre</th>
                                        <th>Proveedor</th>  
                                        <th>Cantidad</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="arrayelemento in arrayBuscador" :key="arrayelemento.id">
                                        <td>
                                            <button type="button" @click="seleccionar(arrayelemento)"
                                                    class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                        </td>
                                        <td v-text="arrayelemento.codigo"></td>
                                        <td v-text="arrayelemento.nombre"></td>
                                        <td v-text="arrayelemento.nombre_proveedor"></td>                                       
                                        <td v-text="arrayelemento.stock_total"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-striped table-sm"
                                v-else-if="tituloModal2 == 'Motivo'">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="arrayelemento in arrayBuscador" :key="arrayelemento.id">
                                        <td>
                                            <button type="button" @click="seleccionar(arrayelemento)"
                                                class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                        </td>
                                        <td v-text="arrayelemento.nombre"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav v-if="tituloModal2 == 'Motivo'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <nav v-else-if="tituloModal2 == 'Productos'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(pagination.current_page - 1, buscar, criterio, idAlmacenSeleccionado)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(page, buscar, criterio, idAlmacenSeleccionado)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(pagination.current_page + 1, buscar, criterio, idAlmacenSeleccionado)">Sig</a>
                                </li>
                            </ul>
                        </nav>
        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click=cerrarModal2()>Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal3 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal3"></h4>
                        <button type="button" class="close" @click="cerrarModal3()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div v-if="tituloModal2 !== 'Proveedors'" class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div v-if="tituloModal2 !== 'Grupos' && tituloModal2 !== 'Lineas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre" class="form-control1"
                                        placeholder="Ingrese nombre del motivo">
                                </div>
                            </div>
                        </form>
                    </div>
                
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal3()">Cerrar</button>

                        <button type="button" v-if="tipoAccion2 == 5" class="btn btn-primary"
                            @click="registrarMarca()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 6" class="btn btn-primary"
                            @click="actualizarMarca()">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>

import { esquemaArticulos, esquemaInventario } from '../constants/validations';

import VueBarcode from 'vue-barcode';
export default {
    data() {
        return {
        idAlmacenSeleccionado: null, // ID del almacén seleccionado
        categoriaSeleccionada: '', // Aquí almacenaremos la categoría seleccionada

            tipo_stock: "paquetes",
            idarticulo : 0,
            fechaVencimientoSeleccion: '0',
            arrayAlmacenes: [],
            AlmacenSeleccionado: null,
            datosFormulario: {
                cantidad: 0,
                idtipobaja: null,
                producto: null,
                idAlmacenSeleccionado: null, // ID del almacén seleccionado
            },

            stock_actual: 0,
            stock_restante: 0,

            errores: {},
            monedaPrincipal: [],

            criterioA: 'nombre',
            buscarA: '',
            tituloModal2: '',
            motivoseleccionado: [],
            industriaseleccionada: [],
            productoseleccionado: [],
            proveedorseleccionada: [],
            gruposeleccionada: [],
            nombre_grupo: '',

            modal2: false,
            idcategoria: 0,
            idmarca: 0,
            idindustria: 0,
            idproveedor: 0,
            idgrupo: 0,
            idmedida: 0,
            nombre_categoria: '',
            nombre_proveedor: '',
            //id:'',//aumente 7 julio
            codigo: '',
            nombre: '',
            nombre_generico: '',
            //validaion para inputs

            lineaseleccionadaVacio: false,
            marcaseleccionadaVacio: false,
            //aumente esto
            unidad_envase: 0,
            precio_costo_unid: 0,
            precio_costo_paq: 0,
            //hasta aqui
            precios: [],
            precio: '',//aumente 5julio

            //aumento 13_junio
            precio_uno: 0,
            precio_dos: 0,
            precio_tres: 0,
            precio_cuatro: 0,
            //hasta aqui

            //--------hasta aqui-----------------
            //--grupo--
            nombre_grupo: '',
            //---hasta aqui
            precio_venta: 0,
            costo_compra: 0,

            stock: 5,
            descripcion: '',
            fotografia: '',
            fotoMuestra: null,
            arrayAjuste: [],
            arrayBuscador: [],
            modal: 0,

            tituloModal: '',
            tipoAccion: 0,
            tipoAccion2: 0,
            //------registro industia, marcas--
            modal3: 0,
            tituloModal3: '',
            marca_id: 0,
            condicion: 1,
            nombre_industria: '',
            //--------hasta aqui---
            pagination: this.createPaginationObject(),
            offset: {
                pagination: 3,
            },
            criterio: 'articulos.nombre', // Por defecto
        categoria: '', // Nueva categoría seleccionada
            buscar: '',

            //CONFIGURACIONES
            mostrarSaldosStock: '',
            mostrarProveedores: '',
            mostrarCostos: '',
            rolUsuario: '',


            descripcion_medida: '',
            medidaseleccionada: [],
        }
    },
    components: {
        'barcode': VueBarcode
    },
    computed: {
        isActived: function () {
            return this.pagination.current_page;
        },
        pagesNumber: function () {
            return this.calculatePages(this.pagination, this.offset.pagination);
        }
    },
    //añadido en fecha 14/03/25
    // Corrección de los watchers
    watch: {
        // Para el producto seleccionado
        'productoseleccionado': {
            handler(newVal) {
                console.log("Producto seleccionado cambiado:", newVal);
                if (newVal && newVal.id) {
                    // Actualizar el ID del producto en datosFormulario
                    this.datosFormulario.producto = newVal.id;
                    // Llamar a obtenerStock
                    this.obtenerStock();
                }
            },
            deep: true
        },
        
        // Para el almacén seleccionado
        'idAlmacenSeleccionado': function(newVal) {
            console.log("Almacén seleccionado cambiado:", newVal);
            this.AlmacenSeleccionado = newVal; // Sincronizar ambas variables
            if (newVal && this.productoseleccionado && this.productoseleccionado.id) {
                this.obtenerStock();
            }
        },
        
        // Para la cantidad
        'datosFormulario.cantidad': function(newVal) {
            console.log("Cantidad cambiada:", newVal);
            this.actualizarStock();
        }
    },

    //watch: {
      //  previewCsv: 'parseCsv', // Llama a parseCsv cuando previewCsv cambie
    //},
    methods: {
        toastSuccess(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `.<br>
    </div>`, {
                type: "success",
                position: "bottom-right",
                duration: 4000
            });
        },
        toastError(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `<br>
    </div>`, {
                type: "error",
                position: "bottom-right",
                duration: 4000
            });
        },
        asignarCampos() {
            console.log("Asignar campos");
            console.log(this.datosFormulario.producto);
            console.log(this.datosFormulario.idAlmacenSeleccionado);

            this.datosFormulario.producto = this.productoseleccionado.id
            this.datosFormulario.idtipobaja = this.motivoseleccionado.id
            this.datosFormulario.idAlmacenSeleccionado = this.idAlmacenSeleccionado
        },
        async validarCampo(campo) {
            this.asignarCampos();
            try {
                await esquemaArticulos.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },

        
        async enviarFormulario() {
        //this.asignarCampos();

        console.log("DATOS FORMULARIO ANTES DE VALIDAR: ", this.datosFormulario);

        let validacionExitosa = true;


        if (validacionExitosa) {
            // Registro del ajuste

            try {
                await this.registrarAjuste(this.datosFormulario);
                console.log("Registro de ajuste exitoso", this.datosFormulario);
            } catch (error) {
                console.error("Error al registrar el ajuste: ", error);
            }
        }
    },


        obtenerConfiguracionTrabajo() {
            // Utiliza Axios para realizar la solicitud al backend
            axios.get('/configuracion')
                .then(response => {
                    console.log(response)
                })
                .catch(error => {
                    console.error('Error al obtener configuración de trabajo:', error);
                });
        },

        calculatePages: function (paginationObject, offset) {
            if (!paginationObject.to) {
                return [];
            }

            var from = paginationObject.current_page - offset;
            if (from < 1) {
                from = 1;
            }

            var to = from + (offset * 2);
            if (to >= paginationObject.last_page) {
                to = paginationObject.last_page;
            }

            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        createPaginationObject() {
            return {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            }
        },

        //-------------hasta qui calcular -----------
        seleccionar(selected) {
            if (this.tituloModal2 == "Motivo") {
                this.motivoseleccionado = selected;
                this.marcaseleccionadaVacio = false;
                
            } else if (this.tituloModal2 == "Productos") {
                if (selected.condicion == 1) {
                    this.productoseleccionado = selected;
                    //this.validarCampo("idproducto");

                } else if (selected.condicion == 0) {
                    this.advertenciaInactiva('Productos');
                }

            }

            this.arrayBuscador = [];
            this.modal2 = false;
        },

        cerrarModal2(){
            this.arrayBuscador = [];
            this.modal2 = false;
            this.buscarA = '';
        },
       
        listarProducto(page, buscar, criterio, idAlmacen) {
            let me = this;

            // Construir la URL con la categoría seleccionada
            let url = `/articuloAjusteInven?page=${page}&buscar=${buscar}&criterio=${criterio}&idAlmacen=${idAlmacen}&categoria=${me.categoriaSeleccionada}`;

            axios.get(url)
                .then(response => {
                    me.arrayBuscador = response.data.articulos.data;
                    me.pagination = response.data.pagination;
                })
                .catch(error => {
                    console.error('Error al listar los productos:', error);
                });
        },

        seleccionarCategoria(categoria) {
    this.categoriaSeleccionada = categoria; // Almacenar la categoría seleccionada
    this.listarProducto(1, this.buscarA, this.criterioA, this.idAlmacenSeleccionado); // Actualizar la lista con la nueva categoría
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

        abrirModal2(titulo) {
            if (titulo === "Motivo") {
                this.listarMotivo(1, '', 'nombre');
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.marcaseleccionadaVacio = false;
            } else if (titulo === "Productos") {
                this.listarProducto(1, this.buscarA, this.criterioA, this.idAlmacenSeleccionado)                // Validar si el almacén está seleccionado y tiene un ID válido
                if (!this.idAlmacenSeleccionado) {
                    alert("Primero seleccione un almacén válido");
                    return;
                }
                //this.listarProducto(1, '', 'nombre', this.idAlmacenSeleccionado);
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.lineaseleccionadaVacio = false;
            }
        },

        almacenSeleccionado() {
            console.log("ID del almacén seleccionado:", this.idAlmacenSeleccionado);
        },

        listarAlmacenes(page, buscar, criterio) {
            let me = this;
            var url = `/almacen?page=${page}&buscar=${buscar}&criterio=${criterio}`;
            axios.get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayBuscador = respuesta.almacenes.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarArticulo(page, buscar, criterio) {
            let me = this;
            var url = `/ajusteinv?page=${page}&buscar=${buscar || ''}&criterio=${criterio || 'articulos.nombre'}&categoria=${this.categoria}`;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayAjuste = respuesta.ajuste.data;
                me.pagination = respuesta.pagination;
            })
            .catch(function (error) {
                console.error("Error al listar artículos:", error);
            });
        },
    filtrarPorCategoria(categoria) {
        this.categoria = categoria; // Establece la categoría seleccionada
        this.listarArticulo(1, this.buscar, this.criterio); // Recarga la lista con la categoría aplicada
    },

        listarMarca(page, buscar, criterio) {
            let me = this;
            var url = '/marca?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;

            axios.get(url).then(function (response) {

                var respuesta = response.data;

                me.arrayBuscador = respuesta.marcas.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
       
        listarMotivo(page, buscar, criterio) {
            let me = this;
            var url = '/motivos?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                console.log(respuesta);
                me.arrayBuscador = respuesta.motivos.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log('ERRORES', error);
                });
        },

        cambiarPagina(page) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarArticulo(page, me.buscar || '', me.criterio || 'articulos.nombre', me.categoria);
        },

        cambiarPaginaMarca(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarMotivo(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaLinea(page, buscar, criterio, idAlmacenSeleccionado) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarProducto(page, buscar, criterio, idAlmacenSeleccionado);
            //Envia la petición para visualizar la data de esa página
        },

        // Método para obtener el stock actual 14/03/25
        obtenerStock() {
            console.log("Llamando a obtenerStock - Inicio");
            
            // Obtener los IDs del producto y almacén (manejar múltiples posibilidades)
            const productoId = this.datosFormulario.producto || (this.productoseleccionado ? this.productoseleccionado.id : null);
            const almacenId = this.idAlmacenSeleccionado || this.AlmacenSeleccionado;
            
            console.log("Datos para obtenerStock:", {
                productoId: productoId,
                almacenId: almacenId
            });
            
            // Verificar que tenemos tanto el producto como el almacén seleccionados
            if (productoId && almacenId) {
                console.log("Enviando petición al servidor...");
                
                // Hacer la petición al endpoint correcto
                axios.get('/ajuste-inventario/obtenerStock', {
                    params: {
                        producto: productoId,
                        almacen: almacenId
                    }
                })
                .then(response => {
                    console.log("Respuesta recibida del servidor:", response.data);
                    
                    // Actualizar el stock actual con la respuesta del servidor
                    this.stock_actual = response.data.stock_actual;
                    console.log("Stock actual actualizado:", this.stock_actual);
                    
                    // Calcular inmediatamente el stock restante
                    this.actualizarStock();
                })
                .catch(error => {
                    console.error("Error al obtener el stock:", error);
                    console.error("Detalles del error:", error.response ? error.response.data : error.message);
                    this.stock_actual = 0; // En caso de error, establecer en 0
                    this.stock_restante = 0;
                });
            } else {
                console.log("No hay producto o almacén seleccionado");
                // Si no hay producto o almacén seleccionado, poner en 0
                this.stock_actual = 0;
                this.stock_restante = 0;
            }
        },
        actualizarStock() {
            console.log("Ejecutando actualizarStock");
            // Calcular el stock restante basado en la cantidad a dar de baja
            const cantidad = parseInt(this.datosFormulario.cantidad) || 0;
            console.log("Cantidad:", cantidad);
            console.log("Stock actual:", this.stock_actual);
            
            this.stock_restante = Math.max(0, this.stock_actual - cantidad);
            console.log("Stock restante calculado:", this.stock_restante);
        },


        calcularPrecioValorMoneda(precio) {
            return ((precio * parseFloat(this.monedaPrincipal)).toFixed(2))
        },


        registrarAjuste(data) {
            let me = this;
            var formulario = new FormData();
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    formulario.append(key, data[key]);
                }
            }

            axios.post('/ajuste/registrar', formulario, {
                headers: {
                    'Content-Type': 'multipart/form-data' 
                }
            }).then(function (response) {
                swal({
                        type: 'success',
                        title: 'REGISTRADO',
                        text: "El ajuste fue registrado con exito",
                        showConfirmButton: false,
                        timer: 2000, // El mensaje se cerrará automáticamente después de 1.5 segundos
                    });
                var respuesta = response.data;
                me.idarticulo = respuesta.id;
                console.log("respuesta = ", me.idarticulo)
                me.cerrarModal();
                me.listarArticulo(1, '', 'id');
                me.toastSuccess("Ajuste de Inventario registrado correctamente");

            }).catch(function (error) {
                console.error(error);
                me.toastError("Hubo un error al registrar el ajuste de inventario");
            });
        },

       
        advertenciaInactiva(nombre) {
            swal({
                title: 'Opción Inactiva',
                text: 'No puede seleccionar esta opción porque está inactiva.',
                type: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar',
                confirmButtonClass: 'btn btn-success',
                buttonsStyling: false,
            }).then(() => {

                    this.abrirModal2(nombre);

            });
        },
        
        
        //#################registro industria############
        registrarMarca() {
            let me = this;

            axios.post('/motivo/registrar', {
                'nombre': this.nombre
            }).then(function (response) {
                me.cerrarModal3();
                //me.modal3=0;
                me.listarMotivo(1, '', 'id');
            }).catch(function (error) {
                console.log(error);
            });
        },        
      
        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';
           
            //
            this.cantidad = 0;
            this.idtipobaja = '',
            this.producto = ''
            this.buscarA = '';

        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "articulo":
                    {
                        switch (accion) {

                            case 'registrar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Ajuste';

                                    this.tipoAccion = 1;
                                    this.fotografia = '';

                                    this.datosFormulario = {
                                        cantidad: 0,
                                        idtipobaja: null,
                                        producto: null,
                                        idAlmacenSeleccionado: null,
                                    };

                                    this.idAlmacenSeleccionado= null, 
                                    this.categoriaSeleccionada= '', 
                                    this.productoseleccionado = [],
                                    this.motivoseleccionado = [],
                                    this.buscarA = '';

                                    this.errores = {};
                                    break;
                                }
                            case 'actualizar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Actualizar Artículo';
                                    this.tipoAccion = 2;
                                    this.datosFormulario = {
                                        nombre: data['nombre'],
                                        descripcion: data['descripcion'],
                                        nombre_generico: data['nombre_generico'],
                                        unidad_envase: data['unidad_envase'],
                                        precio_costo_unid: this.calcularPrecioValorMoneda(data['precio_costo_unid']),
                                        precio_costo_paq: this.calcularPrecioValorMoneda(data['precio_costo_paq']),
                                        precio_venta: this.calcularPrecioValorMoneda(data['precio_venta']),
                                        precio_uno: 0,
                                        precio_dos: 0,
                                        precio_tres: 0,
                                        precio_cuatro: 0,
                                        stock: this.tipo_stock == "paquetes" ? data['stock'] / data['unidad_envase'] : data['stock'],
                                        costo_compra: this.calcularPrecioValorMoneda(data['costo_compra']),
                                        codigo: data['codigo'],
                                        codigo_alfanumerico: data['codigo_alfanumerico'] ? data['codigo_alfanumerico'] : "",
                                        descripcion_fabrica: data['descripcion_fabrica'] ? data['descripcion_fabrica'] : "",
                                        idcategoria: null,
                                        idmarca: null,
                                        idindustria: null,
                                        idgrupo: null,
                                        idproveedor: null,
                                        idmedida: data['idmedida'],
                                        id: data['id']
                                    };
                                    this.errores = {};
                                    this.idmedida = data['idmedida'];

                                    this.fotografia = data['fotografia'];
                                    this.fotoMuestra = data['fotografia'] ? 'img/articulo/' + data['fotografia'] : null;
                                    //this.industriaseleccionada = { nombre: data['industriaseleccionada.nombre'] };

                                    //this.industriaseleccionada = {nombre: data['nombre_industria']};
                                    this.industriaseleccionada = { nombre: data['nombre_industria'], id: data['idindustria'] };
                                    //this.lineaseleccionada = {nombre: data['nombre_categoria']};
                                    this.lineaseleccionada = { nombre: data['nombre_categoria'], id: data['idcategoria'] };
                                    //this.marcaseleccionada = {nombre: data['nombre_marca']};
                                    this.marcaseleccionada = { nombre: data['nombre_marca'], id: data['idmarca'] };
                                    this.proveedorseleccionada = { nombre: data['nombre_proveedor'], id: data['idproveedor'] };
                                    //this.gruposeleccionada = {nombre_grupo: data['nombre_grupo']};
                                    this.gruposeleccionada = { nombre_grupo: data['nombre_grupo'], id: data['idgrupo'] };
                                    this.medidaseleccionada = { descripcion_medida: data['descripcion_medida'], id: data['idmedida'] };

                                    this.precio_uno = this.calcularPrecioValorMoneda(data['precio_uno']);
                                    this.precio_dos = this.calcularPrecioValorMoneda(data['precio_dos']);
                                    this.precio_tres = this.calcularPrecioValorMoneda(data['precio_tres']);
                                    this.precio_cuatro = this.calcularPrecioValorMoneda(data['precio_cuatro']);
                                    // this.precios.forEach((precio) => {
                                    // });
                                    this.fechaVencimientoSeleccion = data['vencimiento'];
                                    break;

                                }
                            case 'registrarInd':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Industria';
                                    this.nombre = '';
                                    //this.descripcion = '';
                                    this.tipoAccion = 3;
                                    break;
                                }
                        }
                    }
            }
        },


        cerrarModal3() {
            this.modal3 = 0;
            this.tituloModal3 = '';
            this.nombre = '';
        },


        abrirModal3(modelo3, accion3, data = []) {
            switch (modelo3) {
               
                case "Marca":
                    {
                        switch (accion3) {
                            case 'registrarMar':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Motivo de Bajass';
                                    this.nombre = '';
                                    this.tipoAccion2 = 5;
                                    break;
                                }
                            case 'actualizar':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar marca';
                                    this.tipoAccion2 = 6;
                                    this.marca_id = data['id'];
                                    this.nombre = data['nombre'];
                                    this.condicion = data['condicion'];
                                    break;
                                }
                        }
                    }
            }
        },
        
        
        datosConfiguracion() {
            let me = this;
            var url = '/configuracion';

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.mostrarSaldosStock = respuesta.configuracionTrabajo.mostrarSaldosStock;
                me.mostrarCostos = respuesta.configuracionTrabajo.mostrarCostos;
                me.mostrarProveedores = respuesta.configuracionTrabajo.mostrarProveedores;

                me.monedaPrincipal = [respuesta.configuracionTrabajo.valor_moneda_principal, respuesta.configuracionTrabajo.simbolo_moneda_principal]

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
        },

    },
    mounted() {
        this.selectAlmacen();
        this.recuperarIdRol();
        this.datosConfiguracion();
        this.obtenerConfiguracionTrabajo();
        this.listarArticulo(1, this.buscar, this.criterio);
    },

        
        
        
    



}
</script>
<style scoped>
.card-error {
    margin-bottom: 10px;
    width: 100%;
    padding: 15px;
    border-radius: 15px;
    border: 2px solid #ab7078;
    background-color: #fec0ca;

}

.csv-headers-container {
    margin-top: 10px;
}

.csv-headers-list {
    list-style-type: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
}

.csv-header {
    background-color: #3498db;
    color: white;
    padding: 5px 10px;
    margin: 5px;
    border-radius: 5px;
    cursor: pointer;
}

.csv-header label {
    display: flex;
    align-items: center;
}

.csv-header input {
    margin-right: 5px;
}

.selected-headers-container {
    margin-top: 10px;
}

.selected-headers-list {
    list-style-type: none;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
}


.modal-content {
    width: 100% !important;
    position: absolute !important;
}

.mostrar {

    display: list-item !important;
    opacity: 1 !important;
    position: absolute !important;
    background-color: #3c29297a !important;
}

.div-error {
    display: flex;
    justify-content: center;
}

.text-error {
    color: red !important;
    font-weight: bold;
}

.table-responsive {
    overflow-x: auto;
}


.sticky-column {
    position: sticky;
    left: 0;
    z-index: 1;
    background-color: white;
}

.border-red {
    border-color: red !important;
}
</style>