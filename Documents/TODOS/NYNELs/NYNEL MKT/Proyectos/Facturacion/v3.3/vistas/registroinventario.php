<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Logistica'] == 1) {

    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Inventario anual <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#agregarinventario"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    
                                    <th>AÑO</th>
                                    <th>CODIGO</th>
                                    <th>DENOMINACION</th>
                                    <th>COSTO INICIAL</th>
                                    <th>SALDO INICIAL</th>
                                    <th>VALOR INICIAL</th>
                                    <th>COMPRAS</th>
                                    <th>VENTAS</th>
                                    <th>SALFO FINAL</th>
                                    <th>COSTO</th>
                                    <th>VALOR FINAL</th>
                                    <th>OPCIONES</th>
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


            <div class="modal fade text-left" id="agregarinventario" tabindex="-1" role="dialog" aria-labelledby="agregarinventario" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="agregarinventario">Agregar inventario anual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                      <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Año:</label>
                          <input type="hidden" name="idregistro" id="idregistro">
                          <input type="ano" id="ano" name="ano" class="form-control">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Código:</label>
                          <input type="text" class="form-control" name="codigo" id="codigo">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="recipient-name" class="col-form-label">Descripción:</label>
                          <input type="text" class="form-control" name="denominacion" id="denominacion">
                        </div>
                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Costo inicial:</label>
                          <input type="text" class="form-control" name="costoinicial" id="costoinicial">
                        </div>
               

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Saldo inicial:</label>
                          <input type="text" class="form-control" name="saldoinicial" id="saldoinicial">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Valor inicial:</label>
                          <input type="text" class="form-control" name="valorinicial" id="valorinicial">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Compras:</label>
                          <input type="text" class="form-control" name="compras" id="compras">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Ventas:</label>
                          <input type="text" class="form-control" name="ventas" id="ventas">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Saldo Final:</label>
                          <input type="text" class="form-control" name="saldofinal" id="saldofinal">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Costo:</label>
                          <input type="text" class="form-control" name="costo" id="costo">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Valor final:</label>
                          <input type="text" class="form-control" name="valorfinal" id="valorfinal">
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
    <script type="text/javascript" src="scripts/registroinventario.js"></script>
    <?php
}
ob_end_flush();
?>