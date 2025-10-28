<?php
//Activamos el almacenamiento en el buffer
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
    <link rel="stylesheet" href="number.css">
    <link rel="stylesheet" href="enviosunat.css">
    <link rel="stylesheet" media="all" href="../public/css/letter.css" data-turbolinks-track="reload">

    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="">

              <div class="box-header with-border">
                <h1 class="box-title">PEDIDO RÁPIDO</h1>
                <label style="font-size: 20px;">NRO</label> <label id="nropedido" style="font-size: 20px;"></label>


                <div>
                  PRODUCTOS <input type="checkbox" name="chkpr" id="chkpr" onclick="mostrarp()"> COMIDA

                </div>

                <div>
                  FACTURA <input type="checkbox" name="chkcomp" id="chkcomp" onclick="tipocomprobante()"> BOLETA
                </div>
              </div>



              <form name="formulario" id="formulario" method="POST" autocomplete="off">
                <input type="hidden" name="idplato" id="idplato">
                <input type="hidden" name="nombreplato" id="nombreplato">
                <input type="hidden" name="precioplato" id="precioplato">
                <input type="hidden" name="cantidadplato" id="cantidadplato">

                <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                    <label>Fecha operación:</label>
                    <input type="date" style="font-size: 12pt;" class="" name="fechaemision" id="fechaemision"
                      required="true" onchange="focusTdoc()">
                  </div>

                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                    <label>Moneda:</label>
                    <select class="" name="tipo_moneda_24" id="tipo_moneda_24">
                      <option value="PEN" selected="true">SOLES</option>
                      <option value="USD">DOLARES</option>

                    </select>
                  </div>

                  <div class="form-group col-lg-1 col-md-4 col-sm-6 col-xs-12">
                    <label>T. camb:</label>
                    <input type="text" name="tcambio" id="tcambio" class="">
                  </div>

                  <!--  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                           <a data-toggle="modal" href="#modalTcambio"><button class="btn btn-primary" id="tcambio" name="tcambio"><i class="fa fa-money"  data-toggle="tooltip" title="Tipo de cambio"></i> T.cambio</button> </a>
                         </div> -->
                </div>


                <div id="clientefactura">
                  <div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <label>Número de ruc</label>
                    <input type="text" style="font-size: 20pt;" name="nruc" id="nruc" class="form-control" required="true"
                      onkeypress="agregarClientexRuc(event)" onchange="quitasuge1()">
                    <div id="suggestions"></div>
                    <input type="hidden" name="idpersona" id="idpersona">
                  </div>

                  <div class="form-group col-lg-9 col-md-6 col-sm-6 col-xs-12">
                    <label>Razón social</label>
                    <input type="text" style="font-size: 20pt;" name="rsocial" id="rsocial" class="form-control"
                      required="true" onchange="quitasuge2()">
                    <div id="suggestions2"></div>
                  </div>

                  <div class="form-group col-lg-12 col-md-6 col-sm-6 col-xs-12">
                    <label>Domicilio fiscal</label> <input type="text" style="font-size: 20pt;" name="dfiscal" id="dfiscal"
                      class="form-control" required="true">
                  </div>



                </div>


                <div id="clienteboleta" style="display: none;">
                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                    <label>Tipo documento</label> <select class="" name="tipo_doc_ide" id="tipo_doc_ide" onchange="focusI()"
                      style="font-size: 15pt;">
                      <OPTION value="0">SIN DOCUMENTO</OPTION>
                      <OPTION value="1">DNI</OPTION>
                      <OPTION value="4">C.E.</OPTION>
                      <OPTION value="7">PASAPORTE</OPTION>
                      <OPTION value="A">CED. D. IDE.</OPTION>
                      <OPTION value="6">RUC</OPTION>
                    </select>
                  </div>

                  <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <label>Nro. documento</label> <input type="text" style="font-size: 20pt;" name="ndocumento"
                      id="ndocumento" class="form-control" required="true" placeholder=""
                      onkeypress="agregarClientexDoc(event)">
                  </div>

                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Nombre y apellido</label> <input type="text" style="font-size: 20pt;" name="nombrea" id="nombrea"
                      class="form-control" required="true" placeholder="">
                  </div>
                </div>


                <div class="form-group col-lg-12 col-md-6 col-sm-6 col-xs-12">
                  <label>Correo</label> <input type="text" style="font-size: 20pt;" name="correo" id="correo"
                    class="form-control" required="true" placeholder="@">
                </div>


                <div id="detalleplatosdiv" style="display: none;">
                  <div class="panel-body table-responsive" id="listadoregistros">
                    <table border="0" cellspacing="5" cellpadding="5">
                      <tbody>

                      </tbody>
                    </table>
                    <table id="detalleplatos" class="table table-striped table-bordered table-condensed  table-hover">
                      <thead>
                        <th>Eliminar</th>
                        <th>Plato</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Imagen</th>
                      </thead>
                      <tbody>
                      </tbody>

                    </table>
                  </div>
                </div>
              </form>



              <!--RESTAURANT -->
              <div class="modal fade" id="myModalrestaurant" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                aria-hidden="true" role="Documento">
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">

                    <div class="panel-body" id="formularioregistrosresta">

                      <table>
                        <tr>
                          <td>
                            <table id="tbllistado" class="table table-striped table-bordered table-condensed  table-hover">
                              <thead>
                                <th style="text-align: center;">ENTRADAS</th>
                              </thead>
                            </table>
                          </td>


                          <td>
                            <table id="tbllistado2" class="table table-striped table-bordered table-condensed  table-hover">
                              <thead>
                                <th style="text-align: center;">PLATOS DE FONDO</th>
                              </thead>
                            </table>
                          </td>

                          <td>
                            <table id="tbllistado3" class="table table-striped table-bordered table-condensed  table-hover">
                              <thead>
                                <th style="text-align: center;">POSTRES</th>
                              </thead>
                            </table>
                          </td>
                        </tr>
                      </table>

                    </div>

                  </div>
                </div>
              </div>
              <!-------------------------------FACTURA--------------------------------------------------------------->



          <!--RESTAURANT INTERFACE 2 -->
              <div class="modal fade" id="myModalrestaurant2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                aria-hidden="true" role="Documento">
                <div class="modal-dialog" style="width: 40% !important;">
                  <div class="modal-content">

                    <div class="panel-body" id="formularioregistrosresta">

                      <table>
                        <tr>
                          <td align="center">

                            <input type="hidden" name="imagenactual" id="imagenactual">
                            <img width="300px" height="500px" id="imagenpedido">
                            <label id="tituloplato" style="align-content: center; font-size: 22px;"></label>
                            <label id="precioplato" style="align-content: center; font-size: 22px; color: blue;">111</label>

                          </td>
                          <td>
                            <label id="titulo" style="align-content: center; font-size: 22px;">Cantidad:</label>
                          </td>
                          <td>
                            <div class="number-input md-number-input">
                              <button onclick="this.parentNode.querySelector('input[type=number]').stepDown()"
                                class="minus"></button>
                              <input class="quantity" min="1" name="quantity" value="1" type="number">
                              <button onclick="this.parentNode.querySelector('input[type=number]').stepUp()"
                                class="plus"></button>
                            </div>
                            <select id="mesaselect"></select>
                            <label id="estadoplato" style="align-content: center; font-size: 20px;"></label>
                          </td>
                        </tr>

                        <tfoot>
                          <tr>
                            <td></td>
                            <td></td>
                            <td>
                              <input type="button" name="" id="btnaceptaritem" class="btn-success" value="ACEPTAR"
                                onclick="agregaraldetalle();"> <input type="button" name="" id="btncancelaritem"
                                class="btn-danger" value="CANCELAR">
                            </td>
                          </tr>
                        </tfoot>
                      </table>

                    </div>

                  </div>
                </div>
              </div>
              <!-------------------------------FACTURA--------------------------------------------------------------->


          <!--Fin centro -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->
    <!--Fin-Contenido-->


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

            <!-- <table id="tblaclientes" class="table table-striped table-bordered table-condensed table-hover" width=-5px>
            <thead>
                <th >Opciones</th>
                <th >Nombre</th>
                <th >RUC</th>
                <th >Dirección</th>
                
            </thead>
            <tbody>
               
            </tbody>
            <tfoot>
                <th>Opciones</th>
                <th>Nombre</th>
                <th>RUC</th>
                <th>Dirección</th>

            </tfoot>
          </table> -->

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
                    <td align="center"><a onclick="cargarbien()" name="tipoitem" id="tipoitem" value="bien"><img
                          src="../public/images/producto.png"><br>Productos</a></td>
                    <td align="center"><a onclick="cargarservicio()" name="tipoitem" id="tipoitem" value="servicio"><img
                          src="../public/images/servicio.png"><br>Servicios</a></td>
                    <input type="hidden" name="familia" id="familia">
                    <input type="hidden" name="nombre" id="nombre">
                    <input type="hidden" name="codigo_proveedor" id="codigo_proveedor">
                    <input type="hidden" name="stock" id="stock">
                    <input type="hidden" name="cicbper" id="cicbper">
                  </tr>

                  <tr>
                    <td>Cantidad:</td>
                    <td><input type="text" name="icantidad" id="icantidad" class="form-control"
                        onkeyup="calculartotalitem();"></td>

                    <td>Précio unitario:</td>
                    <td><input type="text" name="ipunitario" id="ipunitario" class="form-control"
                        onkeyup="calvaloruniitem();"></td>
                  </tr>

                  <tr>
                    <td>Unidad de medida:</td>
                    <td><input type="text" name="iumedida" id="iumedida" class="form-control" readonly size="4">
                      <select name="unidadm" id="unidadm" class="" onchange="cambioUm()" size="2"></select>
                    </td>
                    <td>Valor unitario:</td>
                    <td><input type="text" name="ivunitario" id="ivunitario" class="form-control" readonly></td>
                  </tr>

                  <tr>
                    <td>Código:</td>
                    <td><input type="hidden" name="iiditem" id="iiditem" class="form-control">
                      <input type="text" name="icodigo" id="icodigo" class="form-control">
                    </td>
                    <td>Descuento:</td>
                    <td><input type="text" name="idescuento" id="idescuento" class="form-control" readonly></td>
                  </tr>

                  <tr>
                    <td>Descripción:</td>
                    <td><textarea name="idescripcion" id="idescripcion" class="form-control"></textarea></td>
                    <td>IGV (18%):</td>
                    <td>
                      <input type="radio" name="iigv" id="iigv" value="grav" onclick="calcuigv()"> Gvdo &nbsp;&nbsp;
                      <input type="radio" name="iigv" id="iigv" value="exo" onclick="calcuigv()" disabled> Exo.&nbsp;&nbsp;
                      <input type="radio" name="iigv" id="iigv" value="ina" onclick="calcuigv()" disabled> Ina.
                    </td>
                  </tr>


                  <tr>
                    <td>ICBPER:</td>

                    <td>
                      <!-- <input type="text" name="iicbper1" id="iicbper1" class="form-control" size="4"> -->
                      <input type="text" name="iicbper2" id="iicbper2" xclass="form-control" readonly>
                    </td>
                    <td>
                    </td>

                    <td><input type="text" name="iigvresu" id="iigvresu" class="form-control" value="0" readonly=""></td>
                  </tr>

                  <tr>
                    <td>Impuesto ICBPER:</td>
                    <td><input type="text" name="iimpicbper" id="iimpicbper" class="form-control" readonly=""></td>
                    <td>Importe total del Item:</td>
                    <td><input type="text" name="iimportetotalitem" id="iimportetotalitem" class="form-control" readonly>
                    </td>
                  </tr>

                  <tr>
                  </tr>

                  <tr></tr>

                  <tr>
                    <td></td>
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
                  <input type="number" class="form-control" name="nrucbusqueda" id="nrucbusqueda"
                    placeholder="Ingrese RUC o DNI" pattern="([0-9][0-9][0-9][0-9][0-9][0-9]

[0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])" autofocus>
                </div>
                <button type="submit" class="btn btn-success" name="btn-submit" id="btn-submit" value="burcarclientesunat">
                  <i class="fa fa-search"></i> Buscar SUNAT
                </button>
              </form>
            </div>

            <form name="formularioncliente" id="formularioncliente" method="POST">
              <div class="">
                <input type="hidden" name="idpersona" id="idpersona">
                <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
              </div>

              <div class="form-group col-lg-1 col-md-12 col-sm-12 col-xs-12">
                <label>Tipo Doc.:</label>
                <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" required>
                  <option value="6"> RUC </option>
                </select>
              </div>

              <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                <label>N. Doc.:</label>
                <input type="text" class="form-control" name="numero_documento3" id="numero_documento3" maxlength="20"
                  placeholder="Documento" onkeypress="return focusRsocial(event, this)">
              </div>


              <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                <label>Razón social:</label>
                <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="100"
                  placeholder="Razón social" required onkeypress="return focusDomi(event, this)">
              </div>


              <div class="form-group col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <label>Domicilio:</label>
                <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal" maxlength="100"
                  placeholder="Domicilio fiscal" required onkeypress="focustel(event, this)">
              </div>


              <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                <input type="number" class="form-control" name="telefono1" id="telefono1" maxlength="15"
                  placeholder="Teléfono 1"
                  pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])"
                  onkeypress="return focusemail(event, 

this)">
              </div>

              <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                <input type="text" class="form-control" name="email" id="email" maxlength="50" placeholder="CORREO"
                  required="true" onkeypress="return focusguardar(event, this)">
              </div>

              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                <button class="btn btn-primary" type="submit" id="btnguardarncliente" name="btnguardarncliente"
                  value="btnGuardarcliente">
                  <i class="fa fa-save"></i> Guardar
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
                      alert("Ya esta registrado cliente, se agregarán sus datos!");
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

                      $.ajax({
                        data: { "nruc": $("#nruc").val() },
                        type: "POST",
                        dataType: "json",
                        url: "../ajax/consultasunat.php",
                      }).done(function (data, textStatus, jqXHR) {

                        if (data['success'] != "false" && data['success'] != false) {

                          if (typeof (data['result']) != 'undefined') {
                            $("#tbody").html("");
                            $("#numero_documento3").val(data.result.RUC);
                            $("#razon_social").val(data.result.RazonSocial);
                            $("#domicilio_fiscal").val(data.result.Direccion);
                            $("#telefono1").css("background-color", "#D1F2EB");
                            $("#email").css("background-color", "#D1F2EB");
                            $("#telefono1").focus();
                          }

                          $("#error").hide();
                          $(".result").show();

                        }
                        else {

                          if (typeof (data['msg']) != 'undefined') {
                            alert(data['msg']);
                          }
                          $("#nruc").focus();

                        }
                      }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert("Solicitud fallida:" + textStatus);
                        $this.button('reset');
                        $.ajaxblock();
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
    <div class="modal fade" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 100% !important;">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione el tipo de précio</h4>

            <select class="form-control" id="tipoprecio" onchange="listarArticulos()" style="background-color: #85d197;">
              <option value='1'>PRECIO PÚBLICO</option>
              <option value='2'>PRÉCIO POR MAYOR</option>
              <option value='3'>PRÉCIO DISTRIBUIDOR</option>
            </select>

          </div>

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
    <div class="modal fade" id="myModalArt_" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 100% !important;">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione el tipo de précio</h4>

            <select class="form-control" id="tipoprecio" onchange="listarArticulos()" style="background-color: #85d197;">
              <option value='1'>PRECIO PÚBLICO</option>
              <option value='2'>PRÉCIO POR MAYOR</option>
              <option value='3'>PRÉCIO DISTRIBUIDOR</option>
            </select>

          </div>

          <div class="table-responsive">
            <table id="tblarticulos_" class="table table-striped table-bordered table-condensed table-hover">
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
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->

    <input type="hidden" name="idultimocom" id="idultimocom">





    <!-- Modal -->
    <!-- <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-lg" style="width: 100% !important;" >
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">COMPROBANTE</h4>
        </div>
        
    <iframe name="modalCom" id="modalCom" border="0" frameborder="0"  width="100%" style="height: 800px;" marginwidth="1" 
    src="">
    </iframe>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>        
      </div>
    </div>
  </div>   -->
    <!-- Fin modal -->



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
              ENVIAR POR CORREO FACTURA N°: <h3 style="" id="ultimocomprobante"> </h3> AL CORREO: <h3 style=""
                id="ultimocomprobantecorreo"></h3>
              <a onclick="enviarcorreoprew()">
                <img src="../public/images/mail.png">
              </a>
            </div>
            <button class="btn btn-info" name="estadoenvio" id="estadoenvio" value="ESTADO ENVIO A SUNAT"
              onclick="estadoenvio()">Estado envio</button>
            <h3 id="estadofact">Documento emitido</h3>

            <h3 id="estadofact2" style="color: red;"> Recuerde que para enviar por correo debe hacer la vista previa para
              que se generen los archivos PDF.</h3>

            <h4>Recuerde que puede enviar los comprobantes por correo. Cuide el planeta.</h4> <img
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
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">FACTURA</h4>
          </div>

          <iframe name="modalCom" id="modalCom" border="0" frameborder="0" width="100%" style="height: 800px;"
            marginwidth="1" src="">
          </iframe>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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
                <input id="pizza_diferencia_vuelto" readonly="readonly" type="text" name="invoice[diferencia_vuelto]">
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
    <div class="modal fade" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 70% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">FACTURA</h4>
          </div>

          <iframe name="modalxml" id="modalxml" border="0" frameborder="0" width="100%" style="height: 800px;"
            marginwidth="1" src="">
          </iframe>

          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
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




  <script type="text/javascript" src="scripts/pedidorapido.js"></script>






  <?php
}
ob_end_flush();
?>