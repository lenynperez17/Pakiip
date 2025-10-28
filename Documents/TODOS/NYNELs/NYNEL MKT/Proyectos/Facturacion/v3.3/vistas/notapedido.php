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

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <div class="">
      <!-- Main content -->
      <section class="">
        <div class="content-header">
          <h1>Nota de venta
            <button class="btn btn-primary btn-sm" id="btnagregar" onclick="mostrarform(true)">Nuevo</button>
          </h1>
        </div>

        <div class="row" style="background:white;">
          <div class="col-md-12">
            <div class="">

              <div class="table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                  <thead style="text-align:center;">
                    <th>Opciones</th>
                    <!--  <th><i class="fa fa-send"></i></th> -->
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Comprobante</th>
                    <th>Total Articulo</th>
                    <th>Adelanto</th>
                    <th>Faltante</th>
                    <!-- <th>Total</th> -->
                    <th>Estado</th>
                  </thead>

                  <tbody style="text-align:center;">
                  </tbody>
                </table>
              </div>

              <div class="panel-body" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                  <div class="row">

                    <div class="col-md-12">
                      <div class="card">
                        <div class="card-body">

                          <div class="row">

                            <div class="mb-3 col-lg-2">
                              <label>Serie</label>
                              <select class="form-control" name="serie" id="serie" onchange="incremetarNum()"></select>
                              <input type="hidden" name="idnumeracion" id="idnumeracion">
                              <input type="hidden" name="SerieReal" id="SerieReal">
                            </div>

                            <div class="mb-3 col-lg-1">
                              <label>Número</label> <input type="text" name="numero_boleta" id="numero_boleta"
                                class="form-control" required="true" readonly>
                            </div>

                            <div class="mb-3 col-lg-2">
                              <label>Fecha operación:</label>
                              <input type="date" disabled="true" style="font-size: 12pt;" class="form-control"
                                name="fecha_emision_01" id="fecha_emision_01" disabled="true" required="true"
                                onchange="focusTdoc()">
                            </div>

                            <div class="mb-3 col-lg-2">
                              <label>Tipo de nota</label>
                              <select class="form-control" name="tiponota" id="tiponota" onchange="cambiarlistado()">
                                TIPO DE NOTA
                                <option value="st">SELECCIONE TIPO DE NOTA</option>
                                <option value="productos" selected="true">PRODUCTOS</option>
                                <!-- <option value="servicios">SERVICIOS</option> -->
                              </select>
                            </div>

                            <div hidden class="mb-3 col-lg-2">
                              <label>Cotización n°:</label>
                              <input type="text" class="" name="ncotizacion" id="ncotizacion">
                            </div>

                            <div class="mb-3 col-lg-1">
                              <label>Moneda:</label>
                              <select class="form-control" name="tipo_moneda_24" id="tipo_moneda_24">
                                <option value="PEN" selected="true">SOLES</option>
                                <!-- <option value="USD">DOLARES</option>
                            <option value="EUR">EUROS</option> -->
                              </select>
                            </div>


                            <!--Campos para guardar comprobante Factura-->
                            <input type="hidden" name="idboleta" id="idboleta">
                            <input type="hidden" name="firma_digital_36" id="firma_digital_36" value="44477344">

                            <!--Datos de empresa Estrella-->
                            <input type="hidden" name="idempresa" id="idempresa" value=<?php echo $_SESSION['idempresa'] ?>>

                            <input type="hidden" name="tipo_documento_06" id="tipo_documento_06" value="50">
                            <input type="hidden" name="numeracion_07" id="numeracion_07" value="">

                            <!--Datos del cliente-->
                            <input type="hidden" name="idcliente" id="idcliente">

                            <input type="hidden" name="tipo_documento_cliente" id="tipo_documento_cliente" value="0">
                            <!--Datos del cliente-->

                            <!--Datos de impuestos-->
                            <input type="hidden" name="codigo_tipo_15_1" id="codigo_tipo_15_1" value="1001">

                            <!--IGV-->
                            <input type="hidden" name="codigo_tributo_18_3" id="codigo_tributo_3" value="1000">
                            <input type="hidden" name="nombre_tributo_18_4" id="nombre_tributo_4"
                              value="IGV IMPUESTO GENERAL A LAS VENTAS">
                            <input type="hidden" name="codigo_internacional_18_5" id="codigo_internacional_5" value="VAT">
                            <!--IGV-->

                            <!-- <input type="hidden" name="tipo_moneda_24" id="tipo_moneda_24" value="PEN"> -->

                            <input type="hidden" name="tipo_documento_25_1" id="tipo_documento_25_1" value="">

                            <input type="hidden" name="codigo_leyenda_26_1" id="codigo_leyenda_26_1" value="1000">
                            <input type="hidden" name="descripcion_leyenda_26_2" id="descripcion_leyenda_26_2"
                              value="DESCRIPCION DE LEYENDA">

                            <input type="hidden" name="version_ubl_37" id="version_ubl_37" value="2.0">
                            <input type="hidden" name="version_estructura_38" id="version_estructura_38" value="1.0">

                            <input type="hidden" name="tasa_igv" id="tasa_igv" value="0.18">
                            <!--Fin de campos-->

                            <!--DETALLE-->
                            <input type="hidden" name="codigo_precio_14_1" id="codigo_precio" value="01">
                            <input type="hidden" name="afectacion_igv_3" id="afectacion_igv_3" value="10">
                            <input type="hidden" name="afectacion_igv_4" id="afectacion_igv_4" value="1000">
                            <input type="hidden" name="afectacion_igv_5" id="afectacion_igv_5" value="IGV">
                            <input type="hidden" name="afectacion_igv_6" id="afectacion_igv_6" value="VAT">

                            <input type="hidden" name="hora" id="hora">
                            <!--DETALLE-->


                            <div class="mb-3 col-lg-2">
                              <label>DOC.:</label>
                              <select class="form-control" name="tipo_doc_ide" id="tipo_doc_ide" onchange="focusI()">
                                <OPTION value="0">S/D</OPTION>
                                <OPTION value="1">DNI</OPTION>
                                <OPTION value="4">C.E.</OPTION>
                                <OPTION value="7">PASAPORTE</OPTION>
                                <OPTION value="A">CED. D. IDE.</OPTION>
                              </select>
                            </div>

                            <div class="mb-3 form-group col-lg-2">
                              <label>Documento + enter:</label>
                              <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                                maxlength="15" placeholder="Número + enter" value="-" onfocus="focusTest(this);"
                                onkeypress="agregarClientexDoc(event)" onchange="agregarClientexDoc2();">
                              <!-- true"  onkeypress="agregarClientexDoc(event)" onkeyup="agregarClientexDoc2()">  -->
                            </div>

                            <div class="mb-3 form-group col-lg-3">
                              <label>Nombres y apellidos:</label> <!--Datos del cliente-->
                              <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="50"
                                placeholder="" width="50x" value="-" required="true" onkeyup="mayus(this);"
                                onkeypress="focusDir(event)">
                            </div>

                            <div class="mb-3 form-group col-lg-3">
                              <label>Dirección:</label>
                              <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal"
                                value="-" required="true" onkeyup="mayus(this);" placeholder="Dirección"
                                onkeypress="agregarArt(event)">
                            </div>

                            <div class="mb-3 form-group col-lg-2">
                              <label>Vendedor:</label>
                              <select autofocus name="vendedorsitio" id="vendedorsitio" class="form-control"></select>
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Nro de Guía:</label>
                              <input type="text" name="guia_remision_25" id="guia_remision_25" class="form-control"
                                placeholder="NRO DE GUÍA">
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Embal + transp agencia:</label>
                              <input type="text" name="ambtra" id="ambtra" class="form-control"
                                placeholder="EMBALAJE + TRANSPORTE AGENCIA">
                            </div>

                            <!-- Nuevos campos Para Creditos Pendientes-->
                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Monto N Pago:</label>
                              <input type="number" name="montoNpago" id="montoNpago" class="form-control"
                                placeholder="0.00">
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Fecha V. Crédito:</label>
                              <input type="date" name="fechavecredito" id="fechavecredito" class="form-control">
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Cantidad Cuotas:</label>
                              <input type="number" name="ccuotas" id="ccuotas" class="form-control" placeholder="0">
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Monto Cuota:</label>
                              <input type="number" name="montocuota" id="montocuota" class="form-control"
                                placeholder="0.00">
                            </div>

                            <div hidden class="mb-3 form-group col-lg-2">
                              <label>Forma Pago:</label>
                              <select name="formapago" id="formapago" class="form-control">
                                <option disabled selected value="">-Seleccionar-</option>
                                <option value="0">CRÉDITO</option>
                              </select>
                            </div>

                            <div hidden class="mb-3 col-lg-2">
                              <label>Cuotas:</label>
                              <div class="input-group">
                                <span style="cursor:pointer;" class="input-group-text" data-bs-toggle="modal"
                                  title="mostrar cuotas" data-bs-target="#modalcuotas" id="basic-addon1">&#9769;</span>
                                <span style="cursor:pointer;" class="input-group-text" title="Editar cuotas">&#10000;</span>
                                <input name="ccuotasmodal" id="ccuotasmodal" class="form-control" value="1">
                              </div>
                            </div>


                            <input type="hidden" name="pagoCuota" id="pagoCuota">
                            <input type="hidden" name="estadoMontoCredito" id="montoNpago">
                            <input type="hidden" name="estadoCuota" id="estadoCuota">

                            <!--MODAL CUOTAS-->
                            <div class="modal fade text-left" id="modalcuotas" tabindex="-1" role="dialog"
                              aria-labelledby="modalcuotas" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-scrollable" role="document">
                                <div class="modal-content">

                                  <div class="modal-header">
                                    <h5 class="modal-title" id="modalcuotas">Pago de crédito</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                      aria-label="Close"></button>
                                  </div>

                                  <div class="modal-body">
                                    <div class="container">
                                      <div id="tipopagodiv" style="text-align: center;" class="row">

                                        <div class="col-lg-6">
                                          Monto de cuotas
                                          <div id="divmontocuotas" class="mt-3"></div>
                                        </div>

                                        <div class="col-lg-6">
                                          Fechas de pago
                                          <div id="divfechaspago" class="mt-3"></div>
                                        </div>

                                      </div>
                                    </div>
                                  </div>

                                  <div class="modal-footer">
                                    <button id="btnGuardar" type="submit" class="btn btn-primary ml-1"
                                      data-bs-dismiss="modal">
                                      <i class="bx bx-check d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Pagar</span>
                                    </button>
                                  </div>

                                </div>
                              </div>
                            </div>
                            <!--FIN MODAL CUOTAS-->


                            <!-- Fin Nuevos campos Para Creditos Pendientes-->


                            <div class="row">

                              <div class="form-group mb-3  mt-3 col-lg-8">
                                <label>Codigo barra:</label>
                                <input type="text" name="codigob" id="codigob" class="form-control"
                                  onkeypress="agregarArticuloxCodigo(event)" onkeyup="mayus(this);"
                                  placeholder="INGRESE O SCANEE CÓDIGO DE BARRA DEL PRODUCTO"
                                  style="background-color: #F5F589;">
                              </div>

                              <div class="form-group col-lg-2 mt-4">
                                <input type="hidden" name="itemno" id="itemno" value="0">
                                <a data-bs-toggle="modal" data-bs-target="#myModalArt">
                                  <button id="btnAgregarArt" type="button" class="btn btn-primary"
                                    style="top: 8px;margin-top: 10px;"> <span class="fa fa-shopping-cart"></span> Agregar
                                    Artículos </button>
                                </a>
                              </div>

                            </div>

                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                              <label style="font-size: 16pt; color: red;" hidden="true" id="mensaje700"
                                name="mensaje700">Agregar DNI del cliente.</label>
                            </div>

                            <div class="table-responsive">
                              <table id="detalles" class="table table-striped"
                                style="font-size: 14px; max-width: 100%; !important;">
                                <thead style="text-align:center;">
                                  <th>Sup.</th>
                                  <th>Item</th>
                                  <th>Artículo</th>
                                  <th>Descrip.</th>
                                  <th>Cant.</th>
                                  <th>Cód. Prov.</th>
                                  <th>-</th>
                                  <th>U.M.</th>
                                  <th>Prec. Uni.</th>
                                  <th>Stock</th>

                                  <th>Importe</th>
                                </thead>

                                <tfoot>

                                  <tr>
                                    <td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <div class="saldos">
                                    </div>

                                    <th id="Titulo" style="font-weight: bold; vertical-align: center;">TOTAL A PAGAR</th>
                                    <!--Datos de impuestos--> <!--IGV-->
                                    <th id="CuadroT" style="font-weight: bold;">
                                      <h4 id="total" style="font-weight: bold; vertical-align: center;">0.00
                                      </h4>
                                      <input type="hidden" name="total_final" id="total_final">
                                      <input type="hidden" name="pre_v_u" id="pre_v_u">
                                      <input type="hidden" name="subtotal_boleta" id="subtotal_boleta">
                                      <input type="hidden" name="total_igv" id="total_igv">

                                    </th><!--Datos de impuestos--> <!--TOTAL-->
                                    </td>
                                  </tr>

                                  <tr>
                                    <td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <th id="Titulo" style="font-weight: bold; vertical-align: center;">ADELANTO
                                      <label id="porade" style="font-weight: bold;">%</label>
                                    </th>
                                    <th style="font-weight: bold;">
                                      <input type="text" class="" name="adelanto" id="adelanto"
                                        onchange="modificarSubototales();" value="0.00" onfocus="focusTest(this)">
                                    </th>
                                    </td>
                                  </tr>

                                  <tr>
                                    <td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <th id="Titulo" style="font-weight: bold; vertical-align: center;">FALTANTE
                                      <label id="porfalt" style="font-weight: bold;">%</label>
                                    </th>
                                    <!--Datos de impuestos--> <!--IGV-->
                                    <th style="font-weight: bold;">
                                      <input type="text" class="" name="faltante" id="faltante" readonly>
                                    </th>
                                    </td>
                                  </tr>

                                  <tr>
                                    <td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <th id="Titulo" style="font-weight: bold; vertical-align: center;">TOTAL
                                      ABONAR
                                    </th> <!--Datos de impuestos--> <!--IGV-->
                                    <th id="CuadroT" style="font-weight: bold;">
                                      <h4 id="total_g" style="font-weight: bold; vertical-align: center; color:white;">0.00
                                      </h4>
                                      <input type="hidden" name="total_g" id="total_g">
                                    </th><!--Datos de impuestos--> <!--TOTAL-->
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

                  </div>
              </div>

            </div>
            </form>
          </div>
      </section>
    </div>





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


    <div class="modal fade text-left" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalArt"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalArt">Agrega tu producto</h5>
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
              <span class="d-none d-sm-block">Cancelar</span>
            </button>
            <!-- <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
              <i class="bx bx-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Guardar</span>
            </button> -->
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
            <!-- <iframe border="1" frameborder="1" height="310" width="100%" src="https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias"></iframe> -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->





    <div class="modal fade text-left" id="modalnuevoarticulo" tabindex="-1" role="dialog"
      aria-labelledby="modalnuevoarticulo" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalnuevoarticulo">Agrega nuevo artículo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <form name="formularionarticulo" id="formularionarticulo" method="POST">
              <div class="row ">
                <div class="mb-3 col-lg-3">
                  <label>Almacen</label>
                  <input type="hidden" name="idarticulonuevo" id="idarticulonuevo">
                  <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                  <select class="form-control" name="idalmacennarticulo" id="idalmacennarticulo" required
                    data-live-search="true">
                  </select>
                </div>

                <div class="mb-3 col-lg-3">
                  <label>Categoría</label>
                  <select class="form-control" name="idfamilianarticulo" id="idfamilianarticulo" required
                    data-live-search="true">
                  </select>
                </div>


                <div class="mb-3 col-lg-3">
                  <label>Tipo</label>
                  <select class="form-control" name="tipoitemnarticulo" id="tipoitemnarticulo" onchange="focuscodprov()">
                    <option value="productos" selected="true">PRODUCTO</option>
                    <option value="servicios">SERVICIO</option>
                  </select>
                </div>


                <div class="mb-3 col-lg-3">
                  <label>Descripción / Nombre:</label>
                  <input type="text" class="form-control focus" name="nombrenarticulo" id="nombrenarticulo"
                    placeholder="Nombre" onkeyup="mayus(this);" onkeypress=" return limitestockf(event, this)"
                    autofocus="true" onchange="generarcodigonarti()">
                </div>


                <div class="mb-3 col-lg-3">
                  <label>Stock:</label>
                  <input type="text" class="form-control" name="stocknarticulo" id="stocknarticulo" maxlength="100"
                    placeholder="Stock" required="true" onkeypress="return NumCheck(event, this)">
                </div>



                <div class="mb-3 col-lg-3">
                  <label>Precio venta (S/.):</label>
                  <input type="text" class="form-control" name="precioventanarticulo" id="precioventanarticulo"
                    onkeypress="return NumCheck(event, this)">
                </div>

                <div class="mb-3 col-lg-3">
                  <label>Código:</label>
                  <input type="text" class="form-control" name="codigonarticulonarticulo" id="codigonarticulonarticulo">
                </div>

                <div class="mb-3 col-lg-3">
                  <label>Unidad medida:</label>
                  <select class="form-control" name="umedidanp" id="umedidanp" required data-live-search="true">
                  </select>
                </div>


                <div class="mb-3 col-lg-3">
                  <label>Descripción:</label>
                  <textarea class="form-control" id="descripcionnarticulo" name="descripcionnarticulo" rows="3" cols="70"
                    onkeyup="mayus(this)" onkeypress="return focusDescdet(event, this)"> </textarea>
                </div>
              </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Cancelar</span>
            </button>
            <button id="btnguardarncliente" name="btnguardarncliente" value="btnGuardarcliente" type="submit"
              class="btn btn-primary ml-1" data-bs-dismiss="modal">
              <i class="bx bx-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Agregar artículo</span>
            </button>
          </div>
          </form>
        </div>
      </div>
    </div>



    <!-- Modal  nuevo articulo -->
    <div class="modal fade" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 70% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">NUEVO ARTÍCULO - SOLO PARA UNIDAD</h4>
          </div>
          <form name="formularionarticulo" id="formularionarticulo" method="POST">
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
              <input type="text" class="form-control focus" name="nombrenarticulo" id="nombrenarticulo" placeholder="Nombre"
                onkeyup="mayus(this);" onkeypress=" return limitestockf(event, this)" autofocus="true"
                onchange="generarcodigonarti()">
            </div>


            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
              <label>Stock:</label>
              <input type="text" class="" name="stocknarticulo" id="stocknarticulo" maxlength="100" placeholder="Stock"
                required="true" onkeypress="return NumCheck(event, this)">
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
              <textarea id="descripcionnarticulo" name="descripcionnarticulo" rows="3" cols="70" onkeyup="mayus(this)"
                onkeypress="return focusDescdet(event, this)"> </textarea>
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



    <!-- Modal -->
    <div class="modal fade" id="modalPreviewXml" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 70% !important;">
        <div class="modal-content">

          <div class="modal-header">
            <h4 class="modal-title">NOTA DE PEDIDO</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <iframe name="modalCom" id="modalCom" border="0" frameborder="0" width="100%" style="height: 800px;" src="">
          </iframe>

          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
          </div>

        </div>
      </div>
    </div>

    <!-- Fin modal -->



    <div class="modal fade" id="modalPreviewticket" tabindex="-1" aria-labelledby="modalPreviewticketLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" style="max-width: 24% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPreviewticketLabel">Ticket de venta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="imp1">
            <div>
              <iframe name="modalComticket" id="modalComticket" border="0" frameborder="0" width="100%"
                style="height: 800px;" marginwidth="1" src="">
              </iframe>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Fin modal -->


    <div class="modal fade" id="modalPreview2Hojas" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Formato 2 Copias</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="imp1">
            <div>
              <iframe name="modalCom2Hojas" id="modalCom2Hojas" frameborder="0" style="height: 800px; width: 100%;"
                src=""></iframe>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>



    <script>
      function imprim1(imp1) {
        var printContents = document.getElementById('imp1').innerHTML;
        w = window.open();
        w.document.write(printContents);
        w.document.close(); // necessary for IE >= 10
        w.focus(); // necessary for IE >= 10
        w.print();
        w.close();
        return true;
      }
    </script>





    <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/notapedido.js"></script>
  <?php
}
ob_end_flush();
?>