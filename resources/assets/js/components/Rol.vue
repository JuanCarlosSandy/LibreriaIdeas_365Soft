<template>
    <div class="main">
        <Card class="custom-card">
          <template #title>
            <i class="pi pi-list"></i> Roles
          </template>
          <template #content>
              <div class="p-col-12 p-md-8">
                <div class="p-inputgroup">
                  <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label" optionValue="value" class="p-col-4-sm" />
                  <InputText v-model="buscar" placeholder="Texto a buscar" @keyup.enter="listarRol(1, buscar, criterio)" />
                  <Button icon="pi pi-search" @click="listarRol(1, buscar, criterio)" label="Buscar" />
                </div>
              </div>
                <DataTable :value="arrayRol"class="p-datatable-gridlines p-datatable-sm" :paginator="true" :rows="10"  :containerStyle="{width: '70vw'}"
                           :totalRecords="pagination.total" :lazy="true" 
                           @page="onPage($event)" :loading="loading" 
                           dataKey="id" >
                  <Column field="nombre" header="Nombre"></Column>
                  <Column field="descripcion" header="Descripción"></Column>
                  <Column field="condicion" header="Estado">
                    <template #body="slotProps">
                      <Tag :severity="slotProps.data.condicion ? 'success' : 'danger'" 
                           :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'" />
                    </template>
                  </Column>
                </DataTable>
          </template>
        </Card>
    </div>
</template>
<script>
  import Breadcrumb from 'primevue/breadcrumb';
  import Card from 'primevue/card';
  import Dropdown from 'primevue/dropdown';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import Tag from 'primevue/tag';
  
  export default {
    components: {
      Breadcrumb,
      Card,
      Dropdown,
      InputText,
      Button,
      DataTable,
      Column,
      Tag
    },
    data() {
      return {
        home: { icon: 'pi pi-home', to: '/' },
        items: [{ label: 'Escritorio' }],
        rol_id: 0,
        nombre: '',
        descripcion: '',
        arrayRol: [],
        loading: false,
        pagination: {
          'total': 0,
          'current_page': 0,
          'per_page': 0,
          'last_page': 0,
          'from': 0,
          'to': 0,
        },
        criterio: 'nombre',
        criterioOptions: [
          {label: 'Nombre', value: 'nombre'},
          {label: 'Descripción', value: 'descripcion'}
        ],
        buscar: ''
      }
    },
    methods: {
      listarRol(page, buscar, criterio) {
        this.loading = true;
        let url = `/rol?page=${page}&buscar=${buscar}&criterio=${criterio}`;
        axios.get(url).then(response => {
          let respuesta = response.data;
          this.arrayRol = respuesta.roles.data;
          this.pagination = respuesta.pagination;
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;
        });
      },
      onPage(event) {
        this.listarRol(event.page + 1, this.buscar, this.criterio);
      }
    },
    mounted() {
      this.listarRol(1, this.buscar, this.criterio);
    }
  }
  </script>
  