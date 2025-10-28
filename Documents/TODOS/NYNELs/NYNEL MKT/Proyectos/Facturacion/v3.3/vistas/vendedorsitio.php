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
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <!--Contenido-->

        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Lista de vendedores  <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#agregarvendedor"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                <th scope="col">Opciones</th>
                                <th scope="col">Nombre</th>
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

        <div class="modal fade text-left" id="agregarvendedor" tabindex="-1" role="dialog" aria-labelledby="agregarvendedor" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="agregarvendedor">Añade nuevo vendedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Nombre:</label>
                          <input type="text" class="form-control" name="nombre" id="nombre" maxlength="50" placeholder="Nombre" onkeyup="mayus(this);" required autofocus>
                          <input type="hidden" name="id" id="id">

                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Empresa:</label>
                          <select name="empresa" id="empresa" class="form-control"></select>
                          <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
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
    <script type="text/javascript" src="scripts/vendedorsitio.js"></script>
    <?php
}
ob_end_flush();
?>