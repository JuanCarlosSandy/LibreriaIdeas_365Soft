<template>
  <div class="main">
    <Panel>
      <template #header>
        <div class="flex justify-content-between align-items-center">
          <h5 class="m-0">Almacenes</h5>
          <Button icon="pi pi-plus" label="Nuevo" @click="abrirModal('almacenes', 'registrar')" />
        </div>
      </template>
      
      <div class="p-grid">
        <div class="p-col-12 p-md-6">
          <div class="p-inputgroup-sm">
            <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label" optionValue="value" class="p-inputgroup-addon-sm" style="width: 150px;" />
            <InputText v-model="buscar" placeholder="Texto a buscar" @keyup.enter="listarAlmacenes(1, buscar, criterio)" />
            <Button icon="pi pi-search" @click="listarAlmacenes(1, buscar, criterio)" />
          </div>
        </div>
      </div>

      <DataTable :value="arrayAlmacen" class="p-datatable-sm" :rows="10" :paginator="true" :rowsPerPageOptions="[5,10,20]" responsiveLayout="scroll">
        <Column header="Acciones">
          <template #body="slotProps">
            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('almacenes', 'actualizar', slotProps.data)" />
          </template>
        </Column>
        <Column field="nombre_almacen" header="Nombre del Almacén"></Column>
        <Column field="ubicacion" header="Dirección (Ubicación)"></Column>
        <Column field="encargados_nombres" header="Encargado"></Column>
        <Column field="telefono" header="Teléfono"></Column>
        <Column field="nombre_sucursal" header="Sucursal"></Column>
        <Column field="observacion" header="Observación"></Column>
      </DataTable>
    </Panel>

    <Dialog :visible.sync="modal" :containerStyle="{width: '50vw'}" :modal="true" :closable="false">
      <template #header>
        <h3>{{ tituloModal }}</h3>
      </template>

      <form @submit.prevent="enviarFormulario">
        <div class="p-fluid p-formgrid p-grid">
          <div class="p-field p-col-12 p-md-6">
            <label for="nombre_almacen">Nombre del almacén</label>
            <InputText id="nombre_almacen" v-model="datosFormulario.nombre_almacen" :class="{'p-invalid': errores.nombre_almacen}" @input="validarCampo('nombre_almacen')" />
            <small class="p-error" v-if="errores.nombre_almacen">{{ errores.nombre_almacen }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="ubicacion">Ubicación</label>
            <InputText id="ubicacion" v-model="datosFormulario.ubicacion" :class="{'p-invalid': errores.ubicacion}" @input="validarCampo('ubicacion')" />
            <small class="p-error" v-if="errores.ubicacion">{{ errores.ubicacion }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="encargado">Encargados</label>
            <MultiSelect v-model="usuariosSeleccionados" :options="arrayUsuario" optionLabel="nombre" 
              placeholder="Buscar encargados..." :filter="true" :showClear="true" 
              @filter="onFilterUsuarios" @change="getDatosUsuarios">
            </MultiSelect>
            <small class="p-error" v-if="errores.encargado">{{ errores.encargado }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="telefono">Teléfono</label>
            <InputNumber id="telefono" v-model="datosFormulario.telefono" :class="{'p-invalid': errores.telefono}" @input="validarCampo('telefono')" />
            <small class="p-error" v-if="errores.telefono">{{ errores.telefono }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="sucursal">Sucursal</label>
            <Dropdown v-model="sucursalSeleccionado" :options="arraySucursal" optionLabel="nombre" 
              placeholder="Buscar Sucursales..." :class="{'p-invalid': errores.sucursal}" 
              @change="getDatosSucursales" :filter="true" @filter="onFilterSucursales" />
            <small class="p-error" v-if="errores.sucursal">{{ errores.sucursal }}</small>
          </div>
          <div class="p-field p-col-12">
            <label for="observaciones">Observaciones</label>
            <Textarea id="observaciones" v-model="datosFormulario.observaciones" rows="3" />
          </div>
        </div>
      </form>

      <template #footer>
        <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-text" />
        <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" @click="enviarFormulario" autofocus />
        <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check" @click="enviarFormulario" autofocus />
      </template>
    </Dialog>
  </div>
</template>

<script>
import { esquemaAlmacen } from "../constants/validations";

import Panel from 'primevue/panel';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';

export default {
  components: {
    Panel,
    Button,
    Dropdown,
    InputText,
    DataTable,
    Column,
    Dialog,
    MultiSelect,
    InputNumber,
    Textarea
  },
  data() {
    return {
      criterioOptions: [
        { label: 'Nombre Almacen', value: 'nombre_almacen' },
        { label: 'Nombre Encargado', value: 'nombre_encargado' },
        { label: 'Nombre Sucursal', value: 'nombre_sucursal' }
      ],
      arraySucursal: [],
      sucursalSeleccionado: null,
      arrayUsuario: [],
      usuariosSeleccionados: [],
      datosFormulario: {
        nombre_almacen: "",
        ubicacion: "",
        encargado: "",
        telefono: null,
        sucursal: null,
        observaciones: "",
      },
      errores: {},
      arrayAlmacen: [],
      modal: false,
      tituloModal: "",
      tipoAccion: 0,
      criterio: "nombre_almacen",
      buscar: "",
    };
  },
  methods: {
    onFilterUsuarios(event) {
      this.selectUsuario(event.value);
    },
    selectUsuario(search) {
      let me = this;
      let url = "/user/selectUser/filter?filtro=" + search + "&idrol=3";
      axios.get(url)
        .then(function (response) {
          me.arrayUsuario = response.data.usuarios;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    onFilterSucursales(event) {
      this.selectSucursal(event.value);
    },
    selectSucursal(search) {
      console.log(search);
      let me = this;
      let url = "/sucursal/selectedSucursal/filter?filtro=" + search;
      axios.get(url)
        .then(function (response) {
          me.arraySucursal = response.data.sucursales;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getDatosSucursales(val) {
      console.log(val.value.id);
      this.datosFormulario.sucursal = val.value && val.value.id ? val.value.id : null;
    },
    getDatosUsuarios() {
      console.log(this.usuariosSeleccionados);
      this.datosFormulario.encargado = this.usuariosSeleccionados && this.usuariosSeleccionados.length > 0 
        ? this.usuariosSeleccionados.map((v) => v.id).join(",") 
        : "";
    },
    async validarCampo(campo) {
      try {
        await esquemaAlmacen.validateAt(campo, this.datosFormulario);
        this.errores[campo] = null;
      } catch (error) {
        this.errores[campo] = error.message;
      }
    },
    async enviarFormulario() {
    console.log(this.datosFormulario);
      try {
        await esquemaAlmacen.validate(this.datosFormulario, { abortEarly: false });
        if (this.tipoAccion == 2) {
          this.actualizarAlmacen(this.datosFormulario);
        } else {
          this.registrarAlmacen(this.datosFormulario);
        }
      } catch (error) {
        const erroresValidacion = {};
        error.inner.forEach((e) => {
          erroresValidacion[e.path] = e.message;
        });
        this.errores = erroresValidacion;
      }
    },
    listarAlmacenes(page, buscar, criterio) {
      let me = this;
      var url = "/almacen?page=" + page + "&buscar=" + buscar + "&criterio=" + criterio;
      axios.get(url)
        .then(function (response) {
          me.arrayAlmacen = response.data.almacenes.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    registrarAlmacen(data) {
      let me = this;
      axios.post("/almacen/registrar", data)
        .then(function (response) {
          swal({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Datos registrados con éxito',
            confirmButtonText: 'Aceptar'
          });
          me.cerrarModal();
          me.listarAlmacenes(1, "", "nombre_almacen");
          me.$toast.add({severity:'success', summary: 'Éxito', detail: 'Almacén registrado', life: 3000});
        })
        .catch(function (error) {
          console.log(error);
          me.$toast.add({severity:'error', summary: 'Error', detail: 'No se pudo registrar el almacén', life: 3000});
        });
    },
    actualizarAlmacen(data) {
      let me = this;
      axios.put("/almacen/editar", data)
        .then(function (response) {
          swal({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Datos registrados con éxito',
            confirmButtonText: 'Aceptar'
          });
          me.cerrarModal();
          me.listarAlmacenes(1, "", "nombre_almacen");
          me.$toast.add({severity:'success', summary: 'Éxito', detail: 'Almacén actualizado', life: 3000});
        })
        .catch(function (error) {
          console.log(error);
          me.$toast.add({severity:'error', summary: 'Error', detail: 'No se pudo actualizar el almacén', life: 3000});
        });
    },
    cerrarModal() {
      this.modal = false;
      this.tituloModal = "";
      this.sucursalSeleccionado = null;
      this.usuariosSeleccionados = [];
      this.datosFormulario = {
        nombre_almacen: "",
        ubicacion: "",
        encargado: "",
        telefono: null,
        sucursal: null,
        observaciones: "",
      };
      this.errores = {};
    },
    abrirModal(modelo, accion, data = []) {
      switch (modelo) {
        case "almacenes": {
          switch (accion) {
            case "registrar": {
              this.modal = true;
              this.tituloModal = "Registrar Almacen";
              this.tipoAccion = 1;
              break;
            }
            case "actualizar": {
              this.modal = true;
              this.tituloModal = "Actualizar Almacen";
              this.tipoAccion = 2;
              this.datosFormulario = { ...data };
              this.sucursalSeleccionado = { id: data.sucursal, nombre: data.nombre_sucursal };
              this.usuariosSeleccionados = data.encargados ? data.encargados.map(e => ({ id: e.id, nombre: e.nombre })) : [];
              break;
            }
          }
        }
      }
    },
  },
  mounted() {
    this.listarAlmacenes(1, this.buscar, this.criterio);
  },
};
</script>

<style scoped>
.p-inputgroup {
  width: 100%;
}
</style>