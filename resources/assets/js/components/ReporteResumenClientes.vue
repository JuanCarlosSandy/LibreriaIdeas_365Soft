<template>
    <main class="main">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="/">Escritorio</a> </li>
        </ol>
        <div class="container-fluid">
        
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Resumen de Clientes
                </div>
                <br>
                
                <div class="card-header">

                    <div class="form-row">
                        
                    </div>
                    <div class="form-row">
                        <div class="col-md-2">
                            <label for="fechaInicio">Fecha Inicio:</label>
                            <input type="date" id="fechaInicio" v-model="fechaInicio" @change="" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="fechaFin">Fecha Fin:</label>
                            <input type="date" id="fechaFin" v-model="fechaFin" @change="" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>VENDEDORES(*)</label>
                                <select class="form-control" v-model="vendedorId" @change="">
                                    <option value="0" disabled>Seleccione</option>
                                    <option value="todos">TODOS</option>
                                    <option v-for="opcion in arrayEjecutivos" :key="opcion.id" :value="opcion.id">{{ opcion.nombre }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>SUCURSAL(*)</label>
                                <select class="form-control" v-model="sucursalId" @change="">
                                    <option value="0" disabled>Seleccione</option>
                                    <option value="todos">TODOS</option>
                                    <option v-for="opcion in arraySucursales" :key="opcion.id" :value="opcion.id">{{ opcion.nombre }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <br>
                                <button type="submit" @click="obtenerClientesPorVendedor()" class="btn btn-primary" style="margin-top: 6px; margin-left: 50px;">
                                    <i class="fa fa-search"></i> Generar Reporte
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre cliente</th>
                                        <th>Num Documento</th>
                                        <th>Ventas</th>
                                        <th>Cobros</th>
                                        <th>Total cuotas</th>
                                        <th>Saldo Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr  v-for="cliente in clientes" :key="cliente.id_cliente">
                                        <td>{{ cliente.cliente_id }}</td>
                                        <td>{{ cliente.cliente_nombre }}</td>
                                        <td>{{ cliente.num_documento }}</td>
                                        <td>{{ cliente.total_ventas }}</td>
                                        <td>{{ cliente.cobros }}</td>
                                        <td>{{ cliente.numero_cuotas }}</td>
                                        <td>{{ cliente.total_deuda }}</td>
                                    </tr>
                                </tbody>
                        </table>

                        <br>
                        <div class="text-right">
                            <strong>Total Ventas: </strong>Bs. {{ calcularTotal('total_ventas') }}
                        </div>
                        <div class="text-right">
                            <strong>Total Cobros: </strong>Bs. {{ calcularTotalCobros() }}
                        </div>
                        <div class="text-right">
                            <strong>Total Cuotas: </strong> {{ calcularTotal('numero_cuotas') }}
                        </div>
                        <div class="text-right">
                            <strong>Saldo Total: </strong>Bs. {{ calcularTotal('total_deuda') }}
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between mt-3">
                            <button @click="exportarPDF" class="btn btn-danger">Exportar a PDF</button>
                            <button @click="exportarExcel" class="btn btn-success">Exportar a Excel</button>
                        </div>
                    </div>   
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import jsPDF from 'jspdf';
import 'jspdf-autotable';
import * as XLSX from 'xlsx-js-style';

export default {
data() {
    return {
        pagination: {
            'total': 0,
            'current_page': 0,
            'per_page': 0,
            'last_page': 0,
            'from': 0,
            'to': 0,
        },
        offset: 3,
        currentPage: 1,
        
        arraySucursales : [],
        arrayEjecutivos : [],

        clientes: [],
        filtrosSeleccionados: [],
        vendedorId: null,
        sucursalId: null,
        fechaInicio: '2024-01-01',
        fechaFin: '2024-12-30',
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
    },
},
methods: {        
    calcularTotal(campo) {
        return this.clientes.reduce((total, cliente) => {
            return total + (+cliente[campo]);
        }, 0);
    },

    calcularTotalCobros() {
        return this.clientes.reduce((total, cliente) => {
            const cobros = cliente.total_deuda === 0 ? cliente.total_ventas : (cliente.total_ventas - cliente.total_deuda);
            return total + Number(cobros);
        }, 0);
    },


    selectSucursal() {
        let me = this;
        var url = '/sucursal/selectSucursal';
        axios.get(url).then(function (response) {
            var respuesta = response.data;
            me.arraySucursales = respuesta.sucursales;
            console.log('SUCURSAL',me.arraySucursales);
        })
        .catch(function (error) {
            console.log(error);
        });
    },
    selectEjecutivos() {
        let me = this;
        var url = '/user/selectUser/rol?filtro=' + 2;
        axios.get(url).then(function (response) {
            var respuesta = response.data;
            me.arrayEjecutivos = respuesta.usuarios;
            console.log('Ejecutivos',me.arrayEjecutivos);
        })
        .catch(function (error) {
            console.log(error);
        });
    },

    obtenerClientesPorVendedor() {
        let me = this;
        
        if (this.vendedorId == null) {
            this.vendedorId = 'todos';
        }
        
        if (this.sucursalId == null) {
            this.sucursalId = 'todos';
        }
        
        var url = '/reporte-resumen-clientes?vendedorId=' + this.vendedorId + '&sucursalId=' + this.sucursalId + '&fechaInicio=' + this.fechaInicio  + '&fechaFin=' + this.fechaFin;
        
        axios.get(url).then(function (response) {
            var respuesta = response.data;
            me.clientes = respuesta.clientes;
            me.filtrosSeleccionados = respuesta.filtros;

            console.log('CLIENTES: ', me.clientes);
            console.log('FILTROS: ', me.filtrosSeleccionados);
        })
        .catch(function (error) {
            console.log(error);
        });
        },

    exportarExcel() {
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet([]);

        const startRow = 5;

        worksheet['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
        worksheet['A1'] = { t: 's', v: 'RESUMEN DE CLIENTES', s: { 
            font: { sz: 16, bold: true, color: { rgb: 'FFFFFF' } },
            alignment: { horizontal: 'center', vertical: 'center' },
            fill: { fgColor: { rgb: '3669a8' } } } };

        const fechaStyle = { font: { bold: true, color: { rgb: '000000' } }, border: { top: { style: 'thin', color: { auto: 1 } }, right: { style: 'thin', color: { auto: 1 } }, bottom: { style: 'thin', color: { auto: 1 } }, left: { style: 'thin', color: { auto: 1 } } } };
        worksheet['A2'] = { t: 's', v: `Fecha: ${new Date().toLocaleDateString('es-ES', { year: 'numeric', month: 'numeric', day: 'numeric' })}`, s: fechaStyle };

        const headers = ['Codigo Cliente', 'Nombre Cliente', 'Num Documento', 'Total Ventas', 'Cobros', 'Num Cuotas', 'Total Deuda'];
        const headerStyle = { font: { bold: true, color: { rgb: 'FFFFFF' } }, fill: { fgColor: { rgb: '3669a8' } } };

        headers.forEach((header, index) => {
            worksheet[XLSX.utils.encode_cell({ r: 3, c: index })] = { t: 's', v: header, s: headerStyle };
        });

        for (let i = 0; i < this.clientes.length; i++) {
            const { cliente_id, cliente_nombre, num_documento, total_ventas, cobros, numero_cuotas, total_deuda } = this.clientes[i];
            const rowData = [cliente_id, cliente_nombre, num_documento, total_ventas, cobros, numero_cuotas, total_deuda ];
            XLSX.utils.sheet_add_aoa(worksheet, [rowData], { origin: `A${startRow + i}` });
        }

        const columnWidths = [
            { wch: 25 },
            { wch: 25 },
            { wch: 20 },
            { wch: 12 },
            { wch: 10 },
            { wch: 12 },
            { wch: 15 },
            { wch: 15 }
        ];
        worksheet['!cols'] = columnWidths;

        XLSX.utils.book_append_sheet(workbook, worksheet, 'Resumen de clientes');

        XLSX.writeFile(workbook, 'resumen_clientes.xlsx');
    },

    exportarPDF() {
        const pdf = new jsPDF();

        pdf.setFont('helvetica');
        const titulo = 'Resumen de clientes';
        const fecha = `Fecha: ${new Date().toLocaleDateString('es-ES', { year: 'numeric', month: 'numeric', day: 'numeric' })}`;
        pdf.setFont('helvetica');

        const pageSize = pdf.internal.pageSize;
        const pageWidth = pageSize.width;
        const textWidth = pdf.getStringUnitWidth(titulo) * pdf.internal.getFontSize() / pdf.internal.scaleFactor;

        const xPosition = (pageWidth - textWidth) / 2;

        pdf.text(titulo, xPosition, 10);
        pdf.text(fecha, 15, 20);

        const tableYPosition = 30;

        const columns = ['Codigo Cliente', 'Nombre cliente', 'Num Documento', 'Total Ventas', 'Cobros', 'Num Cuotas', 'Total Deuda'];
        const rows = this.clientes.map(datos => [datos.cliente_id, datos.cliente_nombre, datos.num_documento, datos.total_ventas, datos.cobros, datos.numero_cuotas, datos.total_deuda]);
        pdf.autoTable({ head: [columns], body: rows, startY: tableYPosition });

        pdf.setFont('helvetica');
        pdf.save('resumen_cliente.pdf');
    },

},
mounted() {
        this.selectSucursal();
        this.selectEjecutivos();
        this.obtenerClientesPorVendedor();
    },
}
</script>