<template>
    <main class="main">

        <!-- Ejemplo de tabla Listado -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Linea
                <button type="button" @click="abrirModal('categoria', 'registrar')" class="btn btn-secondary">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
                <button type="button" @click="cargarExcel()" class="btn btn-info">
                    <i class="icon-doc"></i>&nbsp;Exportar
                </button>
                <button type="button" @click="abrirModalImportLinea()" class="btn btn-success">
                    <i class="icon-plus"></i>&nbsp;Importar
                </button>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control col-md-3" v-model="criterio">
                                <option value="nombre">Nombre</option>
                                <option value="descripcion">Descripción</option>
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarCategoria(1, buscar, criterio)"
                                class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarCategoria(1, buscar, criterio)" class="btn btn-primary"><i
                                    class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Opciones</th>
                                <th>Nombre</th>
                                <th>Codigo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="categoria in arrayCategoria" :key="categoria.id">
                                <td class="sticky-column">
                                    <button type="button" @click="abrirModal('categoria', 'actualizar', categoria)"
                                        class="btn btn-warning btn-sm">
                                        <i class="icon-pencil"></i>
                                    </button> &nbsp;
                                    <template v-if="categoria.condicion">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            @click="desactivarCategoria(categoria.id)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button type="button" class="btn btn-info btn-sm"
                                            @click="activarCategoria(categoria.id)">
                                            <i class="icon-check"></i>
                                        </button>
                                    </template>
                                </td>
                                <td v-text="categoria.nombre"></td>
                                <td v-text="categoria.codigoProductoSin"></td>
                                <td v-text="categoria.descripcion"></td>
                                <td>
                                    <div v-if="categoria.condicion">
                                        <span class="badge badge-success">Activo</span>
                                    </div>
                                    <div v-else>
                                        <span class="badge badge-danger">Desactivado</span>
                                    </div>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination">
                        <li class="page-item" v-if="pagination.current_page > 1">
                            <a class="page-link" href="#"
                                @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                        </li>
                        <li class="page-item" v-for="page in pagesNumber" :key="page"
                            :class="[page == isActived ? 'active' : '']">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)"
                                v-text="page"></a>
                        </li>
                        <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                            <a class="page-link" href="#"
                                @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Fin ejemplo de tabla Listado -->

        <!--Inicio del modal agregar/actualizar-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre" class="form-control"
                                        placeholder="Nombre de categoría">

                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Descripción</label>
                                <div class="col-md-9">
                                    <input type="email" v-model="descripcion" class="form-control"
                                        placeholder="Ingrese descripción">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 form-control-label" for="email-input"><strong>Código SIAT <span class="obligatorio">(Obligatorio)</span></strong></label>
                                <div class="col-md-9">
                                    <select v-model="codigoProductoSin" class="form-control">
                                        <option value="0" disabled>Seleccione el Codigo Siat</option>
                                        <option v-for="productoServicio in arrayProductoServicio" :value="productoServicio.codigoProducto"
                                            v-text="productoServicio.descripcionProducto"></option>
                                    </select>
                                </div>
                            </div>
                            <div v-show="errorCategoria" class="form-group row div-error">
                                <div class="text-center text-error">
                                    <div v-for="error in errorMostrarMsjCategoria" :key="error" v-text="error">

                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                        <button type="button" v-if="tipoAccion == 1" class="btn btn-primary"
                            @click="registrarCategoria()">Guardar</button>
                        <button type="button" v-if="tipoAccion == 2" class="btn btn-primary"
                            @click="actualizarCategoria()">Actualizar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!--Fin del modal-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modalImportar }" role="dialog"
            aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Importar Marca</h4>
                        <button type="button" class="close" @click="cerrarModalImportar()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="mainFormUsers">
                            <div class="form-group">
                                <table class="table">
                                    <tr>
                                        <label class="btn btn-primary" style="margin: 5px;"
                                            v-if="showUpload">Cargar_Archivo
                                            <input type="submit" style="display: none;" name="upload"
                                                @click.prevent="saveExecelUser">
                                        </label>
                                        <div class="border border-dashed border-3 p-3 text-center"
                                            style="cursor: pointer">
                                            <label class="custom-file">
                                                <i class="fa fa-cloud-upload fa-2x" aria-hidden="true"></i>
                                                <p class="custom-file-label">Seleccionar archivo CSV</p>
                                                <input type="file" class="custom-file-input" @change="showUploadButton"
                                                    name="select_users_file">
                                            </label>
                                        </div>
                                    </tr>
                                </table>
                            </div>
                        </form>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalImportar()">Cerrar</button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            categoria_id: 0,
            nombre: '',
            descripcion: '',
            arrayCategoria: [],
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,
            errorCategoria: 0,
            arrayProductoServicio: [],
            arrayActividadEconomica: [],
            codigoActividadEconomica: '',
            codigoProductoSin: '',
            errorMostrarMsjCategoria: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            criterio: 'nombre',
            buscar: '',
            modalImportar: 0,
            showUpload: false,
        }
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
    methods: {
        //---------------- Exportar de excel----
        cargarExcel() {
            window.open('/linea/exportexcel', '_blank');
        },
        abrirModalImportLinea() {
            this.modalImportar = 1;
        },
        cerrarModalImportar() {
            this.modalImportar = 0;
            this.showUpload = false;
        },
        showUploadButton(event) {
            // Verifica si se ha seleccionado un archivo
            this.showUpload = event.target.files.length > 0;
        },
        //----------importar-------
        saveExecelUser() {
            var $mainFormUsers = $('#mainFormUsers')
            var data = new FormData(mainFormUsers)
            axios.post('/linea/import_excel', data)
                .then((res) => {
                    console.log("Importado");
                    swal(
                        'IMPORTADO!',
                        'Lista de Linea.',
                        'success'
                    );
                    this.cerrarModalImportar();
                    this.listarCategoria(1, '', 'nombre');
                }).catch(function (error) {
                    console.log(error);
                });
        },
        listarCategoria(page, buscar, criterio) {
            let me = this;
            var url = '/categoria?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                //consol.log('Linea',respuesta);
                me.arrayCategoria = respuesta.categorias.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        consultaProductosServicios() {
                let me = this;
                var url = '/categoria/consultaProductosServicios';
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayProductoServicio = respuesta.RespuestaListaProductos.listaCodigos;
                    console.log(respuesta.RespuestaListaProductos.listaCodigos);
                }).catch(function (error) {
                    console.log(error);
                });
            },

            consultaActividadEconomica() {
                let me = this;
                var url = '/categoria/consultaActividadEconomica';
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayActividadEconomica = respuesta.RespuestaListaActividades.listaActividades;
                    console.log(respuesta.RespuestaListaActividades.listaActividades);
                }).catch(function (error) {
                    console.log(error);
                });
            },

        cambiarPagina(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarCategoria(page, buscar, criterio);
        },
        registrarCategoria() {
            if (this.validarCategoria()) {
                return;
            }

            let me = this;
            axios.post('/categoria/registrar', {
                'nombre': this.nombre,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin

            }).then(function (response) {
                me.cerrarModal();
                me.listarCategoria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarCategoria() {
            if (this.validarCategoria()) {
                return;
            }

            let me = this;

            axios.put('/categoria/actualizar', {
                'nombre': this.nombre,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin,
                'id': this.categoria_id
            }).then(function (response) {
                me.cerrarModal();
                me.listarCategoria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        desactivarCategoria(id) {
            swal({
                title: 'Esta seguro de desactivar esta categoría?',
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
                    let me = this;

                    axios.put('/categoria/desactivar', {
                        'id': id
                    }).then(function (response) {
                        me.listarCategoria(1, '', 'nombre');
                        swal(
                            'Desactivado!',
                            'El registro ha sido desactivado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        activarCategoria(id) {
            swal({
                title: 'Esta seguro de activar esta categoría?',
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
                    let me = this;

                    axios.put('/categoria/activar', {
                        'id': id
                    }).then(function (response) {
                        me.listarCategoria(1, '', 'nombre');
                        swal(
                            'Activado!',
                            'El registro ha sido activado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        validarCategoria() {
            this.errorCategoria = 0;
            this.errorMostrarMsjCategoria = [];

            if (!this.nombre) this.errorMostrarMsjCategoria.push("El nombre de la categoría no puede estar vacío.");

            if (this.errorMostrarMsjCategoria.length) this.errorCategoria = 1;

            return this.errorCategoria;
        },
        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';
            this.nombre = '';
            this.descripcion = '';
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "categoria":
                    {
                        switch (accion) {
                            case 'registrar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Categoría';
                                    this.nombre = '';
                                    this.descripcion = '';
                                    this.codigoProductoSin = 0;
                                    this.tipoAccion = 1;
                                    break;
                                }
                            case 'actualizar':
                                {
                                    //console.log(data);
                                    this.modal = 1;
                                    this.tituloModal = 'Actualizar categoría';
                                    this.tipoAccion = 2;
                                    this.categoria_id = data['id'];
                                    this.nombre = data['nombre'];
                                    this.descripcion = data['descripcion'];
                                    this.codigoProductoSin = data['codigoProductoSin'];
                                    break;
                                }
                        }
                    }
            }
        }
    },
    mounted() {
        this.listarCategoria(1, this.buscar, this.criterio);
        this.consultaProductosServicios();
        this.consultaActividadEconomica();
    }
}
</script>
<style>
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
</style>
