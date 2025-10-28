<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
//$ms=$_POST['ms'];

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';


  if ($_SESSION['Ventas'] == 1) {

    ?>

            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

            <!--Contenido-->

            <!-- Content Wrapper. Contains page content -->

            <div class="box-header with-border">
                        <h1 class="box-title"> GUÍA DE REMISIÓN
                          <button class="btn btn-success btn-sm" id="btnagregar" onclick="mostrarform(true)"><i
                              class="fa fa-newspaper-o"></i> NUEVO</button>

                        </h1>
                        <div class="box-tools pull-right">
                        </div>
                      </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="card custom-card">
                      <div class="card-body">
                      

                      <!-- /.box-header -->
                      <!-- centro -->
                      <div class="panel-body table-responsive" id="listadoregistros">
                        <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                          <thead>
                            <th>OPCIONES</th>
                            <th>FECHA E.</th>
                            <th>NÚMERO</th>
                            <th>DESTINATARIO</th>
                            <th>COMPROBANTE</th>
                            <th>ESTADO</th>
                          </thead>
                          <tbody>
                          </tbody>
                          <!-- <tfoot>
                    <th>OPCIONES</th>
                    <th>FECHA E.</th>
                    <th>NÚMERO</th>
                    <th>DESTINATARIO</th>
                    <th>COMPROBANTE</th>
                    <th>ESTADO</th>

                  </tfoot> -->

                        </table>

                      </div>


                      <div class="card-body" id="formularioregistros">
                        <form name="formulario" id="formulario" method="POST" onkeypress="return anular(event)">
                          <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                              <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#datosinitras" role="tab"
                                aria-controls="pills-home" aria-selected="true">DATOS DE INICIO DE TRASLADO</a>
                            </li>
                            <!-- más li aquí -->
                          </ul>

                          <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="datosinitras" role="tabpanel"
                              aria-labelledby="pills-home-tab">
                              <div class="row">

                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label for="serie" class="form-label">SERIE</label>
                                    <select class="form-select" name="serie" id="serie" onchange="incrementarNum()"></select>
                                    <input type="hidden" name="idnumeracion" id="idnumeracion">
                                    <input type="hidden" name="SerieReal" id="SerieReal">
                                  </div>
                                </div>


                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label for="numero" class="form-label">NÚMERO</label>
                                    <input type="text" name="numero_guia" id="numero_guia" readonly class="form-control"
                                      required="true">
                                  </div>
                                </div>

                                <!--Campos para guardar comprobante Factura-->
                                <input type="hidden" name="idcomprobante" id="idcomprobante">
                                <input type="hidden" name="idguia" id="idguia">
                                <input type="hidden" name="tipo_documento" id="tipo_documento" value="01">
                                <input type="hidden" name="numeracion" id="numeracion" value="">
                                <input type="hidden" name="ocompra" id="ocompra" value="">
                                <!--Datos del cliente-->

                                <input type="hidden" name="idpersona" id="idpersona">
                                <input type="hidden" name="tipo_documento_cliente" id="tipo_documento_cliente">

                                <!--Datos del cliente-->

                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label for="fechaemision" class="form-label">Fecha emisión</label>
                                    <input type="date" class="form-control" name="fecha_emision" id="fecha_emision" required="true">
                                  </div>
                                </div>



                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label class="form-label">Fecha inicio traslado:</label>
                                    <input type="date" class="form-control" name="fechatraslado" id="fechatraslado" required="true">
                                  </div>
                                </div>



                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label class="form-label">Motivo de traslado :</label>
                                    <select name="motivo" id="motivo" class="form-select">
                                      <option value="01">VENTA </option>
                                      <option value="14">VENTA SUJETA A CONFIRMACION DEL COMPRADOR </option>
                                      <option value="02">COMPRA </option>
                                      <option value="04">TRASLADO ENTRE ESTABLECIMIENTOS DE LA MISMA EMPRESA </option>
                                      <option value="18">TRASLADO EMISOR ITINERANTE CP </option>
                                      <option value="08">IMPORTACION </option>
                                      <option value="09">EXPORTACION </option>
                                      <option value="19">TRASLADO A ZONA PRIMARIA </option>
                                      <option value="13">OTROS </option>
                                    </select>
                                  </div>
                                </div>



                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label class="form-label">Tipo doc público:</label>
                                    <select id="codtipotras" name="codtipotras" class="form-select">
                                      <option value="01" selected="true">TRANSPORTE PÚBLICO</option>
                                      <option value="02">TRANSPORTE PRIVADO</option>
                                    </select>
                                  </div>
                                </div>



                              </div>
                            </div>

                            <ul class="nav nav-pills mt-2 mb-3" id="pills-tab" role="tablist">
                              <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#datosinitras" role="tab"
                                  aria-controls="pills-home" aria-selected="true">DATOS DEL DESTINATARIO</a>
                              </li>
                              <!-- más li aquí -->
                            </ul>

                            <div class="tab-pane fade show active" id="datosinitras" role="tabpanel"
                              aria-labelledby="pills-home-tab">
                              <div class="row">
                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <label class="form-label">Comprobante:</label>
                                  <div class="input-group mb-3">
                                    <select name="tipocomprobante" id="tipocomprobante" class="form-select"
                                      onchange="boletafactura()">
                                      <option value="01">FACTURA </option>
                                      <option value="03">BOLETA</option>
                                    </select>
                                    <div class="input-group-append">
                                      <a data-bs-toggle="modal" href="#myModalComprobante" class="btn btn-success">
                                        <i class="fas fa-plus"></i></a>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <div class="mb-3">
                                    <label class="form-label">Destinatario:</label>
                                    <input type="text" class="form-control" name="destinatario" id="destinatario" maxlength="100"
                                      placeholder="" required="true" onkeypress="mayus(this);">
                                  </div>
                                </div>

                                <div class="col-lg-2 col-md-4 col-sm-6">
                                  <label class="form-label">RUC:</label>
                                  <input type="text" class="form-control" name="nruc" id="nruc" maxlength="11" placeholder=""
                                    required="true" onkeypress=" return NumCheck(event,this)">
                                </div>



                              </div>

                            </div>

                            <ul class="nav nav-pills mt-2" id="pills-tab" role="tablist">
                              <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#datosinitras" role="tab"
                                  aria-controls="pills-home" aria-selected="true">DATOS DEL TRANSPORTISTA</a>
                              </li>
                              <!-- más li aquí -->
                            </ul>

                            <div class="tab-pane fade show active" id="datosinitras" role="tabpanel"
                              aria-labelledby="pills-home-tab">
                              <div class="row">



                              </div>
                            </div>


                          </div>


                          <!-- <ul class="nav nav-pills" id=  "pills-tab"> -->
                          <ul class="ps-0" id="pills-tab">

                            <li class="nav-item">
                              <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#datostransp" role="tab"
                                aria-controls="adquisicion" aria-selected="false">
                                <span class="round-tabs two">
                                </span>
                              </a>

                            </li>
                            <div class="tab-content mb-3" id="pills-tabContent">
                              <div class="row">

                                <div class="col-12 col-sm-4 col-md-4 col-lg-4 mb-3">
                                  <label class="form-label">Tipo :</label>
                                  <select id="tipodoctrans" name="tipodoctrans" class="form-control">
                                    <option value="01" selected="true">DNI</option>
                                    <option value="06" selected="true">RUC</option>
                                  </select>

                                </div>

                                <div class="col-12 col-sm-4 col-md-4 col-lg-4 mb-3">
                                  <label class="form-label">Ruc transp. :</label>
                                  <input type="text" class="form-control" name="ructran" id="ructran" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="col-12 col-sm-4 col-md-4 col-lg-4 mb-3">
                                  <label class="form-label">Razón social transportista :</label>
                                  <input type="text" class="form-control" name="rsocialtransportista" id="rsocialtransportista"
                                    maxlength="100" placeholder="" required="true" onkeypress="mayus(this);">
                                </div>

                              </div>


                            </div>


                            <li class="nav-item">
                              <a class="nav-link text-center mb-3" id="pills-contact-tab" data-toggle="pill" href="#otrosd"
                                role="tab" aria-controls="documentos" aria-selected="false">
                                <span class="round-tabs two">
                                  DATOS DEL PUNTO DE PARTIDA Y LLEGADA & OTROS DATOS
                                </span>
                              </a>


                              <div class="row">

                                <div class="form-group col-12 col-sm-5 col-md-5 col-lg-3 mb-3">
                                  <label class="form-label">Dirección punto de partida:</label>
                                  <input type="text" class="form-control" name="ppartida" id="ppartida" placeholder=""
                                    required="true" value="" oninput="mayus(this);">
                                  <!-- <input type="text" class="form-control" name="ppartida" id="ppartida" placeholder=""
                            required="true" value="" onkeypress="mayus(this);"> -->
                                </div>

                                <script>
                                  function mayus(input) {
                                    input.value = input.value.toUpperCase();
                                  }

                                </script>
                                <style>
                                  #ppartida {
                                    text-transform: uppercase;
                                  }
                                </style>

                                <div class="form-group col-10 col-sm-5 col-md-5 col-lg-2 mb-3">
                                  <label class="form-label">Ubigeo partida:</label>
                                  <input type="text" class="form-control" name="ubigeopartida" id="ubigeopartida">
                                </div>

                                <div
                                  class="form-group col-2 col-sm-2 col-md-2 col-lg-1 mb-3 d-flex justify-content-center align-items-center">
                                  <a href="../files/ubigueo/ubigueo.xlsx" class="p-0" data-togle="tooltip"
                                    title="Buscar ubigeo"><img src="../files/ubigueo/ubigeo.png"></a>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                                  <label class="form-label">Dirección punto de llegada:</label>
                                  <input type="text" class="form-control" name="pllegada" id="pllegada" placeholder=""
                                    required="true" onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                                  <label class="form-label">Ubigeo llegada:</label>
                                  <input type="text" class="form-control" name="ubigeollegada" id="ubigeollegada">
                                </div>

                                <div class="form-group col-12 mb-3">
                                  <label class="form-label">Observaciones:</label>
                                  <textarea id="observaciones" class="form-control" rows="1" cols="7"
                                    name="observaciones"></textarea>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                  <label class="form-label">DNI conductor:</label>
                                  <input type="text" class="form-control" name="dniconduc" id="dniconduc"
                                    placeholder="DNI CONDUCTOR">
                                </div>

                                <div class="form-group col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                  <label class="form-label">Nombre coductor:</label>
                                  <input type="text" class="form-control" name="ncoductor" id="ncoductor" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-12 col-sm-4 col-md-4 col-lg-2 mb-3">
                                  <label class="form-label">Marca:</label>
                                  <input type="text" class="form-control" name="marca" id="marca" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-12 col-sm-4 col-md-6 col-lg-2 mb-3">
                                  <label class="form-label">Placa:</label>
                                  <input type="text" class="form-control" name="placa" id="placa" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-lg-2 col-md-6 col-sm-4 col-xs-12 mb-3">
                                  <label class="form-label">Constancia de inscr.:</label>
                                  <input type="text" class="form-control" name="cinc" id="cinc" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-12 mb-3">

                                  <label class="form-label">Container:</label>
                                  <textarea name="container" class="form-control" id="container" cols="" rows="3"
                                    onkeypress="mayus(this);" style="height: 20px;"></textarea>

                                  <!-- <input type="text" class="form-control" name="container" id="container" placeholder="" onkeypress="mayus(this);"> -->

                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Nro de licencia:</label>
                                  <input type="text" class="form-control" name="nlicencia" id="nlicencia" placeholder=""
                                    onkeypress="mayus(this);">

                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">U. M. P. bruto:</label>
                                  <select class="form-control" name="umedidapbruto" id="umedidapbruto" required
                                    data-live-search="true">
                                  </select>

                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Peso bruto:</label>
                                  <input type="text" class="form-control" name="pesobruto" id="pesobruto" placeholder="0.00"
                                    onkeypress="mayus(this);">

                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Orden de compra:</label>
                                  <input type="text" class="form-control" name="ocompra" id="ocompra" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Nro de pedido:</label>
                                  <input type="text" class="form-control" name="npedido" id="npedido" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Vendedor:</label>
                                  <input type="text" class="form-control" name="vendedor" id="vendedor" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Costo min. de tras.:</label>
                                  <input type="text" class="form-control" name="costmt" id="costmt" placeholder=""
                                    onkeypress="mayus(this);">
                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Nro. Comprobante:</label> <!--Datos del cliente-->
                                  <input type="text" class="form-control" name="numero_comprobante" id="numero_comprobante"
                                    placeholder="" width="50x" r>
                                </div>

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12 mb-3">
                                  <label class="form-label">Fecha comprobante:</label> <!--Datos del cliente-->
                                  <input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante"
                                    placeholder="" width="50x">
                                </div>

                              </div>

                            </li>
                          </ul>

                          <div class="form-group col-12 mb-3">

                            <div class="table-responsive">

                              <div class="col-12">

                                <table id="detalles" class="table table-striped table-bordered table-condensed table-hover">

                                  <thead style="background-color:#35770c; color: #fff;">

                                    <th>CANT.</th>

                                    <th>CÓDIGO</th>

                                    <th>DESCRIPCIÓN</th>

                                    <th>U. MED.</th>



                                  </thead>

                                </table>

                              </div>

                            </div>

                          </div>


                          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button class="btn btn-primary me-md-2" type="submit" id="btnGuardar"><i class="fas fa-save"></i>
                              EMITIR
                              GUÍA</button>
                            <button id="btnCancelar" class="btn btn-danger" onclick="cancelarform()" type="button"><i
                                class="fas fa-arrow-circle-left"></i> CANCELAR</button>
                          </div>



                        </form>

                      </div>
                      </div>
                    </div>
                    <!-- Hasta quiii termina el contenedor , el de abajo es el modelo  -->




                    <!--Fin centro -->

                  </div><!-- /.box -->

                </div><!-- /.col -->

            </div><!-- /.row -->


            <!--Fin-Contenido-->









            <!-- Modal -->

            <div class="modal fade" id="myModalComprobante" tabindex="-1" aria-labelledby="myModalComprobanteLabel"
              aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="myModalComprobanteLabel">Seleccione un comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()"><i class="fas fa-sync-alt"></i>
                      Refrescar</button>
                    <div class="table-responsive">
                      <table id="tblacomprobante" class="table table-striped table-bordered table-hover" style="width: 100%;">
                        <thead>
                          <tr>
                            <th>Opciones</th>
                            <th>Num. Documento</th>
                            <th>Razon Social</th>
                            <th>Domicilio</th>
                            <th>Numero comprobante</th>
                            <th>Neto</th>
                            <th>IGV</th>
                            <th>Totalt</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

      <script type="text/javascript" src="scripts/guiaremision.js"></script>


      <?php

}

ob_end_flush();

?>