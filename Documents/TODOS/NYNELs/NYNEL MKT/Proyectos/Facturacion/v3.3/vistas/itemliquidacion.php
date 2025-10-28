<?php
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['Ventas'] == 1) {
    ?>
             <!-- Custom CSS -->

                <!-- <link rel="stylesheet" href="../public/css/main.css" > -->

                    <!-- html5tooltips Styles & animations -->

                <link href="../public/css/html5tooltips.css" rel="stylesheet">
                <link href="../public/css/html5tooltips.animation.css" rel="stylesheet">



            <!--Contenido-->



                  <!-- Content Wrapper. Contains page content -->

                  <div class="content-wrapper">        

                    <!-- Main content -->

                    <section class="content">

                        <div class="row">

                          <div class="col-md-12">

                              <div class="">
                                <div class="box-header with-border">

                                      <h1 class="box-title" >LIQUIDACIÓN 
                                        <button class="btn btn-primary btn-sm" id="btnagregar" onclick="mostrarform(true)"> Agregar liquidación
                                        </button>
                                      </h1>


                <button class="btn btn-primary btn-sm" id="refrescartabla" onclick="refrescartabla()">
                Refrescar</button>

                                    <div class="box-tools pull-right">
                                    </div>
                                </div>

                                <!-- /.box-header -->
                                <!-- centro -->
                                <div class="panel-body table-responsive" id="listadoregistros">
                                  <h3>PRODUCTOS</h3>
                                    <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover" >
                                      <thead>
                                        <th>Opciones</th>
                                        <th>Tipo de item</th>
                                        <th>Nombre Item</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                      </thead>
                                      <tbody>                            
                                      </tbody>
                                      <tfoot>
                                      </tfoot>

                                    </table>
                                </div>



                    

                    <div class="panel-body" style="height: 400px;" id="formularioregistros">
                          <img src="../public/img/fondoliqui.jpg">
                       <form name="formulario" id="formulario" method="POST">
                              <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                      <label>Tipo de liquidación</label> 
                                      <input type="hidden" name="iditemli" id="iditemli" >
                            <select  class="" name="tservicio" id="tservicio" onchange="seltipoliq()" >
                                <option value="s">SERVICIO</option>
                                <option value="v">VUELO</option>
                         </select>
                                </div>



                      <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Cód. reserva/File:</label>
                              <input type="text" name="creserv" id="creserv" >
                             </div>

                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Dni/Ruc:</label>
                              <input type="text" name="dnir" id="dnir" onkeypress="agregarCliente(event)">
                              <div  class=""  id="suggestions2">
                                </div>
                              <input type="hidden" name="idcliente" id="idcliente">
                             </div>

                             <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                             <label>Datos pasajero:</label>
                              <input type="text" name="datoscli" id="datoscli" onfocus="focusTest(this)">
                              <div  class=""  id="suggestions">
                                </div>
                             </div>





               <div id="divvuelo" class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12" style="background-color: #b3e0e2;" hidden="true">

                             <div class="panel-body table-responsive" id="detallevuelo">
                                    <table id="detalles" class="table table-striped table-bordered table-condensed table-hover nowrap" >
                                      <thead>
                                        <th>...</th>
                                        <th>Aerolinea</th>
                                        <th>N° vuelo</th>
                                        <th>Fecha</th>
                                        <th>Destino</th>
                                        <th>H.salida</th>
                                        <th>H.retorno</th>
                                        <th>...</th>
                                      </thead>
                                      <tbody>                            
                                      </tbody>
                                      <tfoot>
                                      </tfoot>

                                    </table>
                                </div>
         

                     <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                             <label>Condiciones:</label>
                              <textarea class="" cols="40" rows="2"></textarea>
                             </div>

                             <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                             <label>Tarifa no reembolsable / No endosable / No transferible:</label>
                              <textarea class="" cols="40" rows="2"></textarea>
                             </div>


           



                      </div>







               <div id="divservicio" class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12" style="background-color: #bccdd3;" hidden="true">

                      <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                             <label>File:</label>
                              <input type="text" name="file" id="file">
                             </div>


                             <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                             <label>Programa:</label>
                              <textarea class="" cols="40" rows="4"></textarea>
                             </div>


                         <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                             <label>Observaciones:</label>
                              <textarea class="" cols="40" rows="2"></textarea>
                             </div>


                               <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                             <label>Restricciones de tarifas de alojamiento:</label>
                              <textarea class="" cols="40" rows="2"></textarea>
                             </div>


                      </div>



                      <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Item:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>

                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Précio:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Cantidad:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Total:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Entidad bancaria:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                             <label>Tipo:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                    <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                             <label>Titular de la cuenta:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                             <label>Número de la cuenta:</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>


                             <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                             <label>Código inter. (CCI):</label>
                              <input type="text" name="creserv" id="creserv">
                             </div>

               


                 


                                      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <button class="btn btn-primary btn-sm" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>

                                        <button class="btn btn-danger btn-sm" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>

                                      </div>

                                    </form>

                                </div>

                                <!--Fin centro -->

                              </div><!-- /.box -->

                          </div><!-- /.col -->

                      </div><!-- /.row -->

                  </section><!-- /.content -->



                </div><!-- /.content-wrapper -->

              <!--Fin-Contenido-->

            <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
      <script type="text/javascript" src="scripts/liquidacion.js"></script>

      <?php
}
ob_end_flush();
?>