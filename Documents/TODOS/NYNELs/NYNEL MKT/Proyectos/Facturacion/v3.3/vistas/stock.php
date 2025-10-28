<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
//Activamos el almacenamiento del Buffer
ob_start();


if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Logistica'] == 1) {

    ?>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <div class="content-header">
      <h1>Stock y Precios de Artículos</h1>
    </div>

    <div class="row">

      <div class="col-md-12">
        <div class="card">
          <div class="card-body">

            <div class="table-responsive">
              <div class="row">
                <div class="col-lg-2">
                  <select id="seleccionarAlmacenes" class="js-example-placeholder-single js-states form-control">
                  </select>
                </div>
              </div>

              <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                <thead>
                  <tr>
                    <th hidden scope="col">id</th>
                    <th hidden scope="col">idalmacen</th>
                    <th scope="col">Codigo</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Imagen</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Almacen</th>
                    <th style="background:red;" scope="col">Stock</th>
                    <th scope="col">C. Compra</th>
                    <th scope="col">P. Venta</th>
                    <th scope="col">P. Mayor</th>
                    <th scope="col">P. Distribuidor</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">T. U. vendidas</th>
                    <th scope="col">T. ventas</th>
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




    <?php
  } else {
    require 'noacceso.php';
  }


  require 'footer.php';
  ?>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script type="text/javascript" src="scripts/scriptstock.js"></script>
  <?php
}
ob_end_flush();
?>