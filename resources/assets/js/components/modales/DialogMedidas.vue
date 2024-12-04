<template>
    <main class="main">
      <Dialog
        header="Medidas"
        :visible.sync="modal1"
        :modal="true"
        :containerStyle="{ width: '700px' }"
        :closable="false" 
        :closeOnEscape="false"
      >
        <div class="toolbar-container">
          <div class="search-bar">
            <span class="p-input-icon-left">
              <i class="pi pi-search" />
              <InputText
                id="search"
                type="text"
                placeholder="Texto a buscar"
                class="p-inputtext-sm"
                v-model="buscar"
                @keyup="buscarMedida"
              />
            </span>
          </div>
        </div>
        <DataTable
          class="p-datatable-sm p-datatable-gridlines"
          :value="arrayMedida"
          :paginator="true"
          responsiveLayout="scroll"
          :rows="5"
        >
          <Column field="opciones" header="Opciones">
            <template #body="slotProps">
              <Button
                icon="pi pi-check"
                class="p-button-sm p-button-success"
                @click="seleccionarMedida(slotProps.data)"
              />
              <Button
                icon="pi pi-pencil"
                class="p-button-warning p-button-sm"
                @click="abrirModal('medida', 'actualizar', slotProps.data)"
              />
            </template>
          </Column>
          <Column field="descripcion_medida" header="Descripcion" />
          <Column field="codigoClasificador" header="Código Clasificador" />
          <Column field="estado" header="Estado">
            <template #body="slotProps">
              <span :class="['status-badge', slotProps.data.estado === 1 ? 'active' : 'inactive']">
                {{ slotProps.data.estado === 1 ? 'Activo' : 'Inactivo' }}
              </span>
            </template>
          </Column>
        </DataTable>
        <template #footer>
          <Button
            label="Cerrar"
            icon="pi pi-times"
            class="p-button-danger p-button-sm"
             @click="closeDialog()"
          />
        </template>
      </Dialog>
      <Dialog :visible.sync="modal" modal :header="tituloModal" :containerStyle="{ width: '700px' }"  :closable="false" :closeOnEscape="false">
          <template #footer>
            <Button label="Cancelar" icon="pi pi-times" class="p-button-text" @click="cerrarModal()" />
            <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check" class="p-button-sm p-button-warning" @click="actualizarMedida()" />
          </template>
          <div class="p-fluid">
            <div class="p-field input-container">
              <label for="descripcionMedida">Descripción</label>
              <InputText 
                id="descripcionMedida" 
                v-model="descripcionMedida" 
                required 
                autofocus 
                :class="{'p-invalid': descripcionMedidaError}" 
                @input="validarDescripcionEnTiempoReal"
              />
              <small class="p-error error-message" v-if="descripcionMedidaError"><strong>{{ descripcionMedidaError }}</strong></small>
            </div>
            <div class="p-field input-container">
              <label for="codigoClasificador">Código Clasificador</label>
              <InputText 
                id="codigoClasificador" 
                v-model="codigoClasificador" 
                required 
                :class="{'p-invalid': codigoClasificadorError}" 
                @input="validarCodigoEnTiempoReal"
              />
              <small class="p-error error-message" v-if="codigoClasificadorError"><strong>{{ codigoClasificadorError }}</strong></small>
            </div>
          </div>
        </Dialog>
    </main>
  </template>
  
<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
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

    },
    data() {
        return {
            modal1: this.visible,
            modal: false,
            arrayCategoria: [],
            medida_id: 0,
            descripcionMedida: '',
            codigoClasificador: '',
            tipoAccion: 0,
            arrayMedida: [],
            criterio: 'descripcion_medida',
            buscar: '',
            tituloModal:'',
            descripcionMedidaError: '',
            codigoClasificadorError: '',
            medidaSeleccionado: null
        };
    },
    methods: {
        closeDialog() {
            this.$emit('close');
        },
        validarDescripcionEnTiempoReal() {
            if (!this.descripcionMedida.trim()) {
            this.descripcionMedidaError = "La descripción de la medida no puede estar vacía.";
            } else {
            this.descripcionMedidaError = '';
            }
        },
        validarCodigoEnTiempoReal() {
            if (!this.codigoClasificador.trim()) {
            this.codigoClasificadorError = "El código clasificador no puede estar vacío.";
            } else {
            this.codigoClasificadorError = '';
            }
        },
        listarMedidas(page, buscar, criterio) {
            let me = this;
            var url = '/medida2?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios
            .get(url)
            .then(function (response) {
                var respuesta = response.data;
                me.arrayMedida = respuesta.medidas;
                me.paginationMedida = respuesta.paginationMedida;
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        actualizarMedida() {
            if (this.validarMedida()) {
            return;
            }

            let me = this;
            axios
            .put('/medida/actualizar', {
                descripcion_medida: this.descripcionMedida,
                codigoClasificador: this.codigoClasificador,
                id: this.medida_id,
            })
            .then(function (response) {
                me.cerrarModal();
                me.listarMedidas(1, '', 'descripcion_medida');
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        validarMedida() {
            this.descripcionMedidaError = '';
            this.codigoClasificadorError = '';
            let hasError = false;

            if (!this.descripcionMedida.trim()) {
            this.descripcionMedidaError = "La descripción de la medida no puede estar vacía.";
            hasError = true;
            }

            if (!this.codigoClasificador.trim()) {
            this.codigoClasificadorError = "El código clasificador no puede estar vacío.";
            hasError = true;
            }

            return hasError;
        },
        seleccionarMedida(data){
            if (data.estado == 0) {
                swal({
                    title: 'Marca Inactiva',
                    text: 'No puede seleccionar esta opción porque está inactiva.',
                    type: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    buttonsStyling: false,
                })
            } else {
                this.medidaSeleccionado = data;
                this.$emit('medida-seleccionado', this.medidaSeleccionado);
                this.closeDialog();
            }
            
        },
        buscarMedida() {
            this.listarMedidas(1, this.buscar, this.criterio);
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case 'medida': {
                    switch (accion) {
                        case 'registrar': {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = 'Registrar Medida';
                            this.descripcionMedida = '';
                            this.codigoClasificador = '';
                            this.tipoAccion = 1;
                            break;
                        }
                        case 'actualizar': {
                            this.modal = true;
                            this.modal1 = false;
                            this.tituloModal = 'Actualizar medida';
                            this.tipoAccion = 2;
                            this.medida_id = data['id'];
                            this.descripcionMedida = data['descripcion_medida'];
                            this.codigoClasificador = data['codigoClasificador'];
                            break;
                        }
                    }
                }
            }
        },
        cerrarModal() {
            this.modal = false;
            this.modal1 =true;
            this.tituloModal = '';
            this.descripcionMedida = '';
            this.codigoClasificador = '';
            this.descripcionMedidaError = '';
            this.codigoClasificadorError = '';
        },
    },
    mounted(){
        this.listarMedidas(1, this.buscar, this.criterio);
    }
};
</script>
<style scoped>
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
  display: flex;
  align-items: center;
  gap: 10px;
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
.status-badge {
  padding: 0.25em 0.5em;
  border-radius: 4px;
  color: white;
}
.status-badge.active {
  background-color: rgb(0, 225, 0);
}
.status-badge.inactive {
  background-color: red;
}
</style>