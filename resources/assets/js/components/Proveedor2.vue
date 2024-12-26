<template>
    <div class="main">
        <Card>
            <template #header>
                <div class="flex justify-content-between align-items-center">
                    <h5>Proveedores</h5>
                    <div>
                        <Button icon="pi pi-plus" label="Nuevo" class="p-button-secondary mr-2" @click="abrirModal('persona', 'registrar')" />
                        <Button icon="pi pi-upload" label="Importar" class="p-button-success" @click="abrirModalImportar()" />
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-fluid p-formgrid p-grid">
                    <div class="p-field p-col-12 p-md-6">
                        <span class="p-input-icon-left">
                            <i class="pi pi-search" />
                            <InputText v-model="buscar" placeholder="Texto a buscar" @input="buscarProveedores" />
                        </span>
                    </div>
                </div>

                <DataTable :value="arrayPersona" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                           :totalRecords="pagination.total" :lazy="true" @page="onPageChange" responsiveLayout="scroll">
                    <Column header="Opciones">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('persona', 'actualizar', slotProps.data)" />
                        </template>
                    </Column>
                    <Column field="nombre" header="Nombre_proveedor"></Column>
                    <Column field="tipo_documento" header="Tipo Documento">
                        <template #body="slotProps">
                            {{ getTipoDocumentoText(slotProps.data.tipo_documento) }}
                        </template>
                    </Column>
                    <Column field="num_documento" header="NIT/CI"></Column>
                    <Column field="direccion" header="Dirección"></Column>
                    <Column field="telefono" header="Teléfono"></Column>
                    <Column field="email" header="Email"></Column>
                    <Column field="contacto" header="Contacto"></Column>
                </DataTable>
            </template>
        </Card>

        <Dialog :visible.sync="modal" :style="{width: '700px'}" modal :closable="false">
            <template #header>
                <h3>{{ tituloModal }}</h3>
            </template>

            <form @submit.prevent="enviarFormulario">
                <div class="p-fluid p-formgrid p-grid">
                    <div class="p-field p-col-12 p-md-6">
                        <label for="nombre">Nombre del proveedor</label>
                        <InputText id="nombre" v-model="datosFormulario.nombre" :class="{'p-invalid': errores.nombre}" @input="validarCampo('nombre')" />
                        <small class="p-error" v-if="errores.nombre">{{ errores.nombre }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="direccion">Dirección</label>
                        <InputText id="direccion" v-model="datosFormulario.direccion" :class="{'p-invalid': errores.direccion}" @input="validarCampo('direccion')" />
                        <small class="p-error" v-if="errores.direccion">{{ errores.direccion }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="tipo_documento">Tipo de documento</label>
                        <Dropdown id="tipo_documento" v-model="datosFormulario.tipo_documento" :options="tiposDocumentos" optionLabel="etiqueta" optionValue="valor" placeholder="Selecciona un tipo de documento" :class="{'p-invalid': errores.tipo_documento}" @change="validarCampo('tipo_documento')" />
                        <small class="p-error" v-if="errores.tipo_documento">{{ errores.tipo_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="num_documento">Nro de documento</label>
                        <InputText id="num_documento" v-model="datosFormulario.num_documento" :class="{'p-invalid': errores.num_documento}" @input="validarCampo('num_documento')" />
                        <small class="p-error" v-if="errores.num_documento">{{ errores.num_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="email">Correo electrónico</label>
                        <InputText id="email" v-model="datosFormulario.email" :class="{'p-invalid': errores.email}" @input="validarCampo('email')" />
                        <small class="p-error" v-if="errores.email">{{ errores.email }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="telefono">Teléfono</label>
                        <InputNumber id="telefono" v-model="datosFormulario.telefono" :class="{'p-invalid': errores.telefono}" @input="validarCampo('telefono')" />
                        <small class="p-error" v-if="errores.telefono">{{ errores.telefono }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="contacto">Contacto</label>
                        <InputText id="contacto" v-model="datosFormulario.contacto" :class="{'p-invalid': errores.contacto}" @input="validarCampo('contacto')" />
                        <small class="p-error" v-if="errores.contacto">{{ errores.contacto }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="telefono_contacto">Teléfono de contacto</label>
                        <InputNumber id="telefono_contacto" v-model="datosFormulario.telefono_contacto" :class="{'p-invalid': errores.telefono_contacto}" @input="validarCampo('telefono_contacto')" />
                        <small class="p-error" v-if="errores.telefono_contacto">{{ errores.telefono_contacto }}</small>
                    </div>
                </div>
            </form>

            <template #footer>
                <Button label="Cerrar" icon="pi pi-times" class="p-button-text" @click="cerrarModal" />
                <Button v-if="tipoAccion === 1" label="Guardar" icon="pi pi-check" class="p-button-text" @click="enviarFormulario" />
                <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check" class="p-button-text" @click="enviarFormulario" />
            </template>
        </Dialog>

        <div v-if="modalImportar">
            <ImportarExcelProvedores @cerrar="cerrarModalImportar" />
        </div>
    </div>
</template>

<script>
import { esquemaProveedor } from '../constants/validations';

import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';

export default {
  components: {
    Card,
    Button,
    InputText,
    DataTable,
    Column,
    Dialog,
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
      errores: {},
      tiposDocumentos: [
        { valor: "1", etiqueta: "CI - CEDULA DE IDENTIDAD" },
        { valor: "2", etiqueta: "CEX - CEDULA DE IDENTIDAD DE EXTRANJERO" },
        { valor: "5", etiqueta: "NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA" },
        { valor: "3", etiqueta: "PAS - PASAPORTE" },
        { valor: "4", etiqueta: "OD - OTRO DOCUMENTO DE IDENTIDAD" }
      ],
      persona_id: 0,
      arrayPersona: [],
      modal: false,
      tituloModal: '',
      tipoAccion: 0,
      errorPersona: 0,
      errorMostrarMsjPersona: [],
      pagination: {
        total: 0,
        current_page: 1,
        last_page: 0
      },
      offset: 3,
      criterio: 'todos',
      buscar: '',
      modalImportar: false,
      
    }
  },
  computed: {
    isActived() {
      return this.pagination.current_page;
    },
    pagesNumber() {
      let from = this.pagination.current_page - 2;
      if (from < 1) {
        from = 1;
      }
      let to = from + 4;
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page;
      }
      let pagesArray = [];
      for (let page = from; page <= to; page++) {
        pagesArray.push(page);
      }
      return pagesArray;
    },
  },
  methods: {
    onPageChange(event) {
      let page = event.page + 1; // PrimeVue pages are 0-based, while your logic uses 1-based
      this.cambiarPagina(page, this.buscar, this.criterio);
    },
    cambiarPagina(page, buscar, criterio) {
      this.pagination.current_page = page;
      this.listarPersona(page, buscar, criterio);
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
    getTipoDocumentoText(value) {
      const documento = this.tiposDocumentos.find(doc => doc.valor === value);
      return documento ? documento.etiqueta : '';
    },
    abrirModalImportar() {
      this.modalImportar = true;
    },
    cerrarModalImportar() {
      this.modalImportar = false;
      this.listarPersona(1, '', 'nombre');
    },
    listarPersona(page, buscar, criterio) {
      let me = this;
      var url = `/proveedor?page=${page}&buscar=${buscar}&criterio=${criterio}`;
      axios.get(url)
        .then(function (response) {
          var respuesta = response.data;
          me.arrayPersona = respuesta.personas.data;
          me.pagination = respuesta.pagination;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    buscarProveedores() {
      this.listarPersona(1, this.buscar, this.criterio);
    },
    registrarPersona(data) {
      let me = this;
      axios.post('/proveedor/registrar', data).then(function (response) {
        me.cerrarModal();
        me.listarPersona(1, '', 'nombre');
      }).catch(function (error) {
        console.log(error);
      });
    },
    actualizarPersona(data) {
      let me = this;
      axios.put('/proveedor/actualizar', data).then(function (response) {
        me.cerrarModal();
        me.listarPersona(1, '', 'nombre');
      }).catch(function (error) {
        console.log(error);
      });
    },
    cerrarModal() {
      this.modal = false;
      this.tituloModal = '';
    },
    abrirModal(modelo, accion, data = []) {
      switch (modelo) {
        case "persona":
          switch (accion) {
            case 'registrar':
              this.modal = true;
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
            case 'actualizar':
              this.modal = true;
              this.tituloModal = 'Actualizar Proveedor';
              this.tipoAccion = 2;
              this.datosFormulario = data;
              this.persona_id = data['id'];
              break;
          }
          break;
      }
    }
  },
  mounted() {
    this.listarPersona(1, this.buscar, this.criterio);
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
