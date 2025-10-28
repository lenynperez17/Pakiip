<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Configuracion'] == 1) {

    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Configurar notificaciones <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#crearnotificaciones"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    
                                    <th>Nombre</th>
                                    <th>Fecha creación</th>
                                    <th>Fecha de aviso</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Opciones</th>
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

            <div class="modal fade text-left" id="crearnotificaciones" tabindex="-1" role="dialog" aria-labelledby="crearnotificaciones" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="crearnotificaciones">Crea tu notificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Fecha de creación</label>
                          <input type="hidden" name="idalmacen" id="idalmacen">
                          <input type="date" class="form-control" name="fechacreacion" id="fechacreacion">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Fecha de aviso</label>
                          <input type="date" class="form-control" name="fechaaviso" id="fechaaviso">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Cliente</label>
                          <input type="hidden" name="idcliente" id="idcliente">
                          <select class="form-control select-pickert"  data-live-search="true" name="cliente" id="cliente" ></select>
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Nombre de notificación</label>
                          <input type="hidden" name="idnotificacion" id="idnotificacion">
                          <input type="text" class="form-control" name="nombrenotificacion" id="nombrenotificacion">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Contador</label>
                          <input type="text" class="form-control" name="contador" id="contador">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Poner continuo</label>
                          <input class="form-control" type="hidden" name="selconti" id="selconti">
                          <input class="form-control" type="checkbox" name="continuo" id="continuo" onclick="continuoNoti()"> SI
                        </div>
           
                      </div>

                  </div>
                  <div class="modal-footer">
                    <button onclick="cancelarform()" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cancelar</span>
                    </button>
                    <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                      <i class="bx bx-check d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Crear notificación</span>
                    </button>
                  </div>
                  </form>
                </div>
              </div>
            </div>


        <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
    <script type="text/javascript" src="scripts/notificaciones.js"></script>
    <?php
}
ob_end_flush();
?>