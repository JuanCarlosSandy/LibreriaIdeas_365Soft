<template>
    <main class="main">
        <Dialog header="Almacenes" :visible.sync="modal1" :modal="true" :containerStyle="{width: '100%', maxWidth: '700px'}" :closable="false" :closeOnEscape="false">
            <div class="toolbar-container">
            <div class="toolbar">
                <Button label="Nuevo" icon="pi pi-plus" @click="abrirModal('almacenes', 'registrar')" class="p-button-secondary p-button-sm"/>
            </div>
            <div class="search-bar">
                <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText type="text" v-model="buscar" placeholder="Texto a buscar" class="p-inputtext-sm"  @keyup="buscarAlmacenes" />
                </span>
            </div>
            </div>
            <DataTable class="p-datatable-sm p-datatable-gridlines" :value="arrayAlmacen" :paginator="true" responsiveLayout="scroll" :rows="5">
            <Column field="opciones" header="Opciones">
                <template #body="slotProps">
                <Button icon="pi pi-check" class="p-button-sm p-button-success custom-icon-size" @click="seleccionarAlmacen(slotProps.data)" />
                <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('almacenes', 'actualizar', slotProps.data)" />
                </template>
            </Column>
            <Column field="nombre_almacen" header="Nombre Almacen"/>
            <Column field="ubicacion" header="Ubicacion"/>
            <Column field="encargados_nombres" header="Encargados"/>
            <Column field="nombre_sucursal" header="Sucursal"/>
            </DataTable>
            <template #footer>
            <Button label="Cerrar" icon="pi pi-times" class="p-button-danger p-button-sm" @click="closeDialog"/>
            </template>
        </Dialog>

        <Dialog
            :header="tituloModal"
            :visible.sync="modal"
            :modal="true"
            :closable="false"
            :containerStyle="{width: '700px'}"
            :closeOnEscape="false"
        >
            <div class="p-fluid p-grid">
                <div class="p-field p-col-12 p-md-6">
                    <label for="nombreAlmacen">Nombre del almacén *</label>
                    <InputText id="nombreAlmacen" placeholder="Ej. Almacén Principal" v-model="datosFormulario.nombre_almacen" :class="{ 'p-invalid': errores.nombre_almacen }" @input="validarCampo('nombre_almacen')" />
                    <small v-if="errores.nombre_almacen" class="p-error">{{ errores.nombre_almacen }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="ubicacion">Ubicación *</label>
                    <InputText id="ubicacion" placeholder="Ej. Calle 123, Ciudad" v-model="datosFormulario.ubicacion" :class="{ 'p-invalid': errores.ubicacion }" @input="validarCampo('ubicacion')" />
                    <small v-if="errores.ubicacion" class="p-error">{{ errores.ubicacion }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="encargados">Encargados *</label>
                    <AutoComplete :multiple="true" v-model="usuariosSeleccionados" :suggestions="arrayUsuario" :dropdown="true" @complete="selectUsuario($event)"  @item-select="actualizarEncargados" @item-unselect="actualizarEncargados" field="nombre" :class="{ 'p-invalid': errores.encargado }" @input="validarCampo('encargado')" placeholder="Buscar Usuarios..."/>
                    <small v-if="errores.encargado" class="p-error">{{ errores.encargado }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="telefono">Teléfono *</label>
                    <InputText id="telefono" placeholder="Ej. 123456789" v-model="datosFormulario.telefono" :class="{ 'p-invalid': errores.telefono }" @input="validarCampo('telefono')" />
                    <small v-if="errores.telefono" class="p-error">{{ errores.telefono }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="sucursal">Sucursal *</label>
                    <AutoComplete class="p-inputtext-sm" v-model="sucursalSeleccionado" :suggestions="arraySucursal" @complete="selectSucursal($event)" @item-select="getDatosSucursales" :dropdown="true" field="nombre" forceSelection :class="{ 'p-invalid': errores.sucursal }" placeholder="Buscar Sucursales..." />
                    <small v-if="errores.sucursal" class="p-error">{{ errores.sucursal }}</small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                    <label for="observaciones">Observaciones</label>
                    <Textarea id="observaciones" placeholder="Ej. Horario de funcionamiento, Capacidad de almacenamiento, etc." rows="3" v-model="datosFormulario.observaciones" />
                </div>
            </div>
            <template #footer>
                <Button label="Cerrar" icon="pi pi-times" class="p-button-sm p-button-danger " @click="cerrarModal" />
                <Button label="Guardar" icon="pi pi-check" class="p-button-sm p-button-success" @click="enviarFormulario()" v-if="tipoAccion == 1" />
                <Button label="Actualizar" icon="pi pi-check" class="p-button-primary p-button-warning" @click="enviarFormulario()" v-if="tipoAccion == 2" />
            </template>
        </Dialog>
    </main>
</template>

<script>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import AutoComplete from 'primevue/autocomplete';
import Textarea from 'primevue/textarea';
import { esquemaAlmacen } from "../../constants/validations";
import Swal from 'sweetalert2';

// Asegúrate de importar tu esquema de validación

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
    },
    components:{
        Button,
        DataTable,
        Column,
        Dialog,
        InputText,
        Dropdown,
        InputNumber,
        AutoComplete,
        Textarea
    },
    data() {
        return {
            datosFormulario: {
                nombre_almacen: "",
                ubicacion: "",
                encargado: -1,
                telefono: "",
                sucursal: -1,
                observaciones: "",
            },
            modal1: this.visible,
            errores: {},
            buscar: '',
            tipoAccion : 0,
            arraySucursal: [],
            modal: false,
            tituloModal: '',
            arrayUsuario: [],
            usuariosSeleccionados: [],
            sucursalSeleccionado: null,
            almacenSeleccionado: null,
            arrayAlmacen: [],
        };
    },
    methods: {
        closeDialog() {
            this.$emit('close');
        },
        buscarAlmacenes(){
            this.listarAlmacenes(1,this.buscar);
        },
        selectSucursal(event) {
            let me = this;

            if (!event.query.trim().length) {

                var url = "/sucursal/selectedSucursal/filter?filtro=";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arraySucursal = respuesta.sucursales;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
            else
            {
                this.loading = true;

                var url = "/sucursal/selectedSucursal/filter?filtro=" + me.sucursalSeleccionado;
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arraySucursal = respuesta.sucursales;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
                
            }
        },
        getDatosSucursales(val1) {
            console.log("Ejecucion de sucursales");
            if (this.tipoAccion == 2) {
                this.datosFormulario.sucursal =
                val1 && val1.id ? val1.id : this.datosFormulario.sucursal2;
                delete this.datosFormulario["sucursal2"];
            } else {
                this.datosFormulario.sucursal = val1 && val1.value.id ? val1.value.id : null;
            }
        },

        actualizarEncargados() {
            const val1 = this.usuariosSeleccionados;
            if (this.tipoAccion === 2) {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0
                        ? val1.map((v) => v.id).join(",")
                        : this.datosFormulario.encargado2;
                delete this.datosFormulario["encargado2"];
                console.log("form ",this.datosFormulario)

            } else {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0 ? val1.map((v) => v.id).join(",") : "";
                console.log("form ",this.datosFormulario)
            }
            console.log("val 1", val1)
            
        },
        getDatosUsuarios(event) {
            const val1 = event.value;
            if (this.tipoAccion === 2) {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0
                        ? val1.map((v) => v.id).join(",")
                        : this.datosFormulario.encargado2;
                delete this.datosFormulario["encargado2"];
            } else {
                this.datosFormulario.encargado =
                    val1 && val1.length > 0 ? val1.map((v) => v.id).join(",") : "";
            }
            console.log("val 1", val1)
            console.log("datos formulario", this.datosFormulario)
        },
        selectUsuario(event) {
            let me = this;

            if (!event.query.trim().length) {

                var url = "/user/selectUser/filter?idrol=3";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arrayUsuario = respuesta.usuarios;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
            else
            {
                this.loading = true;

                var url = "/user/selectUser/filter?filtro="+event.query.toLowerCase() + "&idrol=3";
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.arrayUsuario = respuesta.usuarios;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
                
            }
        },
        seleccionarAlmacen(data){
            this.almacenSeleccionado = data;
            this.$emit('almacen-seleccionado', this.almacenSeleccionado);
            this.$emit('close');
        },
        async validarCampo(campo) {
            try {
                console.log("formulario",this.datosFormulario)
                await esquemaAlmacen.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {
            await esquemaAlmacen.validate(this.datosFormulario, { abortEarly: false })
            .then(() => {
                // Verificar si el nombre del almacén está vacío
                if (!this.datosFormulario.nombre_almacen) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo vacío',
                    text: 'El nombre del almacén debe ser llenado.',
                })
                return
                }
                if (this.tipoAccion == 2) {
                this.actualizarAlmacen(this.datosFormulario)
                } else {
                this.registrarAlmacen(this.datosFormulario)
                }
            })
            .catch((error) => {
                console.log(error)
                const erroresValidacion = {}
                error.inner.forEach((e) => {
                erroresValidacion[e.path] = e.message
                })

                this.errores = erroresValidacion
            })
        },
        listarAlmacenes(page, buscar, criterio) {
            let me = this;
            var url =
                "/almacennewview?page=" + page + "&buscar=" + buscar + "&criterio=" + criterio;
            axios
                .get(url)
                .then(function (response) {
                let respuesta = response.data;
                me.arrayAlmacen = respuesta.almacenes;
                me.pagination = respuesta.pagination;
                console.log("Array de almacenes:", me.arrayAlmacen); // Verifica los datos en arrayAlmacen
                })
                .catch(function (error) {
                console.log(error);
                });
            },
        
        registrarAlmacen(data) {
            console.log("sucursal ",this.sucursalSeleccionado);
            console.log("usuarios ",this.usuariosSeleccionados);
            console.log("DATA ",data)
            let me = this;
            axios
                .post("/almacen/registrar", data)
                .then(function (response) {
                me.cerrarModal();
                me.listarAlmacenes(1, "", "nombre_almacen");
                me.usuariosSeleccionados = [];
                me.arrayUsuario = [];
                })
                .catch(function (error) {
                console.log(error);
                });
        },
        actualizarAlmacen(data) {
            let me = this;
            axios
                .put("/almacen/editar", data)
                .then(function (response) {
                me.cerrarModal();
                //console.log(response)
                me.listarAlmacenes(1, "", "nombre_almacen");
                })
                .catch(function (error) {
                console.log(error);
                });
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "almacenes": {
                    switch (accion) {
                        case "registrar": {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = "Registrar Almacen";
                            this.tipoAccion = 1;
                            this.datosFormulario = {
                                nombre_almacen: "",
                                ubicacion: "",
                                encargado: "",
                                telefono: "",
                                sucursal: "",
                                observaciones: "",
                            };
                            this.errores = {};
                            break;
                        }
                        case "actualizar": {
                            console.log("Datos almacen:", data);
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = "Actualizar Almacen";
                            this.tipoAccion = 2;
                            this.datosFormulario = {
                                id: data["id"],
                                nombre_almacen: data["nombre_almacen"],
                                ubicacion: data["ubicacion"],
                                encargado: data["encargado"],
                                telefono: data["telefono"],
                                sucursal: data["sucursal"],
                                sucursal2: data["sucursal"],
                                encargado2: data["encargado"],
                                observaciones:
                                data["observacion"] == null ? "" : data["observacion"],
                            };
                            this.sucursalSeleccionado = data["nombre_sucursal"];
                            this.usuarioSeleccionado = data["nombre_encargado"];

                            this.errores = {};

                            break;
                        }
                    }
                }
            }
        },
        cerrarModal() {
            this.modal = false;
            this.modal1 = true;
            this.tituloModal = '';
            this.errores = {};
            this.sucursalSeleccionado = "";
            this.usuarioSeleccionado = "";
            this.usuariosSeleccionados='';
        },
    },
    mounted(){
        this.listarAlmacenes(1,'');
    }
};
</script>

<style scoped>
.p-dialog-mask {
  z-index: 9999 !important;
}

.p-dialog {
  z-index: 10000 !important;
}

.form-group {
  margin-bottom: 15px;
}

>>>.p-dialog .p-dialog-content {
  padding-top: 10px;
  padding-bottom: 0px;
}

.toolbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

.search-bar {
  flex-grow: 0.5;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.search-bar .p-input-icon-left {
  width: 100%;
}

.search-bar .p-inputtext-sm {
  width: 100%;
}

.bold-input {
  font-weight: bold;
}

.p-fluid .p-field {
  margin-bottom: 1rem;
}
</style>
