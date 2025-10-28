<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['RRHH'] == 1) {
    ?>

            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

            <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Clientes <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal"
                      data-bs-target="#agregarclientes"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                <th>Opciones</th>
                                <th>Razon social</th>
                                <th>Doc.</th>
                                <th>Número</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado</th>
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

            <div class="modal fade text-left" id="agregarclientes" tabindex="-1" role="dialog" aria-labelledby="agregarclientes"
              aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="agregarclientes">Agrega tu cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <div class="row">
                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Tipo Documento:</label>
                          <select class="form-control" name="tipo_documento" id="tipo_documento" required onchange="focusnd()">
                            <option value="0"> S/D </option>
                            <option value="1"> DNI </option>
                            <option value="4"> CE </option>
                            <option value="6"> RUC </option>
                            <option value="7"> PASAPORTE </option>
                            <option value="A"> CED </option>
                          </select>
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label" id="l_tipo_documento">Número de documento:</label>
                          <input type="text" class="form-control" name="numero_documento" id="numero_documento" maxlength="11"
                            placeholder="Documento" onkeypress="return NumCheckrz(event, this)">
                          <!-- onblur="validarCliente();" -->
                          <input type="hidden" name="ndocu" id="ndocu">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="recipient-name" class="col-form-label">Nombres:</label>
                          <input type="hidden" name="idpersona" id="idpersona">
                          <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
                          <input type="text" class="form-control" name="nombres" id="nombres" maxlength="100" placeholder="Nombres"
                            required="true" onkeyup="mayus(this);" onkeypress="focusnombre(event)">
                        </div>
                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Apellidos:</label>
                          <input type="text" class="form-control" name="apellidos" id="apellidos" maxlength="100"
                            placeholder="Apellidos " required onkeyup="mayus(this);" onkeypress="focusapellido(event)">
                        </div>


                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Razón social:</label>
                          <input type="text" class="form-control" name="razon_social" id="razon_social" placeholder="Razón social"
                            required onkeyup="mayus(this);" onkeypress="focusrz(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Nombre comercial:</label>
                          <input type="text" class="form-control" name="nombre_comercial" id="nombre_comercial"
                            placeholder="Nombre comercial" required onkeyup="mayus(this);" onkeypress="focusnc(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Domicilio fiscal:</label>
                          <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal"
                            placeholder="Domicilio fiscal" required onkeyup="mayus(this);" onkeypress="focusdf(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Departamento:</label>
                          <!-- <select class="form-control" name="iddepartamento" id="iddepartamento" data-live-search="true"
                    onchange="llenarCiudad()" onkeypress="focusdep(event)"> -->
                          </select>
                          <input type="text" class="form-control" name="iddepartamento" id="iddepartamento" onkeypress="focusdep(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Ciudad:</label>
                          <!-- <select class="form-control" name="idciudad" id="idciudad" onchange="llenarDistrito()"
                    data-live-search="true" onkeypress="focusciu(event)">
                  </select> -->
                          <input type="text" class="form-control" name="idciudad" id="idciudad" onkeypress="focusciu(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Distrito:</label>
                          <!-- <select class="form-control" name="iddistrito" id="iddistrito" data-live-search="true"
                    onkeypress="focusdist(event)" onchange="focusoctel1()"> -->
                          </select>
                          <input type="text" class="form-control" name="iddistrito" id="iddistrito" onkeypress="focusdist(event)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Teléfono 1:</label>
                          <input type="text" class="form-control" name="telefono1" id="telefono1" maxlength="15"
                            placeholder="Teléfono 1" onkeypress="return NumChecktel1(event, this)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Teléfono 2:</label>
                          <input type="text" class="form-control" name="telefono2" id="telefono2" maxlength="15"
                            placeholder="Teléfono 2" onkeypress="return NumChecktel2(event, this)">
                        </div>

                        <div class="mb-3 col-lg-3">
                          <label for="message-text" class="col-form-label">Email:</label>
                          <input type="text" class="form-control" name="email" id="email" maxlength="50" placeholder="Email"
                            required="true" onkeypress="focusmail(event)">
                        </div>

                      </div>


                  </div>
                  <div class="modal-footer">
                    <button onclick="cancelarform()" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cancelar</span>
                    </button>
                    <button id="btnGuardar" type="submit" class="btn btn-primary ml-1">
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
      <script type="text/javascript" src="scripts/cliente.js"></script>
    <?php
}
ob_end_flush();
?>