<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['acceso'] == 1) {

    ?>
        <!-- <link rel="stylesheet" href="carga.css" > -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <!-- <div class="loader"></div> -->
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->


        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Manipulación de información delicada, tome sus precauciones</h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table class="table table-striped" style="width: 100% !important;">
                          <tr><td>Copia de seguridad de base de datos</td>
                                      <td><input type="checkbox" name="chk1" id="chk1" onclick="selectopt()"  data-toggle="tooltip" title="Activar copia de base de datos"></td>
                                      <td> <input type="radio" name="tipoi" id="tipoi1" hidden="true" onclick="selectinstalacion()" value="local">Windows / 
                                      <input type="radio" name="tipoi" id="tipoi2" hidden="true" onclick="selectinstalacion()" value="mac">MAC / 
                                         <input type="radio" name="tipoi" id="tipoi3" hidden="true" onclick="selectinstalacion()" value="web">Web <input type="text" name="rutainsta" id="rutainsta" hidden="true"> </td>
                                      <input type="hidden" name="tipodato" id="tipodato">
                                </tr>
                                <tr><td>Reiniciar base de datos</td>
                                      <td><input type="checkbox" name="chk2" id="chk2" onclick="selectopt2()"  data-toggle="tooltip" title="Reiniciar base de datos"></td>
                                </tr>
                            <tbody>
                    
                            </tbody>
                          </table>
                          <button type="button" class="btn btn-primary" value="" onclick="copiabd()">Aceptar</button>
                        </div>
                      </div>
                    </div>
                  </div>



                </div><!-- /.row -->


              </div><!-- End Container-->
            </div><!-- End Content-->



        <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
    <script type="text/javascript" src="scripts/limpiarbd.js"></script>
    <?php
}
ob_end_flush();
?>