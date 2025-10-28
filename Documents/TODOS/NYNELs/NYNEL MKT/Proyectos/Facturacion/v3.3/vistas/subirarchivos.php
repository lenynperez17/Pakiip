<?php
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['acceso'] == 1) {

    ?>
            <!DOCTYPE html>
            <html>

            <head>
              <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
              <link rel="stylesheet" href="subir.css">

            </head>



            <body>
              <div class="content-start transition">
                <!--Contenido-->
                <!-- Content Wrapper. Contains page content -->
                <div class="container-fluid dashboard">
                  <!-- Main content -->
                  <section class="content">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="box">

                          <div class="box-header with-border">
                            <h1 class="box-title">SUBIR ARCHIVOS CONTINGENCIA</h1>
                            <div class="box-tools pull-right">
                            </div>
                          </div>
                          <!-- centro -->
                          <div class="panel-body table-responsive" id="listadoregistros">

                            <!-- Formulario para subir los archivos -->
                            <div class="mensage"> Archivos Subidos Correctamente </div>

                            <form id="frmsubidajson" name="frmsubidajson">
                              <table align="center">
                                <tr>
                                  <td>Archivo</td>
                                  <td><input type="file" multiple="multiple" id="archivos"></td>
                                  <!-- Este es nuestro campo input File-->
                                </tr>
                                <tr>
                                  <td>&nbsp;</td>
                                  <td><button type="submit" id="enviar" class="btn btn-success">SUBIR JSON</button></td>
                                </tr>
                              </table>
                            </form>




                            <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                              <div class="panel-body table-responsive" id="listadoregistros">
                                <table id="tbllistado" name="tbllistado"
                                  class="table table-striped table-bordered table-condensed table-hover">
                                  <thead>
                                    <th>COMPROBANTE</th>
                                    <th>FECHA EMISIÓN</th>
                                    <th>VENDEDOR</th>
                                    <th>OPCIONES</th>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                              </div>
                            </div>

                            <!-- <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
  <br>
<input type="button" name="btncargar" value="CARGAR ARCHIVOS" class="btn btn-success" >
</div>


<div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
  <br>
<input type="button" name="btnsubir" value="SUBIR ARCHIVOS" class="btn btn-danger  " >
</div> -->

                            <!-- </form> -->






                          </div>

                          <!--Fin centro -->
                        </div><!-- /.box -->
                      </div><!-- /.col -->
                    </div><!-- /.row -->
                  </section><!-- /.content -->

                </div>

              </div>
            </body>

            </html>

            <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>

      <!-- <script type="text/javascript" src="scripts/subirarchivos.js"></script>  -->

      <script src="scripts/subirarchivos.js"></script>

      <?php
}
ob_end_flush();
?>