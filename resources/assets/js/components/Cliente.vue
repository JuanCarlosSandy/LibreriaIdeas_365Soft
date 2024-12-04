<template>
    <main class="main">
        <!-- Tabla de Clientes -->
        <h5>Clientes</h5>
        <Card>
            <template #title>
                <div class="p-d-flex p-jc-between p-ai-center">
                   
                    <div>
                        <Button icon="pi pi-plus" label="Nuevo" class="p-button-secondary p-mr-2" @click="abrirModal('persona', 'registrar')" />
                        <Button icon="pi pi-file" label="Reporte" class="p-button-info p-mr-2" @click="cargarReporteExcel()" />
                        <Button icon="pi pi-upload" label="Importar" class="p-button-success" @click="abrirModalImportar()" />
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-grid p-mb-3">
                    <div class="p-col-12 p-md-6">
                        <div class="p-inputgroup">
                            <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label" optionValue="value" placeholder="Seleccionar criterio" class="p-mr-2" />
                            <InputText v-model="buscar" placeholder="Texto a buscar" @input="listarPersona(1, buscar, criterio)" />
                        </div>
                    </div>
                    <div v-if="rolUsuario == 1" class="p-col-12 p-md-6">
                        <label class="p-mb-2"><strong>Buscar Cliente por Usuario</strong></label>
                        <AutoComplete v-model="usuarioSeleccionadodos" :suggestions="arrayUsuarioFiltro" @complete="selectUsuarioFiltro" field="nombre" @item-select="getDatosUsuarioFiltro" placeholder="Buscar Usuario..." />
                    </div>
                </div>

                <DataTable :value="arrayPersona" class="p-datatable-gridlines p-datatable-sm" :paginator="true" :rows="10" :rowsPerPageOptions="[5,10,20]" responsiveLayout="scroll">
                    <Column v-if="rolUsuario == 1" header="Acciones">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('persona', 'actualizar', slotProps.data)" />
                        </template>
                    </Column>
                    <Column field="nombre" header="Nombres"></Column>
                    <Column field="tipo_documento" header="Tipo Documento">
                        <template #body="slotProps">
                            {{ getTipoDocumentoText(slotProps.data.tipo_documento) }}
                        </template>
                    </Column>
                    <Column field="num_documento" header="Número de documento"></Column>
                </DataTable>

                <Paginator :rows="10" :totalRecords="pagination.total" :first="pagination.current_page - 1" @page="onPageChange($event)"></Paginator>
            </template>
        </Card>

        <!-- Modal para agregar/actualizar -->
        <Dialog :visible.sync="modal" :containerStyle="{width: '50vw'}":modal="true">
            <template #header>
                <h3>{{ tituloModal }}</h3>
            </template>

            <form @submit.prevent="enviarFormulario">
                <div class="p-fluid p-formgrid p-grid">
                    <div class="p-field p-col-12">
                        <label for="nombre">Nombre del cliente <span class="p-error">*</span></label>
                        <InputText id="nombre" v-model="datosFormulario.nombre" :class="{'p-invalid': errores.nombre}" @input="validarCampo('nombre')" />
                        <small class="p-error" v-if="errores.nombre">{{ errores.nombre }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="tipo_documento">Tipo de documento <span class="p-error">*</span></label>
                        <Dropdown id="tipo_documento" v-model="datosFormulario.tipo_documento" :options="tipoDocumentoOptions" optionLabel="label" optionValue="value" placeholder="Selecciona un tipo de documento" :class="{'p-invalid': errores.tipo_documento}" @change="validarCampo('tipo_documento')" />
                        <small class="p-error" v-if="errores.tipo_documento">{{ errores.tipo_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-3">
                        <label :for="'num_documento_' + datosFormulario.tipo_documento">N° {{ getTipoDocumentoLabel() }} <span class="p-error">*</span></label>
                        <InputText :id="'num_documento_' + datosFormulario.tipo_documento" v-model="datosFormulario.num_documento" :class="{'p-invalid': errores.num_documento}" @input="validarCampo('num_documento')" />
                        <small class="p-error" v-if="errores.num_documento">{{ errores.num_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-3">
                        <label for="complemento">Complemento</label>
                        <div class="p-inputgroup">
                            <InputText id="complemento" v-model="datosFormulario.complemento" :disabled="!mostrarComplemento" />
                            <Button icon="pi pi-check" @click="mostrarComplemento = !mostrarComplemento" />
                        </div>
                    </div>
                </div>

                <div v-if="tipoAccion == 2" class="p-mb-3">
                    <Message severity="info">Este cliente fue creado por: <strong>{{ usuarioSeleccionado }}</strong>.</Message>
                </div>

                <div v-if="activaredit && rolUsuario == 1" class="p-field">
                    <label for="user">Usuario <span class="p-error">*</span></label>
                    <AutoComplete id="user" v-model="usuarioSeleccionado" :suggestions="arrayUsuarioV" @complete="selectUsuario" field="nombre" @item-select="getDatosUsuario" placeholder="Buscar Usuario..." />
                </div>
            </form>

            <template #footer>
                <Button label="Cerrar" icon="pi pi-times" class="p-button-text p-button-danger" @click="cerrarModal" />
                <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" class="p-button-text p-button-success" @click="enviarFormulario" />
                <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check" class="p-button-text p-button-success" @click="enviarFormulario" />
            </template>
        </Dialog>

        <Dialog v-if="modalImportar" :visible.sync="modalImportar" :modal="true">
            <ImportarExcelCliente @cerrar="cerrarModalImportar"></ImportarExcelCliente>
        </Dialog>
    </main>
</template>

<script>
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import AutoComplete from 'primevue/autocomplete';
import Dialog from 'primevue/dialog';
import Paginator from 'primevue/paginator';
import Message from 'primevue/message';
import { esquemaCliente } from '../constants/validations';

export default {
    components: {
        Card,
        Button,
        DataTable,
        Column,
        InputText,
        Dropdown,
        AutoComplete,
        Dialog,
        Paginator,
        Message
    },
    data() {
        return {
            tipoDocumentoOptions: [
                {label: 'CI - CEDULA DE IDENTIDAD', value: '1'},
                {label: 'CEX - CEDULA DE IDENTIDAD DE EXTRANJERO', value: '2'},
                {label: 'NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA', value: '5'},
                {label: 'PAS - PASAPORTE', value: '3'},
                {label: 'OD - OTRO DOCUMENTO DE IDENTIDAD', value: '4'}
            ],
            criterioOptions: [
                {label: 'Nombre', value: 'nombre'},
                {label: 'Documento', value: 'num_documento'},
                {label: 'Email', value: 'email'},
                {label: 'Teléfono', value: 'telefono'}
            ],
            datosFormulario: {
                nombre: '',
                tipo_documento: '',
                num_documento: '',
                complemento: '',
            },
            errores: {},
            rolUsuario: '',
            arrayUsuarioV: [],
            usuarioSeleccionado: '',
            idusuario: '',
            arrayDetalleUsuario: [],
            activaredit: false,
            arrayUsuarioFiltro: [],
            usuarioSeleccionadodos: '',
            usuariodos_id: '',
            role_id: '',
            mostrarComplemento: false,
            modalImportar: false,
            arrayPersona: [],
            modal: false,
            tituloModal: '',
            tipoAccion: 0,
            errorPersona: 0,
            errorMostrarMsjPersona: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            criterio: 'nombre',
            buscar: ''
        }
    },
    methods: {
        getTipoDocumentoLabel() {
            switch(this.datosFormulario.tipo_documento) {
                case '1': return 'CI';
                case '2': return 'CEX';
                case '3': return 'Pasaporte';
                case '5': return 'NIT';
                default: return 'Documento';
            }
        },
        onPageChange(event) {
            this.cambiarPagina(event.page + 1, this.buscar, this.criterio);
        },
        async validarCampo(campo) {
            try {
                await esquemaCliente.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {
            try {
                await esquemaCliente.validate(this.datosFormulario, { abortEarly: false });
                if (this.tipoAccion == 1) {
                    this.registrarPersona(this.datosFormulario);
                } else {
                    this.datosFormulario.usuariodos_id = this.idusuario;
                    this.actualizarPersona(this.datosFormulario);
                }
                this.mostrarComplemento = false;
            } catch (error) {
                const erroresValidacion = {};
                error.inner.forEach((e) => {
                    erroresValidacion[e.path] = e.message;
                });
                this.errores = erroresValidacion;
            }
        },
        listarPersona(page, buscar, criterio) {
            let url = `/cliente?page=${page}&buscar=${buscar}&criterio=${criterio}&usuarioid=${this.usuariodos_id}`;
            axios.get(url).then(response => {
                let respuesta = response.data;
                this.arrayPersona = respuesta.usuarios.data;
                this.pagination = respuesta.pagination;
            }).catch(error => {
                console.log(error);
            });
        },
        selectUsuarioFiltro(event) {
            let url = '/cliente/usuario/filtro?filtro=' + event.query;
            axios.get(url).then(response => {
                this.arrayUsuarioFiltro = response.data.usuariodos;
            }).catch(error => {
                console.log(error);
            });
        },
        getDatosUsuarioFiltro(event) {
            if (event.value && event.value.iduse) {
                this.usuariodos_id = event.value.iduse;
                this.usuarioSeleccionadodos = event.value.nombre;
                this.listarPersona(1, this.buscar, this.criterio);
            }
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarPersona(page, buscar, criterio);
        },
        registrarPersona(datos) {
            axios.post('/cliente/registrar', datos).then(() => {
                this.cerrarModal();
                this.listarPersona(1, '', 'nombre');
            }).catch(error => {
                console.log(error);
            });
        },
        actualizarPersona(datos) {
            axios.put('/cliente/actualizar', datos).then(() => {
                this.cerrarModal();
                this.listarPersona(1, '', 'nombre');
            }).catch(error => {
                console.log(error);
            });
        },
        cerrarModal() {
            this.modal = false;
            this.tituloModal = '';
            this.errorPersona = 0;
            this.activaredit = false;
            this.idusuario = '';
        },
        abrirModal(modelo, accion, data = []) {
            if (modelo === "persona") {
                this.modal = true;
                if (accion === 'registrar') {
                    this.tituloModal = 'Registrar Cliente';
                    this.tipoAccion = 1;
                    this.datosFormulario = {
                        nombre: '',
                        tipo_documento: '',
                        num_documento: '',
                        complemento: '',
                        direccion: '',
                        telefono: '',
                        email: ''
                    };
                } else if (accion === 'actualizar') {
                    this.tituloModal = 'Actualizar Cliente';
                    this.tipoAccion = 2;
                    this.datosFormulario = { ...data, usuariodos_id: '' };
                    this.activaredit = true;
                    this.verUsuario(data);
                }
            }
        },
        cargarReporteExcel() {
            window.open('/cliente/listarReporteClienteExcel', '_blank');
        },
        getTipoDocumentoText(value) {
            const tipos = {
                '1': 'CI',
                '2': 'CEX',
                '4': 'NIT',
                '3': 'PAS'
            };
            return tipos[value] || '';
        },
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
        },

        selectUsuario(search, loading) {
            let me = this;
            loading(true)
            let url = '/cliente/selectUusarioVend?filtro=' + search;
            axios.get(url).then(function (response) {
                //console.log(response.clientes);
                let respuesta = response.data;
                q: search
                me.arrayUsuarioV = respuesta.clientes;
                loading(false)
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        getDatosUsuario(val1) {
            let me = this;
            me.loading = true;

            if (val1 && val1.ID_use) {
                me.idusuario = val1.ID_use;
                me.usuarioSeleccionado = val1.nombre;
            }
        },
        verUsuario(data) {
            this.idusuario = data.usuario;
            let me = this;
            let url = '/cliente/usuario?idusuario=' + this.idusuario;
            axios.get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayDetalleUsuario = respuesta.usuario;
                    me.usuarioSeleccionado = me.arrayDetalleUsuario[0].nombre;

                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarDatosuser() {
            axios.get('/user-info')
                .then(response => {
                    const userData = response.data.user;
                    this.usuariodos_id = userData.iduse;
                    this.role_id = userData.idrol;
                    if (this.role_id == 1) {
                        this.usuariodos_id = '';
                        this.listarPersona(1, this.buscar, this.criterio);
                    } else {
                        this.listarPersona(1, this.buscar, this.criterio);
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la información del usuario:', error);
                });
        },

        abrirModalImportar() {
            this.modalImportar = 1;
        },
        cerrarModalImportar() {
            this.modalImportar = 0;
            this.listarPersona(1, '', 'nombre');
        },
    },

    mounted() {
        this.listarDatosuser();
        this.recuperarIdRol();
    },
}
</script>
