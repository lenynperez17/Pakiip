<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Ventas'] == 1) {
    ?>
    <style>
      input[type=number].hidebutton::-webkit-inner-spin-button,
      input[type=number].hidebutton::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }
    </style>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <!-- <link rel="stylesheet" href="style.css"> -->
    <div class="">
      <!-- Main content -->
      <section class="">
        <div class="content-header">
          <h1>Boleta electrónica <button class="btn btn-secondary btn-sm" id="btnagregar"
              onclick="mostrarform(true); limpiar()">Nuevo</button> <button class="btn btn-success btn-sm"
              id="refrescartabla" onclick="refrescartabla()">Refrescar</button></h1>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="automaticoboleta" hidden>
              OFF <input checked type="checkbox" name="chk1" id="chk1" onclick="pause()" data-toggle="tooltip"
                title="Mostrar estado de enviados a SUNAT"> ON
            </div>
            <!-- centro -->
            <div class="table-responsive" id="listadoregistros">
              <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                <thead style="text-align:center;">
                  <th>Exportar</th>
                  <!--  <th><i class="fa fa-send"></i></th> -->
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Vendedor</th>
                  <th>Comprobante</th>
                  <th>Pago</th>
                  <th>Producto atendido</th>
                  <th>Total</th>
                  <!-- <th></th>
                <th></th> -->
                  <th>Sunat</th>
                  <th>XML</th>
                  <th>CDR</th>
                  <th>PDF</th>
                </thead>
                <tbody style="text-align:center;">
                </tbody>
              </table>
            </div>
            <div class="panel-body" id="formularioregistros">
              <form name="formulario" id="formulario" method="POST" autocomplete="off">
                <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">

                <div class="row">
                  <div class="col-md-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Serie:</label>
                            <SELECT class="form-control" name="serie" id="serie" onchange="incremetarNum()"></SELECT>
                            <input type="hidden" name="idnumeracion" id="idnumeracion">
                            <input type="hidden" name="SerieReal" id="SerieReal">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Número:</label>
                            <input type="text" name="numero_boleta" id="numero_boleta" class="form-control" required="true"
                              readonly>
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Tipo de
                              boleta:</label>
                            <select class="form-control" name="tipoboleta" id="tipoboleta" onchange="cambiarlistado()">
                              TIPO DE BOLETA
                              <option value="st">SELECCIONE TIPO DE BOLETA</option>
                              <option value="productos" selected="true">PRODUCTOS</option>
                              <option value="servicios">SERVICIOS</option>
                            </select>
                          </div>
                          <!--Campos para guardar comprobante Factura-->
                          <input type="hidden" name="idboleta" id="idboleta">
                          <input type="hidden" name="firma_digital_36" id="firma_digital_36" value="44477344">
                          <!--Datos de empresa -->
                          <input type="hidden" name="idempresa" id="idempresa"
                            value="<?php echo $_SESSION['idempresa']; ?>">
                          

                          <input type="hidden" name="tipo_documento_06" id="tipo_documento_06" value="03">
                          <input type="hidden" name="numeracion_07" id="numeracion_07" value="">
                          <!--Datos del cliente-->
                          <input type="hidden" name="idcliente" id="idcliente">
                          <input type="hidden" name="tipo_documento_cliente" id="tipo_documento_cliente" value="0">
                          <!--Datos del cliente-->
                          <!--Datos de impuestos-->
                          <input type="hidden" name="codigo_tipo_15_1" id="codigo_tipo_15_1" value="1001">
                          <input type="hidden" name="codigo_tributo_h" id="codigo_tributo_h">
                          <input type="hidden" name="nombre_tributo_h" id="nombre_tributo_h">
                          <input type="hidden" name="codigo_internacional_5" id="codigo_internacional_5" value="">
                          <input type="hidden" name="tipo_documento_25_1" id="tipo_documento_25_1" value="">
                          <input type="hidden" name="codigo_leyenda_26_1" id="codigo_leyenda_26_1" value="1000">
                          <input type="hidden" name="version_ubl_37" id="version_ubl_37" value="2.0">
                          <input type="hidden" name="version_estructura_38" id="version_estructura_38" value="1.0">
                          <input type="hidden" name="tasa_igv" id="tasa_igv" value="0.18">
                          <!--Fin de campos-->
                          <input type="hidden" name="codigo_precio_14_1" id="codigo_precio" value="01">
                          <!--DETALLE-->
                          <input type="hidden" name="hora" id="hora">
                          <!--DETALLE-->
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Fe. emisión:</label>
                            <input type="date" disabled="true" style="font-size: 12pt;" class="form-control"
                              name="fecha_emision_01" id="fecha_emision_01" disabled="true" required="true"
                              onchange="focusTdoc()">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">F.
                              vencimiento:</label>
                            <input type="date" class="form-control" name="fechavenc" id="fechavenc" required="true"
                              min="<?php echo date('Y-m-d'); ?>">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Moneda:</label>
                            <select class="form-control" name="tipo_moneda_24" id="tipo_moneda_24"
                              onchange="tipodecambiosunat();">
                              <option value="PEN" selected="true">PEN</option>
                              <option value="USD">USD</option>
                            </select>
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">T. camb:</label>
                            <input type="text" name="tcambio" id="tcambio" class="form-control" readonly="true">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Documento:</label>
                            <select class="form-control" name="tipo_doc_ide" id="tipo_doc_ide" onchange="focusI()">
                              <!-- <OPTION value="0">S/D</OPTION>
                            <OPTION value="1">DNI</OPTION>
                            <OPTION value="4">C.E.</OPTION>
                            <OPTION value="7">PASAPORTE</OPTION>
                            <OPTION value="A">CED. D. IDE.</OPTION> -->
                            </select>
                          </div>
                          <div class="mb-3 col-lg-12">
                            <label for="recipient-name" class="col-form-label">Nro (Presione
                              Enter):</label>
                            <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                              placeholder="Número" value="-" required="true" onkeypress="agregarClientexDoc(event)"
                              onchange="agregarClientexDocCha();">
                            <div id="suggestions">
                            </div>
                          </div>
                          <div class="mb-3 col-lg-12">
                            <label for="recipient-name" class="col-form-label">Nombres y
                              apellidos:</label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="50"
                              placeholder="NOMBRE COMERCIAL" width="50x" value="-" required="true" onkeyup="mayus(this);"
                              onkeypress="focusDir(event)" onblur="quitasuge2()">
                            <div id="suggestions2">
                            </div>
                          </div>
                          <div class="mb-3 col-lg-12">
                            <label for="recipient-name" class="col-form-label">Dirección:</label>
                            <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal" value="-"
                              onkeyup="mayus(this);" placeholder="Dirección" onkeypress="agregarArt(event)">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Vendedor:</label>
                            <select autofocus name="vendedorsitio" id="vendedorsitio" class="form-control">
                            </select>
                          </div>
                          <div hidden class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Nro Guia:</label>
                            <input type="text" name="guia_remision_25" id="guia_remision_25" class="form-control"
                              placeholder="NRO DE GUÍA">
                          </div>
                          <div class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Impuesto:</label>
                            <select class="form-control" name="codigo_tributo_18_3" id="codigo_tributo_18_3"
                              onchange="tributocodnon()">TRIBUTO</select>
                          </div>
                          <div hidden class="mb-3 col-lg-6">
                            <label for="recipient-name" class="col-form-label">Nro
                              transferencia:</label>
                            <input type="text" name="nroreferencia" id="nroreferencia" class="form-control"
                              style="color: blue;" placeholder="N° Operación">
                          </div>
                          <div class="mt-2 mb-3 col-lg-12">
                            <textarea name="descripcion_leyenda_26_2" id="descripcion_leyenda_26_2" cols="5" rows="3"
                              class="form-control" placeholder="Observaciones"></textarea>
                          </div>
                          <div class="mb-3 col-lg-12">
                            <label for="recipient-name" class="col-form-label">Tipo de pago:</label>
                            <select class="form-control" name="tipopago" id="tipopago" onchange="contadocredito()">
                              <option value="nn">SELECCIONE LA FORMA DE PAGO</option>
                              <option value="Contado" selected>CONTADO</option>
                              <option value="Credito">CRÉDITO</option>
                              <option value="Yape">YAPE</option>
                              <option value="Tarjeta">TARJETA</option>
                              <option value="Transferencia">TRANSFERENCIA</option>
                              <option value="Plin">PLIN</option>
                            </select>
                          </div>
                          <div id="tipopagodiv" style="display: none;">
                            <div class="mb-3 col-lg-12">
                              <label for="recipient-name" class="col-form-label">N° de
                                cuotas:</label>
                              <div class="input-group">
                                <span style="cursor:pointer;" class="input-group-text" data-bs-toggle="modal"
                                  title="mostrar cuotas" data-bs-target="#modalcuotas" id="basic-addon1">&#9769;</span>
                                <span style="cursor:pointer;" class="input-group-text" onclick="borrarcuotas()"
                                  title="Editar cuotas">&#10000;</span>
                                <input name="ccuotas" id="ccuotas" onchange="focusnroreferencia()" class="form-control"
                                  value="1" onkeypress="return NumCheck(event, this)">
                              </div>
                            </div>
                          </div>
                          <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" hidden>
                            <img src="../files/articulos/tarjetadc.png" data-toggle="tooltip" title="Pago por tarjeta">
                            <input type="checkbox" name="tarjetadc" id="tarjetadc" onclick="activartarjetadc();">
                            <input type="hidden" name="tadc" id="tadc">
                          </div>
                          <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" hidden>
                            <img src="../files/articulos/transferencia.png" data-toggle="tooltip"
                              title="Pago por transferencia"> <input type="checkbox" name="transferencia" id="transferencia"
                              onclick="activartransferencia();">
                            <input type="hidden" name="trans" id="trans">
                          </div>
                          <div class="modal fade text-left" id="modalcuotas" tabindex="-1" role="dialog"
                            aria-labelledby="modalcuotas" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable" role="document">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="modalcuotas">Pago al crédito
                                  </h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                                    <span class="d-none d-sm-block">Cancelar</span>
                                  </button>
                                  <button id="btnGuardar" type="submit" class="btn btn-primary ml-1"
                                    data-bs-dismiss="modal">
                                    <i class="bx bx-check d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Agregar</span>
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="modal fade" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog" style="width: 70% !important;">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                                  <h4 class="modal-title">CUOTAS Y FECHAS DE PAGO</h4>
                                </div>
                                <h2 id="totalcomp"></h2>
                                <div class="table-responsive">
                                  <table
                                    class="table table-sm table-striped table-bordered table-condensed table-hover nowrap">
                                    <tr>
                                      <td>CUOTAS</td>
                                      <td>
                                        <div>
                                          <label>Monto de cuotas</label>
                                          <div id="divmontocuotas">
                                          </div>
                                        </div>
                                      </td>
                                      <td>
                                        <div>
                                          <label>Fechas de pago</label>
                                          <div id="divfechaspago">
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
                      </div>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <div class="card">
                      <div class="card-body">
                        <input type="hidden" name="itemno" id="itemno" value="0">
                        <button style="margin-left:0px;" type="button" data-bs-toggle="modal" data-bs-target="#myModalArt"
                          id="btnAgregarArt" class="btn btn-danger btn-sm mb-3" onclick="cambiarlistadoum2()">
                          Agregar Productos o serivicios
                        </button>
                        <div class="mb-3 col-lg-12">
                          <!-- <label for="recipient-name" class="col-form-label">Código barra:</label> -->
                          <input type="text" name="codigob" id="codigob" class="form-control"
                            onkeypress="agregarArticuloxCodigo(event)" onkeyup="mayus(this);"
                            placeholder="Digite o escanee el código de barras" onchange="quitasuge3()"
                            style="background-color: #F5F589;">
                          <div id="suggestions3">
                          </div>
                        </div>
                        <!-- <button data-bs-toggle="modal" data-bs-target="#myModalnuevoitem" id="btnAgregarArt" type="button" class="btn btn-danger btn-sm" onclick="cambiarlistadoum()"> Otra u. medida </button> -->
                        <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                          <label style="font-size: 16pt; color: red;" hidden="true" id="mensaje700"
                            name="mensaje700">Agregar DNI o C.E. del
                            cliente.</label>
                        </div>
                        <div class="table-responsive">
                          <table id="detalles" class="table table-striped" style="text-align:center;">
                            <thead align="center" style="">
                              <th>Sup.</th>
                              <th>Item</th>
                              <th>Artículo</th>
                              <!-- <th style="color:white;">Descripción</th> -->
                              <th>Cantidad</th>
                              <th>Dcto. %</th>
                              <!-- <th style="color:white;">Cód. Prov.</th> -->
                              <!-- <th style="color:white;">-</th> -->
                              <th>U.M.</th>
                              <th>Prec. Uni.</th>
                              <th>Val. u.</th>
                              <th>Stock</th>
                              <th>Importe</th>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>
                        </div>
                        <div class="form-group" style="background-color: #b4d4ee;">
                          <div class="text-center">
                            <div style="color:#081A51; font-weight: bold">Items de venta</div>
                            <!-- <button style="background:#081A51; border:none;" type="button" value="1" name="botonpago1" id="botonpago1"
                          class="btn btn-success btn-sm"
                          onclick="botonrapido1()">1</button>
                          <button style="background:#081A51; border:none;" type="button" value="2" name="botonpago2" id="botonpago2"
                          class="btn btn-success btn-sm"
                          onclick="botonrapido2()">2</button>
                          <button style="background:#081A51; border:none;" value="5" type="button" name="botonpago5" id="botonpago5"
                          class="btn btn-success btn-sm"
                          onclick="botonrapido5()">5</button>
                          <button style="background:#081A51; border:none;" value="10" type="button" name="botonpago10" id="botonpago10"
                          class="btn btn-success btn-sm"
                          onclick="botonrapido10()">10</button>
                          <button style="background:#081A51; border:none;" value="20" name="botonpago20" type="button" id="botonpago20"
                          class="btn btn-success btn-sm"
                          onclick="botonrapido20()">20</button>
                          <button style="background:#081A51; border:none;" ="button" value="50" name="botonpago50" type="button"
                          id="botonpago50" class="btn btn-success btn-sm"
                          onclick="botonrapido50()">50</button>
                          <button style="background:#081A51; border:none;" value="100" name="botonpago100" id="botonpago100"
                          type="button" class="btn btn-success btn-sm"
                          onclick="botonrapido100()">100</button>
                          <button style="background:#081A51; border:none;" value="200" name="botonpago200" id="botonpago200"
                          type="button" class="btn btn-success btn-sm"
                          onclick="botonrapido200()">200</button> -->
                            <!-- <button style="background:#081A51; border:none;" value="200"
                          name="botonpago200" id="botonpago200" type="button"
                          class="btn btn-success btn-sm" onclick="agregarMontoPer()">Agregar
                          monto perzonalizado</button> -->
                          </div>
                        </div>

                


                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-4">
                          <div class="card" style="border:none;">
                            <div class="card custom-card">
                              <div class="">
                                <h4 class="card-title">Detalle del ingreso</h4>
                                <p class="">
                                  <th id="CuadroT" style="font-weight: bold; background-color:#FFB887;">
                                    <div style="display:flex;">
                                      <label for="">SubT. : </label>
                                      <!-- <h6 hidden style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" id="subtotal"> 0.00</h6> -->
                                      <input placeholder="0.00" readonly
                                        style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; text-align: right; border:none;width: 95px;"
                                        name="subtotal_boleta" id="subtotal_boleta">
                                    </div>
                                    <div style="display:flex;">
                                      <label for="">IGV : </label>
                                      <input placeholder="0.00" readonly
                                        style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; text-align: right; border:none; width: 95px;"
                                        name="total_igv" id="total_igv">
                                    </div>
                                    <div style="display:flex;">
                                      <label for="">Descuento : </label>
                                      <h6
                                        style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; "
                                        name="" id="tdescuentoL"> 0.00</h6>
                                      <!-- <h6 hidden style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" name="total_dcto" id="total_dcto"> 0.00</h6> -->
                                    </div>
                                    <div style="display:flex;">
                                      <label for="">Total a pagar : </label>
                                      <h6
                                        style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;"
                                        id="total"> 0.00</h6>
                                    </div>
                                    <br>
                                    <h5 class="card-title">Calcular vuelto</h5>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                      <label for="">Pago con : </label>
                                      <!-- <h6 name="ipagado" id="ipagado"
                                  style="font-weight: bold; margin: 0 auto; top: 10px; margin-right: 0px;">
                                  0.00</h6> -->
                                      <input type="number" class="form-control text-end hidebutton" name="ipagado"
                                        id="ipagado" value="0.00" style="width: 100px;">
                                      <!-- <input hidden name="ipagado_final" id="ipagado_final" style="font-weight: bold; margin-left: 75px; width: 100px;"> -->
                                    </div>
                                    <div class="d-flex align-items-center">
                                      <label for="" id="vuelto_text">Vuelto : </label>
                                      <h6 style="font-weight: bold; margin: 0 auto; margin-right: 0px;" name="saldo"
                                        id="saldo"> 0.00</h6>
                                      <!-- <h6 hidden style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" name="saldo_final" id="saldo_final"> 0.00</h6> -->
                                    </div>
                                    <input type="hidden" name="total_final" id="total_final">
                                    <input type="hidden" name="pre_v_u" id="pre_v_u">
                                    <!-- <input type="hidden" name="subtotal_boleta" id="subtotal_boleta"> -->
                                    <!-- <input type="hidden" name="total_igv" id="total_igv"> -->
                                    <input type="hidden" name="total_icbper" id="total_icbper">
                                    <input type="hidden" name="total_dcto" id="total_dcto">
                                    <input type="hidden" name="ipagado_final" id="ipagado_final">
                                  </th>
                                  <!--Datos de impuestos--> <!--TOTAL-->
                                  <input type="hidden" name="saldo_final" id="saldo_final">
                                  </th><!--Datos de impuestos--> <!--TOTAL-->
                                <h4 hidden id="icbper">0</h4>
                                </th><!--Datos de impuestos--> <!--TOTAL-->
                                <!-- </th> -->
                                </p>
                              </div>
                            </div>
                          </div>
                        </div>
                        <button style="margin-left:0px;" class="btn btn-primary btn-sm" type="submit" id="btnGuardar"
                          data-toggle="tooltip" title="Guardar boleta"><i class="fa fa-save"></i>
                          Guardar
                        </button>
                        <button style="margin-left:0px;" id="btnCancelar" class="btn btn-danger btn-sm"
                          onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left" data-toggle="tooltip"
                            title="Cancelar"></i> Cancelar</button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <!--Fin centro -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!--Fin-Contenido-->
    <!-- Modal -->
    <div class="modal fade" id="myModalCli" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->
    <!-- Modal   SELECCION DE PRODUCTO O SERVICIO -->
    <div class="modal fade" id="myModalnuevoitem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
      aria-hidden="true" role="Documento">
      <div class="modal-dialog" style="width: 50% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Nuevo Item</h4>
          </div>
          <div class="col-sm-12">
            <div class="form-inline">
              <div class="form-group">
                <table>
                  <tr>
                    <td align="center"><a onclick="cargarbien()" name="tipoitem" id="tipoitem" value="bien"><img
                          src="../public/images/producto.png"><br>Productos</a>
                    </td>
                    <!-- <td align="center"><a onclick="cargarservicio()" name="tipoitem" id="tipoitem" value="servicio"><img src="../public/images/servicio.png"><br>Servicios</a></td> -->
                    <input type="hidden" name="familia" id="familia">
                    <input type="hidden" name="nombre" id="nombre">
                    <input type="hidden" name="codigo_proveedor" id="codigo_proveedor">
                    <input type="hidden" name="stock" id="stock">
                    <input type="hidden" name="cicbper" id="cicbper">
                    <input type="hidden" name="cantidadrealitem" id="cantidadrealitem">
                    <input type="hidden" name="factorcitem" id="factorcitem">
                    <input type="hidden" name="umedidaoculto" id="umedidaoculto">
                  </tr>
                  <tr>
                    <td>Articulo:</td>
                    <td> <input type="text" name="nombrearti" id="nombrearti" readonly></td>
                    <td>Cantidad:</td>
                    <td><input type="text" name="icantidad" id="icantidad" class="" onkeyup="calculartotalitem();"
                        value="1">
                    </td>
                  </tr>
                  <tr>
                    <td>Stock:</td>
                    <td><input type="text" name="stoc" id="stoc" readonly></td>
                    <td>Précio unitario:</td>
                    <td><input type="text" name="ipunitario" id="ipunitario" class="" onkeyup="calvaloruniitem();"></td>
                  </tr>
                  <tr>
                    <td>ABRE:</td>
                    <td><input type="text" name="iumedida" id="iumedida" class="" readonly size="4"></td>
                    <td>Cambiar medida:</td>
                    <td><select name="unidadm" id="unidadm" class="" onchange="cambioUm()" size="2"></select></td>
                  </tr>
                  <tr>
                    <td>Valor unitario:</td>
                    <td><input type="text" name="ivunitario" id="ivunitario" class="" readonly></td>
                    <td>Código:</td>
                    <td><input type="hidden" name="iiditem" id="iiditem" class="">
                      <input type="text" name="icodigo" id="icodigo" class="">
                    </td>
                  </tr>
                  <tr>
                    <td>Descuento:</td>
                    <td><input type="text" name="idescuento" id="idescuento" class="" readonly></td>
                    <td>Descripción:</td>
                    <td><textarea name="idescripcion" id="idescripcion" class="">  </textarea></td>
                  </tr>
                  <tr>
                    <td>IGV (18%):</td>
                    <td>
                      <input type="radio" name="iigv" id="iigv" value="grav" onclick="calcuigv()" checked>
                      Gvdo &nbsp;&nbsp;
                      <input type="radio" name="iigv" id="iigv" value="exo" onclick="calcuigv()" disabled>
                      Exo.&nbsp;&nbsp;
                      <input type="radio" name="iigv" id="iigv" value="ina" onclick="calcuigv()" disabled>
                      Ina.
                    </td>
                    <td>ICBPER:</td>
                    <td>
                      <!-- <input type="text" name="iicbper1" id="iicbper1" class="" size="4"> -->
                      <input type="text" name="iicbper2" id="iicbper2" xclass="" readonly>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    </td>
                    <td><input type="text" name="iigvresu" id="iigvresu" class="form-control" value="0" readonly="">
                    </td>
                    <td>Impuesto ICBPER:</td>
                    <td><input type="text" name="iimpicbper" id="iimpicbper" class="form-control" readonly=""></td>
                  </tr>
                  <tr>
                  </tr>
                  <tr></tr>
                  <tr>
                    <td>Importe total del Item:</td>
                    <td><input type="text" name="iimportetotalitem" id="iimportetotalitem" class="" readonly></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td align="justify">
                      <button type="button" class="btn btn-success" data-dismiss="modal" onclick="agregarItemdetalle()"><i
                          class="fa fa-check"></i> Aceptar </button>
                    </td>
                    <td align="justify">
                      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cancelar
                      </button>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->
    <!-- Modal -->
    <div class="modal fade text-left" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalArt"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalArt">Agrega tu producto o servicio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Precio:</label>
                <select class="form-control" id="tipoprecio" onchange="listarArticulos()">
                  <option value='1'>PRECIO PÚBLICO</option>
                  <option value='2'>PRECIO POR MAYOR</option>
                  <option value='3'>PRECIO DISTRIBUIDOR</option>
                </select>
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Sucursal:</label>
                <select class="form-control" id="almacenlista" onchange="listarArticulos()">
                </select>
              </div>
              <div class="mb-3 col-lg-6">
                <button class="btn btn-danger" id="refrescartabla" data-bs-target="#modalnuevoarticulo"
                  data-bs-toggle="modal" onclick="nuevoarticulo()">
                  <span class="sr-only"></span>Agregar producto al inventario</button>
              </div>
              <div class="mb-3 col-lg-6">
                <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()">
                  <span class="sr-only"></span>Actualizar Tabla</button>
              </div>
              <div class="mb-3 col-lg-12">
                <div class="table-responsive">
                  <table id="tblarticulos" class="table table-striped table-bordered  table-hover">
                    <thead>
                      <th>+++</th>
                      <th>Nombre</th>
                      <th>Código</th>
                      <th>Un. Med.</th>
                      <th>Precio</th>
                      <th>Stock</th>
                      <th>Imagen</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Cerrar</span>
            </button>
            <!-- <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
              <i class="bx bx-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Guardar</span>
            </button> -->
          </div>
        </div>
      </div>
    </div>
    <!-- Modal  SERVICIOS -->
    <div class="modal fade" id="myModalserv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione un bien o servicio</h4>
          </div>
          <div class="table-responsive">
            <table id="tblaservicios" class="table table-striped table-bordered table-condensed table-hover">
              <thead>
                <th>Opciones</th>
                <th>Descripción</th>
                <th>Código</th>
                <th>um</th>
                <th>Precio</th>
                <th>stock </th>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
                <th>Opciones</th>
                <th>Descripción</th>
                <th>Código</th>
                <th>um</th>
                <th>Precio</th>
                <th>stock </th>
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
    <!-- Modal -->
    <div class="modal fade" id="myModalArtItem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" style="width: 65% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione tipo de precio</h4>
            <select class="" id="tipoprecio" style="background-color: #85d197;">
              <option value='1'>PRECIO PÚBLICO</option>
              <option value='2'>PRECIO POR MAYOR</option>
              <option value='3'>PRECIO DISTRIBUIDOR</option>
            </select>
            <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla2()"><i
                class="fa fa-refresh fa-spin fa-1x fa-fw"></i>
              <span class="sr-only"></span>Actualizar</button>
          </div>
          <div class="table-responsive">
            <table id="tblarticulositem" name="tblarticulositem" class="table table-striped table-bordered  table-hover">
              <thead>
                <th></th>
                <th>Nombre</th>
                <th>Código</th>
                <th>Un. Med.</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Imagen</th>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
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
    <!-- Modal -->
    <div class="modal fade" id="modalTcambio">
      <div class="modal-dialog" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
          </div>
          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- <iframe border="1" frameborder="1" height="310" width="100%" src="https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias"></iframe> -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <input type="hidden" name="idultimocom" id="idultimocom">
    <!-- Modal VISTA PREVIA IMPRESION -->
    <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">SELECCIONE EL FORMATO DE IMPRESIÓN</h4>
          </div>
          <div class="text-center">
            <a onclick="preticket()">
              <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <img src="../files/vistaprevia/ticket.jpg">
              </div>
            </a>
            <a onclick="prea42copias()">
              <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <img src="../files/vistaprevia/a42copias.jpg">
              </div>
            </a>
            <a onclick="prea4completo()">
              <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <img src="../files/vistaprevia/a4completo.jpg">
              </div>
            </a>
            <img src="../files/vistaprevia/hoja.jpg"> RECUERDE QUE PUEDE ENVIAR LOS COMPROBANTES POR CORREO. EVITE
            IMPRIMIR.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->
    <!-- Modal a4-->
    <div class="modal fade text-left" id="modalPreview2" tabindex="-1" role="dialog" aria-labelledby="modalPreview2"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPreview2">PDF Boleta A4</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <iframe name="modalCom" id="modalCom" border="0" frameborder="0" width="100%" style="height: 800px;"
              marginwidth="1" src="">
            </iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Cerrar</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- modal ticket -->
    <div class="modal fade text-left" id="modalPreviewticket" tabindex="-1" role="dialog"
      aria-labelledby="modalPreviewticket" aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPreviewticket">Ticket de Boleta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="printMe" id="printSection">
              <iframe name="modalComticket" id="modalComticket" border="0" frameborder="0" width="100%"
                style="height: 800px;" marginwidth="1" src="">
              </iframe>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Cerrar</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="ModalNcliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" style="width: 100% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title ">Nuevo cliente</h4>
          </div>
          <div class="modal-body">
            <div class="container">
              <form role="form" method="post" name="busqueda" id="busqueda">
                <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                  <input type="number" class="" name="nruc" id="nruc" placeholder="Ingrese RUC o DNI"
                    pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                    autofocus>
                </div>
                <button type="submit" class="btn btn-success" name="btn-submit" id="btn-submit" value="burcarclientesunat">
                  <i class="fa fa-search"></i> Buscar SUNAT
                </button>
              </form>
            </div>
            <form name="formularioncliente" id="formularioncliente" method="POST">
              <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
              <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
              <div class="">
                <input type="hidden" name="idpersona" id="idpersona">
                <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
              </div>
              <div class="form-group col-lg-1 col-md-12 col-sm-12 col-xs-12">
                <label>Tipo Doc.:</label>
                <select class=" select-picker" name="tipo_documento" id="tipo_documento" required>
                  <option value="6"> RUC </option>
                </select>
              </div>
              <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                <label>N. Doc.:</label>
                <input type="text" class="" name="numero_documento3" id="numero_documento3" maxlength="20"
                  placeholder="Documento" onkeypress="return focusRsocial(event, this)">
              </div>
              <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                <label>Razón social:</label>
                <input type="text" class="" name="razon_social3" id="razon_social3" maxlength="100"
                  placeholder="Razón social" required onkeypress="return focusDomi(event, this)">
              </div>
              <div class="form-group col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <label>Domicilio:</label>
                <input type="text" class="" name="domicilio_fiscal3" id="domicilio_fiscal3" maxlength="100"
                  placeholder="Domicilio fiscal" required onkeypress="focustel(event, this)">
              </div>
              <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                <input type="number" class="" name="telefono1" id="telefono1" maxlength="15" placeholder="Teléfono 1"
                  pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                  onkeypress="return focusemail(event, this)">
              </div>
              <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                <input type="text" class="" name="email" id="email" maxlength="50" placeholder="CORREO" required="true"
                  onkeypress="return focusguardar(event, this)">
              </div>
              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                <button class="btn btn-primary" type="submit" id="btnguardarncliente" name="btnguardarncliente"
                  value="btnGuardarcliente">
                  <i class="fa fa-save"></i> Guardar
                </button>
              </div>
              <!--<div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
            <iframe border="0" frameborder="0" height="450" width="100%" marginwidth="1"
            src="https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias">
            </iframe>
            </div> -->
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
                      alert("Ya esta registrado cliente, se agregarán sus datos!");
                      $('#idpersona').val(data.idpersona);
                      $('#numero_documento').val(data.numero_documento);
                      $("#razon_social").val(data.razon_social);
                      $('#domicilio_fiscal').val(data.domicilio_fiscal);
                      //$('#correocli').val(data.email);
                      document.getElementById("btnAgregarArt").style.backgroundColor = '#367fa9';
                      document.getElementById("btnAgregarArt").focus();
                      $("#ModalNcliente").modal('hide');
                    }
                    else {

                      $.ajax({
                        type: 'POST',
                        url: "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + numero,
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
                          }
                          $.ajaxunblock();
                        },
                        error: function (data) {
                          alert("Problemas al tratar de enviar el formulario");
                          $.ajaxunblock();
                        }
                      });
                      //============== original ===========================================================
                    }
                    //============== original ===========================================================
                  });

                });
              });
            </script>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i>
              Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->
    <div class="modal fade text-left" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="modalPreviewXml"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPreviewXml">XML DE BOLETA</h5>
            <a name="bajaxml" id="bajaxml" download><span class="fa fa-font-pencil">DESCARGAR XML </span></a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <iframe name="modalxml" id="modalxml" border="0" frameborder="0" width="100%" style="height: 800px;"
              marginwidth="1" src="">
            </iframe>
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
    <div class="modal fade text-left" id="modalPreviewCdr" tabindex="-1" role="dialog" aria-labelledby="modalPreviewCdr"
      aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPreviewCdr">CDR DE BOLETA</h5>
            <a name="bajacrd" id="bajacrd" download><span class="fa fa-font-pencil">DESCARGAR CDR </span></a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <iframe name="modalcdr" id="modalcdr" border="0" frameborder="0" width="100%" style="height: 800px;"
              marginwidth="1" src="">
            </iframe>
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
    <!-- Modal  nuevo articulo -->
    <div class="modal fade text-left" id="modalnuevoarticulo" tabindex="-1" role="dialog"
      aria-labelledby="modalnuevoarticulo" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalnuevoarticulo">Añade nuevo artículo rapidamente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form name="formularionarticulo" id="formularionarticulo" method="POST" style="margin: 2%;">
            <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
            <div class="row">
              <div class="mb-3 col-lg-6">
                <input type="hidden" name="idarticulonuevo" id="idarticulonuevo">
                <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                <label for="recipient-name" class="col-form-label">Selecciona el almacen:</label>
                <select class="form-control" name="idalmacennarticulo" id="idalmacennarticulo" required
                  data-live-search="true">
                </select>
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Selecciona tu categoria:</label>
                <select class="form-control" name="idfamilianarticulo" id="idfamilianarticulo" required
                  data-live-search="true">
                </select>
              </div>
              <div hidden class="mb-3 col-lg-6">
                <select class="form-control" name="tipoitemnarticulo" id="tipoitemnarticulo" onchange="focuscodprov()">
                  <option value="productos" selected="true">PRODUCTO</option>
                  <option value="servicios">SERVICIO</option>
                </select>
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Nombre del producto:</label>
                <input type="text" class="form-control" name="nombrenarticulo" id="nombrenarticulo" onkeyup="mayus(this);"
                  onkeypress=" return limitestockf(event, this)" autofocus="true" onchange="generarcodigonarti()">
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Cantidad del stock</label>
                <input type="text" class="form-control" name="stocknarticulo" id="stocknarticulo" maxlength="100"
                  required="true" onkeypress="return NumCheck(event, this)">
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Precio de venta:</label>
                <input type="text" class="form-control" name="precioventanarticulo" id="precioventanarticulo"
                  onkeypress="return NumCheck(event, this)">
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Codigo del interno del producto:</label>
                <input type="text" class="form-control" name="codigonarticulonarticulo" id="codigonarticulonarticulo">
              </div>
              <div class="mb-3 col-lg-6">
                <label for="recipient-name" class="col-form-label">Unidad de medida:</label>
                <select class="form-control" name="umedidanp" id="umedidanp" required data-live-search="true">
                </select>
              </div>
              <div hidden class="mb-3 col-lg-6">
                <textarea class="form-control" id="descripcionnarticulo" name="descripcionnarticulo" rows="3" cols="70"
                  onkeyup="mayus(this)" onkeypress="return focusDescdet(event, this)"> </textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button data-bs-target="#myModalArt" data-bs-toggle="modal" type="button" class="btn btn-danger"
                data-bs-dismiss="modal">
                <i class="bx bx-x d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Cancelar</span>
              </button>
              <button id="btnguardarncliente" name="btnguardarncliente" value="btnGuardarcliente" type="submit"
                class="btn btn-primary ml-1">
                <i class="bx bx-check d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Guardar</span>
              </button>
            </div>
          </form>
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
  <script type="text/javascript" src="scripts/boleta.js"></script>
  <?php
}
ob_end_flush();
?>