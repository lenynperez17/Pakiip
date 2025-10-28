<?php
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  $swsession = 0;
  header("Location: ../vistas/login.php");
} else {
  $swsession = 1;
  require 'header.php';

  if ($_SESSION['Ventas'] == 1) {
    ?>
                            <!--Contenido-->
                            <!-- Content Wrapper. Contains page content -->
                            <link rel="stylesheet" href="style.css">
                            <div class="">
                              <!-- Main content -->
                              <section class="">
                                <div class="content-header">
                                  <h1>Cotización <button class="btn btn-primary btn-sm" id="btnagregar" onclick="mostrarform(true)">Nuevo</button>
                                    <button class="btn btn-success btn-sm" id="refrescartabla" onclick="refrescartabla()">Refrescar</button>
                                  </h1>
                                </div>
                                <div class="row" style="background:white;">
                                  <div class="col-md-12">
                                    <div class="">
                                      <!-- /.box-header -->
                                      <!-- centro -->
                                      <div class="table-responsive" id="listadoregistros">
                                        <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                                          <thead style="text-align:center;">
                                            <th>-</th>
                                            <th>Em. fac.</th>
                                            <th>Fecha creación</th>
                                            <th>Número</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Vendedor</th>
                                            <!-- <th>Nro factura</th> -->
                                            <th>Estado</th>
                                            <th>Moneda</th>
                                          </thead>
                                          <tbody style="text-align:center;">
                                          </tbody>
                                        </table>
                                      </div>
                                      <div class="panel-body" id="formularioregistros">
                                        <form name="formulario" id="formulario" method="POST" autocomplete="off">
                                          <div class="row">
                                            <div class="col-md-12">
                                              <div class="card">
                                                <div class="card-body">
                                                  <div class="row">
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Serie:</label>
                                                      <select class="form-control" name="serie" id="serie" onchange="incrementarNum();"></select>
                                                      <input type="hidden" name="idnumeracion" id="idnumeracion">
                                                      <input type="hidden" name="SerieReal" id="SerieReal">
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Número:</label>
                                                      <input class="form-control" name="numero_cotizacion" id="numero_cotizacion" required="true"
                                                        readonly>
                                                    </div>
                                                    <!--Campos para guardar cotizacion-->
                                                    <input type="hidden" name="idcotizacion" id="idcotizacion">
                                                    <!--Datos de empresa Estrella-->
                                                    <input type="hidden" name="idempresa" id="idempresa"
                                                      value="<?php echo $_SESSION['idempresa']; ?>">
                                                    <input type="hidden" name="numeracion" id="numeracion" value="">
                                                    <!--Datos del cliente-->
                                                    <input class="form-control" type="hidden" name="idcliente" id="idcliente">
                                                    <input type="hidden" name="contaedit" id="contaedit">
                                                    <!--Datos del cliente-->
                                                    <input type="hidden" name="hora" id="hora">
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Fecha cotización:</label>
                                                      <input type="date" class="form-control" name="fecha_emision" id="fecha_emision"
                                                        required="true" disabled="true">
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Fecha expiración:</label>
                                                      <input type="date" class="form-control" name="fechavalidez" id="fechavalidez" required="true"
                                                        min="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Moneda:</label>
                                                      <select class="form-control" name="tipo_moneda" id="tipo_moneda"
                                                        onchange="tipodecambiosunat()">
                                                        <option value="PEN">PEN</option>
                                                        <option value="USD">USD</option>
                                                      </select>
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">T. camb:</label>
                                                      <input type="text" name="tcambio" id="tcambio" class="form-control" readonly="true"
                                                        onkeypress="focotcaruc(event, this)">
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Ruc cliente - <small>si es ruc + enter</small></label>
                                                      <input type="text" class="form-control" name="numero_documento2" id="numero_documento2"
                                                        maxlength="11" placeholder="RUC DE CLIENTE-ENTER" onkeypress="agregarClientexRuc(event)"
                                                        onblur="quitasuge1()" onfocus="focusTest(this)">
                                                      <div id="suggestions">
                                                      </div>
                                                    </div>
                                                    <div class="mb-3 col-lg-4">
                                                      <label for="recipient-name" class="col-form-label">Razón social:</label>
                                                      <input type="text" class="form-control" name="razon_social2" id="razon_social2"
                                                        required="true" placeholder="RAZÓN SOCIAL">
                                                    </div>
                                                    <div class="mb-3 col-lg-4">
                                                      <label for="recipient-name" class="col-form-label">Domicilio fiscal:</label>
                                                      <input type="text" class="form-control" name="domicilio_fiscal2" id="domicilio_fiscal2"
                                                        required="true" placeholder="DIRECCIÓN CLIENTE">
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Correo Electrónico:</label>
                                                      <input type="text" class="form-control" name="correocli" id="correocli" required="true"
                                                        placeholder="CORREO CLIENTE" onkeypress="return focusbotonarticulo(event, this)"
                                                        onfocus="focusTest(this)">
                                                    </div>
                                                    <div class="mb-3 col-lg-12">
                                                      <label for="recipient-name" class="col-form-label">Observación:</label>
                                                      <textarea class="form-control" name="observacion" id="observacion" placeholder="OBSERVACIÓN"
                                                        rows="5" cols="70"></textarea>
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Tipo precio:</label>
                                                      <select class="form-control" name="tipocotizacion" id="tipocotizacion"
                                                        onchange="seleccionTipoCot()">
                                                        <option value="servicios">SERVICIOS</option>
                                                        <option value="productos">PRODUCTOS</option>
                                                      </select>
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Selecciona vendedor:</label>
                                                      <select class="form-control" name="vendedorsitio" id="vendedorsitio">
                                                      </select>
                                                    </div>
                                                    <div class="mb-3 col-lg-2">
                                                      <label for="recipient-name" class="col-form-label">Tipo de emisión:</label>
                                                      <select class="form-control" name="estadocoti" id="estadocoti" onchange="seleccionTipoCot()">
                                                        <option value="1">EMITIDO</option>
                                                        <option value="2">APROBADO</option>
                                                      </select>
                                                    </div>
                                                    <div class="col-lg-2" style="margin-top: 41px;">
                                                      <a data-bs-toggle="modal" data-bs-target="#myModalArt" class="btn btn-primary btn-sm"
                                                        id="btnAgregarArt">Agregar Productos</a>
                                                    </div>
                                                    <div class="mb-3 col-lg-2" style="display: none;">
                                                      <button ata-toggle="modal" href="#myModalServ" id="btnAgregarArt" type="button"
                                                        class="btn btn-primary"> Servicios</button>
                                                    </div>
                                                    <div class="table-responsive">
                                                      <div class="table-responsive" id="detallesproductoDIV" style="display: none;">
                                                        <table id="detallesproducto" class="table table-striped"
                                                          style="font-size: 14px; max-width: 100%; !important;">
                                                          <thead style="text-align:center;">
                                                            <th>Sup.</th>
                                                            <th>Item</th>
                                                            <th>Artículo</th>
                                                            <th>Cantidad</th>
                                                            <th>Cód.</th>
                                                            <th>U.M.</th>
                                                            <th>Prec. u.</th>
                                                            <th>Val. u.</th>
                                                            <th>Importe</th>
                                                          </thead>
                                                          <tfoot style="vertical-align: center;">
                                                            <!--SUBTOTAL-->
                                                            <tr>
                                                              <td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <th style="font-weight: bold;">Subtotal </th>
                                                              <th style="font-weight: bold;">
                                                                <h4 style="font-size: 14px" id="subtotal">0.00</h4>
                                                              </th>
                                                              </td>
                                                            </tr>
                                                            <!--IGV-->
                                                            <tr>
                                                              <td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <td></td>
                                                              <th style="font-weight: bold; vertical-align: center;">igv 18%
                                                              </th>
                                                              <th style="font-weight: bold; vertical-align: center;">
                                                                <h4 style="font-size: 14px" id="igv_">0.00</h4>
                                                              </th>
                                                              </td>
                                                            </tr>
                                                            <!--TOTAL-->
                                                            <td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th style="font-weight: bold; vertical-align: center;">Total </th>
                                                            <!--Datos de
                                       Impuestos--> <!--IGV-->
                                                            <th style="font-weight: bold;">
                                                              <h4 id="total"
                                                                style="font-weight: bold; vertical-align: center; font-size: 14px">
                                                                0.00</h4>
                                                            </th>
                                                            <input type="hidden" name="total_final_producto" id="total_final_producto">
                                                            <input type="hidden" name="subtotal_cotizacion_producto"
                                                              id="subtotal_cotizacion_producto">
                                                            <input type="hidden" name="total_igv_producto" id="total_igv_producto">
                                                            </td>
                                                            </tr>
                                                          </tfoot>
                                                          <tbody>
                                                          </tbody>
                                                        </table>
                                                      </div>
                                                    </div>
                                                    <!-- TABLA DETALLE DE PRODUCTO  ===========================================================  -->
                                                    <div class="table-responsive" id="detallesservicioDIV" style="display: none;">
                                                      <table id="detallesservicio" class="table table-striped"
                                                        style="font-size: 14px; max-width: 100%; !important;">
                                                        <thead style="text-align:center;">
                                                          <th>Opciones</th>
                                                          <th>Item</th>
                                                          <th>Servicio</th>
                                                          <th>Código</th>
                                                          <th>Precio</th>
                                                          <th>Importe</th>
                                                        </thead>
                                                        <tfoot style="vertical-align: center;">
                                                          <!--SUBTOTAL-->
                                                          <tr>
                                                            <td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th style="font-weight: bold; ">Subtotal </th>
                                                            <th style="font-weight: bold;">
                                                              <h4 id="subtotal_servicio">0.00</h4>
                                                              </td>
                                                          </tr>
                                                          <!--IGV-->
                                                          <tr>
                                                            <td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th style="font-weight: bold; vertical-align: center;">igv 18%
                                                            </th>
                                                            <th style="font-weight: bold; vertical-align: center;">
                                                              <h4 id="igv_servicio">0.00</h4>
                                                            </th>
                                                            </td>
                                                          </tr>
                                                          <!--TOTAL-->
                                                          <td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <th style="font-weight: bold; vertical-align: center;">Total
                                                          </th>
                                                          <!--Datos de
                                    impuestos--> <!--IGV-->
                                                          <th style="font-weight: bold;">
                                                            <h4 id="total_servicio"
                                                              style="font-weight: bold; vertical-align: center;">0.00</h4>
                                                            <input type="hidden" name="total_final_servicio" id="total_final_servicio">
                                                            <input type="hidden" name="subtotal_cotizacion_servicio"
                                                              id="subtotal_cotizacion_servicio">
                                                            <input type="hidden" name="total_igv_servicio" id="total_igv_servicio">
                                                            </td>
                                                            </tr>
                                                        </tfoot>
                                                        <tbody>
                                                        </tbody>
                                                      </table>
                                                    </div>
                                                    <div class="btn-list">
                                              <button type="submit" id="btnGuardar"
                                                class="btn btn-primary btn-sm btn-wave waves-effect waves-light">Guardar</button>
                                              <button id="btnCancelar" onclick="cancelarform()" type="button"
                                                class="btn btn-danger btn-sm btn-wave waves-effect waves-light">Cancelar</button>
                                            </div>

                                   
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                        </form>
                                      </div>
                              </section>
                            </div>
                            <!-- Modal generar nueva factura-->
                            <div class="modal fade text-left" id="myModalnfac" tabindex="-1" role="dialog" aria-labelledby="myModalnfac"
                              aria-hidden="true">
                              <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="myModalLabel1">Nueva Factura desde cotización</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body" id="formularioregistros">
                                    <form name="formularionfactura" id="formularionfactura" method="POST" autocomplete="off">
                                      <input type="hidden" name="idempresa2" id="idempresa2" value="<?php echo $_SESSION['idempresa']; ?>">
                                      <input type="hidden" name="idcotizacion" id="idcotizacion">
                                      <div class="row">
                                        <div class="mb-3 col-lg-2">
                                          <label>Serie</label>
                                          <select class="form-control" name="seriefactura" id="seriefactura"
                                            onchange="incrementarNumfactura();"></select>
                                          <input type="hidden" name="idnumeracionf" id="idnumeracionf">
                                          <input type="hidden" name="SerieRealfactura" id="SerieRealfactura">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Número</label>
                                          <input type="text" style="font-size: 12pt;" name="numero_factura" id="numero_factura" class="form-control"
                                            required="true" readonly style="font-size: 22px">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Fecha cotización:</label>
                                          <input type="hidden" name="horaf" id="horaf">
                                          <input type="date" style="font-size: 12pt;" class="form-control" name="fechadc" id="fechadc" readonly="">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Fecha operación:</label>
                                          <input type="date" style="font-size: 12pt;" class="form-control" name="fecemifa" id="fecemifa"
                                            required="true">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Moneda:</label>
                                          <select class="form-control" name="tipo_moneda_factura" id="tipo_moneda_factura">
                                            <option value="PEN">SOLES</option>
                                            <option value="USD">DOLARES</option>
                                          </select>
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>T. camb:</label>
                                          <input type="text" name="tcambiofactura" id="tcambiofactura" class="form-control" readonly>
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>RUC:</label>
                                          <input type="hidden" name="idclientef" id="idclientef">
                                          <input type="hidden" name="tipodocucli" id="tipodocucli">
                                          <input type="text" class="form-control" name="numero_documento_factura" id="numero_documento_factura"
                                            readonly>
                                        </div>
                                        <div class="mb-3 col-lg-3">
                                          <label>Nombre comercial:</label>
                                          <input type="text" class="form-control" name="razon_socialnfactura" id="razon_socialnfactura" readonly>
                                        </div>
                                        <div class="mb-3 col-lg-3">
                                          <label>Dirección</label>
                                          <input type="text" class="form-control" name="domicilionfactura" id="domicilionfactura" readonly>
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Tipo de factura:</label>
                                          <select class="form-control" name="tipofacturacoti" id="tipofacturacoti">
                                            TIPO DE FACTURA
                                            <option value="st">SELECCIONE TIPO DE FACTURA</option>
                                            <option value="productos" selected="true">PRODUCTOS</option>
                                            <option value="servicios">SERVICIOS</option>
                                          </select>
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Cotización:</label>
                                          <input type="text" class="form-control" name="nrefcoti" id="nrefcoti" readonly style="font-size: 18px;">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Correo cliente:</label>
                                          <input type="text" name="correocliente" id="correocliente" class="form-control">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Forma de pago:</label>
                                          <select class="form-control" name="tipopago" id="tipopago" onchange="contadocredito()">
                                            <option value="nn">SELECCIONE LA FORMA DE PAGO</option>
                                            <option value="Contado" selected>CONTADO</option>
                                            <option value="Credito">CRÉDITO</option>
                                          </select>
                                        </div>
                                        <div class="mb-3 col-lg-4">
                                          <label>Observaciones:</label>
                                          <textarea id="observaciocoti" name="observaciocoti" class="form-control" style="color: red;"
                                            readonly=""> </textarea>
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label style="top: 25px; position: relative;">Pago con tarjeta</label>
                                          <input class="form-control" type="checkbox" name="tarjetadc" id="tarjetadc" onclick="activartarjetadc();"
                                            style="top: 30px;">
                                          <input class="form-control" type="hidden" name="tadc" id="tadc">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label style="top: 25px; position: relative;">Pago Transferencia</label>
                                          <input type="checkbox" name="transferencia" id="transferencia" onclick="activartransferencia();"
                                            style="top: 30px;">
                                          <input class="form-control" type="hidden" name="trans" id="trans">
                                        </div>
                                        <div class="mb-3 col-lg-2">
                                          <label>Nro de operación:</label>
                                          <input type="text" name="nroreferenciaf" id="nroreferenciaf" class="form-control" style="color: blue;"
                                            placeholder="N° operación del comprobante">
                                        </div>
                                        <div id="tipopagodiv" style="display: none;">
                                          <div class="mb-3 col-lg-2">
                                            <label for="recipient-name" class="col-form-label">N° de cuotas:</label>
                                            <div class="input-group">
                                              <!-- <span style="cursor:pointer;" class="input-group-text" data-bs-toggle="modal" title="mostrar cuotas" data-bs-target="#modalcuotas" id="basic-addon1">&#9769;</span> -->
                                              <span style="cursor:pointer;" class="input-group-text" onclick="borrarcuotas()"
                                                title="Editar cuotas">&#10000;</span>
                                              <input name="ccuotas" id="ccuotas" onchange="focusnroreferencia()" class="form-control" value="1"
                                                onkeypress="return NumCheck(event, this)">
                                            </div>
                                          </div>
                                        </div>
                                        <!--  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                     <label>Condición:</label>
                       <input type="text" class="" name="condicionnfactura" id="condicionnfactura" readonly>
                     </div> -->
                                        <div class="form-group col-lg-12 col-sm-6 col-md-6 col-xs-12">
                                          <div class="table-responsive">
                                            <table id="detallefactura" class="table">
                                            </table>
                                          </div>
                                        </div>
                                        <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-6">
                                          <button class="btn btn-primary" type="submit" id="btnGuardar" data-toggle="tooltip"
                                            title="Guardar factura">
                                            <i class="fa fa-save"></i> Guardar</button>
                                          <button id="btnCancelar" class="btn btn-danger" data-toggle="tooltip" title="Cancelar"
                                            data-dismiss="modal" type="button"><i class="fa fa-arrow-circle-left"> Cancelar</i></button>
                                        </div>
                                        <div class="modal fade text-left" id="modalcuotas" tabindex="-1" role="dialog" aria-labelledby="modalcuotas"
                                          aria-hidden="true">
                                          <div class="modal-dialog modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h5 class="modal-title" id="modalcuotas">Pago al crédito</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                              </div>
                                              <div class="modal-body">
                                                <div class="container">
                                                  <div id="tipopagodiv" style="text-align: center;" class="row">
                                                    <div class="col-lg-6">
                                                      Monto de cuotas
                                                      <div id="divmontocuotas"></div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                      Fechas de pago
                                                      <div id="divfechaspago"></div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                  <i class="bx bx-x d-block d-sm-none"></i>
                                                  <span class="d-none d-sm-block">Confirmar</span>
                                                </button>
                                                <!-- <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                                 <i class="bx bx-check d-block d-sm-none"></i>
                                 <span class="d-none d-sm-block">Agregar</span>
                                 </button> -->
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <!-- Fin modal -->
                                        <div class="modal fade" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                          <div class="modal-dialog" style="width: 70% !important;">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title">CUOTAS Y FECHAS DE PAGO</h4>
                                              </div>
                                              <h2 id="totalcomp"></h2>
                                              <div class="table-responsive">
                                                <table class="table table-sm table-striped table-bordered table-condensed table-hover nowrap">
                                                  <tr>
                                                    <td>CUOTAS</td>
                                                    <td>
                                                      <div>
                                                        <label>Monto de cuotas</label>
                                                        <div id="">
                                                        </div>
                                                      </div>
                                                    </td>
                                                    <td>
                                                      <div>
                                                        <label>Fechas de pago</label>
                                                        <div id="">
                                                        </div>
                                                      </div>
                                                    </td>
                                                  </tr>
                                                </table>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                                <!-- <button type="button" class="btn btn-success" onclick="mesescontinuos()" >Meses continuos</button> -->
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <!-- Fin modal -->
                                      </div>
                                      <div class="modal-footer">
                                      </div>
                                  </div>
                                </div>
                              </div>
                              <!-- Modal -->
                              <div class="modal fade" id="modalTcambio">
                                <div class="modal-dialog" style="width: 100% !important;">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                    </div>
                                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                      <!-- <iframe border="0" frameborder="0" height="310" width="100%" src="https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias"></iframe> -->
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal"
                                        onclick="focotcambio()">Cerrar</button>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- FIN Modal -->
                              <!-- Modal -->
                              <div class="modal fade" id="myModalCli" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"
                                role="Documento">
                                <div class="modal-dialog" style="width: 100% !important;">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                      <h4 class="modal-title">Seleccione un cliente</h4>
                                    </div>
                                    <div class="table-responsive">
                                      <table id="tblaclientes" class="table table-striped table-bordered table-condensed table-hover" width=-5px>
                                        <thead>
                                          <th>Opciones</th>
                                          <th>Nombre</th>
                                          <th>RUC</th>
                                          <th>Dirección</th>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                          <th>Opciones</th>
                                          <th>Nombre</th>
                                          <th>RUC</th>
                                          <th>Dirección</th>
                                        </tfoot>
                                      </table>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Salir
                                      </button>
                                      <a data-toggle="modal" href="#ModalNcliente">
                                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick=""><i class="fa fa-user"
                                            data-toggle="tooltip" title="Nuevo cLiente"></i> Nuevo cliente</button>
                                      </a>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- Fin modal -->

                            </div>

                            <div class="modal fade text-left" id="ModalNcliente" role="dialog" aria-labelledby="ModalNcliente" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-scrollable" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="ModalNcliente">Añade nuevo cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <form role="form" method="post" name="busqueda" id="busqueda">
                                      <div class="row">
                                        <div class="mb-3 col-lg-12">
                                          <div class="input-group">
                                            <span type="submit" style="cursor:pointer;" value="burcarclientesunat" name="btn-submit" id="btn-submit"
                                              class="input-group-text">&#9769;</span>
                                            <input type="number" class="form-control" name="nruc" id="nruc" placeholder="Ingrese RUC o DNI"
                                              pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                                              autofocus>
                                          </div>
                                        </div>
                                      </div>
                                    </form>
                                    <form name="formularioncliente" id="formularioncliente" method="POST">
                                      <div class="row">
                                        <div class="">
                                          <input type="hidden" name="idpersona" id="idpersona">
                                          <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label class="col-form-label">Tipo Doc.:</label>
                                          <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" required>
                                            <option value="6"> RUC </option>
                                          </select>
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">N° Doc:</label>
                                          <input type="text" class="form-control" name="numero_documento3" id="numero_documento3" maxlength="20"
                                            placeholder="Documento" onkeypress="return focusRsocial(event, this)">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Razon Social:</label>
                                          <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="100"
                                            placeholder="Razón social" required onkeypress="return focusDomi(event, this)">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Domicilio Fizcal:</label>
                                          <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal"
                                            placeholder="Domicilio fiscal" required onkeypress="focustel(event, this)">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Departamento:</label>
                                          <input type="text" class="form-control" name="iddepartamento" id="iddepartamento"
                                            onchange="llenarCiudad()">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Ciudad:</label>
                                          <input type="text" class="form-control" name="idciudad" id="idciudad" onchange="llenarDistrito()">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Distrito:</label>
                                          <input type="text" class="form-control" name="iddistrito" id="iddistrito">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Teléfono:</label>
                                          <input type="number" class="form-control" name="telefono1" id="telefono1" maxlength="15"
                                            placeholder="Teléfono 1"
                                            pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                                            onkeypress="return focusemail(event, this)">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Correo:</label>
                                          <input type="text" class="form-control" name="email" id="email" maxlength="50" placeholder="CORREO"
                                            onkeypress="return focusguardar(event, this)">
                                        </div>
                                      </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                      <i class="bx bx-x d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Cancelar</span>
                                    </button>
                                    <button type="submit" id="btnguardarncliente" name="btnguardarncliente" class="btn btn-primary ml-1"
                                      data-bs-dismiss="modal">
                                      <i class="bx bx-check d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Agregar</span>
                                    </button>
                                  </div>
                                  </form>
                                  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
                                  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
                                    integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
                                    crossorigin="anonymous"></script>
                                  <script src="scripts/ajaxview.js"></script>
                                  <script>
                                    //============== original ===========================================================
                                    $(document).ready(function () {
                                      $("#btn-submit").click(function (e) {
                                        var $this = $(this);
                                        e.preventDefault();
                                        //============== original ===========================================================

                                        var documento = $("#nruc").val();
                                        $.post("../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + documento, function (data, status) {
                                          data = JSON.parse(data);
                                          if (data != null) {
                                            swal.fire({
                                              title: "Ya esta registrado cliente, se agregarán sus datos!",
                                              type: "success",
                                              timer: 2000,
                                              showConfirmButton: false
                                            });
                                            $('#idpersona').val(data.idpersona);
                                            $('#numero_documento2').val(data.numero_documento);
                                            $("#razon_social2").val(data.razon_social);
                                            $('#domicilio_fiscal2').val(data.domicilio_fiscal);
                                            $('#correocli').val(data.email);
                                            document.getElementById("btnAgregarArt").style.backgroundColor = '#367fa9';
                                            document.getElementById("btnAgregarArt").focus();
                                            $("#ModalNcliente").modal('hide');
                                          }
                                          else {
                                            //var numero = $('#nruc').val(), url_s = "consulta.php",parametros = {'dni':numero};
                                            var numero = $('#nruc').val(),
                                              //url_s = "https://incared.com/api/apirest";
                                              url_s = "../ajax/factura.php?op=consultaRucSunat&nroucc=" + numero;
                                            parametros = { 'action': 'getnumero', 'numero': numero }

                                            if (numero == '') {
                                              alert("El campo documento esta vacio ");
                                              $.ajaxunblock();
                                            } else {
                                              $.ajax({
                                                type: 'POST',
                                                url: url_s,
                                                dataType: 'json',
                                                //data:parametros,

                                                beforeSend: function () {
                                                },
                                                complete: function (data) {

                                                },
                                                success: function (data) {
                                                  $('.before-send').fadeOut(500);
                                                  if (!jQuery.isEmptyObject(data.error)) {
                                                    alert(data.error);
                                                  } else {

                                                    $("#numero_documento3").val(data.numeroDocumento);
                                                    $('#razon_social').val(data.nombre);
                                                    $('#domicilio_fiscal').val(data.direccion);
                                                    $('#iddistrito').val(data.distrito);
                                                    $('#idciudad').val(data.provincia);
                                                    $('#iddepartamento').val(data.departamento);
                                                  }
                                                  //$.unblockUI();
                                                  //$.ajaxunblock();
                                                },
                                                error: function (data) {
                                                  alert("Problemas al tratar de enviar el formulario");
                                                  //$.unblockUI();
                                                  //$.ajaxunblock();
                                                }
                                              });
                                            }

                                            document.getElementById("btnguardarncliente").focus();
                                            //============== original ===========================================================
                                          }
                                          //============== original ===========================================================
                                        });

                                      });
                                    });
                                  </script>
                                </div>
                                <!-- 
              <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i>
                      Cerrar</button>
              </div> -->
                              </div>
                            </div>

                            <!-- Fin modal -->
                            <div class="modal fade text-left" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalArt"
                              aria-hidden="true">
                              <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="myModalArt">Seleccione un Artículo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                      <div class="mb-3 col-lg-3">
                                        <label for="message-text" class="col-form-label">Selecciona almacen</label>
                                        <select class="form-control" id="almacenlista" onchange="listarArticulos()">
                                        </select>
                                      </div>
                                      <div class="mb-3 col-lg-3">
                                        <label for=""></label><br>
                                        <button class="btn btn-success" id="refrescartabla" style="top: 3px;" onclick="refrescartabla()"><i
                                            class="fa fa-refresh fa-spin fa-1x fa-fw"></i>
                                          <span class="sr-only"></span>Actualizar</button>
                                      </div>
                                    </div>
                                    <div class="table-responsive" id="listadoregistros">
                                      <table id="tblarticulos" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                                        <thead style="text-align:center;">
                                          <th>Opciones</th>
                                          <th>Nombre</th>
                                          <th>Código</th>
                                          <th>U.M.</th>
                                          <th>Precio</th>
                                          <th>Stock</th>
                                          <th>Imagen</th>
                                        </thead>
                                        <tbody style="text-align:center;">
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                      <i class="bx bx-x d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Cerrar</span>
                                    </button>
                                    <!-- <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
               <i class="bx bx-check d-block d-sm-none"></i>
               <span class="d-none d-sm-block">Agregar</span>
               </button> -->
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Modal -->
                            <div class="modal fade" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog" style="width: 100% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Seleccione un Artículo</h4>
                                    <div class="form-group  col-lg-2 col-sm-12 col-md-12 col-xs-12">
                                      <h4 class="modal-title">Seleccione almacen</h4>
                                    </div>
                                    <div class="form-group  col-lg-2 col-sm-12 col-md-12 col-xs-12">
                                    </div>
                                  </div>
                                  <div class="form-group  col-lg-12 col-sm-12 col-md-12 col-xs-12">
                                    <div class="table-responsive">
                                      <table id="tblarticulos" class="table table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                          <th>Opciones</th>
                                          <th>Nombre</th>
                                          <th>Código</th>
                                          <th>U.M.</th>
                                          <th>Precio</th>
                                          <th>Stock</th>
                                          <th>Imagen</th>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal  Servicio o inmueble-->
                            <div class="modal fade" id="myModalServ" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog" style="width: 100% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Seleccione un bien o servicio</h4>
                                  </div>
                                  <div class="table-responsive">
                                    <table id="tblservicios" class="table table-striped table-bordered table-condensed table-hover">
                                      <thead>
                                        <th>Opciones</th>
                                        <th>Descripción</th>
                                        <th>Código</th>
                                        <th>Valor</th>
                                      </thead>
                                      <tbody>
                                      </tbody>
                                      <tfoot>
                                        <th>Opciones</th>
                                        <th>Descripción</th>
                                        <th>Código</th>
                                        <th>Precio</th>
                                      </tfoot>
                                    </table>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <input type="hidden" name="idultimocom" id="idultimocom">
                            <!-- Modal -->
                            <div class="modal fade" id="modalPreview" tabindex="-1" aria-labelledby="modalPreviewLabel" aria-hidden="true">
                              <div class="modal-dialog modal-xl">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h4 class="modal-title">COMPROBANTE</h4>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          <iframe name="modalCom" id="modalCom" border="0" frameborder="0" width="100%" style="height: 800px;" src=""></iframe>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cerrar</button>
                                      </div>
                                  </div>
                              </div>
                          </div>

                            <!-- Fin modal -->

                            <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
              <script type="text/javascript" src="scripts/cotizacion.js"></script>
              <?php
}
ob_end_flush();
?>