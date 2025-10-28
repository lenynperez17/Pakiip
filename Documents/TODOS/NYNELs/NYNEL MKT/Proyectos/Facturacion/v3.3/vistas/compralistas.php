<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Logistica'] == 1) {
    ?>


    <div class="content-header">
      <h1>Lista de compras <a class="btn btn-success btn-sm" href="compra.php">Agregar Compra</a></h1>
    </div>

    <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">

            <div class="table-responsive">
              <table id="tbllistado" class="table text-nowrap table-striped table-hover" style="width: 100% !important;">
                <thead>
                  <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Proveedor</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Número</th>
                    <th scope="col" style="background-color: #A7FF64;">Total</th>
                    <th>Estado</th>
                    <th scope="col">Opciones</th>
                  </tr>
                </thead>
                <tbody>
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
  <script type="text/javascript" src="scripts/compra.js"></script>
  <?php


}
ob_end_flush();
?>