<template>
  <div class="main">
      <Panel>
        <Toast :breakpoints="{ '900px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
                </Toast>
                <template #header>
                    <div class="panel-header">
                        <h4 class="panel-icon">Provedores</h4>
                    </div>
                </template> 
                  <div>
                      <Button icon="pi pi-plus" label="Nuevo" class="p-button-secondary mr-2" @click="abrirModal('persona', 'registrar')" />
                      <Button icon="pi pi-upload" label="Importar" class="p-button-success" @click="abrirModalImportar()" />
                  </div>


          <template>
              <div class="p-fluid p-formgrid p-grid">
                  <div class="p-field p-col-8 p-md-3">
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
                          <Button icon="pi pi-trash" class="p-button-danger p-button-sm" @click="confirmarEliminar(slotProps.data)" />
                        </template>
                  </Column>
                  <Column field="nombre" header="Nombre_proveedor"></Column>
                  <Column field="direccion" header="Dirección"></Column>
                  <Column field="telefono_contacto" header="Teléfono"></Column>
                  <Column field="contacto" header="Contacto"></Column>
              </DataTable>
          </template>
      </Panel>

      <Dialog :visible.sync="modal" :containerStyle="{width: '700px'}" modal :closable="false">
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
              <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal" />
              <Button v-if="tipoAccion === 1" label="Guardar" icon="pi pi-check" class="p-button-success" @click="enviarFormulario" />
              <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check"  class="p-button-success" @click="enviarFormulario" />
          </template>
      </Dialog>

      <div v-if="modalImportar">
          <ImportarExcelProvedores @cerrar="cerrarModalImportar" />
      </div>
  </div>
</template>

<script>
import { esquemaProveedor } from '../constants/validations';

import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Swal from 'sweetalert2';
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
export default {
components: {
  Card,
  Button,
  InputText,
  DataTable,
  Column,
  Dialog,
  Dropdown,
  InputNumber,
  Panel,
  Toast,
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

  confirmarEliminar(proveedor) {
          Swal.fire({
              title: '¿Está seguro?',
              text: "¿Desea eliminar este proveedor?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Sí, eliminar',
              cancelButtonText: 'Cancelar'
          }).then((result) => {
              if (result.isConfirmed) {
                  this.eliminarProveedor(proveedor.id);
              }
          });
      },
      
      eliminarProveedor(id) {
          axios.delete(`/proveedor/eliminar/${id}`)
              .then(response => {
                  Swal.fire(
                      'Eliminado',
                      'El proveedor ha sido eliminado.',
                      'success'
                  );
                  this.listarPersona(this.pagination.current_page, this.buscar, this.criterio);
              })
              .catch(error => {
                  console.error('Error al eliminar proveedor:', error);
                  Swal.fire(
                      'Error',
                      'No se pudo eliminar el proveedor.',
                      'error'
                  );
              });
      },
  
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
    axios.post('/proveedor/registrar', data)
        .then(function (response) {
            console.log(response.data.message); // Mostrar el mensaje del servidor en la consola
            me.cerrarModal();
            me.listarPersona(1, '', 'nombre');
        })
        .catch(function (error) {
            console.error(error.response.data.message); // Mostrar el error del servidor en la consola
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
<style scoped>
>>> .p-panel-header {
    padding: 0.75rem;
}
.panel-header {
    display: flex;
    align-items: center;
}

.panel-icon {
    font-size: 2rem;
    padding-left: 10px;
}

.panel-icon {
    font-size: 1.5rem;
    margin: 0;
}

</style>


