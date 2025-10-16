<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
    header("Location: ../vistas/login.php");
} else {
    require 'header.php';

    if ($_SESSION['Logistica'] == 1) {
        ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <form name="formulario" id="formulario" method="POST">
            <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">

            <div class="mb-3 content-header">
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalAgregarCompra">
                    <i class="ri-add-line"></i> Agregar Compra
                </button>
            </div>
            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xxl-9 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="detalles" class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">Opciones</th>
                                            <th scope="col">Artículo</th>
                                            <th scope="col">Código Prod.</th>
                                            <th scope="col">Descripción</th>
                                            <th scope="col">UM Sistema</th>
                                            <th scope="col">UM SUNAT</th>
                                            <th scope="col">Cantidad</th>
                                            <th scope="col">Costo Unit.</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>



                    </div>

                    <div class="row">
                        <div class="col-auto me-auto">.</div>
                        <div class="col-auto">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Subtotal : </strong> <span id="subtotal">0.00</span></li>
                                <input type="hidden" name="subtotal_compra" id="subtotal_compra">
                                <input type="hidden" name="totalcostounitario" id="totalcostounitario">
                                <input type="hidden" name="totalcantidad" id="totalcantidad">
                                <input type="hidden" name="totalcostounitario" id="totalcostounitario">
                                <input type="hidden" name="totalcantidad" id="totalcantidad">
                                <li class="list-group-item"><strong>IGV :</strong> <span id="igv_">0.00</span></li>
                                <input type="hidden" name="total_igv" id="total_igv">
                                <li class="list-group-item"><strong>Total :</strong> <span id="total">0.00</span></li>
                                <input type="hidden" name="total_final" id="total_final">
                                <input type="hidden" name="pre_v_u" id="pre_v_u">
                            </ul>
                        </div>
                    </div>

                </div>

                <div class="col-xxl-3 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="row">
                                <input type="hidden" name="idcompra" id="idcompra">
                                <input type="hidden" name="idempresa" id="idempresa"
                                    value="<?php echo $_SESSION['idempresa']; ?>">
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Fecha:</label>
                                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                        name="fecha_emision" id="fecha_emision" required onchange="handler(event);">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Tipo Comprobante(*):</label>
                                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-control" required
                                        onchange="cambiotcomprobante()" onfocus="cambiotcomprobante()">
                                        <option value="01">FACTURA</option>
                                        <option value="03">BOLETA</option>
                                        <option value="56">GUIA REMISIÓN</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Serie:</label>
                                    <input type="text" class="form-control" name="serie_comprobante" id="serie_comprobante"
                                        required="true" onkeyup="mayus(this);" onkeypress="EnterSerie(event)">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Número(*):</label>
                                    <input type="text" class="form-control" name="num_comprobante" id="num_comprobante"
                                        required="true" onkeypress="return EnterNumero(event,this)">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Moneda:</label>
                                    <select name="moneda" id="moneda" class="form-control" required onchange="cambiotcambio()">
                                        <option value="PEN">SOLES</option>
                                        <option value="USD">DOLARES</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Sub artículo(*):</label>
                                    <select name="subarticulo" id="subarticulo" class="form-control">
                                        <option value="">Agregar subartículo</option>
                                        <option value="1">Si</option>
                                        <option value="0" selected="true">No</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Código sistema:</label>
                                    <input type="text" class="form-control" name="codigos" id="codigos" readonly>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Nombre artículo:</label>
                                    <input type="text" class="form-control" name="nombrea" id="nombrea" readonly>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Stock:</label>
                                    <input type="text" class="form-control" name="stocka" id="stocka" readonly>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Codigo de barra:</label>
                                    <input type="text" class="form-control" name="codigob" id="codigob"
                                        placeholder="Código de barra" style="background-color: #DAF6A6;"
                                        onkeypress="return agregarDetalleBarra(event,this)">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Un. medida compra:</label>
                                    <input type="text" name="umcompra" id="umcompra" class="form-control" readonly="">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Un. medida venta:</label>
                                    <input type="hidden" name="idumventa" id="idumventa">
                                    <input type="text" name="umventa" id="umventa" class="form-control" readonly="">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Factor de conversion:</label>
                                    <input type="text" name="factorc" id="factorc" class="form-control" readonly="">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Valor venta para todos:</label>
                                    <input type="text" class="form-control" name="vunitario" id="vunitario" value=""
                                        placeholder="Valor unitario para todos los articulos" style="background-color: #DAF7A6;"
                                        onkeypress="return EnterVuni(event,this)" onblur="modificarSubototales()"
                                        onkeyup="modificarSubototales();">
                                    <input type="hidden" name="hora" id="hora">
                                </div>
                                <!-- <div class="mb-3 col-lg-6">
                     <label for="recipient-name" class="col-form-label">Factor de conversion:</label>
                     <input type="text" name="factorc" id="factorc" class="form-control" readonly="">
                     </div> -->
                                <div class="mb-3 col-lg-12">
                                    <label for="message-text" class="col-form-label">Proveedor(*):</label>
                                    <div class="input-group">
                                        <span class="input-group-text" data-bs-toggle="modal" data-bs-target="#ModalNcategoria"
                                            style="cursor:pointer;" id="basic-addon1">+</span>
                                        <select id="idproveedor" name="idproveedor" class="form-control" data-live-search="true"
                                            required onchange="cambioproveedor()"></select>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-12">
                                    <label for="idalmacen" class="col-form-label">
                                        Almacén Destino(*):
                                        <i class="fa fa-info-circle text-info" data-bs-toggle="tooltip"
                                           title="Seleccione el almacén donde se registrará el ingreso de esta compra"></i>
                                    </label>
                                    <select id="idalmacen" name="idalmacen" class="form-control" data-live-search="true" required>
                                        <option value="">Seleccione almacén...</option>
                                    </select>
                                </div>

                                <!-- CAMPOS SUNAT -->
                                <div class="mb-3 col-lg-6">
                                    <label for="ruc_emisor" class="col-form-label">
                                        RUC Emisor:
                                        <i class="fa fa-info-circle text-info" data-bs-toggle="tooltip"
                                           title="RUC del proveedor emisor del comprobante (se llena automáticamente)"></i>
                                    </label>
                                    <input type="text" class="form-control" name="ruc_emisor" id="ruc_emisor"
                                           maxlength="11" readonly placeholder="Se llena automáticamente"
                                           style="background-color: #f0f0f0;">
                                </div>
                                <div class="mb-3 col-lg-12">
                                    <label for="descripcion_compra" class="col-form-label">
                                        Descripción/Observaciones:
                                        <small class="text-muted">(Opcional)</small>
                                    </label>
                                    <textarea class="form-control" name="descripcion_compra" id="descripcion_compra"
                                              rows="2" maxlength="500" placeholder="Descripción general de la compra, observaciones o notas adicionales..."
                                              onkeyup="mayus(this);"></textarea>
                                    <small class="text-muted">Caracteres: <span id="char_count">0</span>/500</small>
                                </div>
                                <!-- FIN CAMPOS SUNAT -->

                            </div>
                            <input type="hidden" name="tcambio" id="tcambio" class="" onkeyup="modificarSubototales()">
                            <input type="hidden" name="idarticulonarti" id="idarticulonarti">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>
                                    Guardar</button>
                                <button id="btnCancelar" class="btn btn-danger" onclick="cancelarform()" type="button"><i
                                        class="fa fa-arrow-circle-left"></i> Cancelar</button>
                            </div>


        </form>

        </div>
        </div>
        </div>
        </div>

        <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModal" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModal">Agregar productos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table id="tblarticulos" class="table table-striped" style="width: 100% !important;">
                                        <thead>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Código proveedor</th>
                                            <th>Um compra</th>
                                            <th>Stock Compra</th>
                                            <th>Último précio</th>
                                            <th>Opciones</th>   
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        <!-- <button type="submit" id="btnGuardarNP" name="btnGuardarNP" value="btnGuardarNP"
                            class="btn btn-danger">Cerrar</button> -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="ModalNcategoria" aria-labelledby="ModalNcategoria" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalNcategoria">Agregar nuevo proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="fnuevoprovee" id="fnuevoprovee" method="POST">
                            <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
                            <input type="hidden" name="tipo_persona" id="tipo_persona" value="proveedor">
                            <div class="row">
                                <div class="mb-3 col-lg-6">
                                    <label for="recipient-name" class="col-form-label">Documento:</label>
                                    <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                                        onblur="validarProveedor();" onkeypress="return NumCheck(event, this)" autofocus="true">
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <label for="message-text" class="col-form-label">Razón social:</label>
                                    <input type="text" class="form-control" name="razon_social" id="razon_social" required
                                        onkeyup="mayus(this);">
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button> -->
                        <button type="submit" id="btnGuardarNP" name="btnGuardarNP" value="btnGuardarNP"
                            class="btn btn-primary">Guardar</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Fin modal -->

        <!-- Modal Agregar Compra -->
        <div class="modal fade" id="modalAgregarCompra" tabindex="-1" aria-labelledby="modalAgregarCompraLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary-gradient">
                        <h5 class="modal-title text-white" id="modalAgregarCompraLabel">
                            <i class="ri-shopping-cart-line"></i> Agregar Nueva Compra
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formAgregarCompra" name="formAgregarCompra" method="POST">
                            <!-- SEGURIDAD: Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">

                            <div class="row">
                                <!-- Fecha -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_compra" class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_compra" id="fecha_compra"
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Tipo de Comprobante -->
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_comprobante_modal" class="form-label">Tipo de Comprobante <span class="text-danger">*</span></label>
                                    <select name="tipo_comprobante_modal" id="tipo_comprobante_modal" class="form-select" required>
                                        <option value="01">Factura</option>
                                        <option value="03">Boleta</option>
                                        <option value="56">Guía de Remisión</option>
                                    </select>
                                </div>

                                <!-- Serie -->
                                <div class="col-md-6 mb-3">
                                    <label for="serie_modal" class="form-label">Serie <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="serie_modal" id="serie_modal"
                                           placeholder="Ej: F001" required onkeyup="mayus(this);">
                                </div>

                                <!-- Número -->
                                <div class="col-md-6 mb-3">
                                    <label for="numero_modal" class="form-label">Número <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="numero_modal" id="numero_modal"
                                           placeholder="Ej: 00000001" required>
                                </div>

                                <!-- Moneda -->
                                <div class="col-md-6 mb-3">
                                    <label for="moneda_modal" class="form-label">Moneda <span class="text-danger">*</span></label>
                                    <select name="moneda_modal" id="moneda_modal" class="form-select" required>
                                        <option value="PEN">Soles (PEN)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>

                                <!-- Código de Barra -->
                                <div class="col-md-6 mb-3">
                                    <label for="codigo_barra_modal" class="form-label">Código de Barra</label>
                                    <input type="text" class="form-control" name="codigo_barra_modal" id="codigo_barra_modal"
                                           placeholder="Código de barra del producto">
                                </div>

                                <!-- Unidad de Medida -->
                                <div class="col-md-6 mb-3">
                                    <label for="unidad_medida_modal" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select name="unidad_medida_modal" id="unidad_medida_modal" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>

                                <!-- Nombre del Artículo -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_articulo_modal" class="form-label">Nombre del Artículo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nombre_articulo_modal" id="nombre_articulo_modal"
                                           placeholder="Nombre del producto" required onkeyup="mayus(this);">
                                </div>

                                <!-- Cantidad -->
                                <div class="col-md-4 mb-3">
                                    <label for="cantidad_modal" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="cantidad_modal" id="cantidad_modal"
                                           placeholder="0" step="0.01" min="0" required onchange="calcularTotalModal();">
                                </div>

                                <!-- Base Imponible -->
                                <div class="col-md-4 mb-3">
                                    <label for="base_imponible_modal" class="form-label">Base Imponible <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="base_imponible_modal" id="base_imponible_modal"
                                           placeholder="0.00" step="0.01" min="0" required onchange="calcularTotalModal();">
                                </div>

                                <!-- IGV -->
                                <div class="col-md-4 mb-3">
                                    <label for="igv_modal" class="form-label">IGV (18%)</label>
                                    <input type="number" class="form-control" name="igv_modal" id="igv_modal"
                                           placeholder="0.00" step="0.01" min="0" readonly>
                                </div>

                                <!-- Importe Total -->
                                <div class="col-md-12 mb-3">
                                    <label for="importe_total_modal" class="form-label">Importe Total</label>
                                    <input type="number" class="form-control bg-light" name="importe_total_modal" id="importe_total_modal"
                                           placeholder="0.00" step="0.01" min="0" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="registrarCompraModal();">
                            <i class="ri-save-line"></i> Registrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin modal Agregar Compra -->

        <?php
    } else {
        require 'noacceso.php';
    }

    require 'footer.php';

    ?>
    <!-- Librería para escaneo de QR -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script type="text/javascript" src="scripts/compra.js"></script>
    <?php
}
ob_end_flush();
?>