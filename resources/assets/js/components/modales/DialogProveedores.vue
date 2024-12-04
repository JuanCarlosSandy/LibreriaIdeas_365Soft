<template>
    <main class="main">
        <Dialog header="Proveedores" :visible.sync="modal1" :modal="true" :containerStyle="{width: '700px', height: '650px'}" :closable="false" :closeOnEscape="false">
                <div class="toolbar-container">
                    <div class="toolbar">
                        <Button label="Nuevo" icon="pi pi-plus" @click="abrirModal('persona', 'registrar')" class="p-button-secondary p-button-sm"/>
                    </div>
                    <div class="search-bar">
                        <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText type="text" placeholder="Texto a buscar" v-model="buscar" class="p-inputtext-sm" @keyup="buscarProveedor"  />
                    </span>
                    </div>
                </div>
                <DataTable class="p-datatable-sm p-datatable-gridlines" :value="arrayProveedor" :paginator="true" responsiveLayout="scroll" :rows="5">
                    <Column field="opciones" header="Opciones">
                        <template #body="slotProps">
                            <Button  icon="pi pi-check" class="p-button-sm p-button-success custom-icon-size" @click="seleccionarProveedor(slotProps.data)"/>
                            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm "  @click="abrirModal('persona', 'actualizar', slotProps.data)" />
                            
                        </template>
                    </Column>
                    <Column field="id" header="Id"/>
                    <Column field="nombre" header="Nombre"/>
                    <Column field="num_documento" header="Nit"/>
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
            <form @submit.prevent="enviarFormulario">
            <div class="p-fluid p-formgrid p-grid">
                <div class="p-field p-col-12 p-md-6">
                <label for="name">Nombre del proveedor *</label>
                <InputText id="name" v-model="datosFormulario.nombre" @input="validarCampo('nombre')" :class="{'p-invalid': errores.nombre}" required />
                <small class="p-error" v-if="errores.nombre"><strong>{{ errores.nombre }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="address">Dirección *</label>
                <InputText id="address" v-model="datosFormulario.direccion" @input="validarCampo('direccion')" :class="{'p-invalid': errores.direccion}" required />
                <small class="p-error" v-if="errores.direccion"><strong>{{ errores.direccion }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="documentType">Tipo de documento *</label>
                <Dropdown id="documentType" v-model="datosFormulario.tipo_documento" :options="tiposDocumentos" optionLabel="etiqueta" optionValue="valor" @change="validarCampo('tipo_documento')" :class="{'p-invalid': errores.tipo_documento}" required />
                <small class="p-error" v-if="errores.tipo_documento"><strong>{{ errores.tipo_documento }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="documentNumber">Nro de documento *</label>
                <InputText id="documentNumber" v-model="datosFormulario.num_documento" @input="validarCampo('num_documento')" :class="{'p-invalid': errores.num_documento}" required />
                <small class="p-error" v-if="errores.num_documento"><strong>{{ errores.num_documento }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="email">Correo electrónico *</label>
                <InputText id="email" v-model="datosFormulario.email" @input="validarCampo('email')" :class="{'p-invalid': errores.email}" required />
                <small class="p-error" v-if="errores.email"><strong>{{ errores.email }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="phone">Teléfono *</label>
                <InputNumber :useGrouping="false" id="phone" v-model="datosFormulario.telefono" @input="validarCampo('telefono')" :class="{'p-invalid': errores.telefono}" required />
                <small class="p-error" v-if="errores.telefono"><strong>{{ errores.telefono }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="contact">Contacto *</label>
                <InputText id="contact" v-model="datosFormulario.contacto" @input="validarCampo('contacto')" :class="{'p-invalid': errores.contacto}" required />
                <small class="p-error" v-if="errores.contacto"><strong>{{ errores.contacto }}</strong></small>
                </div>
                <div class="p-field p-col-12 p-md-6">
                <label for="contactPhone">Teléfono de contacto *</label>
                <InputNumber :useGrouping="false" id="contactPhone" v-model="datosFormulario.telefono_contacto" @input="validarCampo('telefono_contacto')" :class="{'p-invalid': errores.telefono_contacto}" required />
                <small class="p-error" v-if="errores.telefono_contacto"><strong>{{ errores.telefono_contacto }}</strong></small>
                </div>
            </div>
            </form>
            <template #footer>
            <Button label="Cerrar" icon="pi pi-times" class="p-button-danger p-button-sm" @click="cerrarModal" />
            <Button v-if="tipoAccion == 1" class="p-button-success p-button-sm" label="Guardar" icon="pi pi-check" @click="enviarFormulario" />
            <Button v-if="tipoAccion == 2" class="p-button-warning p-button-sm" label="Actualizar" icon="pi pi-check" @click="enviarFormulario" />
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
import { esquemaProveedor } from '../../constants/validations'; // Asegúrate de importar tu esquema de validación

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
        InputNumber
    },
    data() {
        return {
            datosFormulario: {
                nombre: '',
                tipo_documento: '',
                num_documento: '',
                direccion: '',
                telefono: '',
                email: '',
                contacto: '',
                telefono_contacto: ''
            },
            modal1: this.visible,
            errores: {},
            buscar: '',
            tipoAccion : 0,
            arrayProveedor:[],
            modal: false,
            tituloModal: '',
            documentoSeleccionado: null,
            proveedorSeleccionado: null,
            tiposDocumentos: [
                { valor: "1", etiqueta: "CI - CEDULA DE IDENTIDAD" },
                { valor: "2", etiqueta: "CEX - CEDULA DE IDENTIDAD EXTRANJERO" },
                { valor: "5", etiqueta: "NIT - NÚMERO IDENTIFICACIÓN TRIBUTARIA" },
                { valor: "3", etiqueta: "PAS - PASAPORTE" },
                { valor: "4", etiqueta: "OD - OTRO DOCUMENTO DE IDENTIDAD" }
            ],
        };
    },
    methods: {
        closeDialog() {
            this.$emit('close');
        },
        buscarProveedor(){
            this.listarproveedor(this.buscar);
        },
        seleccionarProveedor(data){
            this.proveedorSeleccionado = data;
            this.$emit('proveedor-seleccionado', this.proveedorSeleccionado);
            this.$emit('close');
        },
        async validarCampo(campo) {
            try {
                await esquemaProveedor.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {
            await esquemaProveedor.validate(this.datosFormulario, { abortEarly: false })
                .then(() => {
                    console.log("ENVIO ",this.datosFormulario)
                    if (this.tipoAccion == 2) {
                        
                        this.actualizarPersona(this.datosFormulario);
                    } else {
                        this.registrarPersona(this.datosFormulario);
                    }
                })
                .catch((error) => {
                    const erroresValidacion = {};
                    error.inner.forEach((e) => {
                        erroresValidacion[e.path] = e.message;
                    });

                    this.errores = erroresValidacion;
                });
        },
        listarproveedor(buscar) {
            let me = this;
            var url = '/proveedornewview?buscar=' + buscar;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayProveedor = respuesta.personas;
                me.pagination = respuesta.pagination;
            }).catch(function (error) {
                console.log(error);
            });
        },
        registrarPersona(data) {
            let me = this;
            axios.post('/proveedor/registrar', data).then(function (response) {
                me.cerrarModal();
                me.listarproveedor('');
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarPersona(data) {
            let me = this;

            axios.put('/proveedor/actualizar', data).then(function (response) {
                me.cerrarModal();
                me.listarproveedor('');
            }).catch(function (error) {
                console.log(error);
            });
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "persona":
                    {
                        switch (accion) {
                            case 'registrar':
                                {
                                    
                                    
                                    this.modal1 = false;
                                    this.modal = true;
                                    console.log("modal nuevo",this.modal)
                                    console.log("modal tabla",this.modal1)
                                    this.tituloModal = 'Registrar Proveedor';
                                    this.tipoAccion = 1;
                                    this.datosFormulario = {
                                        nombre: '',
                                        tipo_documento: '',
                                        num_documento: '',
                                        direccion: '',
                                        telefono: '',
                                        email: '',
                                        contacto: '',
                                        telefono_contacto: ''
                                    };
                                    break;
                                }
                            case 'actualizar':
                                {   this.modal1 = false;
                                    this.modal = true;
                                    console.log("modal nuevo",this.modal)
                                    console.log("modal tabla",this.modal1)
                                    this.tituloModal = 'Actualizar Proveedor';
                                    this.tipoAccion = 2;
                                    this.datosFormulario = data;
                                    this.persona_id = data['id'];
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
        },
    },
    mounted(){
        this.listarproveedor('');
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
>>>.p-dialog .p-dialog-content{
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
