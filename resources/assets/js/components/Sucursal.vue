<template>
   <main class="main">
  <div class="p-grid">
    <div class="p-col-12">
      <Breadcrumb :home="{ icon: 'pi pi-home', to: '/' }" :model="breadcrumbItems" />
    </div>
    <div class="p-col-12">
      <Card>
        <template #title>
          <i class="pi pi-align-justify"></i> Sucursales
          <Button icon="pi pi-plus" class="p-button-secondary p-button-sm" @click="abrirModal('sucursal', 'registrar')" label="Nuevo" />
        </template>
        <template #content>
          <div class="p-grid">
            <div class="p-col-12 p-md-6">
              <div class="p-inputgroup-sm">
                <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label" optionValue="value" placeholder="Seleccione criterio"  />
                <InputText  v-model="buscar" placeholder="Texto a buscar" @keyup.enter="listarSucursal(1, buscar, criterio)" />
                <Button     icon="pi pi-search" @click="listarSucursal(1, buscar, criterio)" />
              </div>
            </div>
          </div>
          <DataTable :value="arraySucursal" responsiveLayout="scroll" class="p-datatable-gridlines p-datatable-sm" :rows="10" :responsive="true">
            <Column header="Acciones">
              <template #body="slotProps">
                <Button icon="pi pi-pencil" class="p-button-warning p-button-sm" @click="abrirModal('sucursal', 'actualizar', slotProps.data)" />
                <Button v-if="slotProps.data.condicion" icon="pi pi-trash" class="p-button-danger p-button-sm" @click="desactivarSucursal(slotProps.data.id)" />
                <Button v-else icon="pi pi-check" class="p-button-info p-button-sm" @click="activarSucursal(slotProps.data.id)" />
              </template>
            </Column>
            <Column field="nombre_empresa" header="Empresa"></Column>
            <Column field="nombre" header="Nombre sucursal"></Column>
            <Column field="direccion" header="Dirección"></Column>
            <Column field="correo" header="Correo"></Column>
            <Column field="telefono" header="Teléfono"></Column>
            <Column field="departamento" header="Departamento"></Column>
            <Column header="Estado">
              <template #body="slotProps">
                <Tag :severity="slotProps.data.condicion ? 'success' : 'danger'" :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'" />
              </template>
            </Column>
          </DataTable>
          <Paginator :rows="10" :totalRecords="pagination.total" @page="onPageChange($event)" :rowsPerPageOptions="[10,20,30]"></Paginator>
        </template>
      </Card>
    </div>

    <Dialog :visible.sync="modal" :containerStyle="{width: '600px'}" :modal="true">
      <template #header>
        <h3>{{ tituloModal }}</h3>
      </template>
      <form @submit.prevent="enviarFormulario">
        <div class="p-fluid p-formgrid p-grid">
          <div class="p-field p-col-12 p-md-8">
            <label for="nombre">Nombre de la sucursal</label>
            <InputText id="nombre" v-model="datosFormulario.nombre" :class="{'p-invalid': errores.nombre}" @input="validarCampo('nombre')" />
            <small v-if="errores.nombre" class="p-error">{{ errores.nombre }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="correo">Correo electrónico</label>
            <InputText id="correo" v-model="datosFormulario.correo" :class="{'p-invalid': errores.correo}" @input="validarCampo('correo')" />
            <small v-if="errores.correo" class="p-error">{{ errores.correo }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="telefono">Teléfono</label>
            <InputNumber id="telefono" v-model="datosFormulario.telefono" :class="{'p-invalid': errores.telefono}" @input="validarCampo('telefono')" />
            <small v-if="errores.telefono" class="p-error">{{ errores.telefono }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="direccion">Dirección</label>
            <InputText id="direccion" v-model="datosFormulario.direccion" :class="{'p-invalid': errores.direccion}" @input="validarCampo('direccion')" />
            <small v-if="errores.direccion" class="p-error">{{ errores.direccion }}</small>
          </div>
          <div class="p-field p-col-12 p-md-6">
            <label for="departamento">Departamento</label>
            <Dropdown id="departamento" v-model="datosFormulario.departamento" :options="arrayDepartamentos" :class="{'p-invalid': errores.departamento}" @change="validarCampo('departamento')" placeholder="Seleccione" />
            <small v-if="errores.departamento" class="p-error">{{ errores.departamento }}</small>
          </div>
        </div>
        <div v-if="tipoAccion === 1">
          <strong>Código de Sucursal:</strong> {{ codigoSucursal }}
        </div>
        <div v-if="tipoAccion === 2">
          <strong>Código de Sucursal:</strong> {{ datosFormulario.codigoSucursal }}
        </div>
      </form>
      <template #footer>
        <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-text" />
        <Button v-if="tipoAccion === 1" label="Guardar" icon="pi pi-check" @click="enviarFormulario" autofocus />
        <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check" @click="enviarFormulario" autofocus />
      </template>
    </Dialog>
  </div>
  </main>
</template>
  
<script>
import { esquemaSucursal } from '../constants/validations';
import Breadcrumb from 'primevue/breadcrumb';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Paginator from 'primevue/paginator';
import Tag from 'primevue/tag';
import InputNumber from 'primevue/inputnumber';
export default {
  components: {
    Breadcrumb,
    Card,
    Button,
    InputText,
    Dropdown,
    DataTable,
    Column,
    Dialog,
    Paginator,
    Tag,
    InputNumber
  },
  data() {
    return {
      criterioOptions: [
        { label: 'Nombres', value: 'nombre' },
        { label: 'Dirección', value: 'direccion' },
        { label: 'Correo', value: 'correo' },
        { label: 'Teléfono', value: 'telefono' },
        { label: 'Departamento', value: 'departamento' }
      ],
      datosFormulario: {
        nombre: '',
        direccion: '',
        correo: '',
        telefono: '',
        departamento: '',
      },
      errores: {},

      codigoSucursal: 1,
      arraySucursal: [],
      arrayDepartamentos: ["Beni", "Chuquisaca", "Cochabamba", "La Paz", "Oruro", "Pando", "Potosí", "Santa Cruz", "Tarija"],
      modal: 0,
      tituloModal: '',
      tipoAccion: 0,
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
      arrayEmpresa: [],
      buscar: ''
    }
  },
  computed: {
    isActived: function () {
      return this.pagination.current_page;
    },
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
    async validarCampo(campo) {
      try {
        await esquemaSucursal.validateAt(campo, this.datosFormulario);
        this.errores[campo] = null;
      } catch (error) {
        this.errores[campo] = error.message;
      }
    },
    async enviarFormulario() {
     
      await esquemaSucursal.validate(this.datosFormulario, { abortEarly: false })
        .then(() => {
          if (this.tipoAccion == 2) {
            this.actualizarSucursal(this.datosFormulario);
          } else {
            this.datosFormulario.codigoSucursal = this.codigoSucursal
            this.registrarSucursal(this.datosFormulario);
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

    listarSucursal(page, buscar, criterio) {
      let me = this;
      var url = '/sucursal?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
      axios.get(url).then(function (response) {
        var respuesta = response.data;
        me.arraySucursal = respuesta.sucursales.data;
        me.pagination = respuesta.pagination;
      })
        .catch(function (error) {
          console.log(error);
        });
    },
    cambiarPagina(page, buscar, criterio) {
      let me = this;
      me.pagination.current_page = page;
      me.listarSucursal(page, buscar, criterio);
    },

    obtenerUltimoCodigoSucursal() {
      axios.get('/ruta-api-para-obtener-ultimo-codigo-sucursal')
        .then(response => {
          const ultimoCodigo = response.data.ultimoCodigo;
          this.codigoSucursal = ultimoCodigo + 1;
        })
        .catch(error => {
          console.error('Error al obtener el último código de sucursal:', error);
        });
    },

    registrarSucursal(datos) {
      axios.post('/sucursal/registrar', datos)
        .then((response) => {
          swal(
            'SUCURSAL REGISTRADA',
            'Correctamente',
            'success'
          );
          this.cerrarModal();
          this.listarSucursal(1, '', 'nombre');
        })
        .catch((error) => {
          console.log(error);
          swal(
            'ERROR AL REGISTRAR LA NUEVA SUCURSAL',
            'Intente de nuevo',
            'error'
          );
        });
    },


    actualizarSucursal(datos) {

      axios.put('/sucursal/actualizar', datos)
        .then((response) => {
          swal(
            'SUCURSAL ACTUALIZADA',
            'Correctamente',
            'success'
          );
          this.cerrarModal();
          this.listarSucursal(1, '', 'nombre');
        })
        .catch((error) => {
          console.log(error);
          swal(
            'ERROR AL ACTUALIZAR LA NUEVA SUCURSAL',
            'Intente de nuevo',
            'error'
          );
        });
    },
    desactivarSucursal(id) {
      swal({
        title: 'Esta seguro de desactivar esta sucursal?',
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

          axios.put('/sucursal/desactivar', {
            'id': id
          }).then(function (response) {
            me.listarSucursal(1, '', 'nombre');
            swal(
              'Desactivado!',
              'El registro ha sido desactivado con éxito.',
              'success'
            )
          }).catch(function (error) {
            console.log(error);
          });


        } else if (
          result.dismiss === swal.DismissReason.cancel
        ) {

        }
      })
    },
    activarSucursal(id) {
      swal({
        title: 'Esta seguro de activar esta sucursal?',
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

          axios.put('/sucursal/activar', {
            'id': id
          }).then(function (response) {
            me.listarSucursal(1, '', 'nombre');
            swal(
              'Activado!',
              'El registro ha sido activado con éxito.',
              'success'
            )
          }).catch(function (error) {
            console.log(error);
          });


        } else if (
          result.dismiss === swal.DismissReason.cancel
        ) {

        }
      })
    },
    cerrarModal() {
      this.modal = 0;
      this.tituloModal = '';
      this.errores = {};
    },
    abrirModal(modelo, accion, data = []) {
      switch (modelo) {
        case "sucursal":
          {
            switch (accion) {
              case 'registrar':
                {
                  this.modal = 1;
                  this.tituloModal = 'Registrar sucursal';
                  this.datosFormulario = {
                    nombre: '',
                    direccion: '',
                    correo: '',
                    telefono: '',
                    departamento: '',
                  },
                  this.tipoAccion = 1;
                  this.obtenerUltimoCodigoSucursal();

                  break;
                }
              case 'actualizar':
                {
                  this.modal = 1;
                  this.tituloModal = 'Actualizar sucursal';
                  this.tipoAccion = 2;
                  this.datosFormulario = {
                    id: data['id'],
                    idempresa: data['idempresa'],
                    nombre: data['nombre'],
                    direccion: data['direccion'],
                    correo: data['correo'],
                    telefono: data['telefono'],
                    departamento: data['departamento'],
                    codigoSucursal: data['codigoSucursal']
                  };
                  break;
                }
            }
          }
      }
    }
  },
  mounted() {
    this.listarSucursal(1, this.buscar, this.criterio);
  }
}
</script>

