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
            <div class="content-start transition">
              <!-- Main content -->
              <section class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Créditos Pendientes</h1>
                </div>

                <div class="row" style="background:white;">

                  <div class="col-3 mb-3">
                    <label for="" class="form-label">Tipo de Comprobante</label>
                    <select class="form-control" name="tipo_comprobante" id="tipo_comprobante">
                      <option value="0" selected>Todos</option>
                      <option value="2">Boleta</option>
                      <option value="1">Factura</option>
                    </select>
                  </div>

                  <div class=" col-md-12">

                    <div class="">

                      <div class="table-responsive" id="listadoregistros">
                        <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                          <thead style="text-align:center;">
                            <th>Tipo</th>
                            <th hidden>idcomprobante</th>
                            <th hidden>idcliente</th>
                            <th>Cliente</th>
                            <th>Monto N Pago</th>
                            <th>F.V. Crédito</th>
                            <th>N° Cuotas</th>
                            <th>Monto Cuota</th>
                            <th>Total Pagado</th>
                            <th>Total Restante</th>
                            <th>Opciones</th>
                          </thead>
                          <tbody style="text-align:center;">
                            <!-- <th>Nombre y Apellido</th>
                    <th>1200</th>
                    <th>15/07/2023</th>
                    <th>5</th>
                    <th>240</th>
                    <th>480</th>
                    <th>720</th>
                    <th>
                      <button type="button" class="btn btn-info m-0" data-bs-toggle="modal" title="mostrar cuotas"
                        data-bs-target="#modalcuotas"><i class="fa-solid fa-sack-dollar"></i></button>
                    </th> -->
                          </tbody>

                        </table>
                      </div>

                    </div>

                  </div>
              </section>

              <div class="modal fade text-left" id="modalcuotas" tabindex="-1" role="dialog" aria-labelledby="modalcuotas"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title">Pago de crédito</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                      <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Pagar</span>
                      </button>
                    </div>

                  </div>
                </div>
              </div>





              <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>

        <script type="text/javascript" src="scripts/creditospendiente.js"></script>

        <?php
}
ob_end_flush();
?>