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
                            <style>
                              input[type=number].hidebutton::-webkit-inner-spin-button,
                              input[type=number].hidebutton::-webkit-outer-spin-button {
                              -webkit-appearance: none;
                              margin: 0;
                              }
                            </style>
                            <!--Contenido-->
                            <!-- Content Wrapper. Contains page content -->
                            <!-- <link rel="stylesheet" href="enviosunat.css"> -->
                            <!-- <link rel="stylesheet" media="all" href="../public/css/letter.css" data-turbolinks-track="reload"> -->
                            <link rel="stylesheet" href="style.css">
                            <div class="">
                              <!-- Main content -->
                              <section class="">
                                <div class="content-header">
                                  <h1>Factura electrónica<button class="btn btn-info btn-sm" id="btnagregar"
                                    onclick="mostrarform(true)">Nuevo</button> <button class="btn btn-success btn-sm"
                                    id="refrescartabla" onclick="refrescartabla()">Refrescar</button></h1>
                                </div>
                                <div class="row"">
                                  <div class="col-md-12">
                                    <div class="manej" hidden>
                                      OFF <input type="checkbox" checked name="chk1" id="chk1" onclick="pause()" data-toggle="tooltip"
                                        title="Automatizar envio a SUNAT"> ON
                                    </div>
                                    <div class="table-responsive" id="listadoregistros">
                                      <table id="tbllistado" class="table table-striped"
                                        style="font-size: 14px; max-width: 100%; !important;">
                                        <thead style="text-align:center;">
                                          <th>Opciones</th>
                                          <!--  <th><i class="fa fa-send"></i></th> -->
                                          <th>Fecha</th>
                                          <th>Cliente</th>
                                          <th>Vendedor</th>
                                          <th>Comprobante</th>
                                          <th>Pago</th>
                                          <th>Producto Atendido</th>
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
                                                    <select class="form-control" name="serie" id="serie"
                                                      onchange="incrementarNum();"></select>
                                                    <input type="hidden" name="idnumeracion" id="idnumeracion">
                                                    <input type="hidden" name="SerieReal" id="SerieReal">
                                                    <input type="hidden" name="correo" id="correo">
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Número:</label>
                                                    <input type="text" style="font-size: 12pt;" name="numero_factura"
                                                      id="numero_factura" class="form-control" required="true" readonly>
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Tipo de item:</label>
                                                    <select class="form-control" name="tipofactura" id="tipofactura"
                                                      onchange="cambiarlistado()">
                                                      TIPO DE FACTURA
                                                      <option value="st">TIPO FACTURA</option>
                                                      <option value="productos" selected="true">PRODUCTOS</option>
                                                      <option value="servicios">SERVICIOS</option>
                                                    </select>
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Fe. Emisión:</label>
                                                    <input type="date" class="form-control" name="fecha_emision"
                                                      id="fecha_emision" required="true" disabled="true">
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Fe.
                                                    Vencimiento:</label>
                                                    <input type="date" class="form-control" name="fechavenc" id="fechavenc"
                                                      required="true" min="<?php echo date('Y-m-d'); ?>">
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Vendedor:</label>
                                                    <select class="form-control" name="vendedorsitio" id="vendedorsitio"
                                                      onchange="focusruccliente()">
                                                    </select>
                                                  </div>
                                                  <!--Campos para guardar comprobante Factura-->
                                                  <input type="hidden" name="idfactura" id="idfactura">
                                                  <input type="hidden" name="unidadMedida" id="unidadMedida" value="original">
                                                  <input type="hidden" name="firma_digital" id="firma_digital" value="">
                                                  <!--Datos de empresa Estrella-->
                                                  <input type="hidden" name="idempresa" id="idempresa"
                                                    value="<?php echo $_SESSION['idempresa']; ?>">
                                                  <input type="hidden" name="tipo_documento" id="tipo_documento" value="01">
                                                  <input type="hidden" name="numeracion" id="numeracion" value="">
                                                  <!--Datos del cliente-->
                                                  <input type="hidden" name="idpersona" id="idpersona" required="true">
                                                  <input type="hidden" name="tipo_documento_cliente"
                                                    id="tipo_documento_cliente">
                                                  <!--Datos del cliente-->
                                                  <!--Datos de impuestos-->
                                                  <input type="hidden" name="total_operaciones_gravadas_codigo"
                                                    id="total_operaciones_gravadas_codigo" value="1001">
                                                  <!--IGV-->
                                                  <input type="hidden" name="codigo_tributo_h" id="codigo_tributo_h">
                                                  <input type="hidden" name="nombre_tributo_h" id="nombre_tributo_h">
                                                  <input type="hidden" name="codigo_internacional_5"
                                                    id="codigo_internacional_5" value="">
                                                  <!-- <input type="hidden" name="iva" id="iva" value="<?php $_SESSION['iva']; ?>"> -->
                                                  <!--IGV-->
                                                  <!-- <input type="hidden" name="tipo_moneda" id="tipo_moneda" value="PEN"> -->
                                                  <input type="hidden" name="tipo_documento_guia" id="tipo_documento_guia"
                                                    value="">
                                                  <input type="hidden" name="codigo_leyenda_1" id="codigo_leyenda_1"
                                                    value="1000">
                                                  <input type="hidden" name="version_ubl" id="version_ubl" value="2.0">
                                                  <input type="hidden" name="version_estructura" id="version_estructura"
                                                    value="1.0">
                                                  <input type="hidden" name="tasa_igv" id="tasa_igv" value="0.18">
                                                  <!--Fin de campos-->
                                                  <!--DETALLE-->
                                                  <input type="hidden" name="codigo_precio" id="codigo_precio" value="01">
                                                  <input type="hidden" name="afectacion_igv_3" id="afectacion_igv_3" value="">
                                                  <input type="hidden" name="afectacion_igv_4" id="afectacion_igv_4" value="">
                                                  <input type="hidden" name="afectacion_igv_5" id="afectacion_igv_5" value="">
                                                  <input type="hidden" name="afectacion_igv_6" id="afectacion_igv_6" value="">
                                                  <input type="hidden" name="hora" id="hora">
                                                  <input type="hidden" name="iglobal" id="iglobal">
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Moneda:</label>
                                                    <select class="form-control" name="tipo_moneda" id="tipo_moneda"
                                                      onchange="tipodecambiosunat()">
                                                      <option value="PEN">Soles</option>
                                                      <option value="USD">Dolares</option>
                                                    </select>
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">T. camb:</label>
                                                    <input type="text" name="tcambio" id="tcambio" class="form-control"
                                                      readonly="true">
                                                  </div>
                                                  <div class="mb-3 col-lg-12">
                                                    <label for="recipient-name" class="col-form-label">Ruc cliente:</label>
                                                      <input type="text" class="form-control" name="numero_documento2"
                                                        id="numero_documento2" maxlength="11"
                                                        placeholder="RUC DE CLIENTE-ENTER"
                                                        onkeypress="agregarClientexRuc(event)" onblur="quitasuge1()"
                                                        onfocus="focusTest(this)">
                                                    <div id="suggestions">
                                                    </div>
                                                  </div>
                                                  <div class="mb-3 col-lg-12">
                                                    <label for="recipient-name" class="col-form-label">Nombre
                                                    comercial:</label>
                                                    <input type="text" class="form-control" name="razon_social2"
                                                      id="razon_social2" required="true" placeholder="NOMBRE COMERCIAL"
                                                      onblur="quitasuge2()" onfocus="focusTest(this)">
                                                    <div id="suggestions2">
                                                    </div>
                                                  </div>
                                                  <div class="mb-3 col-lg-12">
                                                    <label for="recipient-name" class="col-form-label">Domicilio
                                                    fiscal:</label>
                                                    <input type="text" class="form-control" name="domicilio_fiscal2"
                                                      id="domicilio_fiscal2" required="true"
                                                      placeholder="DIRECCIÓN CLIENTE">
                                                  </div>
                                                  <div hidden class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Correo:</label>
                                                    <input type="text" class="form-control" name="correocli" id="correocli"
                                                      placeholder="CORREO CLIENTE"
                                                      onkeypress="return focusbotonarticulo(event, this)"
                                                      onfocus="focusTest(this)">
                                                  </div>
                                                  <div class="mt-2 mb-3 col-lg-12">
                                                    <textarea name="descripcion_leyenda_2" id="descripcion_leyenda_2"
                                                      cols="5" rows="3" class="form-control" placeholder="Observaciones"
                                                      value="DESCRIPCION DE LEYENDA"></textarea>
                                                  </div>
                                                  <div hidden class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">N. Guia:</label>
                                                    <input type="text" class="form-control" name="guia_remision_29_2"
                                                      id="guia_remision_29_2" placeholder="Nro de Guía de remisión">
                                                  </div>
                                                  <div hidden class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Tipo precio:</label>
                                                    <select class="form-control" id="tipopreciocod">
                                                      <option value='1'>Precio público</option>
                                                      <option value='2'>Precio por mayor</option>
                                                      <option value='3'>Precio distribuidor</option>
                                                    </select>
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Tipo
                                                    impuesto:</label>
                                                    <select class="form-control" name="nombre_trixbuto_4_p"
                                                      id="nombre_tributo_4_p" onchange="tributocodnon()">
                                                    </select>
                                                  </div>
                                                  <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Tipo de pago:</label>
                                                    <select class="form-control" name="tipopago" id="tipopago"
                                                      onchange="contadocredito()">
                                                      <option value="nn">SELECCIONE LA FORMA DE PAGO</option>
                                                      <option value="Contado" selected>CONTADO</option>
                                                      <option value="Credito">CRÉDITO</option>
                                                    </select>
                                                  </div>
                                                  <div id="tipopagodiv" style="display: none;">
                                                    <div class="mb-3 col-lg-12">
                                                      <label for="recipient-name" class="col-form-label">N° de
                                                      cuotas:</label>
                                                      <div class="input-group">
                                                        <span hidden style="cursor:pointer;" class="input-group-text"
                                                          data-bs-toggle="modal" title="mostrar cuotas"
                                                          data-bs-target="#modalcuotas"
                                                          id="basic-addon1">&#9769;</span>
                                                        <span style="cursor:pointer;" class="input-group-text"
                                                          onclick="borrarcuotas()"
                                                          title="Editar cuotas">&#10000;</span>
                                                        <input  name="ccuotas" id="ccuotas"
                                                          onchange="focusnroreferencia()" class="form-control"
                                                          value="1" onkeypress="return NumCheck(event, this)">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="mb-3 col-lg-6" hidden>
                                                    <label for="recipient-name" class="col-form-label">Tipo
                                                    impuesto:</label>
                                                    IGV<input type="checkbox" name="vuniigv" id="vuniigv"
                                                      onclick="modificarSubototales()" data-toggle="tooltip"
                                                      title="">Valor
                                                  </div>
                                                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" hidden>
                                                    <label>Pago tarj.:</label>
                                                    <img src="../files/articulos/tarjetadc.png" data-toggle="tooltip"
                                                      title="Pago por tarjeta">
                                                    <input type="checkbox" name="tarjetadc" id="tarjetadc"
                                                      onclick="activartarjetadc();">
                                                    <input type="hidden" name="tadc" id="tadc">
                                                  </div>
                                                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" hidden>
                                                    <label>Pago Transf.:</label>
                                                    <img src="../files/articulos/transferencia.png" data-toggle="tooltip"
                                                      title="Pago por transferencia">
                                                    <input type="checkbox" name="transferencia" id="transferencia"
                                                      onclick="activartransferencia();">
                                                    <input type="hidden" name="trans" id="trans">
                                                  </div>
                                                  <div class="modal fade text-left" id="modalcuotas"
                                                    role="dialog" aria-labelledby="modalcuotas" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                                                      <div class="modal-content">
                                                        <div class="modal-header">
                                                          <h5 class="modal-title" id="modalcuotas">Pago al crédito
                                                          </h5>
                                                          <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                          <div class="container">
                                                            <div id="tipopagodiv" style="text-align: center;"
                                                              class="row">
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
                                                          <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
                                                          <i class="bx bx-x d-block d-sm-none"></i>
                                                          <span class="d-none d-sm-block">Listo</span>
                                                          </button>
                                                          <button hidden id="" type="submit"
                                                            class="btn btn-primary ml-1" data-bs-dismiss="modal">
                                                          <i class="bx bx-check d-block d-sm-none"></i>
                                                          <span class="d-none d-sm-block">Agregar</span>
                                                          </button>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="modal fade" id="" tabindex="-1" role="dialog"
                                                    aria-labelledby="myModalLabel" aria-hidden="true">
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
                                                          <button type="button" class="btn btn-default"
                                                            data-dismiss="modal">Cerrar</button>
                                                          <!-- <button type="button" class="btn btn-success" onclick="mesescontinuos()" >Meses continuos</button> -->
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-9">
                                            <div class="card">
                                              <div class="card-body">
                                                <input type="hidden" name="itemno" id="itemno" value="0">
                                                <button style="margin-left:0px;" data-bs-toggle="modal"
                                                  data-bs-target="#myModalArt" id="btnAgregarArt" type="button"
                                                  class="btn btn-danger btn-s mb-3" onclick="cambiarlistadoum2()">
                                                Agregar Productos o Servicios
                                                </button>
                                                <div class="mb-3 col-lg-12">
                                                  <!-- <label for="recipient-name" class="col-form-label">Escanee código de barras:</label> -->
                                                  <input type="text" name="codigob" id="codigob" class="form-control"
                                                    onkeypress="agregarArticuloxCodigo(event)" onkeyup="mayus(this);"
                                                    placeholder="Digite o escanee el código de barras" onblur="quitasuge3()"
                                                    style="background-color: #F5F589;">
                                                  <div id="suggestions3">
                                                  </div>
                                                </div>
                                                <a data-toggle="modal" href="#ModalNcliente" hidden>
                                                <button id="btnAgregarCli" type="button" class="btn btn-danger btn-sm">
                                                Nuevo cliente <span class="fa fa-user"> </span>
                                                </button>
                                                </a>
                                                <div class="table-responsive">
                                                  <table id="detalles" class="table table-striped" style="text-align:center;">
                                                    <thead>
                                                      <th>Sup.</th>
                                                      <th>Item</th>
                                                      <th>Artículo</th>
                                                      <!-- <th style="color:white;">Descripción</th> -->
                                                      <th>Cantidad</th>
                                                      <th>Dcto. %</th>
                                                      
                                                      <th>Código</th>
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
                                                    <!-- <button type="button" value="1" name="botonpago1" id="botonpago1"
                          class="btn btn-success btn-sm" onclick="botonrapido1()">1</button>
                          <button type="button" value="2" name="botonpago2" id="botonpago2"
                          class="btn btn-success btn-sm" onclick="botonrapido2()">2</button>
                          <button value="5" type="button" name="botonpago5" id="botonpago5"
                          class="btn btn-success btn-sm" onclick="botonrapido5()">5</button>
                          <button value="10" type="button" name="botonpago10" id="botonpago10"
                          class="btn btn-success btn-sm" onclick="botonrapido10()">10</button>
                          <button value="20" name="botonpago20" type="button" id="botonpago20"
                          class="btn btn-success btn-sm" onclick="botonrapido20()">20</button>
                          <button type="button" value="50" name="botonpago50" type="button" id="botonpago50"
                          class="btn btn-success btn-sm" onclick="botonrapido50()">50</button>
                          <button value="100" name="botonpago100" id="botonpago100" type="button"
                          class="btn btn-success btn-sm" onclick="botonrapido100()">100</button>
                          <button value="200" name="botonpago200" id="botonpago200" type="button"
                          class="btn btn-success btn-sm" onclick="botonrapido200()">200</button> -->
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
                                                        <p class="card-text">
                                                        <th style="font-weight: bold; background-color:#FFB887;">
                                                          <div style="display:flex;">
                                                            <label for="">Subtotal : </label>
                                                            <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;"
                                                              id="subtotal"> 0.00</h6>
                                                            <input hidden placeholder="0.00" readonly
                                                              style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; text-align: right; border:none;"
                                                              name="subtotal_factura" id="subtotal_factura">
                                                          </div>
                                                          <div style="display:flex;">
                                                            <label for="">IGV : </label>
                                                            <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;"
                                                              id="igv_"> 0.00</h6>
                                                            <input hidden placeholder="0.00" readonly
                                                              style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; text-align: right; border:none;"
                                                              name="total_igv" id="total_igv">
                                                          </div>
                                                          <div style="display:flex;">
                                                            <label for="">Descuento : </label>
                                                            <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px; "
                                                              name="" id="tdescuentoL"> 0.00</h6>
                                                            <!-- <h6 hidden style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" name="total_dcto" id="total_dcto"> 0.00</h6> -->
                                                          </div>
                                                          <div style="display:flex;">
                                                            <label for="">Total a pagar : </label>
                                                            <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;"
                                                              id="total"> 0.00</h6>
                                                          </div>
                                                          <br>
                                                          <h5 class="card-title">Calcular vuelto</h5>
                                                          <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="">Pago con : </label><!--ipad-->
                                                            <!-- <h6 name="ipagado" id="ipagado"
                                  style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;">
                                  0.00</h6> -->
                                                            <input type="number"
                                                              class="form-control text-end hidebutton"
                                                              name="ipagado" id="ipagado" value="0.00"
                                                              style="width: 100px;">
                                                            <!-- <input hidden name="ipagado_final" id="ipagado_final" style="font-weight: bold; margin-left: 75px; width: 100px;"> -->
                                                          </div>
                                                          <div class="d-flex align-items-center">
                                                            <label for="" id="vuelto_text">Vuelto : </label><!--saldo-->
                                                            <h6 style="font-weight: bold; margin: 0 auto; margin-right: 0px;"
                                                              name="saldo" id="saldo"> 0.00</h6>
                                                            <!-- <h6 hidden style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" name="saldo_final" id="saldo_final"> 0.00</h6> -->
                                                          </div>
                                                          <input type="hidden" name="total_final"
                                                            id="total_final">
                                                          <!-- <input type="hidden" name="subtotal_factura" id="subtotal_factura"> 
                                <input type="hidden" name="total_igv" id="total_igv"> -->
                                                          <input type="hidden" name="total_icbper"
                                                            id="total_icbper">
                                                          <input type="hidden" name="total_dcto" id="total_dcto">
                                                          <input type="hidden" name="pre_v_u"
                                                            id="pre_v_u"><!--Datos de impuestos--> <!--TOTAL-->
                                                          <input type="hidden" name="ipagado_final"
                                                            id="ipagado_final"><!--Datos de impuestos-->
                                                          <!--TOTAL-->
                                                          <input type="hidden" name="saldo_final"
                                                            id="saldo_final">
                                                        </th>
                                                        <!--Datos de impuestos--> <!--TOTAL-->
                                                        <h4 hidden id="icbper">0</h4>
                                                        <input hidden type="text" id="otroscargos" name="otroscargos"
                                                          onchange="modificarSubototales();" disabled value="0.00">
                                                        </th><!--Datos de impuestos--> <!--TOTAL-->
                                                        <!-- </th> -->
                                                        </p>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                                <button style="margin-left:0px;" class="btn btn-primary btn-sm" type="submit"
                                                  id="btnGuardar" data-toggle="tooltip" title="Guardar Factura"><i
                                                  class="fa fa-save"></i>
                                                Guardar
                                                </button>
                                                <button style="margin-left:0px;" id="btnCancelar" class="btn btn-danger btn-sm"
                                                  onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"
                                                  data-toggle="tooltip" title="Cancelar"></i> Cancelar</button>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                  </div>
                                  </form>
                                </div>
                                <!--Fin centro -->
                            </div>
                            <!-- /.col -->
                            </div><!-- /.row -->
                            </section><!-- /.content -->
                            </div><!-- /.content-wrapper -->
                            <!--Fin-Contenido-->
                            <!-- Modal -->
                            <div class="modal fade" id="modalTcambio">
                              <div class="modal-dialog" style="width: 100% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                  </div>
                                  <!-- <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <iframe border="0" frameborder="0" height="310" width="100%" src="https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias"></iframe>
        </div> -->
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal">Cerrar</button>
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
                                    <table id="tblaclientes" class="table table-striped table-bordered table-condensed table-hover"
                                      width=-5px>
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
                                            <td align="center"><a onclick="cargarbien()" name="tipoitem" id="tipoitem"
                                              value="bien"><img src="../public/images/producto.png"><br>Productos</a></td>
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
                                            <td><input type="text" name="icantidad" id="icantidad" class=""
                                              onkeyup="calculartotalitem();" value="1">
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>Stock:</td>
                                            <td><input type="text" name="stoc" id="stoc" readonly></td>
                                            <td>Précio unitario:</td>
                                            <td><input type="text" name="ipunitario" id="ipunitario" class=""
                                              onkeyup="calvaloruniitem();"></td>
                                          </tr>
                                          <tr>
                                            <td>ABRE:</td>
                                            <td><input type="text" name="iumedida" id="iumedida" class="" readonly size="4"></td>
                                            <td>Cambiar medida:</td>
                                            <td><select name="unidadm" id="unidadm" class="" onchange="cambioUm()"
                                              size="2"></select></td>
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
                                            <td><input type="text" name="iigvresu" id="iigvresu" class="" value="0" readonly="">
                                            </td>
                                            <td>Impuesto ICBPER:</td>
                                            <td><input type="text" name="iimpicbper" id="iimpicbper" class="" readonly=""></td>
                                          </tr>
                                          <tr>
                                          </tr>
                                          <tr></tr>
                                          <tr>
                                            <td>Importe total del Item:</td>
                                            <td><input type="text" name="iimportetotalitem" id="iimportetotalitem" class=""
                                              readonly></td>
                                            <td></td>
                                            <td></td>
                                          </tr>
                                          <tr>
                                            <td align="justify">
                                              <button type="button" class="btn btn-success" data-dismiss="modal"
                                                onclick="agregarItemdetalle()"><i class="fa fa-check"></i> Aceptar </button>
                                            </td>
                                            <td align="justify">
                                              <button type="button" class="btn btn-danger" data-dismiss="modal"><i
                                                class="fa fa-close"></i> Cancelar </button>
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
                            <div class="modal fade text-left" id="ModalNcliente" tabindex="-1" role="dialog" aria-labelledby="ModalNcliente"
                              aria-hidden="true">
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
                                            <span type="submit" style="cursor:pointer;" value="burcarclientesunat" name="btn-submit"
                                              id="btn-submit" class="input-group-text">&#9769;</span>
                                            <input type="number" class="form-control" name="nruc" id="nruc"
                                              placeholder="Ingrese RUC o DNI"
                                              pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                                              autofocus>
                                          </div>
                                        </div>
                                      </div>
                                    </form>
                                    <form name="formularioncliente" id="formularioncliente" method="POST">
                                      <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
                                      <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
                                      <div class="row">
                                        <div class="">
                                          <input type="hidden" name="idpersona" id="idpersona">
                                          <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label class="col-form-label">Tipo Doc.:</label>
                                          <select class="form-control select-picker" name="tipo_documento" id="tipo_documento"
                                            required>
                                            <option value="6"> RUC </option>
                                          </select>
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">N° Doc:</label>
                                          <input type="text" class="form-control" name="numero_documento3" id="numero_documento3"
                                            maxlength="20" placeholder="Documento" onkeypress="return focusRsocial(event, this)">
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                          <label for="message-text" class="col-form-label">Razon Social:</label>
                                          <input type="text" class="form-control" name="razon_social" id="razon_social"
                                            maxlength="100" placeholder="Razón social" required
                                            onkeypress="return focusDomi(event, this)">
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
                                          <input type="text" class="form-control" name="idciudad" id="idciudad"
                                            onchange="llenarDistrito()">
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
                                          <input type="text" class="form-control" name="email" id="email" maxlength="50"
                                            placeholder="CORREO" onkeypress="return focusguardar(event, this)">
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
                                                            Swal.fire({
                                                                icon: 'warning',
                                                                title: 'Atención',
                                                                text: 'El campo documento está vacío',
                                                                showConfirmButton: false,
                                                                timer: 1500
                                                            });
                                                            //$.ajaxunblock();
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

                                        $('#ModalNcliente').on('shown.bs.modal', function (e) {
                                            $("#btn-submit").trigger("click"); // Simula un clic en el botón para ejecutar la lógica que ya tienes definida
                                        });

                                        $('#ModalNcliente').on('hidden.bs.modal', function (e) {
                                            // Encuentra todos los campos de entrada dentro del modal y los limpia
                                            $(this).find('input').val('');
                                        }); 
                                    });
                                  </script>
                                </div>
                                <!-- <div class="modal-footer">
      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i>
          Cerrar</button>
      </div> -->
                              </div>
                            </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal  SERVICIOS -->
                            <div class="modal fade" id="myModalserv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog" style="width: 100% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Seleccione un bien o servicio</h4>
                                    <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()"><i
                                      class="fa fa-refresh fa-spin fa-1x fa-fw"></i>
                                    <span class="sr-only"></span> Refrescar</button>
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
                            <div class="modal fade text-left" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalArt"
                              aria-hidden="true">
                              <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="myModalArt">Agrega el producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                      <div class="mb-3 col-lg-6">
                                        <label for="recipient-name" class="col-form-label">Selecciona tipo de precio:</label>
                                        <select class="form-control" id="tipoprecio" onchange="listarArticulos()">
                                          <option value='1'>PRECIO PÚBLICO</option>
                                          <option value='2'>PRECIO POR MAYOR</option>
                                          <option value='3'>PRECIO DISTRIBUIDOR</option>
                                        </select>
                                      </div>
                                      <div class="mb-3 col-lg-6">
                                        <label for="message-text" class="col-form-label">Selecciona almacen:</label>
                                        <select class="form-control" id="almacenlista" onchange="listarArticulos()">
                                        </select>
                                        <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()" hidden>
                                        <span class="sr-only"></span>Actualizar</button>
                                        <button class="btn btn-danger" id="refrescartabla" onclick="nuevoarticulo()" hidden>
                                        <span class="sr-only"></span>Nuevo artículo</button>
                                      </div>
                                      <div class="mb-3 col-lg-12">
                                        <div class="table-responsive">
                                          <table id="tblarticulos"
                                            class="table table-striped table-bordered table-condensed table-hover">
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
          <span class="d-none d-sm-block">Agregar</span>
          </button> -->
                                  </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <input type="hidden" name="idultimocom" id="idultimocom">
                            <!-- Modal VISTA PREVIA IMPRESION -->
                            <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 90% !important;">
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
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                      ENVIAR POR CORREO FACTURA N°: 
                                      <h3 id="ultimocomprobante"> </h3>
                                      AL CORREO: 
                                      <h3
                                        id="ultimocomprobantecorreo"></h3>
                                      <a onclick="enviarcorreoprew()">
                                      <img src="../public/images/mail.png">
                                      </a>
                                    </div>
                                    <button class="btn btn-info" name="estadoenvio" id="estadoenvio" value="ESTADO ENVIO A SUNAT"
                                      onclick="estadoenvio()">Estado envio</button>
                                    <h3 id="estadofact">Documento emitido</h3>
                                    <h3 id="estadofact2" style="color: red;"> Recuerde que para enviar por correo debe hacer la vista previa
                                      para que se generen los archivos PDF.
                                    </h3>
                                    <h4>Recuerde que puede enviar los comprobantes por correo. Cuide el planeta.</h4>
                                    <img
                                      src="../files/vistaprevia/hoja.jpg">
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal -->
                            <div class="modal fade" id="modalPreview2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                              aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 100% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                                    <h4 class="modal-title">FACTURA DE VENTA ELECTRÓNICA</h4>
                                  </div>
                                  <iframe name="modalCom" id="modalCom" border="0" frameborder="0" width="100%" style="height: 800px;"
                                    marginwidth="1" src="">
                                  </iframe>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal -->
                            <div class="modal fade" id="modalPreviewticket" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                              aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 24% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <iframe name="modalComticket" id="modalComticket" border="0" frameborder="0" width="100%"
                                    style="height: 800px;" marginwidth="1" src="">
                                  </iframe>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal COMPLETAR COMPRA -->
                            <div class="modal fade" id="modalcompletar" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 50% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h2 class="text-center">Completar</h2>
                                    <div class="large-9 columns">
                                      <h4 class="text-center">Medio de pago</h4>
                                      <div class="callout columns border-green">
                                        <div class="large-6 columns">
                                          <label>Tipo</label>
                                          <select name="000" id="000">
                                            <option value="26814">EFECTIVO</option>
                                            <option value="26815">TARJETA DÉBITO</option>
                                            <option value="26816">TARJETA CRÉDITO</option>
                                            <option value="26817">DEPÓSITO</option>
                                            <option value="26818">GIRO</option>
                                            <option value="26819">CHEQUE</option>
                                            <option value="26820">CUPÓN</option>
                                            <option value="26821">PAYPAL</option>
                                            <option value="26822">CRÉDITO - POR PAGAR</option>
                                            <option value="26823">OTROS</option>
                                          </select>
                                          <label for="invoice_payments_attributes_0_importe">Importe</label>
                                          <input class="form-control" type="text" id="invoice_payments_attributes_0_importe">
                                          <label for="invoice_payments_attributes_0_nota">Nota</label>
                                          <input class="form-control" type="text" id="invoice_payments_attributes_0_nota">
                                          <!-- <label for="nil"><br></label>
                <span class="postfix"><input type="hidden" name="invoice[payments_attributes][0][_destroy]" id="invoice_payments_attributes_0__destroy" value="false"><a class="button tiny alert expanded remove_fields dynamic" href="#"><i class="fa fa-trash"></i></a></span>
                </div> -->
                                        </div>
                                      </div>
                                    </div>
                                    <div class="large-3 columns">
                                      <div class="text-center">
                                        <div>Pago rápido</div>
                                        <i class="fa fa-arrow-down"></i>
                                      </div>
                                      <div class="stacked button-group">
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">1</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">2</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">5</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">10</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">20</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">50</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">100</span>
                                        </a>
                                        <a href="#" class="button tiny bold secondary quick-cash"
                                          quick-cash="invoice_payments_attributes_0_importe">
                                          <spa class="current_currency">S/</spa>
                                          <span class="value">200</span>
                                        </a>
                                        <a href="#" class="button tiny bold alert clean">
                                          <spa class=""></spa>
                                          <span class="">Limpiar</span>
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="large-12 columns">
                                    <div class="callout columns border-green">
                                      <div class="large-4 columns">
                                        <label for="invoice_importe_total">Importa TOTAL</label>
                                        <input type="text" name="importe_total" id="pizza_importe_total" readonly="readonly">
                                      </div>
                                      <div class="large-4 columns">
                                        <label for="invoice_importe_pagado">Importe TOTAL pagado</label>
                                        <input id="pizza_importe_pagado" readonly="readonly" type="text" name="invoice[importe_pagado]">
                                      </div>
                                      <div class="large-4 columns">
                                        <label for="invoice_diferencia_vuelto">Diferencia (vuelto)</label>
                                        <input id="pizza_diferencia_vuelto" readonly="readonly" type="text"
                                          name="invoice[diferencia_vuelto]">
                                      </div>
                                    </div>
                                  </div>
                                  <div class="">
                                    <input type="submit" name="commit" value="Crear Comprobante" class="button large expanded"
                                      data-disable-with="Enviando..." data-confirm="Confirmar que deseas generar este documento">
                                  </div>
                                  <div class="margin-top text-center">
                                    <a class="button warning" data-close="">Cerrar</a>
                                  </div>
                                  <button class="close-button large" type="button" data-close="">
                                  <span aria-hidden="true">×</span>
                                  </button>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal -->
                           <div class="modal fade" id="modalPreviewXml" tabindex="-1" aria-labelledby="modalPreviewXmlLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="max-width: 70% !important;">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h5 class="modal-title" id="modalPreviewXmlLabel">ARCHIVO XML DE FACTURA</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          <a name="bajaxml" id="bajaxml" download><span class="fa fa-font-pencil">DESCARGAR XML</span></a>
                                          <iframe name="modalxml" id="modalxml" border="0" frameborder="0" width="100%" style="height: 800px;"
                                              marginwidth="1" src=""></iframe>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          

                            <!-- Fin modal -->
                            <!-- Modal  nuevo articulo -->
                            <div class="modal fade" id="modalnuevoarticulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                              aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 70% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">NUEVO ARTÍCULO - SOLO PARA UNIDAD</h4>
                                  </div>
                                  <form name="formularionarticulo" id="formularionarticulo" method="POST">
                                    <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
                                    <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                      <label>Almacen</label>
                                      <input type="hidden" name="idarticulonuevo" id="idarticulonuevo">
                                      <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                                      <select class="form-control" name="idalmacennarticulo" id="idalmacennarticulo" required
                                        data-live-search="true">
                                      </select>
                                    </div>
                                    <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                      <label>Categoría</label>
                                      <select class="form-control" name="idfamilianarticulo" id="idfamilianarticulo" required
                                        data-live-search="true">
                                      </select>
                                    </div>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <label>Tipo</label>
                                      <select class="" name="tipoitemnarticulo" id="tipoitemnarticulo" onchange="focuscodprov()">
                                        <option value="productos" selected="true">PRODUCTO</option>
                                        <option value="servicios">SERVICIO</option>
                                      </select>
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                      <label>Descripción / Nombre:</label>
                                      <input type="text" class="form-control focus" name="nombrenarticulo" id="nombrenarticulo"
                                        placeholder="Nombre" onkeyup="mayus(this);" onkeypress=" return limitestockf(event, this)"
                                        autofocus="true" onchange="generarcodigonarti()">
                                    </div>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <label>Stock:</label>
                                      <input type="text" class="" name="stocknarticulo" id="stocknarticulo" maxlength="100"
                                        placeholder="Stock" required="true" onkeypress="return NumCheck(event, this)">
                                    </div>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <label>Precio venta (S/.):</label>
                                      <input type="text" class="" name="precioventanarticulo" id="precioventanarticulo"
                                        onkeypress="return NumCheck(event, this)">
                                    </div>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <label>Código:</label>
                                      <input type="text" class="" name="codigonarticulonarticulo" id="codigonarticulonarticulo">
                                    </div>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <label>Unidad medida:</label>
                                      <select class="form-control" name="umedidanp" id="umedidanp" required data-live-search="true">
                                      </select>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                      <label>Descripción:</label>
                                      <textarea id="descripcionnarticulo" name="descripcionnarticulo" rows="3" cols="70"
                                        onkeyup="mayus(this)" onkeypress="return focusDescdet(event, this)"> </textarea>
                                    </div>
                                    <div class="modal-footer">
                                      <button class="btn btn-primary" type="submit" id="btnguardarncliente" name="btnguardarncliente"
                                        value="btnGuardarcliente">
                                      <i class="fa fa-save"></i> Guardar
                                      </button>
                                      <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            <!-- Fin modal -->
                            <!-- Modal  nuevo articulo -->
                            <div class="modal fade" id="ModalNnotificacion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                              aria-hidden="true">
                              <div class="modal-dialog modal-lg" style="width: 40% !important;">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                      <!-- <img src="../public/images/notifi.png" height="35px"> -->
                                    </div>
                                    <h4 class="modal-title">NUEVA NOTIFICACIÓN</h4>
                                  </div>
                                  <form name="formularionnotificacion" id="formularionnotificacion" method="POST">
                                    <!-- SEGURIDAD: Token CSRF para proteger contra ataques CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>">
                                    <!-- <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
          <label>CÓDIGO</label>
          <input type="hidden" name="idnotificacion" id="idnotificacion">
           <input type="text" name="codigonotificacion" id="codigonotificacion">
          </div> -->
                                    <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                                      <label>Nombre</label>
                                      <input type="text" name="nombrenotificacion" id="nombrenotificacion">
                                    </div>
                                    <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                                      <label>Fecha notificación</label>
                                      <input type="date" name="fechaaviso" id="fechaaviso" class="">
                                      <input type="date" name="fechacreacion" id="fechacreacion" class="" style="visibility: hidden;">
                                    </div>
                                    <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                                      <label>Cliente</label>
                                      <input type="hidden" name="idclientenoti" id="idclientenoti">
                                      <input type="hidden" name="tipo_documento_noti" id="tipo_documento_noti" value="01">
                                      <h3 id="clinoti"></h3>
                                    </div>
                                    <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                                      <label>Documento</label>
                                      <h3>FACTURA</h3>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                      <label>Continua</label>
                                      NO <input type="checkbox" name="continuo" id="continuo" onclick="continuoNoti();"> SI
                                      <input type="hidden" name="selconti" id="selconti">
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                      <label>Activa</label>
                                      NO <input type="checkbox" name="estadonoti" id="estadonoti" onclick="estadoNoti();"> SI
                                      <input type="hidden" name="selestado" id="selestado">
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                      <button class="btn btn-primary" type="submit" id="btnguardarnnotificacion"
                                        name="btnguardarnnotificacion" value="">
                                      <i class="fa fa-save"></i> Guardar
                                      </button>
                                      <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                    <div class="modal-footer">
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
              <script type="text/javascript" src="scripts/factura.js"></script>
              <?php
}
ob_end_flush();
?>
