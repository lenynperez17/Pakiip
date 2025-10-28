<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Contabilidad'] == 1) {

    ?>
           <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Envio de correos a clientes</h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">
                      <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                        <div class="table-responsive" id="listadoregistros">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    <th>Ruc</th>
                                    <th>Razón social</th>
                                    <th>Correo</th>
                                    <th>Comprobante</th>
                                    <th>Fecha/Hora</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>

                              </tr>
                            </tbody>
                          </table>

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
    <script type="text/javascript" src="scripts/enviocorreo.js"></script>
    <?php
}
ob_end_flush();
?>