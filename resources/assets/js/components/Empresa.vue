<template>
    <main class="main">   
        <Card class="p-shadow-4-sm" style="width: 80vw">
            <template #title>
                <div class="p-d-flex p-ai-center p-jc-between">
                    <h2 class="p-m-0"><i class="pi pi-building p-mr-2"></i>Información de la Empresa</h2>
                    <Button v-if="estadoInputs" icon="pi pi-pencil" label="Editar" @click="estadoCampos()" class="p-button p-button-info p-button-sm" />
                </div>
            </template>
            <template #content>
                <div class="p-fluid p-formgrid p-grid">
                    <div class="p-field p-col-12 p-md-4">
                       
                            <img :src="logoUrl" width="150" height="150" alt="Logo empresa" class="p-mb-3 p-shadow-2" style="object-fit: contain;" />
                            <FileUpload v-if="!estadoInputs" mode="basic" accept="image/*" :auto="true" @select="onLogoChange" chooseLabel="Cambiar Logo" class="p-button-rounded p-button-info p-button-sm" />
                        
                    </div>
                    <div class="p-col-12 p-md-8">
                        <div class="p-grid">
                            <div class="p-field p-col-12 p-md-6">
                                <label class="p-font-bold">Nombre:</label>
                                <InputText v-if="!estadoInputs" v-model="nombre" placeholder="Nombre de la empresa" :readonly="estadoInputs" maxlength="30" class="p-inputtext-sm" />
                                <div v-else class="p-text-bold p-p-2">{{ nombre }}</div>
                            </div>
                            <div class="p-field p-col-12 p-md-6">
                                <label class="p-font-bold">Dirección:</label>
                                <InputText v-if="!estadoInputs" v-model="direccion" placeholder="Dirección" :readonly="estadoInputs" maxlength="30" class="p-inputtext-sm" />
                                <div v-else class="p-text-bold p-p-2">{{ direccion }}</div>
                            </div>
                            <div class="p-field p-col-12 p-md-6">
                                <label class="p-font-bold">Teléfono:</label>
                                <InputNumber v-if="!estadoInputs" v-model="telefono" placeholder="Teléfono" :readonly="estadoInputs" class="p-inputtext-sm" />
                                <div v-else class="p-text-bold p-p-2">{{ telefono }}</div>
                            </div>
                            <div class="p-field p-col-12 p-md-6">
                                <label class="p-font-bold">NIT:</label>
                                <InputNumber v-if="!estadoInputs" v-model="nit" placeholder="NIT" :readonly="estadoInputs" class="p-inputtext-sm" />
                                <div v-else class="p-text-bold p-p-2">{{ nit }}</div>
                            </div>
                            <div class="p-field p-col-12">
                                <label class="p-font-bold">Email:</label>
                                <InputText v-if="!estadoInputs" v-model="email" placeholder="Email" :readonly="estadoInputs" class="p-inputtext-sm" />
                                <div v-else class="p-text-bold p-p-2">{{ email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!estadoInputs" class="p-d-flex p-jc-center p-mt-4">
                    <Button label="Cancelar" icon="pi pi-times" @click="estadoCampos(); datosEmpresa()" class="p-button p-button-danger p-button-sm p-mr-2" />
                    <Button label="Guardar" icon="pi pi-check" @click="actualizarEmpresa()" class="p-button p-button-success p-button-sm" />
                </div>
            </template>
        </Card>
    </main>
</template>

<script>
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import FileUpload from 'primevue/fileupload';

export default {
    components: {
        Card,
        Panel,
        Button,
        InputText,
        InputNumber,
        FileUpload
    },
    data() {
        return {
            empresa_id: 0,
            nombre: '',
            direccion: '',
            telefono: '',
            email: '',
            nit: '',
            licencia: '',
            estadoInputs: true,
            logo: null,
            logoUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3bz1rSR914Qj3-mmNDyf-MhhLkdq3GzsVNKUZYXTJaQ&s',
        }
    },
    methods: {
        validarCorreoElectronico(correo) {
            const regexCorreoElectronico = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regexCorreoElectronico.test(correo);
        },
        validarCampos() {
            if (this.telefono.toString().length > 8) {
                this.$toast.add({severity:'error', summary: 'Error', detail:'El número de teléfono no debe contener más de 8 dígitos', life: 3000});
                return false;
            }
            if (!this.validarCorreoElectronico(this.email)) {
                this.$toast.add({severity:'error', summary: 'Error', detail:'Ingrese un correo electrónico válido', life: 3000});
                return false;
            }
            return true
        },
        estadoCampos() {
            this.estadoInputs = !this.estadoInputs;
        },
        datosEmpresa() {
            let me = this;
            var url = '/empresa';

            axios.get(url).then(function (response) {
                var respuesta = response.data;

                me.empresa_id = respuesta.empresa.id;
                me.nombre = respuesta.empresa.nombre;
                me.direccion = respuesta.empresa.direccion;
                me.telefono = respuesta.empresa.telefono;
                me.email = respuesta.empresa.email;
                me.nit = respuesta.empresa.nit;
                me.licencia = respuesta.empresa.licencia;
                me.logoUrl = respuesta.empresa.logo || me.logoUrl;
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        onLogoChange(event) {
            this.logo = event.files[0];
            this.logoUrl = URL.createObjectURL(this.logo);
        },
        actualizarEmpresa() {
            if (!this.validarCampos()) {
                return;
            }

            let formData = new FormData();
            formData.append('nombre', this.nombre);
            formData.append('direccion', this.direccion);
            formData.append('telefono', this.telefono);
            formData.append('email', this.email);
            formData.append('nit', this.nit);
            formData.append('licencia', this.licencia);
            formData.append('id', this.empresa_id);
            if (this.logo) {
                formData.append('logo', this.logo);
            }

            axios.post('/empresa/actualizar', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then((response) => {
                this.$toast.add({severity:'success', summary: 'Éxito', detail:'Datos actualizados correctamente', life: 3000});
                this.estadoCampos();
                this.datosEmpresa();
            }).catch((error) => {
                console.error("Ocurrió un error al actualizar: ", error);
                this.$toast.add({severity:'error', summary: 'Error', detail:'Hubo un error al actualizar los datos de la empresa', life: 3000});
            });
        }
    },
    mounted() {
        this.datosEmpresa()
    }
}
</script>

<style scoped>
.main {
    padding: 2rem;
    background-color: #f8f9fa;
}
.p-card {
    background-color: white;
    border-radius: 10px;
}
.p-panel {
    background-color: #f1f3f5;
}
.p-inputtext-lg {
    font-size: 1.1rem;
}
.p-text-bold {
    font-weight: 700;
}
</style>