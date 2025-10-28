<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if (isset($_SESSION['acceso']) && $_SESSION['acceso'] == 1 || !isset($_SESSION['acceso'])) {
    ?>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title"> Permiso <button class="btn btn-success" id="btnagregar"
                    onclick="mostrarform(true)"><i class="fa fa-plus-circle"></i> Agregar</button> </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <!-- /.box-header -->
              <!-- centro -->
              <div class="panel-body table-responsive" id="listadoregistros">

                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                  <thead>

                    <th>Nombre</th>

                  </thead>

                  <tbody>
                  </tbody>

                  <tfoot>

                    <th>Nombre</th>

                  </tfoot>

                </table>
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
  <script type="text/javascript" src="scripts/permiso.js"></script>
  <?php
}
ob_end_flush();
?>