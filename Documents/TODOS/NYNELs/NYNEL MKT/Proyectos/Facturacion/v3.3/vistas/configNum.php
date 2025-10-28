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
        <!--Contenido-->

        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Series y numeración <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#agregarserieynumero"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    
                                    <th>Documento</th>
                                    <th>Serie</th>
                                    <th>Numero</th>
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

            <div class="modal fade text-left" id="agregarserieynumero" tabindex="-1" role="dialog" aria-labelledby="agregarserieynumero" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="agregarserieynumero">Agregar serie y numeración</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                        <div class="mb-3 col-lg-12">
                          <label for="recipient-name" class="col-form-label">Tipo documento:</label>
                          <input type="hidden" name="idnumeracion" id="idnumeracion">
                          <select  class="form-control select-picker" name="tipo_documento" id="tipo_documento" required>
                            <option value="01">FACTURA</option>
                            <option value="03">BOLETA</option>
                            <option value="07">NOTA DE CREDITO</option>
                            <option value="08">NOTA DE DEBITO</option>
                            <option value="09">GUIA DE REMISION</option>
                            <option value="12">TICKET DE MAQUINA REGISTRADORA</option>
                            <option value="13">DOCUMENTOS EMITIDOS POR BANCOS</option>
                            <option value="18">SUPERINTENDENCIA DE BANCA Y SEGUROS</option>
                            <option value="31">DOCUMENTOS EMITIDOS POR LAS AFP</option>
                            <option value="99">ORDEN DE SERVICIO</option>
                            <option value="50">NOTA DE PEDIDO</option>
                            <option value="20">COTIZACION</option>
                            <option value="30">DOCUMENTO DE COBRANZA</option>
                            <option value="90">BOLETA DE PAGO</option>


                          </select>
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Serie:</label>
                          <input type="text" class="form-control" name="serie" id="serie" maxlength="4" placeholder="Serie" required>
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Número:</label>
                          <input type="text" class="form-control" name="numero" id="numero" maxlength="50" placeholder="Número" required>
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
    <script type="text/javascript" src="scripts/configNum.js"></script>
    <?php
}
ob_end_flush();
?>