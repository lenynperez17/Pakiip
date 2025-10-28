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


        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Empleados <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal" data-bs-target="#aregarnuevoempleado"> Agregar</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    <th>Empresa</th>
                                    <th>Nombre</th>
                                    <th>Apellidos</th>
                                    <th>Fecha ing.</th>
                                    <th>Ocupación</th>
                                    <th>Dni</th>
                                    <th>CUSPP</th>
                                    <th>Sueldo B</th>
                                    <th>Horas trabajo</th>
                                    <th>Tipo seguro</th>
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

            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->


            <div class="modal fade text-left" id="aregarnuevoempleado" tabindex="-1" role="dialog" aria-labelledby="aregarnuevoempleado" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="aregarnuevoempleado">Agrega nuevo personal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                    <input type="hidden" name="idempleado" id="idempleado">
                      <div class="row">
                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Nombres</label>
                          <input type="hidden" name="idalmacen" id="idalmacen">
                          <input type="text" name="nombresE" id="nombresE" onkeyup="mayus(this)" class="form-control" required="true" autofocus="true">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Apellidos</label>
                          <input type="text" name="apellidosE" id="apellidosE" onkeyup="mayus(this)" class="form-control" required="true">
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Fecha ingreso</label>
                          <input type="date" name="fechaingreso"  id="fechaingreso" class="form-control">
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Ocupación</label>
                          <input type="text" class="form-control" name="ocupacion" id="ocupacion"  required="true" onkeyup="mayus(this)"> 
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Tipo de remuneración</label>
                          <input type="text" class="form-control" name="tiporemuneracion" id="tiporemuneracion" required="true" onkeyup="mayus(this)">  
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">DNI (Presione Enter)</label>
                          <input type="text" class="form-control" name="dni" id="dni" required="true"> 
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Autogenerado Essalud</label>
                          <input type="text" class="form-control" name="autogenessa" id="autogenessa" required="true"> 
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">cusspp</label>
                          <input type="text" class="form-control" name="cusspp" id="cusspp" required="true"> 
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Sueldo bruto</label>
                          <input type="text" class="form-control" name="sueldoBruto" id="sueldoBruto" onkeypress="return NumCheck(event, this)" required="true">  
                        </div>

                
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Horas de trabajo</label>
                          <input type="text" class="form-control" name="horasT" id="horasT" onkeypress="return NumCheck(event, this)" required="true">  
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Asignación familiar</label>
                          <input type="text" class="form-control" name="asigFam" id="asigFam" onkeypress="return NumCheck(event, this)" required="true">  
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Trabajo nocturno</label>
                          <input type="text" class="form-control" name="trabNoct" id="trabNoct" onkeypress="return NumCheck(event, this)" required="true">
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Seguro</label>
                          <select  class="select-picker form-control " name="idtipoSeguro" id="idtipoSeguro" required data-live-search="true"> </select>
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Empresa</label>
                          <select  class="select-picker form-control"name="idempresab" id="idempresab" required data-live-search="true"  onchange="foco0()"></select>
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
    <script type="text/javascript" src="scripts/empleadoboleta.js"></script>
    <?php
}
ob_end_flush();
?>

