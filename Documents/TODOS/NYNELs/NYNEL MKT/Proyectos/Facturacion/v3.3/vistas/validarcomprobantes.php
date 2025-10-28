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
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="">

              <div class="box-header with-border">
                <h1 class="box-title"> VALIDAR COMPROBANTES DEL MES
                </h1>
              </div>




              <input type="hidden" name="estadoC" id="estadoC" value="<?php echo $_GET['estadoC']; ?>">
              <input type="hidden" name="idcomprobante" id="idcomprobante">
              <input type="hidden" name="tipo_documento_07" id="tipo_documento_07">

              <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()"><i class="fa fa-repeat"></i>
                  Refrescar</button>
              </div>

              <div class="col-lg-12 col-md-4 col-sm-6 col-xs-12">
                <div class="table-responsive">
                  <table id="tbllistadoEstado" class="">
                    <thead>
                      <th>Opciones</th>
                      <th>Fecha</th>
                      <th>Cliente</th>
                      <th>Vendedor</th>
                      <th>Factura</th>
                      <th>Total</th>
                      <th>Estado</th>
                      <!-- <th>Opciones</th> -->
                    </thead>
                    <tbody>
                    </tbody>

                  </table>
                </div>
              </div>


              <!--Fin centro -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->
    <!--Fin-Contenido-->



    <!-- Modal -->
    <div class="modal fade" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" style="width: 70% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">COMPROBANTE</h4>
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


  <script type="text/javascript" src="scripts/validarcomprobantes.js"></script>


  <?php
}
ob_end_flush();
?>