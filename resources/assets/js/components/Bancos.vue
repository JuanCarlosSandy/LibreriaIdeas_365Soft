<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="/">Escritorio</a></li>
        </ol>
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Banco
                    <icon-button icon="icon-plus" size="small" color="secondary"
                        @click="abrirModal('banco', 'registrar')" label="Nuevo" />
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select class="form-control col-md-3" v-model="criterio">
                                    <option value="nombre_cuenta">Nombre cuenta</option>
                                    <option value="nombre_banco">Nombre del banco</option>/option>
                                    <option value="numero_cuenta">Número de cuenta</option>
                                    <option value="tipo_cuenta">Tipo cuenta</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarBancos(1, buscar, criterio)"
                                    class="form-control" placeholder="Texto a buscar">
                                <button type="submit" @click="listarBancos(1, buscar, criterio)"
                                    class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>

                    </div>


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Nombre</th>
                                    <th>Número de cuenta</th>

                                    <th>Banco</th>
                                    <th>Tipo de cuenta</th>
                                    <th>Logo banco</th>


                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="banco in arrayBancos" :key="banco.id">
                                    <td>
                                        <icon-button icon="icon-pencil" size="small" color="warning"
                                            @click="abrirModal('banco', 'actualizar', banco)" />

                                    </td>
                                    <td v-text="banco.nombre_cuenta"></td>
                                    <td v-text="banco.numero_cuenta"></td>
                                    <td>
                                        {{ banco.nombre_banco }}
                                    </td>

                                    <td v-text="banco.tipo_cuenta"></td>
                                    <td>
                                        <img :src="getBankUrl(banco.nombre_banco)" width="100px" ref="imagen">

                                    </td>


                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#"
                                    @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page"
                                :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)"
                                    v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#"
                                    @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
        <!--Inicio del modal agregar/actualizar-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form @submit.prevent="enviarFormulario">

                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="" class="font-weight-bold">Nombre de cuenta <span
                                            class="text-danger">*</span></label>
                                    <input type="text" v-model="datosFormulario.nombre_cuenta" class="form-control"
                                        placeholder="Ej. Cuenta de ahorros personal"
                                        :class="{ 'is-invalid': errores.nombre_cuenta }"
                                        @input="validarCampo('nombre_cuenta')">
                                    <p class="text-danger" v-if="errores.nombre_cuenta">{{ errores.nombre_cuenta }}</p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Nombre del banco <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="country" v-model="selectedBank"
                                        @input="filterBanks" @keydown.enter="selectClosestCountry"
                                        placeholder="Ej. Banco Nacional de Bolivia S.A."
                                        :class="{ 'is-invalid': errores.pais }" />
                                    <ul class="ulAutocomplete" v-if="showDropdown">
                                        <li class="liAutocomplete" v-for="(country, code) in filteredBanks" :key="code"
                                            @click="selectBank(code)">
                                            {{ country }}
                                        </li>
                                    </ul>
                                    <p class="text-danger" v-if="errores.nombre_banco">{{ errores.nombre_banco }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Número de cuenta <span
                                            class="text-danger">*</span></label>
                                    <input type="number" v-model="datosFormulario.numero_cuenta" class="form-control"
                                        placeholder="Ej. 100000121221" :class="{ 'is-invalid': errores.numero_cuenta }"
                                        @input="validarCampo('numero_cuenta')">
                                    <p class="text-danger" v-if="errores.numero_cuenta">{{ errores.numero_cuenta }}</p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <img :src="getBankUrl(datosFormulario.nombre_banco)" class="bank-img" ref="imagen">
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Tipo de cuenta <span
                                            class="text-danger">*</span></label>
                                    <select id="tipoCuenta" class="form-select" v-model="datosFormulario.tipo_cuenta"
                                        :class="{ 'is-invalid': errores.tipo_cuenta }"
                                        @change="validarCampo('tipo_cuenta')">
                                        <option value="" disabled selected>Seleccione el tipo de cuenta</option>
                                        <option v-for="tipoCuenta in tiposDeCuenta" :key="tipoCuenta"
                                            :value="tipoCuenta">{{ tipoCuenta }}</option>
                                    </select>
                                    <p class="text-danger" v-if="errores.tipo_cuenta">{{ errores.tipo_cuenta }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" @click="cerrarModal()">Cerrar</button>
                            <button type="submit" v-if="tipoAccion == 1" class="btn btn-success">Guardar</button>
                            <button type="submit" v-if="tipoAccion == 2" class="btn btn-success">Actualizar</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!--Fin del modal-->
    </main>
</template>

<script>
import VueBarcode from 'vue-barcode';

import { esquemaBanco } from '../constants/validations';
import { bancos, tiposDeCuenta } from '../constants/banks';
export default {
    data() {
        return {
            bancos: bancos,
            tiposDeCuenta: tiposDeCuenta,
            datosFormulario: {
                nombre_cuenta: "",
                nombre_banco: "",
                numero_cuenta: "",
                tipo_cuenta: "Cuenta corriente"
            },
            errores: {},

            itemsPerPage: 5,

            selectedBank: "",
            filteredBanks: {},
            showDropdown: false,
            arrayBancos: [],
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
            criterio: 'nombre_cuenta',
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

            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }

            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }

            let pagesArray = [];
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
                await esquemaBanco.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {

            await esquemaBanco.validate(this.datosFormulario, { abortEarly: false })
                .then(() => {
                    console.log(this.datosFormulario)
                    if (this.tipoAccion == 2) {
                        this.actualizarBanco(this.datosFormulario);
                    } else {
                        this.registrarBanco(this.datosFormulario);
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

        formatFecha(fechaOriginal) {
            const fecha = new Date(fechaOriginal);
            const dia = fecha.getDate();
            const mes = fecha.getMonth() + 1;
            const anio = fecha.getFullYear();

            const diaFormateado = dia < 10 ? `0${dia}` : dia;
            const mesFormateado = mes < 10 ? `0${mes}` : mes;

            return `${diaFormateado}-${mesFormateado}-${anio}`;
        },
        getBankUrl(bankName) {
            const code = this.getBankCodeByName(bankName);
            return code ? `img/bancos/${code.toUpperCase()}.png` : null;
        },
        getBankCodeByName(bankName) {
            const lowerCaseName = bankName.toLowerCase();
            for (const [code, name] of Object.entries(this.bancos)) {
                if (name.toLowerCase() === lowerCaseName) {
                    return code;
                }
            }
            return null;
        },
        filterBanks() {

            const query = this.selectedBank.toLowerCase();
            const filteredEntries = Object.entries(this.bancos).filter(([code, country]) =>
                country.toLowerCase().includes(query)
            );
            this.filteredBanks = Object.fromEntries(filteredEntries.slice(0, 5));
            this.showDropdown = filteredEntries.length > 0;

        },
        selectBank(code) {
            this.selectedBank = this.bancos[code];
            this.showDropdown = false;
            this.pais = this.selectedBank;
            this.datosFormulario.nombre_banco = this.pais;
            this.validarCampo('nombre_banco');


        },
        selectClosestCountry() {
            const firstBankCode = Object.keys(this.filteredBanks)[0];
            if (firstBankCode) {
                this.selectBank(firstBankCode);
            }
        },
        listarBancos(page = 1, search = '', criteria = 'nombre_banco') {
            console.log("A buscar")
            console.log(search)
            console.log(criteria)

            axios.get('/bancos', {
                params: {
                    page: page,
                    buscar: search,
                    criterio: criteria
                }
            })
                .then(response => {
                    this.arrayBancos = response.data.bancos.data;
                    this.pagination = response.data.pagination;
                    console.log(response)
                })
                .catch(error => {
                    console.error('Error al obtener los datos de los bancos:', error);
                });
        },


        cambiarPagina(page, buscar, criterio) {
            let me = this;
            me.pagination.current_page = page;
            me.listarBancos(page, buscar, criterio);
        },
        registrarBanco(datos) {
            axios.post('/bancos/registrar', datos)
                .then(response => {
                    this.cerrarModal();

                    this.listarBancos(1, '', 'nombre_cuenta');
                    this.toastSuccess("banco registrada correctamente");


                })
                .catch(error => {
                    console.error('Error al agregar un banco:', error);
                });

        },

        actualizarBanco(datos) {
            console.log(datos)

            axios.put(`/bancos/actualizar`, datos)
                .then(response => {
                    this.cerrarModal();
                    this.toastSuccess("banco actualizada correctamente")

                    this.listarBancos(1, '', 'nombre_cuenta');
                })
                .catch(error => {
                    console.error('Error al actualizar el banco:', error);
                });
        },


        cerrarModal() {
            this.selectedBank = '';
            this.showDropdown = false;
            this.modal = 0;
            this.tituloModal = '';
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "banco":
                    {
                        switch (accion) {
                            case 'registrar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Banco';
                                    this.tipoAccion = 1;
                                    this.datosFormulario = {
                                        nombre_cuenta: "",

                                        nombre_banco: "",
                                        numero_cuenta: "",
                                        tipo_cuenta: "Cuenta corriente"

                                    };
                                    this.errores = {};
                                    break;
                                }
                            case 'actualizar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Actualizar Banco';
                                    this.tipoAccion = 2;
                                    this.datosFormulario = {
                                        id: data['id'],
                                        nombre_cuenta: data['nombre_cuenta'],

                                        nombre_banco: data['nombre_banco'],
                                        numero_cuenta: data['numero_cuenta'],
                                        tipo_cuenta: data['tipo_cuenta']
                                    };

                                    this.errores = {};
                                    this.selectedBank = data['nombre_banco'];

                                    break;
                                }
                        }
                    }
            }
        },
        toastSuccess(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `.<br>
    </div>`, {
                type: "success",
                position: "bottom-right",
                duration: 4000
            });
        },
        toastError(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `<br>
    </div>`, {
                type: "error",
                position: "bottom-right",
                duration: 4000
            });
        }
    },
    mounted() {
        this.listarBancos(1, this.buscar, this.criterio);
    }
}
</script>
<style>
.bank-img {
    width: 160px;
    height: auto;
}

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

.ulAutocomplete {
    list-style-type: none;
    padding: 0;
    margin: 0;
    border: 1px solid #ccc;
}

.liAutocomplete {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
</style>


<!-- <template>
    <div>
        <ul>
            <li v-for="banco in bancos" :key="banco.id">
                {{ banco.nombre_banco }}
            </li>
        </ul>
        <form @submit.prevent="agregarBanco">
            <input type="text" v-model="nuevoBanco.nombre_banco" placeholder="Nombre del Banco">
            <input type="text" v-model="nuevoBanco.numero_cuenta" placeholder="Número de Cuenta">
            <input type="text" v-model="nuevoBanco.tipo_cuenta" placeholder="Tipo de Cuenta">
            <button type="submit">Agregar Banco</button>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            bancos: [],
            nuevoBanco: {
                nombre_banco: '',
                numero_cuenta: '',
                tipo_cuenta: ''
            }
        };
    },
    mounted() {
        this.cargarBancos();
    },
    methods: {
        cargarBancos() {
            axios.get('/bancos')
                .then(response => {
                    this.bancos = response.data;
                    console.log(this.bancos);
                })
                .catch(error => {
                    console.error('Error al cargar los bancos:', error);
                });
        },
        agregarBanco() {
            axios.post('/bancos', this.nuevoBanco)
                .then(response => {
                    this.cargarBancos();
                    this.nuevoBanco = {
                        nombre_banco: '',
                        numero_cuenta: '',
                        tipo_cuenta: ''
                    };
                })
                .catch(error => {
                    console.error('Error al agregar un banco:', error);
                });
        }
    }
};
</script> -->