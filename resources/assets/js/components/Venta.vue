<template>
    <main class="main">
        <div class="loading-overlay" v-if="isLoading">
            <div class="loading-container">
                <div class="spinner"></div>
                <div class="loading-text">LOADING...</div>
            </div>
        </div>
        <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 10px;"
            appendTo="body" :baseZIndex="99999"></Toast>
        <Panel class="ingreso-panel">
            <template #header>
                <div class="panel-header">
                    <i class="pi pi-shopping-cart panel-icon"></i>
                    <h4 class="panel-title">VENTAS DIRECTAS</h4>
                </div>
            </template>
            <!--<span class="badge bg-secondary" id="comunicacionSiat" style="color: white;" v-show="mostrarElementos">Desconectado</span>
            <span class="badge bg-secondary" id="cuis" v-show="mostrarElementos">CUIS: Inexistente</span>
            <span class="badge bg-secondary" id="cufd" v-show="mostrarElementos">No existe cufd vigente</span>
            <span class="badge bg-secondary" id="direccion" v-show="mostrarDireccion">No hay dirección registrada</span>
            <span class="badge bg-primary" id="cufdValor" v-show="mostrarCUFD">No hay CUFD</span>-->

            <!-- Listado-->
            <template v-if="listado == 1">
                <div class="d-flex align-items-center mb-2 justify-content-between">
                    <div class="d-flex align-items-center">
                        <!--<span :class="[
                        'badge',
                        estadoFactVisual === 'online' ? 'bg-success' : 'bg-secondary',
                        'd-flex',
                        'align-items-center',
                        ]"
                        style="font-size: 0.85rem; padding: 0.3em 0.7em; min-width: 120px; justify-content: center; gap: 0.4em;">
                        <i v-if="cargandoFactVisual" class="pi pi-spin pi-spinner" style="font-size: 1em;"></i>
                        <i v-else-if="estadoFactVisual === 'online'" class="pi pi-check" style="font-size: 1em;"></i>
                        <i v-else class="pi pi-times" style="font-size: 1em;"></i>
                        {{
                            cargandoFactVisual
                            ? "FACTURACION ONLINE"
                            : estadoFactVisual === "online"
                                ? "FACTURACION ONLINE"
                                : "FACTURACION OFFLINE"
                        }}
                        </span>
                        <button @click="ejecutarSecuencial" class="btn btn-light btn-sm ms-2"
                        style="margin-left: 8px; padding: 2px 7px; font-size: 0.9em; border-radius: 4px; border: 1px solid #ccc;">
                        <i class="pi pi-refresh"></i>
                        </button>-->
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <!--<button :class="[
                        'btn',
                        'btn-sm',
                        filtroVentasActivo === 'factura'
                            ? 'btn-primary'
                            : 'btn-outline-primary',
                        ]" style="margin-left: 8px; min-width: 70px; font-size: 0.85em; padding: 2px 10px;" @click="
                        filtroVentasActivo = 'factura';
                        listarVentaF(1, buscar, criterio);
                        ">
                        FACTURA
                        </button>-->
                        <button :class="[
                            'btn',
                            'btn-sm',
                            filtroVentasActivo === 'recibo'
                                ? 'btn-primary'
                                : 'btn-outline-primary',
                        ]" style="margin-left: 4px; min-width: 70px; font-size: 0.85em; padding: 2px 10px;" @click="cambiarTipoventa('Recibo', buscar, criterio);
                        ">
                            RECIBO
                        </button>
                    </div>
                </div>
                <div class="toolbar-container" style="margin-top: 0; padding-top: 0;">
                    <div class="search-bar">
                        <span class="p-input-icon-left">
                            <i class="pi pi-search" />
                            <InputText v-model="buscar" @input="buscarVenta" placeholder="Texto a buscar"
                                class="p-inputtext-sm" />
                        </span>
                    </div>
                    <div class="toolbar">
                        <Button @click="abrirTipoVenta" :label="mostrarLabel ? 'Nuevo' : ''" icon="pi pi-plus"
                            class="p-button-primary p-button-sm" />
                    </div>
                </div>

                <div>
                    <DataTable responsiveLayout="scroll" class="p-datatable-gridlines p-datatable-sm tabla-venta"
                        :value="arrayVenta">
                        <Column header="Opciones">
                            <template #body="slotProps">
                                <!-- Botón para ver la venta -->
                                <Button icon="pi pi-eye" @click="verVenta(slotProps.data.id)"
                                    class="p-button-sm p-button-success btn-mini" />

                                <!-- Botón para desactivar la venta, solo para recibos y estado registrado -->
                                <template v-if="slotProps.data.estado === 'Registrado' && idrol !== 2">
                                    <Button icon="pi pi-trash" v-if="slotProps.data.tipo_comprobante === 'RESIVO'"
                                        @click="desactivarVenta(slotProps.data.id)"
                                        class="p-button-sm p-button-danger btn-mini" />
                                </template>

                                <!-- Botón para imprimir recibos -->
                                <Button icon="pi pi-print" v-if="slotProps.data.tipo_comprobante === 'RESIVO'"
                                    @click="imprimirResivo(slotProps.data.id, slotProps.data.correo)"
                                    class="p-button-sm p-button-primary btn-mini" />

                                <!-- Botones para acciones de factura -->
                                <template v-if="slotProps.data.tipo_comprobante === 'FACTURA'">
                                    <Button icon="pi pi-check"
                                        @click="verificarFactura(slotProps.data.cuf, slotProps.data.numeroFactura)"
                                        class="p-button-sm btn-mini" />
                                    <Button icon="pi pi-print"
                                        @click="imprimirFactura(slotProps.data.idFactura, slotProps.data.correo)"
                                        class="p-button-sm p-button-primary btn-mini" />
                                    <Button icon="pi pi-trash"
                                        @click="anularFactura(slotProps.data.idFactura, slotProps.data.cuf)"
                                        class="p-button-sm p-button-danger btn-mini" />
                                </template>
                            </template>
                        </Column>
                        <Column field="fecha_hora" header="Fecha y Hora" class="d-none d-md-table-cell"></Column>
                        <Column field="num_comprobante" header="N° de Factura" class="d-none d-md-table-cell"></Column>
                        <Column field="nombreSucursal" header="Sucursal"></Column>
                        <Column field="razonSocial" header="Cliente"></Column>
                        <Column field="documentoid" header="Documento" class="d-none d-md-table-cell"></Column>
                        <Column header="Total" bodyClass="text-right font-weight-bold"
                            headerClass="text-right font-weight-bold">
                            <template #body="slotProps">
                                {{ (slotProps.data.total * parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1]
                                }}
                            </template>
                        </Column>
                        <Column field="usuario" header="Vendedor"></Column>
                        <Column header="Estado" headerClass="text-center" bodyClass="text-center">
                            <template #body="slotProps">
                                <Tag class="tag-mini" :icon="(slotProps.data.estado === 'Registrado' || slotProps.data.estado === '1')
                                    ? 'pi pi-check'
                                    : (slotProps.data.estado === 4 || slotProps.data.estado === '4')
                                        ? 'pi pi-clock'
                                        : 'pi pi-times'
                                    " :severity="(slotProps.data.estado === 'Registrado' || slotProps.data.estado === '1')
                                        ? 'success'
                                        : (slotProps.data.estado === 4 || slotProps.data.estado === '4')
                                            ? 'warning'
                                            : 'danger'
                                        " />
                            </template>
                        </Column>
                    </DataTable>
                    <Paginator :rows="12" :totalRecords="pagination.total" :first="(pagination.current_page - 1) * 12"
                        @page="onPageChange" />
                </div>
            </template>



            <!--Fin Listado-->

            <!--Ver ingreso-->
            <template v-else-if="listado == 2">
                <div class="detalle-venta-pro">
                    <div class="detalle-header-pro">
                        <div class="detalle-section-pro">
                            <h3 class="detalle-titulo-pro">Detalle de Comprobante</h3>
                            <p class="detalle-subtitulo-pro">
                                Resumen completo de la venta registrada
                            </p>
                        </div>
                        <div class="detalle-meta-pro">
                            <div>
                                <span class="label-pro">Tipo Comprobante</span>
                                <p class="valor-pro">{{ tipo_comprobante }}</p>
                            </div>
                            <div>
                                <span class="label-pro">N° Comprobante</span>
                                <p class="valor-pro">#{{ num_comprobante }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- CLIENTE -->
                    <div class="detalle-cliente-pro">
                        <span class="label-pro">Cliente</span>
                        <p class="valor-pro">{{ cliente }}</p>
                    </div>
                    <div class="detalle-tabla-pro">
                        <DataTable :value="arrayDetalle" class="p-datatable-sm p-datatable-gridlines tabla-venta">
                            <Column field="articulo" header="Artículo"></Column>
                            <Column header="Precio">
                                <template #body="slotProps">
                                    {{ (slotProps.data.precio * parseFloat(monedaVenta[0])).toFixed(2) }} {{
                                        monedaVenta[1] }}
                                </template>
                            </Column>
                            <Column field="cantidad" header="Cantidad"></Column>
                            <Column header="Subtotal">
                                <template #body="slotProps">
                                    {{ ((slotProps.data.precio * slotProps.data.cantidad) *
                                        parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1] }}
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <div class="detalle-resumen-pro">
                        <div class="resumen-linea-pro total-final-pro">
                            <span>Total Neto</span>
                            <strong>{{ (total * parseFloat(monedaVenta[0])).toFixed(2) }}
                                {{ monedaVenta[1] }}</strong>
                        </div>
                    </div>
                    <div class="detalle-footer-pro">
                        <Button @click="ocultarDetalle()" label="Cerrar" icon="pi pi-times" severity="danger"
                            class="p-button-danger p-button-sm btn-mini" />
                    </div>
                </div>

            </template>
        </panel>
        <!-- HASTA AQUI DEVOLUCIONES -->
        <template>
            <Dialog :visible.sync="modal2" :containerStyle="dialogContainerStyle" :modal="true" :closable="false"
                :closeOnEscape="false" class="responsive-dialog">
                <template #header>
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Venta</h5>
                        <button class="close-button" @click="modal2 = false">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </template>

                <div class="p-fluid">
                    <div v-if="step === 2" class="step-content p-fluid">
                        <div class="p-grid p-formgrid align-items-start">
                            <div class="p-col-12 p-md-6 d-flex flex-column justify-content-start">
                                <h5 class="mb-3"
                                    style="font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem;">
                                    DATOS DEL CLIENTE
                                </h5>
                                <div style="width: 100%; padding-top: 0.5rem;">
                                    <div class="p-mb-3" style="margin-bottom: 1.5rem; position: relative;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: space-between;">
                                            <label for="nombre" class="label-input">
                                                Documento del Cliente
                                                <span class="text-required">*</span>
                                            </label>
                                            <!-- 🔘 Botón que alterna CI / NIT -->
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                @click="alternarTipoDocumento"
                                                style="font-size: 0.8rem; padding: 2px 8px; border-radius: 6px;">
                                                {{ tipoDocumentoTexto }}
                                            </button>
                                        </div>
                                        <div class="input-con-desplegable">
                                            <div class="p-inputgroup">
                                                <InputText ref="inputDocumentoCliente" id="documento"
                                                    v-model="documento" class="input-full"
                                                    @input="buscarClientePorDocumento"
                                                    @keydown.down="moverSeleccionCliente('abajo')"
                                                    @keydown.up="moverSeleccionCliente('arriba')"
                                                    @keydown.enter="seleccionarClienteConEnter($event)"
                                                    placeholder="Buscar cliente por documento o nombre"
                                                    autocomplete="off" style="margin-top: 2px" />
                                            </div>

                                            <!-- 🔽 Desplegable de clientes -->
                                            <ul v-if="mostrarDesplegableCliente" class="desplegable-simple"
                                                style="position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; margin-top: 2px; border-radius: 4px; padding: 0;">
                                                <li v-for="(cliente, index) in resultadosClientes" :key="cliente.id"
                                                    @click="seleccionarCliente(cliente)" :class="{
                                                        seleccionado: index === indiceSeleccionadoCliente,
                                                    }" style="padding: 8px; cursor: pointer; list-style: none;">
                                                    {{ cliente.nombre }} - {{ cliente.num_documento }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="p-mb-3" style="margin-bottom: 1.5rem; position: relative;">
                                        <label class="label-input">
                                            Razón Social <span class="text-required">*</span>
                                        </label>
                                        <span class="p-float-label">
                                            <InputText ref="inputNombreCliente" id="nombreCliente"
                                                v-model="nombreCliente" class="input-full"
                                                :disabled="!nombreClienteEditable" @input="mensajeRazonSocial = false"
                                                autocomplete="off" style="margin-top: 2px" />
                                        </span>

                                        <!-- 🔹 Mensaje temporal en amarillo -->
                                        <span v-if="
                                            nombreClienteEditable && nombreCliente.trim() === ''
                                        "
                                            style="color: #FFA500; font-size: 0.75rem; position: absolute; top: 70%; left: 12px; transform: translateY(-50%);pointer-events: none;">
                                            Ingrese la razón social del cliente
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <!-- Columna para las opciones de pago -->
                            <div class="p-col-12 p-md-6 d-flex flex-column justify-content-start">
                                <div v-if="tipoVenta === 'contado'">
                                    <!-- Botones de pago -->
                                    <div class="d-flex justify-content-center mb-1">
                                        <!-- Ajustado margen inferior de botones -->
                                        <div class="form-group">
                                            <div class="btn-group">
                                                <button class="btn btn-primary" @click="opcionPago = 'efectivo'">
                                                    <i class="fa fa-money mr-2" aria-hidden="true"></i>
                                                    Efectivo
                                                </button>
                                                <button class="btn btn-primary" @click="opcionPago = 'qr'">
                                                    <i class="fa fa-qrcode mr-2" aria-hidden="true"></i>
                                                    QR
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sección para pago en efectivo -->
                                    <div v-if="opcionPago === 'efectivo'" style="margin-top: -5px;">
                                        <div class="card mb-2" style="font-size: 0.875rem;">
                                            <div class="card-body">
                                                <!-- Monto Recibido -->
                                                <div class="form-group">
                                                    <label for="montoEfectivo" class="font-weight-bold">
                                                        <i class="fa fa-money mr-2"></i> Monto Recibido:
                                                    </label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                {{ monedaVenta[1] }}
                                                            </span>
                                                        </div>
                                                        <input type="number" class="form-control" id="montoEfectivo"
                                                            v-model="recibido"
                                                            placeholder="Ingrese el monto recibido" />
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="cambioRecibir" class="font-weight-bold">
                                                        <i class="fa fa-exchange mr-2"></i> Cambio a
                                                        Entregar:
                                                    </label>
                                                    <input type="text" class="form-control bg-light" id="cambioRecibir"
                                                        :value="(
                                                            recibido -
                                                            calcularTotal * parseFloat(monedaVenta[0])
                                                        ).toFixed(2)
                                                            " readonly />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card" style="font-size: 0.75rem;">
                                            <div class="card-body">
                                                <h5 class="mb-2 text-center text-md-left" style="font-size: 0.95rem;">
                                                    Detalle de Venta
                                                </h5>

                                                <!-- Contenedor del total y el botón -->
                                                <div
                                                    class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                                    <!-- Total a pagar -->
                                                    <div class="d-flex align-items-center mb-2 mb-md-0">
                                                        <i class="fa fa-money mr-2" style="font-size: 0.75rem;"></i>
                                                        <span style="font-size: 0.75rem;">Total a
                                                            Pagar:&nbsp;&nbsp;</span>
                                                        <span class="font-weight-bold h5 mb-0"
                                                            style="font-size: 0.95rem; line-height: 1;">
                                                            {{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2)
                                                            }} {{
                                                                monedaVenta[1] }}
                                                        </span>
                                                    </div>

                                                    <!-- Botón de pago -->
                                                    <button type="button" @click="aplicarDescuento"
                                                        class="btn btn-success">
                                                        <i class="fa fa-check mr-2"></i> Registrar Pago
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sección para pago con QR -->
                                    <div v-else-if="opcionPago === 'qr'" style="margin-top: -5px;">
                                        <div class="card">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <!-- 🔹 TOTAL Y ACCIÓN -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2">

                                                    <div>
                                                        <small class="text-muted">Total a pagar</small>
                                                        <div class="font-weight-bold text-primary"
                                                            style="font-size: 1.1rem;">
                                                            {{ calcularTotal.toFixed(2) }} {{ monedaVenta[1] }}
                                                        </div>
                                                    </div>

                                                    <!--<button class="btn btn-primary" @click="generarQr">
                            <i class="fa fa-qrcode mr-2"></i> Generar QR
                          </button>-->

                                                </div>
                                                <!--<button class="btn btn-primary mb-2" @click="generarQr">Generar QR</button>
                                                <div v-if="qrImage" class="mb-2 text-center">
                                                    <img :src="qrImage" alt="Código QR" class="img-fluid" />
                                                </div>
                                                <button class="btn btn-secondary mb-2" @click="verificarEstado" v-if="qrImage">Verificar Estado de Pago</button>
                                                <div v-if="estadoTransaccion" class="card p-2">
                                                    <div class="font-weight-bold">Estado Actual:</div>
                                                    <div>
                                                        <span :class="'badge badge-' + badgeSeverity">{{ estadoTransaccion.objeto.estadoActual }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <button class="btn btn-light" @click="aplicarDescuentoRecibo(7)">
                                                        <img src="/img/Librerialogin.png" alt="Botón Imagen" class="img-fluid" style="height: 24px;">
                                                    </button>
                                                </div>
                                                <div>
                                                    <button type="button" @click="aplicarDescuento(7)" class="btn btn-success">
                                                        <i class="fa fa-check mr-2"></i> Registrar Pago
                                                    </button>
                                                </div>-->
                                                <!-- 🔹 BOTONES -->
                                                <div class="d-flex justify-content-end">

                                                    <button type="button" @click="aplicarDescuento(7)"
                                                        class="btn btn-success">
                                                        <i class="fa fa-check mr-2"></i> Registrar Pago
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="step === 1" class="step-content">
                        <div class="p-fluid p-grid">
                            <div class="p-col-12 p-md-6" style="flex: 0 0 30%;">
                                <label for="tipo_documento" class="label-input">
                                    Almacén de trabajo <span class="text-required">*</span>
                                </label>
                                <Dropdown v-model="selectedAlmacen" :options="arrayAlmacenes"
                                    optionLabel="nombre_almacen" optionValue="id" placeholder="Seleccione un almacén"
                                    :disabled="arrayDetalle.length > 0" @change="getAlmacenProductos"
                                    class="dropdown-full" />
                            </div>

                            <div class="p-col-12 p-md-6" style="flex: 0 0 70%;">
                                <label for="nombre" class="label-input">
                                    Buscar Producto
                                </label>
                                <div class="input-con-desplegable">
                                    <div class="p-inputgroup">
                                        <InputText ref="inputCodigo" v-model="codigo" placeholder="Buscar..."
                                            class="input-full" :disabled="!idAlmacen" @input="buscarArticulo"
                                            @keydown.down="moverSeleccion('abajo')"
                                            @keydown.up="moverSeleccion('arriba')"
                                            @keydown.enter="seleccionarConEnter" />
                                        <Button icon="pi pi-search" @click="abrirModal" />
                                    </div>

                                    <ul v-if="mostrarDesplegable" class="desplegable-simple"
                                        style="max-height: 300px; overflow-y: auto;">
                                        <li v-for="(articulo, index) in resultadosBusqueda" :key="articulo.id"
                                            @click="seleccionarArticulo(articulo)" :class="{
                                                'seleccionado': index === indiceSeleccionado,
                                                'item-sin-stock': articulo.saldo_stock <= 0
                                            }"
                                            style="padding: 8px 12px; border-bottom: 1px solid #eee; cursor: pointer;">

                                            <div class="item-contenido" style="line-height: 1.4; font-size: 0.85rem;">

                                                <span style="font-weight: bold; color: #333; font-size: 0.9rem;">
                                                    {{ articulo.nombre }}
                                                </span>

                                                <span style="color: #bbb; margin: 0 5px;">/</span>

                                                <span class="text-muted">
                                                    {{ articulo.nombre_proveedor || "Sin Lab." }}
                                                </span>

                                                <span style="color: #bbb; margin: 0 5px;">/</span>

                                                <span class="text-muted">
                                                    {{ articulo.nombre_categoria || "Sin Cat." }}
                                                </span>

                                                <span style="color: #bbb; margin: 0 5px;">/</span>

                                                <span class="text-primary font-weight-bold">
                                                    {{ Number(parseFloat(articulo.precio_uno).toFixed(2)) }} Bs.
                                                </span>

                                                <span style="color: #bbb; margin: 0 5px;">/</span>

                                                <span
                                                    :class="articulo.saldo_stock > 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'">
                                                    Stock: {{ articulo.saldo_stock }}
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- nuevos botones para las opciones de tipo de precio 
                        <div class="precio-tipo-selector">
                            <div class="d-flex justify-content-center">
                                <div class="btn-group flex-wrap" role="group">
                                    <button type="button" class="btn px-3 py-2 text-nowrap"
                                        :class="tipoPrecioSeleccionado === 'factura' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="seleccionarTipoPrecio('factura')">
                                        Factura
                                    </button>
                                    <button type="button" class="btn px-3 py-2 text-nowrap"
                                        :class="tipoPrecioSeleccionado === 'sin_factura' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="seleccionarTipoPrecio('sin_factura')">
                                        S/Factura
                                    </button>
                                    <button type="button" class="btn px-3 py-2 text-nowrap"
                                        :class="tipoPrecioSeleccionado === 'mayor' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="seleccionarTipoPrecio('mayor')">
                                        Por Mayor
                                    </button>
                                </div>
                            </div>
                        </div>-->
                        <!-- 🛒 ESTADO: CARRITO VACÍO -->
                        <div v-if="arrayDetalle.length === 0"
                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 250px; padding: 1rem;">
                            <i class="pi pi-shopping-cart"
                                style="font-size: 2.5rem; color: #ccc; margin-bottom: 0.8rem; opacity: 0.6;"></i>
                            <h4 style="color: #666; font-weight: 500; margin: 0; font-size: 1.1rem;">
                                Carrito de ventas vacío
                            </h4>
                            <p style="color: #999; font-size: 0.9rem; margin-top: 0.5rem;">
                                Agregue productos para comenzar
                            </p>
                        </div>
                        <DataTable v-if="arrayDetalle.length > 0" :value="arrayDetalle" class="p-mt-3"
                            responsiveLayout="scroll">
                            <Column header="Opciones" style="width: 10%">
                                <template #body="slotProps">
                                    <Button icon="pi pi-trash" class="p-button-danger p-button-sm btn-mini"
                                        @click="slotProps.data.medida != 'KIT' ? eliminarDetalle(slotProps.data.id) : eliminarKit(slotProps.data.idkit)" />
                                </template>
                            </Column>
                            <Column field="articulo" header="Artículo" style="width: 30%" />

                            <Column header="Stock Disp." style="width: 100px; text-align: center;">
                                <template #body="slotProps">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge badge-info mb-1">{{ slotProps.data.stock }} Unds.</span>
                                        <!--<small v-if="slotProps.data.unidad_envase > 1" class="text-muted" style="font-size: 0.7em;">
                                                (Max: {{ Math.floor(slotProps.data.stock / slotProps.data.unidad_envase) }} Cajas)
                                            </small>-->
                                    </div>
                                </template>
                            </Column>
                            <Column field="precioUnidad" header="Precio Unitario" style="width: 15%">
                                <template #body="slotProps">
                                    <input type="text" v-model="slotProps.data.precioseleccionado"
                                        @input="actualizarDetalle(slotProps.index, slotProps.data.precioseleccionado)"
                                        class="form-control form-control-sm text-center font-weight-bold" />
                                </template>
                            </Column>
                            <Column field="unidades" header="Cantidad" style="width: 15%">
                                <template #body="slotProps">
                                    <InputNumber v-model="slotProps.data.cantidad" :min="1"
                                        @input="actualizarDetalle(slotProps.index)"
                                        class="p-inputtext-sm input-unidades" />
                                </template>
                            </Column>
                            <Column field="total" header="Total" style="width: 20%">
                                <template #body="slotProps">
                                    {{ (slotProps.data.precioseleccionado * slotProps.data.cantidad *
                                        parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1] }}
                                </template>
                            </Column>
                        </DataTable>

                        <div v-if="arrayDetalle.length > 0" class="p-grid p-mt-3">
                            <div class="p-col-12 p-md-8"></div>
                            <div class="p-col-12 p-md-4" style="text-align: right;">
                                <h5>
                                    Total Neto:
                                    {{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2) }}
                                    {{ monedaVenta[1] }}
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="buttons d-flex justify-content-center">
                        <button class="btn btn-primary mr-2" @click="prevStep" :disabled="step === 1">Anterior</button>
                        <button class="btn btn-primary" @click="validarYAvanzar"
                            :disabled="step === 2">Siguiente</button>
                    </div>
                </div>


            </Dialog>

        </template>

        <template>
            <Dialog :visible="modal" :containerStyle="dialogContainerStyle" style="padding-top: 35px;" :modal="true"
                :closable="false" class="responsive-dialog">
                <template #header>
                    <h3>{{ tituloModal }}</h3>
                </template>

                <TabView>
                    <TabPanel header="Articulos">
                        <div class="p-field p-col-12" style="width: 100%; margin: 0; padding: 0;">
                            <div class="p-inputgroup" style="width: 100%;">
                                <InputText id="buscarA" v-model="buscarA" class="input-full" autocomplete="off"
                                    style="width: 100%; margin: 0;" @input="listarArticulo(buscarA)" />
                                <!-- Texto informativo -->
                                <span v-if="buscarA.trim() === ''"
                                    style="color: #FFA500; font-size: 0.75rem; position: absolute; left: 45px; top: 39.5%; transform: translateY(-20%); pointer-events: none;">
                                    Búsqueda por nombre o código
                                </span>
                                <Button icon="pi pi-refresh" class="p-button-secondary p-button-sm" @click="
                                    buscarA = '';
                                listarArticulo('');
                                " type="button" :disabled="!buscarA" :style="{ minWidth: '36px' }" title="Limpiar" />
                            </div>
                        </div>

                        <DataTable :value="arrayArticulo" :paginator="true" :rows="10"
                            class="p-mt-2 p-datatable-gridlines p-datatable-sm tabla-venta" responsiveLayout="scroll">
                            <Column header="Opciones">
                                <template #body="slotProps">
                                    <Button icon="pi pi-check" class="p-button-success p-button-sm btn-mini"
                                        @click="agregarDetalleModal(slotProps.data)" />
                                </template>
                            </Column>
                            <Column field="codigo" header="Código" />
                            <Column field="nombre" header="Nombre" />
                            <Column field="nombre_proveedor" header="Proveedor" />
                            <Column header="Precio Venta">
                                <template #body="slotProps">
                                    {{ (slotProps.data.precio_uno * parseFloat(monedaVenta[0])).toFixed(2) }} {{
                                        monedaVenta[1] }}
                                </template>
                            </Column>
                            <Column field="saldo_stock" header="Stock" />
                            <Column header="Estado">
                                <template #body="slotProps">
                                    <Tag class="tag-mini" :severity="slotProps.data.condicion ? 'success' : 'danger'"
                                        :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'" />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabView>

                <template #footer>
                    <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-secondary btn-sm" />
                    <Button v-if="tipoAccion === 1" label="Guardar" icon="pi pi-check" @click="registrarPersona" />
                    <Button v-if="tipoAccion === 2" label="Actualizar" icon="pi pi-check" @click="actualizarPersona" />
                </template>
            </Dialog>
        </template>
    </main>
</template>

<script>
import Dropdown from "primevue/dropdown";
import Swal from "sweetalert2";
import InputSwitch from "primevue/inputswitch";
import ProgressBar from "primevue/progressbar";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Steps from "primevue/steps";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import Tag from "primevue/tag";
import SelectButton from "primevue/selectbutton";
import InputNumber from "primevue/inputnumber";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import ToastService from "primevue/toastservice";
import Toast from "primevue/toast";
import Tooltip from "primevue/tooltip";

export default {
    components: {
        Dropdown,
        DataTable,
        Column,
        Button,
        Paginator,
        Card,
        InputText,
        Button,
        Panel,
        Steps,
        Message,
        Dialog,
        Tag,
        SelectButton,
        InputNumber,
        TabView,
        TabPanel,
        ProgressBar,
        InputSwitch,
        ToastService,
        Toast,
    },
    data() {
        return {
            isLoading: false,
            filtroVentasActivo: "recibo",
            mostrarLabel: true,

            opcionesPago: [
                { label: 'Efectivo', value: 'efectivo' },
                { label: 'QR', value: 'qr' }
            ],
            criterioOptions: [
                { label: 'Nombre', value: 'nombre' },
                { label: 'Descripción', value: 'descripcion' },
                { label: 'Código', value: 'codigo' }
            ],
            isDialogVisible: false,
            tipoComprobanteOptions: [
                { name: 'RECIBO', code: 'RESIVO' },
                { name: 'FACTURA', code: 'FACTURA' }
            ],
            opcionPago: 'efectivo',
            tipoVenta: 'contado',
            tipocompro: 'Recibo',

            isLoading: false,
            selectedAlmacen: 1,
            idrol: null,
            step: 1,
            modal2: false,
            modal: false,
            zIndexBase: 1050,
            //qr
            alias: '',
            montoQR: 0,
            qrImage: '',
            aliasverificacion: '',
            estadoTransaccion: null,
            currency: 'BOB', // Define tu moneda
            resivo: "",
            clienteDeudas: 0,
            arrayCuotas: [],
            arraySeleccionado: [],
            cuotaSeleccionada: null,
            modalCuotas: 0,

            tipo_pago: "",
            criterioKit: "nombre",
            buscarKit: "",

            mensajesKit: [],
            arrayArticulosKit: [],
            datosFormularioKit: [],
            modalDetalleKit: 0,
            arrayKit: [],

            arrayPreciosEspeciales: [],
            modalDetalle: 0,
            datosFormularioPE: [],
            arrayArticulosPE: [],

            arrayPromocion: [],
            unidadPaquete: 1,
            tipoVentaOptions: [
                { label: 'Por unidad', value: 0 },
                { label: 'Por paquete', value: 1 }
            ],

            monedaVenta: [],
            permitirDevolucion: "",
            saldosNegativos: 1,
            venta_id: 0,
            idcliente: 0,
            usuarioAutenticado: null,
            puntoVentaAutenticado: null,
            idsucursalAutenticado: null,

            cliente: "",
            email: "",
            nombreCliente: "",
            nombreClienteEditable: false,
            documento: "",
            tipo_documento: "1",
            complemento_id: "",
            tipoDocumentoTexto: "CI",
            mensajeRazonSocial: false, // 🔹 Nueva variable
            resultadosClientes: [],
            mostrarDesplegableCliente: false,
            indiceSeleccionadoCliente: -1,
            buscarTimeout: null,
            resultadosBusqueda: [],
            mostrarDesplegable: false,
            indiceSeleccionado: -1,
            debounceTimer: null,

            descuentoAdicional: 0.0,
            descuentoGiftCard: "",
            tipo_comprobante: "RESIVO",
            serie_comprobante: "",
            last_comprobante: 0,
            num_comprob: "",
            impuesto: 0.18,
            total: 0.0,
            totalImpuesto: 0.0,
            totalParcial: 0.0,
            arrayVenta: [],
            arrayCliente: [],
            arrayDetalle: [],
            arrayProductos: [],
            arrayFactura: [],
            listado: 1,
            tituloModal: "",
            tipoAccion: 0,
            errorVenta: 0,
            errorMostrarMsjVenta: [],
            pagination: {
                total: 0,
                current_page: 1,
                last_page: 0, // Asegúrate de actualizar este valor al obtener datos
            },
            offset: 3,
            criterio: "",
            buscar: "",
            criterioA: "",
            buscarA: "",
            arrayArticulo: [],
            arraySeleccionado: [],

            idarticulo: 0,
            codigo: "",
            articulo: "",
            medida: "",
            codigoClasificador: "",
            codigoProductoSin: "",
            precio: 0,
            unidad_envase: 0,
            cantidad: 1,
            paquni: "",
            precioBloqueado: false,
            descuento: 0,
            descuentoProducto: 0,
            sTotal: 0,
            stock: 0,
            valorMaximoDescuento: "",
            mostrarDireccion: true,

            casosEspeciales: false,
            mostrarCampoCorreo: false,
            leyendaAl: "",
            codigoExcepcion: 0,
            isLoading: false,
            primer_precio_cuota: 0,
            numeroTarjeta: null,
            metodoPago: "",
            criterioVenta: "ci",
            //almacenes
            arrayAlmacenes: [],
            almacenSeleccionado: null,
            almacenPredeterminadoId: null,
            idAlmacen: 1,
            //-----PRECIOS- AUMENTE 3/OCTUBRE--------
            precioseleccionado: "",
            //precio : '',
            arrayPrecios: [],
            nombre_precio: "",
            precio_uno: "",
            precio_dos: "",
            precio_tres: "",
            precio_cuatro: "",
            tipoPrecioSeleccionado: 'factura', // Valor predeterminado: precio con factura
            //-----MODAL 2---

            tituloModal2: "",
            tipoAccion2: "",

            modal3: 0,
            tituloModal3: "",
            tipoAccion3: "",

            recibido: 0,
            efectivo: 0,
            cambio: 0,
            faltante: 0,
            cantidadClientes: 0,
            idtipo_pago: "",
            idtipo_venta: 1,
            tiempo_diaz: "",
            numero_cuotas: "",
            cuotas: [], //---para almacenar las fechas
            estadocrevent: "activo",
            primera_cuota: "",
            habilitarPrimeraCuota: false,
            tipoPago: "EFECTIVO",
            mostrarElementos: true,
            mostrarCUFD: true,
            idPago: '',
            tiposPago: {
                EFECTIVO: 1,
                TARJETA: 2,
                QR: 7,
            },
        };
    },

    watch: {
        codigo(newValue) {
            if (newValue) {
                this.buscarArticulo();
            }
        },
        documento(newDocumento) {
            this.mostrarCampoCorreo =
                newDocumento === "99002" || newDocumento === "99003";
        },
    },
    computed: {
        dialogContainerStyle() {
            if (window.innerWidth <= 480) {
                return { width: "95vw", maxWidth: "95vw", margin: "0 auto" };
            } else if (window.innerWidth <= 768) {
                return { width: "90vw", maxWidth: "90vw", margin: "0 auto" };
            } else if (window.innerWidth <= 1024) {
                return { width: "85vw", maxWidth: "900px", margin: "0 auto" };
            } else {
                return { width: "1100px", maxWidth: "95vw", margin: "0 auto" };
            }
        },
        calcularStockDisponible() {
            return this.unidadPaquete == 1
                ? this.arraySeleccionado.saldo_stock - this.cantidad
                : this.arraySeleccionado.saldo_stock / this.arraySeleccionado.unidad_envase - this.cantidad;
        },

        resultadoMultiplicacion() {
            if (this.arraySeleccionado) {
                return this.precioseleccionado * this.unidadPaquete * this.cantidad;
            }
        },

        totalCantidades() {
            return this.arrayArticulosKit.reduce((total, articulo) => {
                return total + parseInt(articulo.cantidad);
            }, 0);
        },

        isActived: function () {
            return this.pagination.current_page;
        },

        //Calcula los elementos de la paginación
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

        badgeSeverity() {
            if (this.estadoTransaccion && this.estadoTransaccion.objeto.estadoActual === 'PENDIENTE') {
                return 'danger'; // Rojo para estado PENDIENTE
            } else if (this.estadoTransaccion && this.estadoTransaccion.objeto.estadoActual === 'PAGADO') {
                return 'success'; // Verde para estado PAGADO
            } else {
                return 'info'; // Otros estados
            }
        },
        calcularTotal() {
            var resultado = 0.0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                resultado +=
                    this.arrayDetalle[i].precioseleccionado *
                    this.arrayDetalle[i].cantidad -
                    (this.arrayDetalle[i].precioseleccionado *
                        this.arrayDetalle[i].cantidad *
                        this.arrayDetalle[i].descuento) /
                    100;
            }
            resultado -= this.descuentoAdicional;
            //resultado -= this.descuentoGiftCard;
            return resultado;
        },
    },

    methods: {
        handleClickFuera: function (event) {
            // 🔹 Solo hacer algo si el desplegable está visible
            if (!this.mostrarDesplegable) return;

            // 🔹 Referencia al input de búsqueda
            var buscador = this.$refs.inputCodigo
                ? this.$refs.inputCodigo.$el || this.$refs.inputCodigo
                : null;

            // 🔹 Referencia a la lista desplegable
            var lista = document.querySelector(".desplegable-simple");

            // 🔹 Si el clic fue dentro del buscador o dentro de la lista, no hacer nada
            if (
                (buscador && buscador.contains(event.target)) ||
                (lista && lista.contains(event.target))
            ) {
                return;
            }

            // 🔹 Cerrar el desplegable
            this.mostrarDesplegable = false;
            this.codigo = "";

            // 🔹 Enfocar el campo "cantidad" del primer artículo agregado
            var self = this;
            this.$nextTick(function () {
                if (self.arrayDetalle.length > 0) {
                    // 🔹 Primer artículo (índice 0)
                    var refInput = self.$refs["inputCantidad_0"];

                    if (refInput && refInput.$el) {
                        var inputElement = refInput.$el.querySelector("input");
                        if (inputElement) {
                            inputElement.focus();
                            inputElement.select(); // opcional: seleccionar el valor
                        }
                    }
                }
            });
        },
        handleResize() {
            this.mostrarLabel = window.innerWidth > 768; // cambia según breakpoint deseado
        },
        alternarTipoDocumento() {
            if (this.tipo_documento === 1) {
                this.tipo_documento = 5;
                this.tipoDocumentoTexto = "NIT";
            } else {
                this.tipo_documento = 1;
                this.tipoDocumentoTexto = "CI";
            }
        },
        handleKeyPress(event) {
            // Detectar Shift + R
            if (event.shiftKey && event.key === "B") {
                this.abrirModal();
            }
        },
        // metodo para escoger el tipo de precio
        seleccionarTipoPrecio(tipo) {
            this.tipoPrecioSeleccionado = tipo;

            // Si hay artículos en la tabla, preguntar si quiere actualizar sus precios
            if (this.arrayDetalle && this.arrayDetalle.length > 0) {
                this.actualizarTodosPreciosPorTipo();

                //if (confirm('¿Desea actualizar los precios de todos los artículos?')) {
                //}
            }
        },

        actualizarTodosPreciosPorTipo() {
            // Recorrer todos los artículos y actualizar sus precios
            for (let i = 0; i < this.arrayDetalle.length; i++) {
                const articulo = this.arrayDetalle[i];
                this.obtenerPrecioPorTipo(articulo.idarticulo, i);
            }
        },

        obtenerPrecioPorTipo(idArticulo, index) {
            let me = this;
            axios.get(`/ventas/obtener-precio-por-tipo?idArticulo=${idArticulo}&tipoPrecio=${this.tipoPrecioSeleccionado}`)
                .then(function (response) {
                    if (response.data.precio !== undefined) {
                        // Actualizar el precio del artículo
                        me.arrayDetalle[index].precioseleccionado = response.data.precio;
                        // Recalcular el total
                        me.actualizarDetalle(index, response.data.precio);
                    }
                })
                .catch(function (error) {
                    console.error('Error al obtener precio:', error);
                });
        },

        generarCuotas() {
            this.cuotas = [];
            const fechaHoy = new Date();

            const montoEntero = Math.floor(this.calcularTotal / this.numero_cuotas);
            const montoDecimal = (this.calcularTotal - montoEntero * (this.numero_cuotas - 1)).toFixed(2);

            let fechaInicioPago;
            let saldoRestante;
            let estadoCuota;

            if (this.primera_cuota) {
                const primerPago = Number(this.primer_precio_cuota) || 0;
                fechaInicioPago = fechaHoy;
                saldoRestante = (this.calcularTotal - primerPago).toFixed(2);
                estadoCuota = 'Pagado';

                const primeraCuota = {
                    fecha_pago: `${fechaHoy.getFullYear()}-${fechaHoy.getMonth() + 1}-${fechaHoy.getDate()} ${fechaHoy.toLocaleTimeString()}`,
                    precio_cuota: primerPago.toFixed(2),
                    totalCancelado: primerPago.toFixed(2),
                    saldo_restante: saldoRestante,
                    fecha_cancelado: `${fechaHoy.getFullYear()}-${fechaHoy.getMonth() + 1}-${fechaHoy.getDate()} ${fechaHoy.toLocaleTimeString()}`,
                    estado: 'Pagado',
                };

                this.cuotas.push(primeraCuota);

                const montoRestante = this.calcularTotal - primerPago;
                const montoEnteroRestante = Math.floor(montoRestante / (this.numero_cuotas - 1));
                const montoDecimalRestante = (montoRestante - montoEnteroRestante * (this.numero_cuotas - 2)).toFixed(2);

                saldoRestante = montoRestante;
                fechaInicioPago = new Date(fechaHoy.getTime() + this.tiempo_diaz * 24 * 60 * 60 * 1000);
                estadoCuota = 'Pendiente';

                for (let i = 1; i < this.numero_cuotas; i++) {
                    const fechaPago = new Date(fechaInicioPago.getTime() + (i - 1) * this.tiempo_diaz * 24 * 60 * 60 * 1000);
                    const dia = fechaPago.getDate();
                    const mes = fechaPago.getMonth() + 1;
                    const año = fechaPago.getFullYear();

                    const cuota = {
                        fecha_pago: `${año}-${mes}-${dia} ${fechaPago.toLocaleTimeString()}`,
                        precio_cuota: i === this.numero_cuotas - 1 ? parseFloat(montoDecimalRestante).toFixed(2) : montoEnteroRestante,
                        totalCancelado: 0,
                        saldo_restante: saldoRestante,
                        fecha_cancelado: null,
                        estado: 'Pendiente',
                    };

                    saldoRestante -= cuota.precio_cuota;
                    saldoRestante = saldoRestante.toFixed(2);

                    this.cuotas.push(cuota);
                }
            } else {
                fechaInicioPago = new Date(fechaHoy.getTime() + this.tiempo_diaz * 24 * 60 * 60 * 1000);
                saldoRestante = this.calcularTotal;
                estadoCuota = 'Pendiente';

                for (let i = 0; i < this.numero_cuotas; i++) {
                    const fechaPago = new Date(fechaInicioPago.getTime() + i * this.tiempo_diaz * 24 * 60 * 60 * 1000);
                    const dia = fechaPago.getDate();
                    const mes = fechaPago.getMonth() + 1;
                    const año = fechaPago.getFullYear();

                    const cuota = {
                        fecha_pago: `${año}-${mes}-${dia} ${fechaPago.toLocaleTimeString()}`,
                        precio_cuota: i === this.numero_cuotas - 1 ? parseFloat(montoDecimal).toFixed(2) : montoEntero,
                        totalCancelado: 0,
                        saldo_restante: saldoRestante,
                        fecha_cancelado: null,
                        estado: 'Pendiente',
                    };

                    saldoRestante -= cuota.precio_cuota;
                    saldoRestante = saldoRestante.toFixed(2);

                    this.cuotas.push(cuota);
                }
            }
        },
        seleccionarTipoVenta(tipo) {
            this.tipoVenta = 'contado';
            this.idtipo_venta = tipo === 'contado' ? 1 : 2;
            this.opcionPago = 'efectivo'; // Reinicia la opción de pago al cambiar el tipo de venta
        },
        buscarVenta() {
            this.listarVenta(1, this.buscar);
        },

        validarYAvanzar() {
            const errores = [];

            if (this.step === 2) {
                if (!this.idAlmacen) errores.push('Seleccione un almacén');
            }

            if (errores.length > 0) {
                const mensaje = errores.join('\n');
                swal('Campos incompletos', mensaje, 'warning');
            } else {
                this.nextStep();
            }
        },

        cerrarModal2() {
            this.modal2 = false;
        },
        nextStep() {
            if (this.step < 3) {
                this.step++;
            }
        },
        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },

        actualizarFechaHora() {
            const now = new Date();
            this.alias = now.toLocaleString();
        },
        verificarEstado() {
            axios.post('/qr/verificarestado', {
                alias: this.aliasverificacion,
            })
                .then(response => {
                    this.estadoTransaccion = response.data;
                })
                .catch(error => {
                    console.error(error);
                });
        },

        generarQr() {
            this.aliasverificacion = this.alias;
            axios.post('/qr/generarqr', {
                alias: this.alias,
                monto: this.calcularTotal
            })
                .then(response => {
                    const imagenBase64 = response.data.objeto.imagenQr;
                    this.qrImage = `data:image/png;base64,${imagenBase64}`;
                })
                .catch(error => {
                    console.error(error);
                });

            this.alias = '';
            this.montoQR = 0;
        },


        calcularPrecioUnitario(articulo) {
            // Lógica para calcular el precio unitario según el rango total de cantidades
            if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r1 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r1
            ) {
                return this.datosFormularioPE.precio_r1;
            } else if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r2 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r2
            ) {
                return this.datosFormularioPE.precio_r2;
            } else if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r3 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r3
            ) {
                return this.datosFormularioPE.precio_r3;
            } else {
                // Precio predeterminado si no está en ningún rango
                return articulo.precio_costo_unid;
            }
        },
        getClassByCantidad(total) {
            if (
                total >= this.datosFormularioPE.rango_inicio_r1 &&
                total <= this.datosFormularioPE.rango_final_r1
            ) {
                return "rango-1"; // clase para el rango 1
            } else if (
                total >= this.datosFormularioPE.rango_inicio_r2 &&
                total <= this.datosFormularioPE.rango_final_r2
            ) {
                return "rango-2"; // clase para el rango 2
            } else if (
                total >= this.datosFormularioPE.rango_inicio_r3 &&
                total <= this.datosFormularioPE.rango_final_r3
            ) {
                return "rango-3"; // clase para el rango 3
            } else {
                return ""; // clase por defecto si no se cumple ningún rango
            }
        },
        abrirTipoVenta() {
            if (this.idtipo_venta == 1) {
                this.modal2 = true;
                this.cliente = this.nombreCliente;
                this.tipoAccion2 = 1;
                this.scrollToTop();
            }
        },

        seleccionarTipoPago(tipo) {
            console.log("TIPO PAGO ", tipo)
            this.tipoPago = tipo;
            this.tituloModal2 = `TIPO DE PAGO : ${tipo}`;
            this.idtipo_pago = this.tiposPago[tipo];
        },

        agregarKit(kit) {
            if (new Date(kit.fecha_final) < new Date()) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "Este kit ha expirado!",
                });
                return;
            }
            //   this.GetValidateKit(kit['id'])
            this.GetValidateKit(kit["id"])
                .then(() => {
                    if (this.mensajesKit.length == 0) {
                        const totalKit = this.arrayArticulosKit.reduce(
                            (total, producto) => {
                                return total + producto.cantidad * producto.precio_costo_unid;
                            },
                            0
                        );
                        this.arrayArticulosKit.forEach((producto) => {
                            producto.porcentaje =
                                ((producto.cantidad * producto.precio_costo_unid) / totalKit) *
                                100;
                        });

                        this.arrayArticulosKit.forEach((producto) => {
                            producto.nuevo_precio = (kit.precio * producto.porcentaje) / 100;
                        });
                        console.log("Estos son los articulos: ", this.arrayArticulosKit);
                        this.arrayArticulosKit.forEach((articulo) => {
                            this.arrayDetalle.push({
                                idkit: kit["id"],
                                idarticulo: articulo.id,
                                articulo: articulo.nombre,
                                medida: "KIT",
                                unidad_envase: articulo.unidad_envase,
                                cantidad: articulo.cantidad,
                                cantidad_paquetes: articulo.unidad_envase * articulo.cantidad,
                                precio: articulo.nuevo_precio,
                                descuento: 0,
                                stock: articulo.stock,
                                precioseleccionado: articulo.precio_costo_unid,
                            });
                            let actividadEconomica = 461021;

                            this.arrayProductos.push({
                                actividadEconomica: actividadEconomica,
                                codigoProductoSin: articulo.id,
                                codigoProducto: articulo.codigo,
                                descripcion: articulo.nombre,
                                cantidad: articulo.cantidad,
                                unidadMedida: 25,
                                precioUnitario: parseFloat(
                                    articulo.precio_costo_unid * this.monedaVenta[0]
                                ).toFixed(2),
                                montoDescuento: (
                                    articulo.precio_costo_unid *
                                    articulo.cantidad *
                                    this.monedaVenta[0] -
                                    articulo.nuevo_precio * this.monedaVenta[0]
                                ).toFixed(2),
                                subTotal: parseFloat(
                                    articulo.nuevo_precio * this.monedaVenta[0]
                                ).toFixed(2),
                                numeroSerie: null,
                                numeroImei: null,
                            });
                            this.cerrarModal();
                        });

                    } else {
                        swal({
                            type: "error",
                            title: "Stock insuficiente",
                            text: this.mensajesKit.join("\n\n"),
                        });
                    }
                })
                .catch((error) => {
                    // Maneja el error aquí
                    console.error(error);
                });
        },

        agregarPE(kit) {
            console.log("esto:", kit);
            kit["articulos"] = this.arrayArticulosKit;
            kit["precio"] = kit["precio"] / parseFloat(this.monedaVenta[0]);
            axios.put("/ofertasespeciales/actualizar", kit);

            this.modalDetalle = 0;
            if (new Date(kit.fecha_final) < new Date()) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "Este kit ha expirado!",
                });
                return;
            }
            console.log("datos formulario agregar PE", kit);
            //   this.GetValidateKit(kit['id'])
            this.GetValidateKit(kit["id"])
                .then(() => {
                    if (this.mensajesKit.length == 0) {
                        const totalKit = this.arrayArticulosKit.reduce(
                            (total, producto) => {
                                return total + producto.cantidad * producto.precio_costo_unid;
                            },
                            0
                        );
                        this.arrayArticulosKit.forEach((producto) => {
                            producto.porcentaje =
                                ((producto.cantidad * producto.precio_costo_unid) / totalKit) *
                                100;
                        });
                        console.log("precio especial ", this.arrayArticulosKit);
                        this.arrayArticulosKit.forEach((producto) => {
                            producto.nuevo_precio =
                                (this.calcularPrecioUnitario(kit) * producto.porcentaje) / 100;
                        });
                        console.log("Estos son los articulos: ", this.arrayArticulosKit);
                        this.arrayArticulosKit.forEach((articulo) => {
                            this.arrayDetalle.push({
                                idkit: kit["id"],
                                idarticulo: articulo.id,
                                articulo: articulo.nombre,
                                medida: "KIT",
                                unidad_envase: articulo.unidad_envase,
                                cantidad: articulo.cantidad,
                                cantidad_paquetes: articulo.unidad_envase * articulo.cantidad,
                                precio: articulo.nuevo_precio,
                                descuento: 0,
                                stock: articulo.stock,
                                precioseleccionado: this.calcularPrecioUnitario(articulo),
                            });
                            let actividadEconomica = 461021;

                            this.arrayProductos.push({
                                actividadEconomica: actividadEconomica,
                                codigoProductoSin: articulo.id,
                                codigoProducto: articulo.codigo,
                                descripcion: articulo.nombre,
                                cantidad: articulo.cantidad,
                                unidadMedida: 25,
                                precioUnitario: parseFloat(
                                    this.calcularPrecioUnitario(articulo) * this.monedaVenta[0]
                                ).toFixed(2),
                                montoDescuento: (
                                    articulo.precio_costo_unid *
                                    articulo.cantidad *
                                    this.monedaVenta[0] -
                                    articulo.nuevo_precio * this.monedaVenta[0]
                                ).toFixed(2),
                                subTotal: parseFloat(
                                    articulo.nuevo_precio * this.monedaVenta[0]
                                ).toFixed(2),
                                numeroSerie: null,
                                numeroImei: null,
                            });
                            this.cerrarModal();
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Stock insuficiente",
                            text: this.mensajesKit.join("\n\n"),
                        });
                    }
                })
                .catch((error) => {
                    // Maneja el error aquí
                    console.error(error);
                });
        },

        abrirModalDetallesKit(data) {
            this.arrayArticulosSeleccionados = [];

            this.modalDetalleKit = 1;
            this.datosFormularioKit = {
                id: data["id"],
                nombre: data["nombre"],
                porcentaje: data["porcentaje"],
                codigo: data["codigo"],

                fecha_final: new Date(data["fecha_final"]).toISOString().split("T")[0],
                tipo_promocion: data["tipo_promocion"],
                estado: data["estado"],
                precio: data["precio"],
            };
            this.obtenerDatosKit(data["id"]);
        },

        abrirModalDetalles(data) {
            this.arrayArticulosSeleccionados = [];

            this.modalDetalle = 1;
            this.datosFormularioPE = {
                id: data["id"],
                nombre: data["nombre"],
                precio_r1: data["precio_r1"],
                rango_inicio_r1: data["rango_inicio_r1"],
                rango_final_r1: data["rango_final_r1"],
                precio_r2: data["precio_r2"],
                rango_inicio_r2: data["rango_inicio_r2"],
                rango_final_r2: data["rango_final_r2"],
                precio_r3: data["precio_r3"],
                rango_inicio_r3: data["rango_inicio_r3"],
                rango_final_r3: data["rango_final_r3"],

                fecha_final: new Date(data["fecha_final"]).toISOString().split("T")[0],
                tipo_promocion: data["tipo_promocion"],
                estado: data["estado"],
            };
            this.obtenerDatosKit(data["id"]), console.log(this.datosFormularioPE);
        },

        obtenerDatosKit(idPromocion) {
            return axios
                .get("/ofertas/id", {
                    params: {
                        idPromocion: idPromocion,
                    },
                })
                .then((response) => {
                    const datos = response.data.articulosPorPromocion;
                    this.arrayArticulosKit = datos.map((item) => ({
                        ...item.articulo,
                        cantidad: item.cantidad,
                    }));
                })
                .catch((error) => {
                    console.error(error);
                    throw error; // Re-lanza el error para que pueda ser manejado en agregarKit
                });
        },

        getColorForEstado(estado, fecha_final) {
            const fechaFinal = new Date(fecha_final) < new Date();

            if (fechaFinal) {
                return "#ff0000";
            }
            switch (estado) {
                case "Activo":
                    return "#5ebf5f";
                case "Inactivo":
                    return "#d76868";
                case "Agotado":
                    return "#ce4444";
                default:
                    return "#f9dda6";
            }
        },



        listarOfertaEspecial(page, buscar, criterio) {
            let me = this;
            let url = "/ofertasespeciales";

            axios
                .get(url, {
                    params: {
                        page: page,
                        buscar: buscar,
                        criterio: criterio,
                    },
                })
                .then(function (response) {
                    let respuesta = response.data;
                    me.arrayPreciosEspeciales = response.data.ofertas.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        scrollToSection() {
            $("html, body").animate(
                {
                    scrollTop: $("#seccionObjetivo").offset().top,
                },
                50
            );
        },
        scrollToTop() {
            $("html, body").animate(
                {
                    scrollTop: 0,
                },
                50
            );
        },
        calcularPrecioConDescuento(precioOriginal, porcentajeDescuento) {
            const descuento = this.precioseleccionado * (this.descuentoProducto / 100);
            const precioConDescuento = this.precioseleccionado - descuento;
            const precioFinal = precioConDescuento * this.unidadPaquete * this.cantidad;
            console.log("precio: ,", precioFinal)
            return precioFinal;
        },
        calcularDiasRestantes(fechaFinal) {
            const fechaActual = new Date();
            const fechaObjetivo = new Date(fechaFinal);
            const diferenciaEnMilisegundos = fechaObjetivo - fechaActual;
            const diasRestantes = Math.ceil(diferenciaEnMilisegundos / (1000 * 60 * 60 * 24));
            return diasRestantes;
        },

        actualizarDetalle(index) {
            let detalle = this.arrayDetalle[index];
            let producto = this.arrayProductos[index];

            producto.cantidad = detalle.cantidad;
            producto.precioUnitario = detalle.precioseleccionado;
            producto.subTotal = detalle.cantidad * producto.precioUnitario;

            console.log("Se actualizo el arrayFactura: " + producto);

            if (this.arrayDetalle[index] && typeof this.arrayDetalle[index].precioseleccionado !== 'undefined' && typeof this.arrayDetalle[index].cantidad !== 'undefined') {

                this.arrayDetalle[index].total = (this.arrayDetalle[index].precioseleccionado * this.arrayDetalle[index].cantidad).toFixed(2);

                const productoIndex = this.arrayProductos.findIndex(producto => producto.id === this.arrayDetalle[index].id);
                if (productoIndex !== -1) {
                    this.arrayProductos[productoIndex].precio = parseFloat(this.arrayDetalle[index].precioseleccionado).toFixed(2);
                }
                console.log(this.arrayProductos);
                console.log("Se actualizo el arrayDetalle: ");
                console.log(this.arrayDetalle);
                this.calcularTotal();
            } else {
                console.error('Datos inválidos en actualizarDetalle para el índice:', index);
            }
        },

        actualizarDetalleDescuento(index) {
            this.calcularTotal(index);
        },
        validarDescuentoAdicional() {
            if (this.descuentoAdicional >= this.totalParcial) {
                this.descuentoAdicional = 0;
                alert("El descuento adicional no puede ser mayor o igual al total.");
            }
        },

        habilitarNombreCliente() {
            if (this.casosEspeciales) {
                this.$refs.nombreRef.removeAttribute("readonly");
                this.documento = "99001";
                this.idcliente = "2";
                this.tipo_documento = "5";
            } else {
                this.$refs.nombreRef.setAttribute("readonly", true);
                this.documento = "";
                this.idcliente = "";
                this.tipo_documento = "";
            }
        },

        async verificarComunicacion() {
            try {
                const response = await axios.post('/venta/verificarComunicacion');
                if (response.data.RespuestaComunicacion.transaccion === true) {
                    document.getElementById("comunicacionSiat").innerHTML = response.data.RespuestaComunicacion.mensajesList.descripcion;
                    document.getElementById("comunicacionSiat").className = "badge bg-success";
                } else {
                    document.getElementById("comunicacionSiat").innerHTML = "Desconectado";
                    document.getElementById("comunicacionSiat").className = "badge bg-secondary";
                }
            } catch (error) {
                console.log(error);
            }
        },

        async cuis() {
            try {
                const response = await axios.post('/venta/cuis');
                if (response.data.RespuestaCuis.transaccion === false) {
                    document.getElementById("cuis").innerHTML = "CUIS: " + response.data.RespuestaCuis.codigo;
                    document.getElementById("cuis").className = "badge bg-primary";
                } else {
                    document.getElementById("cuis").innerHTML = "CUIS: Inexistente";
                    document.getElementById("cuis").className = "badge bg-secondary";
                }
            } catch (error) {
                console.log(error);
            }
        },
        async cufd() {
            try {
                const response = await axios.post('/venta/cufd');
                console.log("Respuesta Cufd: " + response.data);
                if (response.data.transaccion != false) {
                    document.getElementById("cufd").innerHTML = "CUFD vigente: " + response.data.fechaVigencia.substring(0, 16);
                    document.getElementById("direccion").innerHTML = response.data.direccion;
                    document.getElementById("cufdValor").innerHTML = response.data.codigo;
                    document.getElementById("cufd").className = "badge bg-info";
                } else {
                    document.getElementById("cufd").innerHTML = "No existe CUFD vigente";
                    document.getElementById("cufd").className = "badge bg-secondary";
                }
            } catch (error) {
                console.log(error);
            }
        },

        async ejecutarSecuencial() {
            try {
                await this.verificarComunicacion();
                await this.cuis();
                await this.cufd();
            } catch (error) {
                console.log("Error en la ejecución secuencial:", error);
            }
        },

        validarDescuentoGiftCard() {
            if (this.descuentoGiftCard >= this.calcularTotal) {
                this.descuentoGiftCard = 0;
                alert("El descuento Gift Card no puede ser mayor o igual al total.");
            }
        },
        buscarPromocion(idArticulo) {
            // Supongamos que el ID del artículo es 1, ajusta según tus necesidades

            axios
                .get(`/promocion/id?idArticulo=${idArticulo}`)
                .then((response) => {
                    this.arrayPromocion = response.data.promocion;
                })
                .catch((error) => {
                    // Maneja los errores aquí
                    console.error("Error:", error);
                });
        },

        async obtenerDatosUsuario() {
            try {
                const response = await axios.get('/venta');
                this.usuarioAutenticado = response.data.usuario.usuario;
                this.usuario_autenticado = this.usuarioAutenticado;
                this.idrol = response.data.usuario.idrol;
                this.idsucursalAutenticado = response.data.usuario.idsucursal;
                console.log("Obtener Datos Usuario: " + this.idsucursalAutenticado);
                this.puntoVentaAutenticado = response.data.codigoPuntoVenta;
            } catch (error) {
                console.error(error);
            }
        },

        async obtenerDatosSesionYComprobante() {
            try {
                const idsucursal = this.idsucursalAutenticado;
                console.log("El idsucursal es: " + idsucursal);
                const response = await axios.get('/obtener-ultimo-comprobante', {
                    params: {
                        idsucursal: idsucursal
                    }
                });
                const lastComprobante = response.data.last_comprobante;
                this.last_comprobante = lastComprobante;
                this.last_comprobante++;
                this.num_comprob = this.last_comprobante.toString().padStart(5, "0");
                console.log("El ultimo comprobante es: " + this.last_comprobante);
                //this.nextNumber(lastComprobante);
            } catch (error) {
                console.error('Error al obtener el último comprobante:', error);
            }
        },

        async ejecutarFlujoCompleto() {
            await this.obtenerDatosUsuario();
            await this.obtenerDatosSesionYComprobante();
        },

        nextNumber() {
            if (!this.num_comprob || this.num_comprob === "") {
                this.last_comprobante++;
                // Completa con ceros a la izquierda hasta alcanzar 5 dígitos
                this.num_comprob = this.last_comprobante.toString().padStart(5, "0");
            }
        },

        cambiarTipoventa(tipoventa, buscar, criterio) {
            this.tipocompro = tipoventa;
            console.log("estos es:", this.tipocompro);
            this.listarventaReporte(1, buscar, criterio);
        },

        listarventaReporte(page, buscar, criterio) {
            if (this.tipocompro === "Factura") {
                this.listarVentaF(page, buscar, criterio);
            } else if (this.tipocompro === "Recibo") {
                this.listarVentaR(page, buscar, criterio);
            } else {
                this.listarVenta(page, buscar, criterio);
            }
        },

        listarVenta(page, buscar, criterio) {
            let me = this;
            var url =
                "/venta?page=" + page + "&buscar=" + buscar + "&criterio=" + criterio;
            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    console.log(respuesta);
                    me.arrayVenta = respuesta.ventas.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarVentaF(page, buscar, criterio) {
            let me = this;
            var url = '/ventaReporteFactura?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayVenta = respuesta.ventas.data;
                me.pagination = respuesta.pagination;
                console.log('lista: ', me.arrayVenta);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarVentaR(page, buscar, criterio) {
            let me = this;
            var url = '/ventaReporte?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayVenta = respuesta.ventas.data;
                me.pagination = respuesta.pagination;
                console.log('lista: ', me.arrayVenta);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        resetBuscarCriterio() {
            this.buscar = '';
            this.criterio = '';
            this.tipocompro = '';
            this.arrayVenta = [];
        },


        selectCliente(numero) {
            let me = this;
            var url = "/cliente/selectClientePorNumero?numero=" + numero;
            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    q: numero;
                    me.arrayCliente = respuesta.clientes;
                    console.log(me.arrayCliente);
                    me.cantidadClientes = me.arrayCliente.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        selectClienteNombre(nombre) {
            console.log("nombre ", nombre)
            let me = this;
            var url = "/cliente/selectCliente?filtro=" + nombre;
            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    q: nombre;
                    me.arrayCliente = respuesta.clientes;
                    console.log(me.arrayCliente);
                    me.cantidadClientes = me.arrayCliente.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        getDatosCliente(val1) {
            let me = this;
            if (val1 !== null) {
                me.loading = true;
                me.idcliente = val1.id;
                //console.log(val1);
                this.email = val1.email;
                this.nombreCliente = val1.nombre;
                this.documento = val1.num_documento;
                this.tipo_documento = val1.tipo_documento;
                this.complemento_id = val1.complemento_id;
                this.clienteDeudas = val1.cantidad_creditos;
            }
            else {
                //console.log(val1);
                this.email = '';
                this.nombreCliente = '';
                this.documento = '';
                this.tipo_documento = '';
                this.complemento_id = '';
                this.clienteDeudas = '';
            }
        },
        getDatosCliente2(val1) {
            let me = this;
            me.loading = true;
            if (val1 !== null) {
                me.loading = true;
                me.idcliente = val1.id;
                //console.log(val1);
                this.email = val1.email;
                this.nombreCliente = val1.nombre;
                this.documento = val1.num_documento;
                this.tipo_documento = val1.tipo_documento;
                this.complemento_id = val1.complemento_id;
                this.clienteDeudas = val1.cantidad_creditos;
            }
            else {
                //console.log(val1);
                this.email = '';
                this.nombreCliente = '';
                this.documento = '';
                this.tipo_documento = '';
                this.complemento_id = '';
                this.clienteDeudas = '';
            }
        },
        /*buscarArticulo() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let me = this;
                var url = "/articulo/buscarArticuloVenta?filtro=" + me.codigo + "&idalmacen=" + this.idAlmacen;

                axios
                    .get(url)
                    .then(function (response) {
                        let respuesta = response.data;
                        me.arraySeleccionado = respuesta.articulos[0];
                        me.precioseleccionado = me.arraySeleccionado.precio_uno;
                        console.log(me.arraySeleccionado);
                        me.agregarDetalle();
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }, 200); // 100 ms de retraso
        },*/
        async buscarArticulo() {
            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(async () => {
                if (!this.idAlmacen) {
                    this.mostrarDesplegable = false;
                    swal("Advertencia", "Selecciona un almacén primero", "warning");
                    return;
                }

                if (!this.codigo.trim()) {
                    this.mostrarDesplegable = false;
                    this.resultadosBusqueda = [];
                    return;
                }

                try {
                    const response = await axios.get(
                        `/articulo/buscarArticuloVenta?filtro=${this.codigo}&idalmacen=${this.idAlmacen}`
                    );

                    const articulos = response.data.articulos;

                    // 🔥 SI SOLO HAY UNO → AUTO AGREGAR
                    if (articulos.length === 1) {
                        this.seleccionarArticulo(articulos[0]);
                        return;
                    }

                    this.resultadosBusqueda = articulos;
                    this.mostrarDesplegable = articulos.length > 0;
                    this.indiceSeleccionado = -1;

                } catch (error) {
                    console.error("Error al buscar artículo:", error);
                    this.mostrarDesplegable = false;
                }
            }, 200);
        },

        moverSeleccion(direccion) {
            if (!this.mostrarDesplegable || this.resultadosBusqueda.length === 0)
                return;
            if (direccion === "abajo") {
                this.indiceSeleccionado =
                    (this.indiceSeleccionado + 1) % this.resultadosBusqueda.length;
            } else if (direccion === "arriba") {
                this.indiceSeleccionado =
                    (this.indiceSeleccionado - 1 + this.resultadosBusqueda.length) %
                    this.resultadosBusqueda.length;
            }
        },

        seleccionarConEnter() {
            if (
                this.indiceSeleccionado >= 0 &&
                this.indiceSeleccionado < this.resultadosBusqueda.length
            ) {
                this.seleccionarArticulo(
                    this.resultadosBusqueda[this.indiceSeleccionado]
                );
            }
            else if (this.resultadosBusqueda.length === 1) {
                this.seleccionarArticulo(this.resultadosBusqueda[0]);
            }
        },
        seleccionarArticulo(articulo) {
            let stock = Number(articulo.saldo_stock);

            if (stock <= 0) {
                Swal.fire({
                    icon: "error",
                    title: "Producto Agotado",
                    text: `El artículo "${articulo.nombre}" no tiene existencias disponibles.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            // ✅ CLAVE: asignar el seleccionado
            this.arraySeleccionado = articulo;

            // ✅ asignar precio (ajusta si usas otro)
            this.precioseleccionado = articulo.precio_uno;

            // (opcional) limpiar búsqueda
            this.mostrarDesplegable = false;
            this.resultadosBusqueda = [];
            this.codigo = articulo.codigo; // o "" si quieres limpiar

            // 🔥 ahora sí
            this.agregarDetalle();
        },
        pdfVenta(id) {
            window.open("/venta/pdf/" + id, "_blank");
        },
        onPageChange(event) {
            let page = event.page + 1; // PrimeVue pages are 0-based, while your logic uses 1-based
            this.cambiarPagina(page, this.buscar, this.criterio);
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            if (this.tipocompro === "Factura") {
                this.listarVentaF(page, buscar, criterio);
            } else if (this.tipocompro === "Recibo") {
                this.listarVentaR(page, buscar, criterio);
            } else {
                this.listarVenta(page, buscar, criterio);
            }
        },
        encuentra(id) {
            var sw = 0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                if (this.arrayDetalle[i].idarticulo == id) {
                    sw = true;
                }
            }
            return sw;
        },
        eliminarDetalle(id) {
            const index = this.arrayDetalle.findIndex(item => item.id === id);
            if (index !== -1) {
                this.arrayDetalle.splice(index, 1);
                this.arrayProductos.splice(index, 1);
                this.calcularTotal();
            }
        },
        eliminarKit(id) {
            const indicesEliminar = [];
            for (let i = 0; i < this.arrayDetalle.length; i++) {
                if (this.arrayDetalle[i].idkit === id) {
                    indicesEliminar.push(i);
                }
            }
            indicesEliminar.forEach((index) => {
                this.arrayProductos.splice(index, 1);
            });
            for (let i = indicesEliminar.length - 1; i >= 0; i--) {
                this.arrayDetalle.splice(indicesEliminar[i], 1);
            }
        },

        verificarFactura(cuf, numeroFactura) {
            var url = 'https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=5153610012&cuf=' + cuf + '&numero=' + numeroFactura + '&t=2';
            window.open(url);
        },

        anularFactura(id, cuf) {
            swal({
                title: '¿Está seguro de anular la factura?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    let me = this;
                    axios.get('/factura/obtenerDatosMotivoAnulacion')
                        .then(function (response) {
                            var respuesta = response.data;
                            me.arrayMotivosAnulacion = respuesta.motivo_anulaciones;

                            console.log('Motivos obtenidos:', me.arrayMotivosAnulacion);

                            let options = {};
                            me.arrayMotivosAnulacion.forEach(function (motivo) {
                                options[motivo.codigo] = motivo.descripcion;
                            });

                            // Muestra un segundo modal para seleccionar el motivo
                            swal({
                                title: 'Seleccione un motivo de anulación',
                                input: 'select',
                                inputOptions: options,
                                inputPlaceholder: 'Seleccione un motivo',
                                showCancelButton: true,
                                inputValidator: function (value) {
                                    return new Promise(function (resolve, reject) {
                                        if (value !== '') {
                                            resolve();
                                        } else {
                                            reject('Debe seleccionar un motivo');
                                        }
                                    });
                                }
                            }).then((result) => {
                                if (result.value) {
                                    // Aquí obtienes el motivo seleccionado y puedes realizar la solicitud para anular la factura
                                    const motivoSeleccionado = result.value;
                                    axios.get('/factura/anular/' + cuf + "/" + motivoSeleccionado)
                                        .then(function (response) {
                                            const data = response.data;
                                            if (data === 'ANULACION CONFIRMADA') {
                                                swal(
                                                    'FACTURA ANULADA',
                                                    data,
                                                    'success'
                                                );
                                            } else {
                                                swal(
                                                    'ANULACION RECHAZADA',
                                                    data,
                                                    'warning'
                                                );
                                            }
                                        })
                                        .catch(function (error) {
                                            console.log(error);
                                        });
                                }
                            });
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            });
        },

        agregarDetalle() {
            console.log("Entro a agregarDetalle");

            // Buscar si el artículo ya está en el arrayDetalle
            const detalleExistente = this.arrayDetalle.find(detalle => detalle.idarticulo === this.arraySeleccionado.id);

            if (detalleExistente) {
                // Incrementar la cantidad y recalcular total y descuento
                const cantidadAdicional = this.cantidad * this.unidadPaquete;
                detalleExistente.cantidad += cantidadAdicional;
                detalleExistente.total = (
                    detalleExistente.cantidad * detalleExistente.precio -
                    detalleExistente.cantidad * detalleExistente.precio * (detalleExistente.descuento / 100)
                ).toFixed(2);

                // Actualizar también en arrayProductos
                const productoExistente = this.arrayProductos.find(producto => producto.codigoProducto === this.arraySeleccionado.codigo);
                if (productoExistente) {
                    productoExistente.cantidad += cantidadAdicional;
                    productoExistente.subTotal = (
                        productoExistente.cantidad * productoExistente.precioUnitario -
                        productoExistente.cantidad * productoExistente.precioUnitario * (productoExistente.montoDescuento / 100)
                    ).toFixed(2);
                }

                console.log("Actualizado en Venta:", this.arrayDetalle);
                console.log("Actualizado en Factura:", this.arrayProductos);
                this.codigo = '';

                this.calcularTotal();
                return;
            }

            // Verificar si no hay stock suficiente
            if (this.saldosNegativos === 0 && this.arraySeleccionado.saldo_stock < this.cantidad * this.unidadPaquete) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "No hay stock disponible!",
                });
                return;
            }

            // Si no existe, agregar como un nuevo detalle
            const precioUnitario = parseFloat(this.precioseleccionado);
            const cantidad = this.cantidad * this.unidadPaquete;
            const descuento = (precioUnitario * cantidad * (this.descuentoProducto / 100)).toFixed(2);
            const total = (precioUnitario * cantidad - descuento).toFixed(2);

            const nuevoDetalle = {
                id: Date.now(),
                idkit: -1,
                idarticulo: this.arraySeleccionado.id,
                articulo: this.arraySeleccionado.nombre,
                medida: this.arraySeleccionado.medida,
                unidad_envase: this.arraySeleccionado.unidad_envase,
                cantidad: cantidad,
                cantidad_paquetes: this.arraySeleccionado.unidad_envase,
                precio: precioUnitario,
                descuento: this.descuentoProducto,
                stock: this.arraySeleccionado.saldo_stock,
                precioseleccionado: precioUnitario,
                total: total
            };

            this.arrayDetalle.push(nuevoDetalle);
            console.log("Para la Venta:", this.arrayDetalle);

            const nuevoProducto = {
                actividadEconomica: this.arraySeleccionado.actividadEconomica,
                codigoProductoSin: this.arraySeleccionado.codigoProductoSin,
                codigoProducto: this.arraySeleccionado.codigo,
                descripcion: this.arraySeleccionado.nombre,
                cantidad: cantidad,
                unidadMedida: this.arraySeleccionado.codigoClasificador,
                precioUnitario: precioUnitario.toFixed(2),
                montoDescuento: descuento,
                subTotal: total,
                numeroSerie: null,
                numeroImei: null,
            };

            this.arrayProductos.push(nuevoProducto);
            console.log("Para la Factura:", this.arrayProductos);

            this.precioBloqueado = true;
            this.arraySeleccionado = [];
            this.cantidad = 1;
            this.unidadPaquete = 1;
            this.codigo = "";
            this.descuentoProducto = 0;

            this.calcularTotal();
        },

        agregarDetalleModal(data) {
            //this.scrollToSection();
            this.codigo = data.codigo;
            console.log("SLECCIONE ESTO:", data);

            this.buscarPromocion(data.id);
            this.precioseleccionado = data.precio_uno;

            this.cerrarModal();

        },
        eliminarSeleccionado() {
            this.codigo = "";
            this.arraySeleccionado = [];
        },
        listarArticulo(buscar, criterio) {
            let me = this;
            var url =
                "/articulo/listarArticuloVenta?buscar=" +
                buscar +
                "&criterio=" +
                criterio +
                "&idAlmacen=" +
                this.idAlmacen;
            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayArticulo = respuesta.articulos;
                    console.log("listar articulo", respuesta);
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        datosConfiguracion() {
            let me = this;
            var url = "/configuracion";

            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    console.log(respuesta);
                    me.saldosNegativos = respuesta.configuracionTrabajo.saldosNegativos;
                    me.permitirDevolucion =
                        respuesta.configuracionTrabajo.permitirDevolucion;
                    me.monedaVenta = [
                        respuesta.configuracionTrabajo.valor_moneda_venta,
                        respuesta.configuracionTrabajo.simbolo_moneda_venta,
                    ];
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        async selectAlmacen() {
            let me = this;
            let url = "/almacen/listarAlmacen";
            await axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    me.arrayAlmacenes = respuesta.almacenes;
                    console.log(me.arrayAlmacenes);
                    me.obtenerAlmacenPredeterminado();
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        async obtenerAlmacenPredeterminado() {
            try {
                const response = await axios.get('/api/configuracion/almacen-predeterminado');
                this.almacenPredeterminadoId = response.data.almacen_predeterminado_id;
                console.log("El almacen predeterminado es : " + this.almacenPredeterminadoId);

                this.almacenSeleccionado = this.arrayAlmacenes.find(
                    almacen => almacen.id === this.almacenPredeterminadoId
                );
            } catch (error) {
                console.error('Error al obtener el almacén predeterminado:', error);
            }
        },

        getAlmacenProductos(event) {
            this.idAlmacen = event.value;
        },
        validarVenta() {
            let me = this;
            me.errorVenta = 0;
            me.errorMostrarMsjVenta = [];
            let condicion = true;

            // Verificar stock de cada artículo
            me.arrayDetalle.forEach(function (x) {
                if (x.cantidad > x.stock) {
                    let art = `${x.articulo}: Stock insuficiente`;
                    me.errorMostrarMsjVenta.push(art);
                    condicion = false;
                }
            });

            // Verificar si se seleccionó el tipo de comprobante
            if (me.tipo_comprobante == 0) {
                me.errorMostrarMsjVenta.push("Seleccione el Comprobante");
                condicion = false;
            }

            // Verificar si se ingresó el impuesto
            if (!me.impuesto) {
                me.errorMostrarMsjVenta.push("Ingrese el impuesto de compra");
                condicion = false;
            }

            // Verificar si hay detalles en la venta
            if (me.arrayDetalle.length <= 0) {
                me.errorMostrarMsjVenta.push("Ingrese detalles");
                condicion = false;
            }

            // Verificar si hay errores
            if (me.errorMostrarMsjVenta.length > 0) {
                me.errorVenta = 1;

                // Mostrar todos los errores en un solo mensaje de SweetAlert
                swal({
                    type: "error",
                    title: "Error en la venta",
                    text: me.errorMostrarMsjVenta.join("\n"),
                });
            }

            return condicion;
        },
        aplicarDescuento(idtipopago) {
            this.tipo_comprobante = 'RESIVO';
            var idtipo_pago = idtipopago;

            const descuentoGiftCard = this.descuentoGiftCard;
            const numeroTarjeta = this.numeroTarjeta;

            if (numeroTarjeta && descuentoGiftCard) {
                idtipo_pago = 86;
            } else if (numeroTarjeta && !descuentoGiftCard) {
                idtipo_pago = 10;
            } else if (idtipo_pago == 7) {
                idtipo_pago = 7;
            } else {
                idtipo_pago = descuentoGiftCard ? 35 : 1;
            }

            this.registrarVenta(idtipo_pago);
        },

        aplicarCombinacion() {
            const descuentoGiftCard = this.descuentoGiftCard;
            const idtipo_pago = descuentoGiftCard ? 40 : 2;

            this.registrarVenta(idtipo_pago);
        },

        otroMetodo(metodoPago) {
            const idtipo_pago = metodoPago;
            this.registrarVenta(idtipo_pago);
        },
        emitirComprobante() {
            if (!this.tipo_comprobante) {
                alert("Por favor seleccione un tipo de comprobante.");
                return;
            }

            if (this.tipo_comprobante === "RESIVO") {
                this.emitirResivo();
            } else if (this.tipo_comprobante === "FACTURA") {
                this.emitirFactura();
            }
        },
        async emitirResivo(idVentaRecienRegistrada) {
            let me = this;

            let idventa = idVentaRecienRegistrada;
            let numeroResivo = document.getElementById("num_comprobante").value;
            let id_cliente = document.getElementById("idcliente").value;
            let nombreRazonSocial = document.getElementById("nombreCliente").value;
            let numeroDocumento = document.getElementById("documento").value;
            let complemento = document.getElementById("complemento_id").value;
            let tipoDocumentoIdentidad = document.getElementById("tipo_documento")
                .value;
            let montoTotal = (
                this.calcularTotal * parseFloat(this.monedaVenta[0])
            ).toFixed(2);
            let usuario = document.getElementById("usuarioAutenticado").value;

            try {
                const response = await axios.get("/resivo/obtenerLeyendaAleatoria");
                this.leyendaAl = response.data.descripcionLeyenda;
                console.log("El dato de leyenda llegado es: " + this.leyendaAl);
            } catch (error) {
                console.error(error);
                return '"Ley N° 453: Los servicios deben suministrarse en condiciones de inocuidad, calidad y seguridad."';
            }

            var resivo = [];
            resivo.push({
                cabecera: {
                    municipio: "Cochabamba",
                    telefono: "77490451",
                    numeroResivo: numeroResivo,
                    codigoSucursal: 0,
                    direccion: "Av. Ejemplo 123",
                    codigoPuntoVenta: 0,
                    fechaEmision: new Date().toISOString().slice(0, -1),
                    nombreRazonSocial: nombreRazonSocial,
                    codigoTipoDocumentoIdentidad: tipoDocumentoIdentidad,
                    numeroDocumento: numeroDocumento,
                    complemento: complemento,
                    codigoCliente: numeroDocumento,
                    montoTotal: montoTotal,
                    codigoMoneda: 1,
                    tipoCambio: 1,
                    montoTotalMoneda: montoTotal,
                    usuario: usuario,
                    leyenda: this.leyendaAl,
                },
            });
            me.arrayProductos.forEach(function (prod) {
                resivo.push({ detalle: prod });
            });

            var datos = { resivo };

            axios
                .post("/venta/emitirResivo", {
                    resivo: datos,
                    id_cliente: id_cliente,
                    idventa: idventa,
                })
                .then(function (response) {
                    var data = response.data;

                    if (data === "VALIDADA") {
                        swal("RESIVO VALIDADO", "Correctamente", "success");
                        me.arrayProductos = [];
                        me.cerrarModal2();
                        me.cerrarModal3();
                        me.listarVenta(1, "", "id");
                        me.isLoading = false;
                    } else {
                        me.arrayProductos = [];
                        me.cerrarModal2();
                        me.cerrarModal3();
                        me.listarVenta(1, "", "id");
                        me.isLoading = false;
                        swal("RESIVO VALIDADO", "éxito", "success");
                    }
                })
                .catch(function (error) {
                    me.arrayProductos = [];
                    swal("INTENTE DE NUEVO", "Comunicacion fallida", "error");
                    me.isLoading = false;
                });
        },

        imprimirResivo(id) {
            swal({
                title: "Selecciona un tamaño para imprimir el recibo",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "CARTA",
                cancelButtonText: "ROLLO",
                reverseButtons: true,
            })
                .then((result) => {
                    if (result.value) {
                        console.log("Se seleccionó imprimir en CARTA");
                        axios
                            .get("/resivo/imprimirCarta/" + id, {
                                responseType: "blob",
                            })
                            .then(function (response) {
                                const url = window.URL.createObjectURL(
                                    new Blob([response.data], { type: "application/pdf" })
                                );
                                window.open(url); // Abre el PDF en otra pestaña
                                console.log("Se imprimió el recibo en CARTA correctamente");
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    } else if (result.dismiss === swal.DismissReason.cancel) {
                        console.log("Se seleccionó imprimir en ROLLO");
                        axios
                            .get("/resivo/imprimirRollo/" + id, {
                                responseType: "blob",
                            })
                            .then(function (response) {
                                const url = window.URL.createObjectURL(
                                    new Blob([response.data], { type: "application/pdf" })
                                );
                                window.open(url); // Abre el PDF en otra pestaña
                                console.log("Se imprimió el recibo en ROLLO correctamente");
                            })
                            .catch(function (error) {
                                console.log(error);
                            });

                    }
                })
                .catch((error) => {
                    console.error("Error al mostrar el diálogo:", error);
                });
            this.opcionPago = 'efectivo';

        },

        async buscarOCrearCliente() {
            try {
                // Primero, intenta buscar el cliente
                const response = await axios.get(`/api/clientes/existe?documento=${this.documento}`);

                if (response.data.existe) {
                    // Si el cliente existe, usa ese ID y actualiza los datos del cliente
                    this.idcliente = response.data.cliente.id;
                    this.nombreCliente = response.data.cliente.nombre;
                    this.tipo_documento = response.data.cliente.tipo_documento;
                    this.complemento_id = response.data.cliente.complemento_id;
                } else {
                    // Si el cliente no existe, intenta crearlo
                    const nuevoClienteResponse = await axios.post('/cliente/registrar', {
                        'nombre': this.nombreCliente,
                        'num_documento': this.documento,
                        'tipo_documento': '5'
                    });
                    this.idcliente = nuevoClienteResponse.data.id;
                }
            } catch (error) {
                console.error("Error al buscar o crear cliente:", error);
                // Manejar el error adecuadamente
            }
        },

async registrarVenta(idtipo_pago) {
    if (!(await this.validarVenta())) return;

    try {
        this.isLoading = true; // 🔥 activar desde el inicio

        this.prepararDatosCliente();
        await this.buscarOCrearCliente();

        this.idPago = idtipo_pago;

        if (this.tipo_comprobante === "FACTURA") {
            await this.obtenerNumeroFactura();
        } else if (this.tipo_comprobante === "RESIVO") {
            await this.obtenerDatosSesionYComprobante();
        }

        console.log("El número de comprobante es:" + this.num_comprob);

        let ventaData = this.prepararDatosVenta(idtipo_pago);

        // 🔥 manejo de pago compuesto (lo tenías en la otra función)
        if (idtipo_pago === 13) {
            ventaData.efectivo_pago = this.recibidoCompuesto;
            ventaData.qr_pago = this.montoQRCompuesto;
        }

        const response = await axios.post("/venta/registrar", ventaData);

        if (response.data.id > 0) {
            await this.manejarVentaExitosa(response.data.id);
        } else {
            this.manejarErrorVenta(response.data);
        }

    } catch (error) {
        console.error("Error al registrar venta:", error);
        this.ejecutarFlujoCompleto();
    } finally {
        this.isLoading = false; // 🔥 siempre se apaga
    }
},





        cambiarProducto(index, nuevoProducto) {
            if (index >= 0 && index < this.arrayDetalle.length) {
                this.arrayDetalle[index] = {
                    idarticulo: nuevoProducto.id,
                    articulo: nuevoProducto.nombre,
                    cantidad: 1,
                    precio: nuevoProducto.precio,
                    stock: nuevoProducto.stock,
                    subtotal: nuevoProducto.precio
                };
                this.calcularTotal();
            }
        },



        prepararDatosCliente() {
            if (!this.nombreCliente.trim()) {
                this.nombreCliente = "SIN NOMBRE";
                this.documento = "000000";
            }
        },

        prepararDatosVenta(idtipo_pago) {
            const datosComunes = {
                idcliente: this.idcliente,
                tipo_comprobante: this.tipo_comprobante,
                serie_comprobante: this.serie_comprobante,
                num_comprobante: this.num_comprob,
                impuesto: this.impuesto,
                total: this.calcularTotal,
                idAlmacen: this.idAlmacen,
                idtipo_pago,
                idtipo_venta: this.idtipo_venta,
                data: this.arrayDetalle,
            };

            if (this.tipoVenta === 'credito') {
                const totalCredito = this.primera_cuota
                    ? this.calcularTotal - this.primer_precio_cuota
                    : this.calcularTotal;

                let cuotasActualizadas = [...this.cuotas];
                if (this.primera_cuota) {
                    cuotasActualizadas[0] = {
                        ...cuotasActualizadas[0],
                        totalCancelado: this.primer_precio_cuota,
                        estado: 'Pagado'
                    };
                }
                return {
                    ...datosComunes,
                    idpersona: this.idcliente,
                    numero_cuotasCredito: this.numero_cuotas,
                    tiempo_dias_cuotaCredito: this.tiempo_diaz,
                    totalCredito: this.primera_cuota ? this.calcularTotal - this.cuotas[0].totalCancelado : this.calcularTotal,
                    estadoCredito: "Pendiente",
                    cuotaspago: cuotasActualizadas,
                    primer_precio_cuota: this.primer_precio_cuota,
                    primera_cuota_pagada: this.primera_cuota
                };
            } else if (this.tipo_comprobante === "RESIVO") {
                return { ...datosComunes, resivo: this.resivo };
            } else {
                return { ...datosComunes, idpersona: this.idcliente };
            }
        },

        manejarVentaExitosa(idVenta) {
            this.listado = 1;
            this.obtenerDatosUsuario();
            this.listarventaReporte(1, "", "num_comprob");
            this.cerrarModal2();
            this.cerrarModal3();

            if (this.tipo_comprobante === "RESIVO") {
                this.imprimirResivo(idVenta);
                this.arrayProductos = [];

            } else if (this.tipo_comprobante === "FACTURA") {
                this.emitirFactura(idVenta);
            }

            this.reiniciarFormulario();
        },

        async emitirFactura(idVentaRecienRegistrada) {

            let me = this;

            let idventa = idVentaRecienRegistrada;
            //let numeroFactura = document.getElementById("num_comprobante").value;
            let numeroFacturaPrueba = String(this.num_comprob);
            let numeroFactura = numeroFacturaPrueba.padStart(5, '0');
            let cuf = "464646464";
            let cufdValor = document.getElementById("cufdValor");
            console.log("hola aaaa: ", this.cufdValor);
            let numeroTarjeta = this.numeroTarjeta;
            console.log("El numero de tarjeta es: " + numeroTarjeta);
            let cufd = cufdValor.textContent;
            let direccionValor = document.getElementById("direccion");
            let direccion = direccionValor.textContent;
            var tzoffset = (new Date()).getTimezoneOffset() * 60000;
            let fechaEmision = (new Date(Date.now() - tzoffset)).toISOString().slice(0, -1);
            //let id_cliente = document.getElementById("idcliente").value;
            //let nombreRazonSocial = document.getElementById("cliente").value;
            let nombreRazonSocial = this.nombreCliente;
            //let numeroDocumento = document.getElementById("documento").value;
            let numeroDocumento = this.documento;
            //let complemento = document.getElementById("complemento_id").value;
            let complemento = null;
            //let tipoDocumentoIdentidad = document.getElementById("tipo_documento").value;
            let tipoDocumentoIdentidad = 5;
            let montoTotal = (this.calcularTotal.toFixed(2));
            //let descuentoAdicional = document.getElementById("descuentoAdicional").value;
            let descuentoAdicional = this.descuentoAdicional;
            //let usuario = document.getElementById("usuarioAutenticado").value;
            let usuario = this.usuarioAutenticado;
            //let codigoPuntoVenta = document.getElementById("puntoVentaAutenticado").value;
            let codigoPuntoVenta = this.puntoVentaAutenticado;
            //let montoGiftCard = document.getElementById("descuentoGiftCard").value;
            let montoGiftCard = this.descuentoGiftCard;
            let codigoMetodoPago = this.idPago;
            let montoTotalSujetoIva = montoTotal - this.descuentoGiftCard;
            //let correo = document.getElementById("email").value;
            //let correo = this.email;


            console.log("El monto de Descuento de Gift Card es: " + this.descuentoGiftCard);
            console.log("El tipo de documento es: " + tipoDocumentoIdentidad);
            console.log("El complemento de documento es: " + complemento);
            console.log("hola monto toal: " + this.calcularTotal.toFixed(2));

            try {
                const response = await axios.get('/factura/obtenerLeyendaAleatoria');
                this.leyendaAl = response.data.descripcionLeyenda;
                console.log("El dato de leyenda llegado es: " + this.leyendaAl);
            } catch (error) {
                console.error(error);
                return '"Ley N° 453: Los servicios deben suministrarse en condiciones de inocuidad, calidad y seguridad."';
            }

            try {
                if (tipoDocumentoIdentidad === 5) {
                    const response = await axios.post('/factura/verificarNit/' + numeroDocumento);
                    if (response.data === 'NIT ACTIVO') {
                        me.codigoExcepcion = 0;
                        //alert("NIT VÁLIDO.");
                    } else {
                        me.codigoExcepcion = 1;
                        //alert("NIT INVÁLIDO.");
                    }
                } else {
                    me.codigoExcepcion = 0;
                }
            } catch (error) {
                console.error(error);
                return 'No se pudo verificar el NIT';
            }

            var factura = [];
            factura.push({
                cabecera: {
                    nitEmisor: "5153610012",
                    razonSocialEmisor: "JHENRY EDSON CASTRO CAMACHO",
                    municipio: "Quillacollo",
                    telefono: "60720509",
                    numeroFactura: numeroFactura,
                    cuf: cuf,
                    cufd: cufd,
                    codigoSucursal: 0,
                    direccion: direccion,
                    codigoPuntoVenta: codigoPuntoVenta,
                    fechaEmision: fechaEmision,
                    nombreRazonSocial: nombreRazonSocial,
                    codigoTipoDocumentoIdentidad: tipoDocumentoIdentidad,
                    numeroDocumento: numeroDocumento,
                    complemento: complemento,
                    codigoCliente: numeroDocumento,
                    codigoMetodoPago: codigoMetodoPago,
                    numeroTarjeta: numeroTarjeta,
                    montoTotal: montoTotal,
                    montoTotalSujetoIva: montoTotalSujetoIva,
                    codigoMoneda: 1,
                    tipoCambio: 1,
                    montoTotalMoneda: montoTotal,
                    montoGiftCard: this.descuentoGiftCard,
                    descuentoAdicional: descuentoAdicional,
                    codigoExcepcion: this.codigoExcepcion,
                    cafc: null,
                    leyenda: this.leyendaAl,
                    usuario: usuario,
                    codigoDocumentoSector: 1
                }
            })
            me.arrayProductos.forEach(function (prod) {
                factura.push({ detalle: prod })
            })

            var datos = { factura };

            axios.post('/venta/emitirFactura', {
                factura: datos,
                id_cliente: this.idcliente,
                idventa: idventa,
                cufd: cufd
            })
                .then(function (response) {
                    var data = response.data;
                    var mensaje = data.mensaje;
                    var idFactura = data.idFactura;
                    console.log('Mensaje:', mensaje);
                    console.log('ID de la factura:', idFactura);

                    if (mensaje === "VALIDADA") {
                        me.visibleDialog = false;
                        me.cambiar_pagina = 0;
                        me.ejecutarFlujoCompleto();
                        //me.obtenerNumeroFactura();

                        swal(
                            'FACTURA VALIDADA',
                            'Correctamente',
                            'success'
                        )
                        //me.imprimirTicket(idVentaRecienRegistrada);
                        //me.imprimirFactura(idFactura, correo);

                        me.listarVenta(1, "", "num_comprobante");
                        me.arrayProductos = [];
                        me.codigoExcepcion = 0;
                        me.idtipo_pago = '';
                        me.email = '';
                        me.descuentoGiftCard = '';
                        me.numeroTarjeta = null;
                        me.recibido = '';
                        me.metodoPago = '';
                        me.idcliente = 0;
                        me.cerrarModal2();
                        me.isLoading = false;
                        me.menu = 49;
                        me.tipo_comprobante = 'RESIVO';
                        me.opcionPago = 'efectivo';


                    } else {
                        me.listarVenta(1, "", "num_comprobante");
                        me.visibleDialog = false;
                        me.cambiar_pagina = 0;
                        me.arrayProductos = [];
                        me.codigoExcepcion = 0;
                        me.idtipo_pago = '';
                        me.descuentoGiftCard = '';
                        me.numeroTarjeta = null;
                        me.recibido = '';
                        me.idcliente = 0;
                        me.metodoPago = '';
                        me.last_comprobante = '';
                        me.cerrarModal2();
                        me.isLoading = false;
                        me.tipo_comprobante = 'RESIVO';
                        me.opcionPago = 'efectivo';


                        swal(
                            'FACTURA RECHAZADA',
                            data,
                            'warning'
                        );
                        me.eliminarVenta(idVentaRecienRegistrada);
                    }
                })
                .catch(function (error) {
                    console.error("Este es el error: " + error);
                    me.arrayProductos = [];
                    me.codigoExcepcion = 0;
                    swal(
                        'INTENTE DE NUEVO',
                        'Comunicacion con SIAT fallida',
                        'error');
                    me.isLoading = false;
                    me.idtipo_pago = '';
                    me.numeroTarjeta = null;
                    me.descuentoGiftCard = '';
                    me.recibido = '';
                    me.idcliente = 0;
                    me.metodoPago = '';
                    me.opcionPago = 'efectivo';

                    me.eliminarVentaFalloSiat(idVentaRecienRegistrada);
                    me.ejecutarFlujoCompleto();
                    me.listarVenta(1, "", "num_comprobante");

                });
        },

        imprimirFactura(id) {
            axios.get('/factura/imprimirRollo/' + id)
                .then(function (response) {
                    const fileURL = response.data.url;
                    const newWindow = window.open(fileURL, '_blank');
                    if (newWindow) {
                        newWindow.focus();
                    } else {
                        console.log("No se pudo abrir una nueva pestaña, asegúrate de que los pop-ups no están bloqueados.");
                    }
                    console.log("Se generó la factura en Rollo correctamente");
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        eliminarVenta(idVenta) {
            axios.delete('/venta/eliminarVenta/' + idVenta)
                .then(function (response) {
                    console.log('Venta eliminada correctamente:', response);
                })
                .catch(function (error) {
                    console.error('Error al eliminar la venta:', error);
                });
        },

        eliminarVentaFalloSiat(idVenta) {
            axios.delete('/venta/eliminarVentaFalloSiat/' + idVenta)
                .then(function (response) {
                    console.log('Venta eliminada correctamente:', response);
                })
                .catch(function (error) {
                    console.error('Error al eliminar la venta:', error);
                });
        },

        async obtenerNumeroFactura() {
            try {
                const response = await axios.get('/facturas/ultimo-numero');
                const ultimoNumero = response.data.ultimoNumero;
                this.num_comprob = ultimoNumero + 1;
                console.log("el numero factura es: " + this.num_comprob);
            } catch (error) {
                console.error('Error al obtener el último número de factura:', error);
            }
        },

        manejarErrorVenta(data) {
            if (data.valorMaximo) {
                swal("Aviso", `El valor de descuento no puede exceder el ${data.valorMaximo}`, "warning");
            } else if (data.caja_validado) {
                swal("Aviso", data.caja_validado, "warning");
            } else {
                console.error("Error desconocido al registrar venta:", data);
            }
        },

        reiniciarFormulario() {
            // Restablecer todos los valores del formulario
            Object.assign(this, {
                idproveedor: 0,
                //tipo_comprobante: this.tipo_comprobante === "FACTURA" ? "RESIVO" : "FACTURA",
                tipo_comprobante: "RESIVO",
                nombreCliente: "",
                //idcliente: 0,
                tipo_documento: 0,
                complemento_id: "",
                documento: "",
                imagen: "",
                serie_comprobante: "",
                //num_comprob: "",
                impuesto: 0.18,
                total: 0.0,
                idarticulo: 0,
                articulo: "",
                cantidad: 1,
                precio: 0,
                stock: 0,
                codigo: "",
                descuento: 0,
                arrayDetalle: [],
                //arrayProductos: [],
                primer_precio_cuota: 0,
                step: 1,
                recibido: 0
            });
        },

        eliminarVenta(idVenta) {
            axios.delete('/venta/eliminarVenta/' + idVenta)
                .then(function (response) {
                    console.log('Venta eliminada correctamente:', response);
                })
                .catch(function (error) {
                    console.error('Error al eliminar la venta:', error);
                });
        },

        mostrarDetalle() {
            /*const idsucursal = this.idsucursalAutenticado;
            console.log("El idsucursal es: " + idsucursal);
            axios.get('/obtener-ultimo-comprobante', {
                params: {
                    idsucursal: idsucursal
                }
            })
            .then(response => {
                const lastComprobante = response.data.last_comprobante;
                this.last_comprobante = lastComprobante;
                this.nextNumber(lastComprobante);
            })
            .catch(error => {
                console.error('Error al obtener el último comprobante:', error);
            });*/
            let me = this;
            me.selectAlmacen();
            me.listado = 0;

            me.idproveedor = 0;
            me.tipo_comprobante = 'RESIVO';
            me.serie_comprobante = '';
            //me.nextNumber();
            //me.num_comprobante = '';
            me.impuesto = 0.18;
            me.total = 0.0;
            me.idarticulo = 0;
            me.articulo = '';
            me.cantidad = 1;
            me.precio = 0;
            me.arrayDetalle = [];
            //me.arrayProductos = [];
            me.arraySeleccionado = [];
        },
        ocultarDetalle() {
            this.listado = 1;
            this.codigo = null;
            this.arrayArticulo.length = 0;
            this.precioseleccionado = null;
            this.medida = null;
            this.nombreCliente = null;
            this.documento = null;
            this.email = null;
            this.idAlmacen = 1;
            this.arrayProductos = [];
            this.arrayDetalle = [];
            this.precioBloqueado = false;

        },
        verVenta(id) {
            let me = this;
            me.listado = 2;

            //Obtener datos del ingreso
            var arrayVentaT = [];
            var url = "/venta/obtenerCabecera?id=" + id;

            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    arrayVentaT = respuesta.venta;
                    console.log("VIENDO ", respuesta);

                    me.cliente = arrayVentaT[0]["nombre"];
                    me.tipo_comprobante = arrayVentaT[0]["tipo_comprobante"];
                    me.serie_comprobante = arrayVentaT[0]["serie_comprobante"];
                    me.num_comprobante = arrayVentaT[0]["num_comprobante"];
                    me.impuesto = arrayVentaT[0]["impuesto"];
                    me.total = arrayVentaT[0]["total"];
                })
                .catch(function (error) {
                    console.log(error);
                });

            //obtener datos de los detalles
            var url = "/venta/obtenerDetalles?id=" + id;

            axios
                .get(url)
                .then(function (response) {
                    //console.log(response);
                    var respuesta = response.data;
                    me.arrayDetalle = respuesta.detalles;
                    console.log(array);
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        cerrarModal() {
            this.modal = 0;
            this.tituloModal = "";
            this.buscarA = '';
        },
        abrirModal() {

            this.scrollToTop();
            this.listarArticulo("", "nombre");
            this.selectAlmacen();
            this.arrayArticulo = [];
            this.modal = true;
            this.tituloModal = "Seleccione los articulos que desee";
            console.log("entro siii");
        },
        advertenciaFechaVencimiento() {
            swal({
                title: "No Disponible",
                text: "No puede seleccionar este producto porque esta vencido.",
                type: "warning",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Aceptar",
                confirmButtonClass: "btn btn-success",
                buttonsStyling: false,
            }).then(() => {
                this.cerrarModal();
            });
        },

        desactivarVenta(id) {
            swal({
                title: "Esta seguro de anular esta venta?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Aceptar!",
                cancelButtonText: "Cancelar",
                confirmButtonClass: "btn btn-success",
                cancelButtonClass: "btn btn-danger",
                buttonsStyling: false,
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios
                        .put("/venta/desactivar", {
                            id: id,
                        })
                        .then(function (response) {
                            me.listarVenta(1, "", "num_comprobante");
                            swal(
                                "Anulado!",
                                "La venta ha sido anulado con éxito.",
                                "success"
                            );
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                }
            });
        },
        //-------------OBTENER PRECIOS Y MABRIR_MODAL----------
        listarPrecio() {
            let me = this;
            var url = "/precios";
            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayPrecios = respuesta.precio.data;
                    console.log("PRECIOS", me.arrayPrecios);
                    //me.precioCount = me.arrayBuscador.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        mostrarSeleccion() {
            console.log("Precio seleccionado:", this.precioseleccionado);
        },


        cerrarModal2() {
            this.modal2 = false;
            this.tituloModal2 = "";
            this.idtipo_pago = "";
            this.tipoPago = "";
        },
        cerrarModal3() {
            this.modal3 = 0;
            this.tituloModal3 = "";
            this.numero_cuotas = "";
            this.tiempo_diaz = "";
            this.primera_cuota = false;
            this.cuotas = [];
        },

        calcularCambio() {

            const recibidoNumero = parseFloat(
                this.recibido / parseFloat(this.monedaVenta[0])
            );
            if (recibidoNumero === 0) {
                this.efectivo = recibidoNumero;
                console.log("EFECTIVO", this.efectivo);
                this.cambio = 0;
                this.faltante = 0;
            } else if (recibidoNumero < this.calcularTotal) {
                this.efectivo = recibidoNumero;
                this.faltante = (this.calcularTotal - this.efectivo).toFixed(2);
            } else if (recibidoNumero === this.calcularTotal) {
                this.efectivo = recibidoNumero;
                this.cambio = 0;
                this.faltante = 0;
            } else {
                this.efectivo = recibidoNumero;
                this.cambio = (this.efectivo - this.calcularTotal).toFixed(2);
                this.faltante = 0;
            }
        },

        /* buscarClientePorDocumento() {
             axios.get(`/api/clientes?documento=${this.documento}`)
                 .then(response => {
                     const cliente = response.data;
                     console.log(cliente);
                     this.nombreCliente = cliente.nombre;
                     this.nombreClienteEditable = false; // Deshabilita el input si se encuentra el cliente
                 })
                 .catch(error => {
                     if (error.response && error.response.status === 404) {
                         this.nombreCliente = '';
                         this.nombreClienteEditable = true; // Habilita el input si no se encuentra el cliente
                         Swal.fire({
                             title: 'Cliente no encontrado',
                             text: 'No se encontró ningún cliente con el documento proporcionado.',
                             icon: 'warning',
                             confirmButtonText: 'Ok'
                         });
                     } else {
                         console.error('Error al buscar el cliente:', error);
                         this.nombreCliente = '';
                         this.nombreClienteEditable = false; // Asegura que el input esté deshabilitado en caso de error
                         Swal.fire({
                             title: 'Error',
                             text: 'Hubo un problema al buscar el cliente. Por favor, inténtelo de nuevo más tarde.',
                             icon: 'error',
                             confirmButtonText: 'Ok'
                         });
                     }
                 });
         },*/
        buscarClientePorDocumento() {
            clearTimeout(this.buscarTimeout);

            if (this.documento.trim() === "") {
                this.resultadosClientes = [];
                this.mostrarDesplegableCliente = false;
                this.nombreCliente = "";
                this.nombreClienteEditable = false;
                this.mensajeRazonSocial = false;
                return;
            }

            this.buscarTimeout = setTimeout(() => {
                axios
                    .get(`/api/clientes?documento=${this.documento}`)
                    .then((response) => {
                        const clientes = Array.isArray(response.data)
                            ? response.data
                            : [response.data];

                        if (clientes.length > 0) {
                            // ✅ Cliente encontrado
                            this.resultadosClientes = clientes;
                            this.mostrarDesplegableCliente = true;
                            this.mensajeRazonSocial = false;
                        } else {
                            // ❌ No se encontró cliente
                            this.resultadosClientes = [];
                            this.mostrarDesplegableCliente = false;
                            this.nombreCliente = "";
                            this.nombreClienteEditable = true;
                            this.mensajeRazonSocial = true;
                            // 👇 Ya no enfoca automáticamente aquí
                        }
                    })
                    .catch(() => {
                        // ❌ Error (404 u otro)
                        this.resultadosClientes = [];
                        this.mostrarDesplegableCliente = false;
                        this.nombreCliente = "";
                        this.nombreClienteEditable = true;
                        this.mensajeRazonSocial = true;
                        // 👇 Tampoco enfoca automáticamente
                    });
            }, 300);
        },
        seleccionarCliente(cliente) {
            this.documento = cliente.num_documento;
            this.nombreCliente = cliente.nombre;
            this.nombreClienteEditable = false;
            this.mostrarDesplegableCliente = false;
            this.mensajeRazonSocial = false; // 🔹 Cliente seleccionado → ocultar mensaje
        },

        moverSeleccionCliente(direccion) {
            if (!this.mostrarDesplegableCliente) return;
            if (
                direccion === "abajo" &&
                this.indiceSeleccionadoCliente < this.resultadosClientes.length - 1
            ) {
                this.indiceSeleccionadoCliente++;
            } else if (direccion === "arriba" && this.indiceSeleccionadoCliente > 0) {
                this.indiceSeleccionadoCliente--;
            }
        },

        seleccionarClienteConEnter(event) {
            event.preventDefault(); // Evita que Enter haga submit

            if (this.indiceSeleccionadoCliente >= 0) {
                // ✅ Hay un cliente seleccionado del desplegable
                this.seleccionarCliente(
                    this.resultadosClientes[this.indiceSeleccionadoCliente]
                );
            } else if (this.resultadosClientes.length === 1) {
                // ✅ Solo hay un resultado → seleccionar
                this.seleccionarCliente(this.resultadosClientes[0]);
            } else if (this.resultadosClientes.length === 0) {
                // ❌ No hay resultados → habilitar inputs y mover foco
                this.nombreClienteEditable = true;
                this.mensajeRazonSocial = true;
                // 🔹 Esperar al siguiente ciclo de render antes de enfocar
                this.$nextTick(() => {
                    // Para PrimeVue 2: el input real está dentro de $el
                    const inputWrapper = this.$refs.inputNombreCliente;
                    if (inputWrapper && inputWrapper.$el) {
                        const input = inputWrapper.$el.querySelector("input");
                        if (input) input.focus();
                    }
                });
            }
        },
    },
    created() {
        this.listarPrecio();
    },
    mounted() {
        this.handleResize();
        window.addEventListener("resize", this.handleResize);
        this.datosConfiguracion();
        this.selectAlmacen();
        //this.listarVenta(1, this.buscar, this.criterio);
        this.listarventaReporte(1, "", "num_comprob");
        //this.obtenerDatosUsuario();
        this.actualizarFechaHora();
        this.ejecutarFlujoCompleto();
        //this.ejecutarSecuencial();
        //this.obtenerNumeroFactura();
        document.addEventListener('keypress', this.handleKeyPress);
        window.addEventListener("keydown", this.handleKeyPress);
        document.addEventListener("click", this.handleClickFuera);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
        window.removeEventListener("keydown", this.handleKeyPress);
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleResize);
        document.removeEventListener('keypress', this.handleKeyPress);
        document.removeEventListener("click", this.handleClickFuera);
    }
};
</script>
<style scoped>
/* 🔹 Botones pequeños */
.btn-sm {
  font-size: 0.8rem;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
  line-height: 1.1;
}

.btn-sm .pi {
  font-size: 0.75rem;
  margin-right: 4px;
}
.item-sin-stock {
    background-color: #ffe6e6 !important;
    color: #a00000;
    cursor: not-allowed;
    opacity: 0.9;
}

.item-sin-stock:hover {
    background-color: #ffcccc !important;
}

.stock-error {
    color: red;
    font-weight: bold;
    font-size: 0.9em;
    margin-left: 10px;
}

.stock-ok {
    color: green;
    font-weight: bold;
    font-size: 0.9em;
    margin-left: 10px;
}

.item-contenido {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

/* 🎂 Mensaje de cumpleaños - SOLO PC */
.mensaje-cumpleanos-venta {
    margin-top: 1rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    border-radius: 8px;
    font-weight: 700;
    text-align: center;
    animation: pulsoVenta 1.5s ease-in-out infinite;
    box-shadow: 0 4px 15px rgba(67, 233, 123, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.1rem;
}

.mensaje-cumpleanos-venta i {
    font-size: 1.8rem;
    animation: rotar 2s linear infinite;
}

.precio-wrapper {
    display: flex;
    align-items: center;
    gap: 4px;
}

.input-precio-unidad {
    text-align: center;
    height: 32px;
}

.btn-precio-toggle {
    height: 32px;
    width: 32px;
    padding: 0;
}

.estado-badge {
    padding: 0.15rem 0.6rem;
    border-radius: 6px;
    font-weight: 600;
    color: #fff;
    font-size: 0.7rem;
    display: inline-block;
    text-align: center;
}

/* VERDE - PAGADO */
.estado-verde {
    background-color: #28a745;
}

/* ROJO - ANULADA */
.estado-rojo {
    background-color: #dc3545;
}

/* AMARILLO - CRÉDITO */
.estado-amarillo {
    background-color: #ffc107;
    color: #000;
    /* Amarillo se lee mejor en negro */
}

.tabla-venta {
    width: 100%;
    white-space: nowrap;
    /* evita salto de columnas */
    overflow-x: auto;
}

.tabla-venta .p-datatable-wrapper {
    overflow-x: auto;
}

.tabla-venta th,
.tabla-venta td {
    text-align: center;
    vertical-align: middle;
    font-size: 0.85rem;
    padding: 0.5rem;
}

/* 🔹 Estilo general uniforme para todos los inputs */
.input-uniforme {
    width: 50%;
    font-size: 0.8rem;
    padding: 6px 8px;
    border-radius: 6px;
    box-sizing: border-box;
    height: 30px;
}

.input-cambio {
    width: 100%;
    font-size: 0.8rem;
    padding: 6px 8px;
    border-radius: 6px;
    box-sizing: border-box;
    height: 30px;
}

/* 🔹 Addon uniforme */
.addon-small {
    background-color: #f3f4f6;
    font-size: 0.8rem;
    color: #374151;
    border-radius: 6px 0 0 6px;
    padding: 6px 10px;
}

/* 🔹 Alinear grupos de inputs */
.custom-input-group .form-control {
    border-radius: 0 6px 6px 0;
    font-size: 0.8rem;
    height: 33px;
}

/* 🔹 Input deshabilitado o de solo lectura */
.form-control[readonly],
.form-control:disabled {
    background-color: #f9fafb;
    color: #6b7280;
}

/* Estilos para campos opcionales */
.optional-field {
    display: flex;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 4px;

    align-items: center;
    gap: 0.4rem;
    font-weight: 500;
    color: #6c757d;
}

.optional-icon {
    color: #17a2b8;
    font-size: 0.5rem;
}

.optional-tag {
    background-color: #eff6ff;
    color: #2563eb;
    font-size: 0.7rem;
    border-radius: 4px;
    padding: 0.1rem 0.3rem;
    margin-left: 4px;
}

/* 🔹 Contenedor del input y el botón */
.p-inputgroup {
    display: flex;
    align-items: stretch;
    width: 100%;
}

/* 🔹 Input principal (Buscar Producto) */
.input-full {
    width: 100%;
    font-size: 0.8rem;
    padding: 6px 8px;
    border-radius: 6px 0 0 6px;
    box-sizing: border-box;
}

/* Ajuste para InputText de PrimeVue */
.input-full>>>.p-inputtext {
    width: 100% !important;
    font-size: 0.8rem;
    padding: 6px 8px;
    border-radius: 6px 0 0 6px;
}

/* 🔹 Botón de búsqueda */
.p-inputgroup .p-button {
    border-radius: 0 6px 6px 0;
    font-size: 0.8rem;
    padding: 6px 10px;
}

/* 🔹 Label obligatorio */
.label-input {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.text-required {
    color: #dc2626;
    /* rojo */
    font-weight: 700;
}

/* Estilo uniforme para Dropdown (igual que InputText) */
.dropdown-full {
    width: 100% !important;
    font-size: 0.8rem;
    border-radius: 6px;
    box-sizing: border-box;
}

/* Input dentro del dropdown */
.dropdown-full>>>.p-dropdown-label {
    padding: 6px 8px !important;
    font-size: 0.8rem;
}

/* Flecha del dropdown */
.dropdown-full>>>.p-dropdown-trigger {
    width: 2rem !important;
}

/* Borde al focus */
.dropdown-full>>>.p-dropdown {
    border: 1px solid #ccc;
    transition: border 0.2s;
}

.dropdown-full>>>.p-dropdown.p-focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 0.15rem rgba(14, 165, 233, 0.25);
}

/* 🔹 Opciones del panel (lista desplegable) */
.dropdown-full>>>.p-dropdown-panel .p-dropdown-item {
    font-size: 0.8rem !important;
    padding: 6px 10px !important;
    min-height: auto !important;
    /* evita que queden muy grandes */
}

.form-section {
    margin-bottom: 1rem;
}

.input-label {
    display: block;
    font-size: 0.8rem;
    /* más pequeño */
    font-weight: 600;
    /* seminegrita */
    color: #374151;
    /* gris oscuro elegante */
    margin-bottom: 0.25rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    /* opcional: da un toque más pro */
}

.p-error {
    font-weight: 700;
    font-size: 0.8rem;
}

/* ===== CONTENEDOR GENERAL ===== */
.detalle-venta-pro {
    background: #ffffff;
    border-radius: 0.5rem;
    padding: 0.75rem;
    max-width: 1500px;
    margin: 0 auto;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    animation: fadeIn 0.4s ease;
    font-family: "Inter", "Segoe UI", sans-serif;
}

/* ===== ENCABEZADO ===== */
.detalle-header-pro {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 0.1rem;
    margin-bottom: 1rem;
}

.detalle-titulo-pro {
    font-size: 1.2rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.detalle-subtitulo-pro {
    font-size: 0.7rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.detalle-meta-pro {
    display: flex;
    gap: 2rem;
}

.label-pro {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.04em;
}

.valor-pro {
    font-size: 0.8rem;
    font-weight: 600;
    color: #111827;
    margin-top: 0.15rem;
}

/* ===== CLIENTE ===== */
.detalle-cliente-pro {
    background: #f9fafb;
    border-radius: 0.2rem;
    padding: 0.05rem 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
}

/* ===== TABLA ===== */
.detalle-tabla-pro .p-datatable {
    border-radius: 0.2rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.p-datatable tbody tr:hover {
    background-color: #f9fafb;
}

/* ===== RESUMEN ===== */
.detalle-resumen-pro {
    margin-top: 1.2rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 0.7rem;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    text-align: right;
}

.resumen-linea-pro {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    font-size: 0.8rem;
    color: #374151;
}

.total-final-pro {
    font-size: 0.8rem;
    font-weight: 700;
    color: #111827;
}

/* ===== FOOTER ===== */
.detalle-footer-pro {
    margin-top: 2rem;
    text-align: right;
}

/* ===== ANIMACIÓN ===== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.input-con-desplegable {
    position: relative;
    width: 100%;
}

.desplegable-simple {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 180px;
    /* ligeramente más compacto */
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #d1d5db;
    /* color gris suave como otros inputs */
    border-radius: 6px;
    /* mismo borde que inputs */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    list-style: none;
    padding: 0;
    margin: 2px 0 0 0;
    font-size: 0.8rem;
    /* tamaño uniforme */
}

.desplegable-simple li {
    padding: 6px 8px;
    /* igual que los inputs */
    cursor: pointer;
    transition: background-color 0.2s;
}

.desplegable-simple li:hover,
.desplegable-simple li.seleccionado {
    background-color: #f1f5f9;
    /* color azul muy claro, uniforme con info-box */
}

/* Panel Content Spacing */
>>>.p-panel .p-panel-content {
    padding: 1rem;
}

>>>.p-panel .p-panel-header {
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

>>>.p-panel .p-panel-header .p-panel-title {
    font-weight: 600;
}

/* Arreglar icono de lupa - Centrado perfecto */
.search-bar .p-input-icon-left {
    position: relative;
    width: 100%;
}

.search-bar .p-input-icon-left i {
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    margin: auto 0;
    height: 1rem;
    z-index: 2;
    color: #6c757d;
    pointer-events: none;
    display: flex;
    align-items: center;
    line-height: 1;
}

.search-bar .p-input-icon-left .p-inputtext {
    padding-left: 2.5rem !important;
    width: 100%;
}

.panel-header {
    display: flex;
    align-items: center;
}

.panel-title {
    margin-left: 8px;
}

/* Responsive Dialog Styles */
.responsive-dialog>>>.p-dialog {
    margin: 0.75rem;
    max-height: 90vh;
    overflow-y: auto;
}

.responsive-dialog>>>.p-dialog-content {
    overflow-x: auto;
    padding: 0.8rem;
}

.responsive-dialog>>>.p-dialog-header {
    padding: 1rem 0.75rem;
    font-size: 1.1rem;
}

.responsive-dialog>>>.p-dialog-footer {
    padding: 0.75rem 1rem;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

/* Toolbar Responsive - Mantener en una línea */
.toolbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    gap: 0.75rem;
    flex-wrap: nowrap;
}

.toolbar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}

.search-bar {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    min-width: 0;
    margin-right: 1rem;
}

/* DataTable Responsive */
>>>.p-datatable {
    font-size: 0.75rem;
}

>>>.p-datatable .p-datatable-tbody>tr>td {
    padding: 0.4rem;
    word-break: break-word;
    text-align: left;
}

>>>.p-datatable .p-datatable-thead>tr>th {
    padding: 0.35rem 0.4rem;
    font-size: 0.75rem;
}

/* Inputs compactos en la tabla de detalles */
>>>.p-datatable .p-inputnumber {
    height: 32px !important;
    width: 100% !important;
}

>>>.p-datatable .p-inputnumber .p-inputtext {
    height: 32px !important;
    padding: 0.25rem 0.3rem !important;
    font-size: 0.875rem !important;
    width: 100% !important;
    text-align: center !important;
}

>>>.p-datatable .form-control-sm {
    height: 32px !important;
    padding: 0.25rem 0.3rem !important;
    font-size: 0.875rem !important;
    width: 100% !important;
    text-align: center !important;
}

/* Tablet Styles */
@media (max-width: 1024px) {
    .responsive-dialog>>>.p-dialog {
        margin: 0.5rem;
        max-height: 95vh;
    }

    >>>.p-datatable {
        font-size: 0.85rem;
    }
}

/* Mobile Styles */
@media (max-width: 768px) {
    .toolbar .p-button .p-button-label {
        display: none;
    }

    .responsive-dialog>>>.p-dialog {
        margin: 0.25rem;
        max-height: 98vh;
    }

    .responsive-dialog>>>.p-dialog-content {
        padding: 0.75rem;
    }

    .responsive-dialog>>>.p-dialog-header {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .responsive-dialog>>>.p-dialog-footer {
        padding: 0.5rem 1rem;
        justify-content: flex-end;
    }

    .toolbar-container {
        gap: 0.5rem;
    }

    >>>.p-datatable {
        font-size: 0.8rem;
    }

    >>>.p-datatable .p-datatable-tbody>tr>td {
        padding: 0.4rem 0.3rem;
    }

    >>>.p-datatable .p-datatable-thead>tr>th {
        padding: 0.5rem 0.3rem;
        font-size: 0.75rem;
    }

    /* Ajustar botones en móviles */
    >>>.p-button-sm {
        font-size: 0.75rem !important;
        padding: 0.375rem 0.5rem !important;
        min-width: auto !important;
    }

    /* Reducir altura del input buscador */
    .search-bar .p-inputtext-sm {
        padding: 0.35rem 0.5rem 0.35rem 2.5rem !important;
        font-size: 0.85rem !important;
    }

    /* Inputs más compactos en móviles */
    >>>.p-datatable .p-inputnumber {
        height: 28px !important;
        width: 100% !important;
    }

    >>>.p-datatable .p-inputnumber .p-inputtext {
        height: 28px !important;
        padding: 0.2rem 0.25rem !important;
        font-size: 0.8rem !important;
        width: 100% !important;
        text-align: center !important;
    }

    >>>.p-datatable .form-control-sm {
        height: 28px !important;
        padding: 0.2rem 0.25rem !important;
        font-size: 0.8rem !important;
        width: 100% !important;
        text-align: center !important;
    }
}

/* Extra Small Mobile */
@media (max-width: 480px) {
    .toolbar .p-button .p-button-label {
        display: none;
    }

    .responsive-dialog>>>.p-dialog {
        margin: 0.1rem;
        max-height: 99vh;
    }

    .responsive-dialog>>>.p-dialog-content {
        padding: 0.5rem;
    }

    .responsive-dialog>>>.p-dialog-header {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }

    .responsive-dialog>>>.p-dialog-footer {
        padding: 0.5rem 0.75rem;
        justify-content: flex-end;
    }

    .responsive-dialog>>>.p-dialog-footer .p-button {
        width: auto;
        margin-bottom: 0.25rem;
    }

    /* Toolbar mantiene elementos en una línea */
    .toolbar-container {
        gap: 0.4rem;
        flex-wrap: nowrap;
    }

    .toolbar {
        flex-shrink: 0;
        min-width: auto;
    }

    .search-bar {
        flex: 1;
        min-width: 0;
    }

    /* Reducir más la altura del input buscador en móviles pequeños */
    .search-bar .p-inputtext-sm {
        padding: 0.3rem 0.5rem 0.3rem 2.5rem !important;
        font-size: 0.8rem !important;
    }

    >>>.p-datatable {
        font-size: 0.75rem;
    }

    >>>.p-datatable .p-datatable-tbody>tr>td {
        padding: 0.3rem 0.2rem;
    }

    >>>.p-datatable .p-datatable-thead>tr>th {
        padding: 0.4rem 0.2rem;
        font-size: 0.7rem;
    }

    /* Inputs extra compactos en móviles pequeños */
    >>>.p-datatable .p-inputnumber {
        height: 26px !important;
        width: 100% !important;
    }

    >>>.p-datatable .p-inputnumber .p-inputtext {
        height: 26px !important;
        padding: 0.15rem 0.2rem !important;
        font-size: 0.75rem !important;
        width: 100% !important;
        text-align: center !important;
    }

    >>>.p-datatable .form-control-sm {
        height: 26px !important;
        padding: 0.15rem 0.2rem !important;
        font-size: 0.75rem !important;
        width: 100% !important;
        text-align: center !important;
    }
}

/* Paginator Responsive */
@media (max-width: 768px) {
    >>>.p-paginator {
        flex-wrap: wrap !important;
        justify-content: center;
        font-size: 0.85rem;
        padding: 0.5rem;
    }

    >>>.p-paginator .p-paginator-page,
    >>>.p-paginator .p-paginator-next,
    >>>.p-paginator .p-paginator-prev,
    >>>.p-paginator .p-paginator-first,
    >>>.p-paginator .p-paginator-last {
        min-width: 32px !important;
        height: 32px !important;
        font-size: 0.85rem !important;
        padding: 0 6px !important;
        margin: 2px !important;
    }
}

@media (max-width: 480px) {
    >>>.p-paginator {
        font-size: 0.8rem;
        padding: 0.4rem;
    }

    >>>.p-paginator .p-paginator-page,
    >>>.p-paginator .p-paginator-next,
    >>>.p-paginator .p-paginator-prev,
    >>>.p-paginator .p-paginator-first,
    >>>.p-paginator .p-paginator-last {
        min-width: 28px !important;
        height: 28px !important;
        font-size: 0.8rem !important;
        padding: 0 4px !important;
        margin: 1px !important;
    }
}

/* Action Buttons in DataTable */
>>>.p-datatable .p-button {
    margin-right: 0.25rem;
}

@media (max-width: 768px) {
    >>>.p-datatable .p-button {
        margin-right: 0.15rem;
        margin-bottom: 0.15rem;
    }
}

/* Estilos del loader */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(5px);
    padding: 30px;
    border-radius: 15px;
}

.spinner {
    width: 80px;
    height: 80px;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    border-top: 4px solid rgba(255, 255, 255, 0.9);
    animation: spin 1s linear infinite;
}

.loading-text {
    margin-top: 20px;
    color: rgba(255, 255, 255, 0.9);
    letter-spacing: 3px;
    font-size: 14px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.step-indicators {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.step {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.step.active {
    background-color: #007bff;
    color: white;
}

.step.completed {
    background-color: #28a745;
    color: white;
}

.modal-header {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.5rem 0rem;
    margin: 0;
    box-sizing: border-box;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.modal-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #111827;
    letter-spacing: -0.01em;
    flex: 1;
    text-align: left;
}

.close-button {
    border: none;
    background: #de0000;
    color: #ffffff;
    font-size: 1rem;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.close-button:hover {
    background: #e5e7eb;
    color: #111827;
    transform: scale(1.05);
    cursor: pointer;
}

/* Estilos del loader */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(5px);
    padding: 30px;
    border-radius: 15px;
}

.spinner {
    width: 80px;
    height: 80px;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    border-top: 4px solid rgba(255, 255, 255, 0.9);
    animation: spin 1s linear infinite;
}

.loading-text {
    margin-top: 20px;
    color: rgba(255, 255, 255, 0.9);
    letter-spacing: 3px;
    font-size: 14px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.p-dialog-mask {
    z-index: 9990 !important;
}

.p-dialog {
    z-index: 9990 !important;
}

.swal2-zindex-fix {
    z-index: 100050 !important;
}

/* Estilos específicos para las columnas de inputs en la tabla de ventas */
.column-precio-unidad,
.column-unidades,
.column-descuento {
    min-width: 80px !important;
    max-width: 120px !important;
}

/* Estilos para los inputs de precio, cantidad y descuento */
.input-precio-unidad,
.input-unidades,
.input-descuento {
    width: 100% !important;
    min-width: 70px !important;
    max-width: 110px !important;
    text-align: center !important;
    font-size: 0.8rem !important;
    padding: 0.2rem 0.3rem !important;
}

/* Responsive para tablets */
@media (max-width: 768px) {

    .column-precio-unidad,
    .column-unidades,
    .column-descuento {
        min-width: 70px !important;
        max-width: 90px !important;
    }

    .input-precio-unidad,
    .input-unidades,
    .input-descuento {
        min-width: 60px !important;
        max-width: 80px !important;
        font-size: 0.75rem !important;
        padding: 0.15rem 0.2rem !important;
    }
}

/* Responsive para móviles */
@media (max-width: 480px) {

    .column-precio-unidad,
    .column-unidades,
    .column-descuento {
        min-width: 60px !important;
        max-width: 75px !important;
    }

    .input-precio-unidad,
    .input-unidades,
    .input-descuento {
        min-width: 55px !important;
        max-width: 70px !important;
        font-size: 0.7rem !important;
        padding: 0.1rem 0.15rem !important;
    }

    /* Ajustar headers de las columnas en móviles */
    >>>.p-datatable .p-column-header-content {
        font-size: 0.7rem !important;
        padding: 0.3rem 0.2rem !important;
    }

    /* Hacer que las columnas de inputs sean más compactas */
    >>>.p-datatable .p-datatable-tbody>tr>td.column-precio-unidad,
    >>>.p-datatable .p-datatable-tbody>tr>td.column-unidades,
    >>>.p-datatable .p-datatable-tbody>tr>td.column-descuento {
        padding: 0.2rem 0.1rem !important;
    }
}
</style>

<style>
/* SweetAlert v1 (swal) */
.sweet-alert {
    z-index: 99999 !important;
}

.sweet-overlay {
    z-index: 99998 !important;
}

/* SweetAlert v2 (Swal) */
.swal2-container {
    z-index: 99999 !important;
}

.swal2-popup {
    z-index: 99999 !important;
}

.swal2-backdrop-show {
    z-index: 99998 !important;
}

/* Clase personalizada para z-index */
.swal-zindex {
    z-index: 99999 !important;
}

/* Asegurar que todos los elementos de SweetAlert estén por encima */
div[class*="swal"] {
    z-index: 99999 !important;
}
</style>

<style>
/* Estilos para dispositivos móviles */
@media (max-width: 768px) {

    .p-dropdown,
    .p-inputtext {
        height: 32px !important;
        font-size: 0.875rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    /* FORZAR tamaño y centrado del texto seleccionado en el Dropdown */
    .p-dropdown .p-dropdown-label {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        font-size: 0.75rem !important;
        padding: 0 0.4rem !important;
    }

    .p-dropdown .p-dropdown-label span,
    .p-dropdown .p-dropdown-label-container {
        font-size: 0.75rem !important;
        line-height: 1 !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
    }

    .p-dropdown .p-dropdown-trigger {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 0.3rem !important;
    }

    /* Ajustar el panel del dropdown */
    .p-dropdown-panel .p-dropdown-items .p-dropdown-item {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }

    /* Forzar el tamaño del texto seleccionado */
    .p-dropdown .p-dropdown-label,
    .p-dropdown .p-dropdown-label span {
        font-size: 0.75rem !important;
        line-height: 1.1 !important;
    }

    .p-dropdown .p-dropdown-label-container {
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
    }

    /* Resto de tus estilos */
    .p-inputgroup .p-inputtext {
        height: 32px !important;
    }

    .p-inputgroup .p-button {
        height: 32px !important;
        width: 32px !important;
        padding: 0.25rem !important;
    }

    #buscarA {
        height: 32px !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }

    .p-float-label .p-inputtext {
        height: 36px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
    }

    .p-float-label {
        margin-bottom: 1rem !important;
        position: relative !important;
    }

    .p-float-label label {
        top: 50% !important;
        left: 0.75rem !important;
        transform: translateY(-50%) !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        pointer-events: none !important;
    }

    .p-float-label .p-inputtext:focus~label,
    .p-float-label .p-inputtext.p-filled~label,
    .p-float-label input:not(:placeholder-shown)~label {
        top: -0.25rem !important;
        left: 0.75rem !important;
        font-size: 0.75rem !important;
        transform: translateY(0) !important;
        background: white !important;
        padding: 0 0.25rem !important;
        z-index: 1 !important;
    }

    .step-content h5 {
        margin-bottom: 1rem !important;
        font-size: 1.25rem !important;
    }

    .step-content .p-grid {
        margin-top: 0.5rem !important;
    }

    .card .form-control,
    .input-group .form-control {
        height: 32px !important;
        font-size: 0.875rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    .input-group-text {
        height: 32px !important;
        font-size: 0.875rem !important;
        padding: 0.25rem 0.5rem !important;
        display: flex !important;
        align-items: center !important;
    }

    .card-body label {
        font-size: 0.875rem !important;
        margin-bottom: 0.25rem !important;
    }

    .p-datatable .p-inputtext-sm,
    .p-datatable .input-precio-unidad,
    .p-datatable .input-unidades input,
    .p-datatable .input-descuento input {
        height: 28px !important;
        font-size: 0.75rem !important;
        padding: 0.2rem 0.3rem !important;
    }

    .p-datatable .p-inputnumber input {
        height: 28px !important;
        font-size: 0.75rem !important;
        padding: 0.2rem 0.3rem !important;
    }

    .p-datatable .p-datatable-tbody>tr>td {
        padding: 0.3rem !important;
    }

    .p-datatable .p-button-sm {
        padding: 0.2rem 0.3rem !important;
        font-size: 0.7rem !important;
    }

    .responsive-dialog .p-dialog-content {
        padding: 0.75rem !important;
    }

    .step-content {
        padding: 0.5rem !important;
    }

    .buttons {
        margin-top: 1rem !important;
        gap: 0.5rem !important;
    }

    .buttons .btn {
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
    }
}

/* Estilos adicionales para mejorar la experiencia móvil */
@media (max-width: 480px) {

    .p-dropdown,
    .p-inputtext {
        height: 30px !important;
        font-size: 0.8rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* FORZAR tamaño del texto del Dropdown en pantallas pequeñas */
    .p-dropdown .p-dropdown-label {
        font-size: 0.7rem !important;
        padding: 0 0.3rem !important;
        height: 30px !important;
        display: flex !important;
        align-items: center !important;
    }

    .p-dropdown .p-dropdown-label span,
    .p-dropdown .p-dropdown-label-container {
        font-size: 0.7rem !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
    }

    .p-dropdown-panel .p-dropdown-items .p-dropdown-item {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.8rem !important;
    }

    .p-float-label .p-inputtext {
        height: 34px !important;
        padding: 0.4rem 0.6rem !important;
        font-size: 0.8rem !important;
    }

    .p-float-label label {
        font-size: 0.8rem !important;
        left: 0.6rem !important;
    }

    .p-float-label .p-inputtext:focus~label,
    .p-float-label .p-inputtext.p-filled~label,
    .p-float-label input:not(:placeholder-shown)~label {
        top: -0.2rem !important;
        left: 0.6rem !important;
        font-size: 0.7rem !important;
    }

    .step-content h5 {
        font-size: 1.1rem !important;
        margin-bottom: 0.75rem !important;
    }

    .card .form-control,
    .input-group .form-control {
        height: 30px !important;
        font-size: 0.8rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    .input-group-text {
        height: 30px !important;
        font-size: 0.8rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    .p-datatable .p-datatable-tbody>tr>td {
        padding: 0.25rem !important;
    }

    .p-datatable .p-inputtext-sm,
    .p-datatable .input-precio-unidad,
    .p-datatable .input-unidades input,
    .p-datatable .input-descuento input,
    .p-datatable .p-inputnumber input {
        height: 26px !important;
        font-size: 0.7rem !important;
        padding: 0.15rem 0.25rem !important;
    }

    .text-green {
        color: green;
        font-weight: bold;
    }

    .text-red {
        color: red;
        font-weight: bold;
    }

    .text-orange {
        color: orange;
        font-weight: bold;
    }

    .text-purple {
        color: purple;
        font-weight: bold;
    }

    .text-blue {
        color: blue;
        font-weight: bold;
    }

    .text-yellow {
        color: brown;
        font-weight: bold;
    }
}

/* Contenedor principal del dropdown */
.dropdown-bancos .p-dropdown {
    width: 100%;
}

/* OPCIONES */
.banco-opcion {
    padding: 6px 10px;
    border-radius: 8px;
    background: #fafafa;
    border: 1px solid #eee;
    margin: 4px 0;
    transition: all 0.2s ease-in-out;
    font-size: 0.75rem;
}

/* Hover */
.banco-opcion:hover {
    background: #e3f2fd;
    border-color: #90caf9;
    cursor: pointer;
}

/* Título (nombre del banco) */
.banco-opcion-header {
    font-weight: 600;
    font-size: 0.78rem;
    color: #0d47a1;
    margin-bottom: 4px;
}

/* Datos en fila */
.banco-opcion-detalle {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
    color: #333;
}

/* Valor cuando ya está seleccionado */
.banco-value {
    font-size: 0.78rem;
    font-weight: 600;
    color: #0d47a1;
}

/*Banco*/
.banco-opcion {
    display: flex;
    align-items: center;
    padding: 10px;
    gap: 12px;
    width: 100%;
}

.banco-logo {
    width: 62px;
    height: 62px;
    object-fit: contain;
    /* para que no se distorsione */
    border-radius: 6px;
}

.banco-detalles {
    display: flex;
    flex-direction: column;
    line-height: 1.1rem;
}

.banco-detalles .cuenta {
    font-weight: bold;
    font-size: 0.9rem;
}

.banco-detalles .numero,
.banco-detalles .tipo {
    font-size: 0.75rem;
    opacity: 0.8;
}

.banco-value {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-height: 28px;
    /* evita que crezca */
}

.banco-value img.banco-logo {
    width: 22px;
    height: 22px;
    object-fit: contain;
}

/* Contenedor */
.btn-selector {
    display: inline-flex;
    border-radius: 10px;
    overflow: hidden;
    background: #e9ecef;
    padding: 4px;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.15);
}

/* Botón base */
.btn-selector-btn {
    padding: 10px 18px;
    background: transparent;
    border: none;
    outline: none;
    cursor: pointer;
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
    transition: 0.25s ease all;
    border-radius: 8px;
    display: flex;
    align-items: center;
}

/* Hover ligero */
.btn-selector-btn:hover {
    background: rgba(0, 123, 255, 0.12);
    color: #007bff;
}

/* ACTIVADO: efecto moderno */
.btn-selector-btn.active {
    background: #007bff;
    color: white;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    transform: translateY(-1px);
}

/* Animación sutil */
.btn-selector-btn.active i {
    animation: bounce 0.35s ease;
}

@keyframes bounce {
    0% {
        transform: scale(1);
    }

    40% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

/* === Botones modernos === */
.pago-buttons .pago-btn {
    font-weight: 600;
    border-radius: 6px;
    padding: 6px 16px;
    border: 2px solid #007bff;
    background: white;
    color: #007bff;
    transition: 0.25s ease;
}

.pago-buttons .pago-btn:hover {
    background: #e9f2ff;
    transform: translateY(-2px);
}

/* Estado activo */
.pago-buttons .active-btn {
    background: #007bff;
    color: white;
    transform: scale(1.04);
    box-shadow: 0 3px 10px rgba(0, 123, 255, 0.4);
}

/* Animación al salir el dropdown */
.fade-in {
    animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.p-dialog .p-dialog-header .p-dialog-header-close {
    display: none !important;
}

.badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-contado {
    background-color: #28a74520;
    color: #28a745;
    border: 1px solid #28a74550;
}

.badge-credito {
    background-color: #007bff20;
    color: #007bff;
    border: 1px solid #007bff50;
}

.descuento-btn {
    background: #d93025 !important;
    color: white !important;
    font-weight: bold;
}

/* === Estilos para Auto-Verificar InputSwitch === */
.auto-verificar-switch :deep(.p-inputswitch) {
    width: 50px !important;
    height: 26px !important;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    padding: 2px !important;
    display: inline-flex !important;
    align-items: center !important;
}

.auto-verificar-switch :deep(.p-inputswitch.p-highlight) {
    background-color: #28a745 !important;
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.4) !important;
}

.auto-verificar-switch :deep(.p-inputswitch:not(.p-highlight)) {
    background-color: #c0c0c0 !important;
}

.auto-verificar-switch :deep(.p-inputswitch .p-inputswitch-slider) {
    background: transparent !important;
    border-radius: 12px !important;
    transition: 0.3s ease !important;
    padding: 0 !important;
    height: 100% !important;
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
}

.auto-verificar-switch :deep(.p-inputswitch .p-inputswitch-slider:before) {
    content: '' !important;
    background-color: white !important;
    width: 20px !important;
    height: 20px !important;
    border-radius: 50% !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    transition: transform 0.3s ease !important;
    margin: 0 3px !important;
    flex-shrink: 0 !important;
}

.auto-verificar-switch :deep(.p-inputswitch.p-highlight .p-inputswitch-slider:before) {
    transform: translateX(24px) !important;
}

.auto-verificar-switch :deep(.p-inputswitch:hover) {
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.1) !important;
}

.auto-verificar-switch :deep(.p-inputswitch.p-disabled) {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

/* Espaciado entre switch y label */
.auto-verificar-switch {
    margin: 0 2px !important;
    vertical-align: middle !important;
}
</style>