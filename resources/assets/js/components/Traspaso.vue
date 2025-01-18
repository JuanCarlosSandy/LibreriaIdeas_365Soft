<template>
    <main class="main">
        <ol class="breadcrumb">
             <li class="breadcrumb-item"><a class="text-decoration-none" href="/">Escritorio</a></li>
        </ol>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Almacenes
                    <button type="button" @click="abrirModal('traspaso','registrar')" class="btn btn-secondary">
                        <i class="icon-plus"></i>&nbsp;Nuevo Traspaso
                    </button>
                </div>
                <div class="table-responsive">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <label for="fechaInicio">Fecha Inicio:</label>
                                <input type="date" id="fechaInicio" v-model="fechaInicio">
                                <label for="fechaFin">Fecha Fin:</label>
                                <input type="date" id="fechaFin" v-model="fechaFin">
                                <button @click="fetchTraspasos">Filtrar</button>
                            </div>
                        </div>                       
                    </div>
                    <div class="table-responsive">
                        <div style="text-align: center; font-size: 18px;">Traspaso registrado</div>
                        <table class="table table-bordered table-striped table-sm">  
                            <thead>                    
                                <tr>
                                    <th>Opciones</th>
                                    <th>Almacen Origen</th>
                                    <th>Almacen Destino</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <!-- <th>Usuario</th>
                                    <th>Observacion</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="traspaso in traspasos" :key="traspaso.id">
                                    <td>
                                        <button type="button" @click="verTraspaso(traspaso.id)"
                                            class="btn btn-success btn-sm">
                                            <i class="icon-eye"></i>
                                        </button> &nbsp;
                                    </td>
                                    <td v-text="traspaso.almacen_origen"></td>
                                    <td v-text="traspaso.almacen_destino"></td>
                                    <td>{{ formatDate(traspaso.fecha_traspaso) }}</td>
                                    <td>{{ formatTime(traspaso.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>   
                    <div class="table-responsive">
                        <div style="text-align: center; font-size: 18px;">Detalle De Traspaso</div>
                        <!-- <td colspan="8" style="text-align: center; font-size: 18px;">Traspaso registrado</td> -->
                        <table class="table table-bordered table-striped table-sm">  
                            <thead>                    
                                <tr>
                                    <th>Producto</th>
                                    <!-- <th>Lote</th> -->
                                    <!-- <th>Paquete</th> -->
                                    <!-- <th>Unidades</th> -->
                                    <th>Total Traspasado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="traspDet in arrayInventarioTrasp" :key="traspDet.id">
                                    <td v-text="traspDet.nombre_producto"></td>
                                    <td v-text="traspDet.cantidad_traspaso"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>   
                </div>
            </div>
        </div><!--Listado -->>
        <!--Modelo de Registro de traspaso y muesTra de lo agregado-->
        <div class="modal " tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header"><!--muesTra la vista verde-->
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                        <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body"><!--muestra la vista marfil-->
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">                           
                            <div class="form-group row border">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <label class="small col-4">Almacen Origen(*)</label>
                                            <div class="col-8">
                                                <select class="form-control" v-model="AlmacenSeleccionado" @change="getDatosAlmacen">
                                                    <option value="0" disabled>Seleccione</option>
                                                    <option v-for="opcion in arrayAlmacenes" :key="opcion.id" :value="opcion.id">{{ opcion.nombre_almacen }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <label class="small col-4">Almacen Destino(*)</label>
                                            <div class="col-8">
                                                <select class="form-control" v-model="AlmacenDestSeleccionado" @change="getDatosAlmacenDest">
                                                    <option value="0" disabled>Seleccione</option>
                                                    <option v-for="opcion in arrayAlmacenesDest" :key="opcion.id" :value="opcion.id">{{ opcion.nombre_almacen }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <label class="small col-4">Tipo Traspaso(*)</label>
                                            <div class="col-8">
                                                <select class="form-control" v-model="tipotraspo">
                                                    <option value="0" disabled>Seleccione</option>
                                                    <option v-for="tipotraspo in arrayTraspaso" :key="tipotraspo" :value="tipotraspo" v-text="tipotraspo"></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!--para elegir producto de inventario-->
                        <div class="card d-flex flex-row align-items-center p-2">
                            <button @click="abrirModal2()" class="btn btn-primary me-3">Buscar Producto</button>
                            <div class="d-flex align-items-center flex-wrap">
                                <h5 class="card-title me-3 mb-0">
                                    <strong>Artículo:</strong> {{ nombre_producto || 'Seleccione un articulo' }}
                                </h5>
                                <h5 class="card-title mb-0">
                                    <strong>Código:</strong> {{ codigo || '---' }}
                                </h5>
                            </div>
                        </div>


                        <div class="form-group row border p-2">
                            <div class="d-flex align-items-center w-100">
                                <!-- Stock (Origen) -->
                                <div class="d-flex align-items-center me-3">
                                    <label class="small me-2">Stock (Origen)</label>
                                    <input type="number" class="form-control form-control-sm" v-model="saldo_stock" readonly>
                                </div>

                                <!-- Stock (Destino) -->
                                <div class="d-flex align-items-center me-3">
                                    <label class="small me-2">Stock (Destino)</label>
                                    <input type="number" class="form-control form-control-sm" v-model="saldoStockTotal" readonly>
                                </div>

                                <!-- Cantidad Trasp -->
                                <div class="d-flex align-items-center me-3">
                                    <label class="small me-2">Cantidad Trasp</label>
                                    <input type="number" class="form-control form-control-sm" v-model="cantidad_traspaso">
                                </div>

                                <!-- Botón agregar -->
                                <button @click="agregarDetalle()" class="btn btn-success btn-sm d-flex align-items-center">
                                    <i class="icon-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>

            
                        <!--################################-Muestra los datos cuando se da click a AGREGAr-#######################-->
                        <div class="form-group row border">
                            <div class="table-responsive col-md-12">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Eliminar</th>
                                            <th>Codigo</th>
                                            <th>Producto</th>
                                            <!--<th>Unid./Paq.</th>
                                            <th>F.Vencimiento</th>-->
                                            <th>Saldo Stock Origen</th>
                                            <th>Cant. Traspaso Destino</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayDetalle.length">
                                        <tr v-for="(detalle, index) in arrayDetalle" :key="detalle.id">
                                            <td>
                                                <button @click="eliminarDetalle(index)" type="button"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="icon-close"></i>
                                                </button>
                                            </td>
                                            <td v-text="detalle.codigo"></td>
                                            <td v-text="detalle.nombre_producto"></td>
                                            <!--<td v-text="detalle.unidad_envase"></td>
                                            <td v-text="detalle.fecha_vencimiento"></td>-->
                                            <td>{{ calcularPrecioP(detalle.saldo_stock, detalle.cantidad_traspaso) }}</td>
                                            <td v-text="detalle.cantidad_traspaso"></td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="5">
                                                No hay articulos agregados
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--######################################-hasta QUI-################################-->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                        <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarTraspaso()">Guardar</button>
                        <!-- <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarAlmacen()">Actualizar</button> -->
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div><!--finaliza aqui el modal de traspaso registro-->          
        <!--#####################################-LIStADO DE INvENtARIO PARA SELECCIONAR-#################-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal2 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal2"></h4>
                        <button type="button" class="close" @click="cerrarModal2()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Campo de búsqueda -->
                        <div class="form-group row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        v-model="buscar"
                                        @keyup="actualizarBusqueda"
                                        class="form-control"
                                        placeholder="Texto a buscar"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de resultados -->
                        <div class="table-responsive" style="height: auto; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Codigo</th>
                                        <th>Articulo</th>
                                        <th v-if="anio_motor === 'P'">Color</th> <!-- Solo muestra esta columna si anio_motor es 'P' -->
                                        <th v-if="anio_motor === 'P'">Capacidad</th> <!-- Solo muestra esta columna si anio_motor es 'P' -->
                                        <th>Cantidad</th>
                                        <th>Ubicacion</th>
                                        <th>Proveedor Habitual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="inventario in arrayInventario" :key="inventario.id">
                                        <td>
                                            <button type="button" @click="agregarDetalleModal(inventario)" class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                        </td>
                                        <td v-text="inventario.codigo"></td>
                                        <td v-text="inventario.nombre_producto"></td>
                                        <td v-if="anio_motor === 'P'" v-text="inventario.color"></td> <!-- Solo muestra si anio_motor es 'P' -->
                                        <td v-if="anio_motor === 'P'" v-text="inventario.num_chasis"></td> <!-- Solo muestra si anio_motor es 'P' -->
                                        <td v-text="inventario.saldo_stock"></td>
                                        <td v-text="inventario.ubicacion"></td>
                                        <td v-text="inventario.nombre_proveedor"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal2()">Cerrar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        <!--######################################-hasta QUI-################################-->
    </main>
</template>
<script>
    export default {
        data (){
            return{
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,//para mostrar lo botones de guardar/cancelar
                //---modal2---
                modal2 : 0,
                tituloModal2 : '',
                //----almacen--
                idalmacen : 0,
                AlmacenSeleccionado : 1,
                arrayAlmacenes : [],
                //AlmacenDestSeleccionado : '',
                idalmacendes : 0,
                AlmacenDestSeleccionado : 1,
                arrayAlmacenesDest : [],
                //--fechas---
                fecha_traspaso : '',
                //---Tipo traspaso--
                tipotraspo : 'Salida',
                arrayTraspaso : ['Salida', 'Entrada'],
                tipounipaq : 'Unidad',
                arrayUndPaq : ['Unidad', 'Paquete'],
                //---Producto de inventario mostrar al seleccionar--
                arrayInventario : [],
                codigo : '',
                saldo_stock : '',
                nombre_producto : '',
                saldoStockTotal : '',
                fecha_vencimiento : '',
                arrayArticuloSeleccionado : {},//talbes de por []
                fotografia : '',
                //---insertar cantidad--
                cantidad_traspaso : 1,
                //---array de mostrar lo selecciondo para recorrer el--
                arrayDetalle : [],
                //----datos adicional para registrar---
                idinventario: 0,
                idarticulo: 0,
                //----para mostrar lo que se registro por id
                idtraspaso : 0,
                //-----lisTado por filtro de fecha--
                traspasos: [],
                fechaInicio: "",
                fechaFin: "",
                arrayInventarioTrasp : [],
                //----listarel saldo_stock
                arrayInventarioStock : [],
                saldoStockTotal : '',
                precio_costo_unid : '',
                precio_costo_paq : '',
                //--buscador--
                pagination: {
                    'total': 0,
                    'current_page': 0,
                    'per_page': 0,
                    'last_page': 0,
                    'from': 0,
                    'to': 0,
                },
                offset: 2,
                criterio :'',
                buscar :'',
                anio_motor: null, // Valor actual del filtro por categoría (C, A, T)
                nombre : '',

            }
        },
        watch: {
            codigo(newValue) {
                if (newValue) {
                    this.listarInventarioSalStock();
                }
            },
        },
        
        computed: {
            isActived: function () {
                return this.pagination.current_page;
            },
            //Calcula los elementos de la paginación
            pagesNumber: function () {
                if (!this.pagination.to) {
                    return [];
                }

                var from = this.pagination.current_page - this.offset;
                if (from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2);
                if (to >= this.pagination.last_page) {
                    to = this.pagination.last_page;
                }

                var pagesArray = [];
                while (from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;

            }
        },
        methods : {
            cambiarTipoUnidad(){
                if (this.tipounipaq=="Unidad"){
                }else{
                    this.tipounipaq="Paquete";

                }
            },
            //--------calcular cantidad_traspaso - saldo_stock--
            calcularPrecioP(saldo_stock, cantidad_traspaso) {
                const precioP = parseFloat(saldo_stock) - parseFloat(cantidad_traspaso);
                return precioP.toFixed(2);
            },
            //---------listar por Trasapso por FILTRO DE FECHA---------
            //---listado por filtro de fecha--
            fetchTraspasos() {
                let me = this;
                var url = '/list/traspasos'; // No incluimos el parámetro 'page' aquí

                // Si las fechas están seleccionadas, agregamos los parámetros de consulta a la URL
                if (this.fechaInicio && this.fechaFin) {
                    url += `?fechaInicio=${this.fechaInicio}&fechaFin=${this.fechaFin}`;
                }

                axios
                    .get(url)
                    .then(function (response) {
                    var respuesta = response.data;
                    me.traspasos = respuesta.traspasos.data;
                    me.pagination = respuesta.pagination;
                    })
                    .catch(function (error) {
                    console.log(error);
                });
            },
            formatDate(date) {
                return new Date(date).toLocaleDateString("es-ES");
            },
            formatTime(date) {
                return new Date(date).toLocaleTimeString("es-ES");
            },
            //--inicializasa fecha acTual
            inicializarFechas() {
                const fechaActual = new Date().toISOString().substr(0, 10);
                this.fechaInicio = fechaActual;
                this.fechaFin = fechaActual;
            },
            //-----------------hasta aqui--------------
            //-----------alamcen-----
            //--garrar el id para que liste el inventario con ese almacen
            getDatosAlmacen() {
                let me = this;
                if (me.AlmacenSeleccionado !== '') { // Comprobar si hay un almacén seleccionado
                    me.loading = true;
                    me.idalmacen = Number(me.AlmacenSeleccionado); // Convertir a número antes de asignarlo
                    console.log('IDalmacen: ' + me.idalmacen);
                }
            },
            getDatosAlmacenDest() {
                let me = this;
                if (me.AlmacenDestSeleccionado !== '') { // Comprobar si hay un almacén seleccionado
                    me.loading = true;
                    me.AlmacenDestSeleccionado = me.AlmacenDestSeleccionado;
                    me.idalmacendes = Number(me.AlmacenDestSeleccionado); // Convertir a número antes de asignarlo
                    console.log('IDalmacenSaldo: ' + me.idalmacendes);
                    //me.listarInventarioSalStock();
                    me.listarInventarioSalStock(me.idalmacendes);
                }
            },
            selectAlmacen() {
                let me = this;
                var url = '/almacen/selectAlmacen';
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayAlmacenes = respuesta.almacenes;
                    console.log('ORIGEN',me.arrayAlmacenes);

                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectAlmacenDestino() {
                let me = this;
                var url = '/almacen/selectAlmacenDest';
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayAlmacenesDest = respuesta.almacenes;
                    console.log("DesTino_Almacen:",me.arrayAlmacenesDest);
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            //--------hasta aqui-----
            //------listado producto de inventario----
            listarInventarios(page) {
                let me = this;
                let url = `/inventariosTraspaso?page=${page}&buscar=${me.buscar}&idAlmacen=${me.idalmacen}`;
                axios
                    .get(url)
                    .then(function (response) {
                        let respuesta = response.data;
                        me.arrayInventario = respuesta.inventarios.data;
                        me.pagination = respuesta.pagination;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            filtrarPorAnioMotor(valor) {
                this.anio_motor = valor; // Actualiza el filtro
                this.buscar = ""; // Limpia el texto de búsqueda
                this.listarInventarios(1); // Refresca la lista
            },

            actualizarBusqueda() {
                this.listarInventarios(1); // Llama a listarInventarios con la página 1
            },

            cambiarPagina(page, buscar, criterio) {
                let me = this;
                me.pagination.current_page = page;
                me.listarInventarios(page, buscar, criterio);
            },
            //----------listar invenTario de producto para mostra el saldoctock---
                listarInventarioSalStock (){
                let me=this;
                var url = '/inventarios/saldostock' + '?idAlmacen=' + me.idalmacendes + '&idArticulo=' + me.idarticulo;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    console.log("list SALDO_STOCK:",respuesta);
                    if (respuesta.invenstock.length > 0) {
                        me.arrayInventarioStock = respuesta.invenstock.data;
                        me.saldoStockTotal = respuesta.invenstock[0].saldo_stock_totaldos;
                    } else {
                        // No hay datos, por lo que saldoStockTotal se establece en 0 o en lo que quieras
                        me.arrayInventarioStock = [];
                        me.saldoStockTotal = 0; // O cualquier valor predeterminado
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            //------agregar detalle al modal al dar click para que se muestre los datos como imagen, nombre producto
            encuentra(id) {
                var sw = 0;
                for (var i = 0; i < this.arrayDetalle.length; i++) {
                    if (this.arrayDetalle[i].idarticulo == id) {
                        sw = true;
                    }
                }
                return sw;
            },           
            agregarDetalleModal(data = []) {
                let me = this;
                if (me.encuentra(data['id'])) {
                    swal({
                        type: 'error',
                        title: 'Error...',
                        text: 'Este Artículo ya se encuentra agregado!',
                    });
                } else {
                    console.log("==========Agregando inventario-==============");
                    console.log(data);
                    console.log("=============hasta aqui=================");
                    me.arrayArticuloSeleccionado = [{
                        codigo: data['codigo'],
                        fotografia: data['fotografia'],
                        id: data['id'],
                        //nombre: data['nombre'],
                        saldo_stock: data['saldo_stock'],
                        nombre_producto: data['nombre_producto'],
                        fecha_vencimiento: data['fecha_vencimiento'],
                        idarticulo: data['idarticulo'],
                        precio_costo_unid: data['precio_costo_unid'],
                        unidad_envase : data['unidad_envase'],
                    }]
                    me.codigo = me.arrayArticuloSeleccionado[0].codigo;
                    me.nombre_producto = data['nombre_producto'];
                    me.saldo_stock = me.arrayArticuloSeleccionado[0].saldo_stock;
                    me.precio_costo_unid = me.arrayArticuloSeleccionado[0].precio_costo_unid;
                    me.idarticulo = me.arrayArticuloSeleccionado[0].idarticulo;
                    me.precio_costo_paq = data['precio_costo_paq'];
                    me.fecha_vencimiento = me.arrayArticuloSeleccionado[0].fecha_vencimiento;
                    me.unidad_envase = me.arrayArticuloSeleccionado[0].unidad_envase;
                    me.listarInventarioSalStock()
                }
                this.cerrarModal2();
            },
            //--------hasta aqui------------
            //----agregar los datos para mostrar en la Tabla de abajo---
            agregarDetalle() {
                let me = this;
                console.log("----------Almacen seleccionado---------");
                console.log(me.AlmacenSeleccionado);
                console.log('TODO', me.arrayDetalle);
                console.log("----------Almacen seleccionado----------");
                if (me.arrayArticuloSeleccionado.length == 0 || me.cantidad_traspaso == 0 || me.AlmacenDestSeleccionado == 0) {
                    console.log("Seleccione un producto o la fecha o verifique la cantidad o el almacén");
                } else {
                    if (me.encuentra(me.arrayArticuloSeleccionado[0].id)) {
                        swal({
                            type: 'error',
                            title: 'Error...',
                            text: 'Este Artículo ya se encuentra agregado!',
                        });
                    } else {
                        // Verificar si AlmacenDestSeleccionado es igual a AlmacenSeleccionado
                        if (me.AlmacenDestSeleccionado === me.AlmacenSeleccionado) {
                            this.advertenciaSwalAlmDes();
                            console.log("El almacén de destino debe ser diferente al almacén seleccionado.");
                            return; // No agregar el detalle y salir del método
                        }
                        if (me.tipounipaq == "Paquetes") {
                            me.arrayDetalle.push({
                                idinventario: me.arrayArticuloSeleccionado.id,
                                idarticulo: me.arrayArticuloSeleccionado.idarticulo,
                                //idalmacen: me.AlmacenDestSeleccionado,
                                idalmacen : me.AlmacenSeleccionado,
                                idalmacendes: me.AlmacenDestSeleccionado,
                                codigo: me.arrayArticuloSeleccionado.codigo,
                                nombre_producto: me.arrayArticuloSeleccionado.nombre_producto,
                                fecha_vencimiento: me.arrayArticuloSeleccionado.fecha_vencimiento,
                                saldo_stock: me.arrayArticuloSeleccionado.saldo_stock,
                                unidad_envase: me.arrayArticuloSeleccionado[0].unidad_envase,
                                precio_costo_unid : me.arrayArticuloSeleccionado[0].precio_costo_unid,
                                cantidad_traspaso: me.cantidad_traspaso,
                            });
                        } else {
                            me.arrayDetalle.push({
                                idinventario: me.arrayArticuloSeleccionado[0].id,
                                idarticulo: me.arrayArticuloSeleccionado[0].idarticulo,
                                idalmacen : me.AlmacenSeleccionado,
                                idalmacendes: me.AlmacenDestSeleccionado,
                                codigo: me.arrayArticuloSeleccionado[0].codigo,
                                nombre_producto: me.arrayArticuloSeleccionado[0].nombre_producto,
                                fecha_vencimiento: me.arrayArticuloSeleccionado[0].fecha_vencimiento,
                                saldo_stock: me.arrayArticuloSeleccionado[0].saldo_stock,
                                unidad_envase: me.arrayArticuloSeleccionado[0].unidad_envase,
                                precio_costo_unid : me.arrayArticuloSeleccionado[0].precio_costo_unid,
                                cantidad_traspaso: me.cantidad_traspaso,
                                //son para que semuestre al agragar e la vista y enviar datos para registrar
                            });
                        }
                        me.arrayArticuloSeleccionado = [];
                        me.codigo = '';
                        me.idinventario = 0;
                        me.nombre_producto = '';
                        me.cantidad_traspaso = 1;
                        me.saldo_stock = '';
                        me.saldoStockTotal = 0;
                    }
                }
            },
            //------------hasta aqui-----------
            //---------eliminar lo agrgagado----
            eliminarDetalle(index) {
                let me = this;
                me.arrayDetalle.splice(index, 1);
            },
            //------Registrar ------
            registrarTraspaso() {

                let me = this;
                if (me.arrayDetalle.length === 0) {
                    // Mostrar un mensaje de error o alerta indicando que se debe agregar al menos un detalle
                    this.advertenciaSwal();
                    return; // No continúes con el proceso de registro
                }
                for (let i = 0; i < me.arrayDetalle.length; i++) {
                    console.log('INvENtARIO',me.arrayDetalle[i].idinventario);
                    console.log('ARtICULOID',me.arrayDetalle[i].idarticulo);
                    console.log(me.arrayDetalle[i].cantidad_traspaso);
                    console.log(me.arrayDetalle[i].fecha_vencimiento);
                    //console.log(me.arrayDetalle[i].cantidad);
                    console.log('LLEGA ARRAYDATA',me.arrayDetalle[i]);
                }
                let almacenOrigen = me.arrayAlmacenes.find(almacen => almacen.id === me.AlmacenSeleccionado);
                let almacenDestino = me.arrayAlmacenes.find(almacen => almacen.id === me.AlmacenDestSeleccionado);
                axios.post('/traspaso/registrar', {
                'tipo_traspaso': this.tipotraspo,
                'almacen_origen': almacenOrigen ? almacenOrigen.nombre_almacen : null,
                'almacen_destino': almacenDestino ? almacenDestino.nombre_almacen : null,
                'fecha_traspaso': this.fecha_traspaso,
                //'total': this.total,
                'data': this.arrayDetalle,

                }).then(function (response) {
                    swal({
                        type: 'success',
                        title: 'REGISTRADO',
                        text: "El traspaso fue registrado con exito",
                        showConfirmButton: false,
                        timer: 2000, // El mensaje se cerrará automáticamente después de 1.5 segundos
                    });
                    me.cerrarModal_1(); // Cierra el modal después de un traspaso exitoso
                    me.eliminarDetalle();
                }).catch(function (error) {
                    console.log(error);
                });
            },
            //-----ver traspaso lo que se registro en un listado----
            verTraspaso(id) {
                let idtraspaso = id; // Cambio de variable a idtraspaso
                console.log('ID_TRASPASO', idtraspaso);
                let me = this;
                //me.listado = 1;

                var url = '/traspaso/obtenerTraspaso?idtraspaso=' + idtraspaso;
                axios.get(url)
                    .then(function (response) {
                    var respuesta = response.data;
                    console.log('LLEGOO:', respuesta);
                    me.arrayInventarioTrasp = respuesta.detalletrasp; // Corrección aquí
                    console.log('TRASPASO', me.arrayInventarioTrasp); // Corrección aquí
                    })
                    .catch(function (error) {
                    console.log(error);
                });
            },
            //---abrir modal de registro de traspaso--
            abrirModal(modelo, accion, data = []){
                switch(modelo){
                    case "traspaso":
                    {
                        switch(accion){
                            case 'registrar':
                            {
                                this.modal = 1;
                                this.tituloModal = 'Registrar Traspaso o Transferencia';                               
                                this.tipo_traspaso= '';
                                this.almacen_origen = '';
                                this.alamcen_destino = '';
                                const fechaActual = new Date().toISOString().substr(0, 10);
                                this.fecha_traspaso = fechaActual;
                                this.getDatosAlmacen();
                                this.getDatosAlmacenDest();
                                this.tipoAccion = 1;
                                break;
                            }
                        }
                    }
                }
            },
            //-----abrir modal de listado de producTo de inventario--
            abrirModal2() {
                //this.listarInventarios("","");
                this.arrayInventario = [];
                this.modal2 = 1;
                this.tituloModal2 = 'Seleccione los Productos que desee';

            },
            //------cerrar modal y modal2--
            cerrarModal(){
                swal({
                    title: '¿Desea Cerrar la ventana y cancelar el Traspaso?',
                    //icon: 'warning',
                    //type: 'warning',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, mantener',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (result.value) {
                        this.modal = 0;
                        this.tituloModal = '';
                        this.saldo_stock = '';
                        this.saldoStockTotal = 0;
                        this.idarticulo = 0;
                        this.precio_costo_unid = '';
                        this.nombre_producto = '';
                        this.codigo = '';
                        this.AlmacenSeleccionado = 1;
                        this.cantidad_traspaso = 1;
                        this.fecha_vencimiento = 1;
                        this.eliminarDetalle();
                        this.AlmacenDestSeleccionado = 1;
                        if (this.arrayArticuloSeleccionado[0]) {
                            this.arrayArticuloSeleccionado[0].fotografia = '';
                        }
                    }
                })
            },
            cerrarModal_1() {
                    // Cierra el modal
                    this.modal = 0;
                    this.modal = 0;
                        this.tituloModal = '';
                        this.saldo_stock = '';
                        this.saldoStockTotal = 0;
                        this.idarticulo = 0;
                        this.precio_costo_unid = '';
                        this.nombre_producto = '';
                        this.codigo = '';
                        this.AlmacenSeleccionado = 1;
                        this.cantidad_traspaso = 1;
                        this.fecha_vencimiento = 1;
                        this.eliminarDetalle();
                        this.AlmacenDestSeleccionado = 1;
                        if (this.arrayArticuloSeleccionado[0]) {
                            this.arrayArticuloSeleccionado[0].fotografia = '';
                        }
                },

            advertenciaSwal(){
                swal({
                    title: "Advertencia",
                    text: "Debe agregar al menos un producto antes de guardar.",
                    type: "error",
                });
            },
            advertenciaSwalAlmDes(){
                swal({
                    title: "Advertencia",
                    text: "Almacen destino tiene que ser distinto.",
                    type: "error",
                });
            },
            cerrarModal2() {
                this.modal2 = 0;
                this.tituloModal2 = '';
                this.buscar = "";
            },
        },
        mounted() {
            this.selectAlmacen();
            this.selectAlmacenDestino();
            this.inicializarFechas();
            //window.addEventListener('keydown', this.atajoButton);
        }
    }
</script>
<style scoped>  
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
        background-color: #3c29297a !important;
        }
        .div-error{
        display: flex;
        justify-content: center;
        }
        .text-error{
        color: red !important;
        font-weight: bold;
        }
        .table-responsive {
        margin: 20px 0;
        }

        .table-hover tbody tr:hover {
        background-color: #f1f1f1;
        }

        .btn-sm {
        padding: 0.25rem 0.5rem;
        }

        .thead-dark th {
        background-color: #343a40;
        color: white;
        }

        .table-bordered th,
        .table-bordered td {
        border: 1px solid #dee2e6;
        }

        .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
        }

        .obligatorio {
        color: red;
        }
    .card-img {
        width: 120px;
        height: auto;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
</style>  