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
                  <h1>Configuración catalogo #7 sunat tipo de afectación IGV <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#agregarcatalogo7sunat"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    
                                    <th>Código SUNAT</th>
                                    <th>Descripción</th>
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

            <div class="modal fade text-left" id="agregarcatalogo7sunat" tabindex="-1" role="dialog" aria-labelledby="agregarcatalogo7sunat" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="agregarcatalogo7sunat">Añade nuevos documentos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                        <div hidden class="mb-3 col-lg-6">
                          <label  for="recipient-name" class="col-form-label">Tipo documento:</label>
                          <input type="hidden" name="id" id="id">
                        </div>
                        <div class="mb-3 col-lg-12">
                          <label for="message-text" class="col-form-label">Código SUNAT:</label>
                          <input type="text" class="form-control" name="codigo" id="codigo" maxlength="4" placeholder="Código " required>
                        </div>
                        <div class="mb-3 col-lg-12">
                          <label for="message-text" class="col-form-label">Descripción de afectación:</label>
                          <input type="text" class="form-control" name="descripcion" id="descripcion" maxlength="100" placeholder="Descripción" required>
                        </div>
                        <!-- <div class="mb-3 col-lg-12">
                  <label for="message-text" class="col-form-label">Abreviatura:</label>
                  <input type="text" class="form-control" name="abrev" id="abrev" maxlength="20" placeholder="" required>
                </div> -->
                      </div>

                  </div>
                  <div class="modal-footer">
                    <button onclick="cancelarform()" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cancelar</span>
                    </button>
                    <button id="btnGuardar" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                      <i class="bx bx-check d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Agregar</span>
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
    <script type="text/javascript" src="scripts/catalogo7.js"></script>
    <?php
}
ob_end_flush();
?>