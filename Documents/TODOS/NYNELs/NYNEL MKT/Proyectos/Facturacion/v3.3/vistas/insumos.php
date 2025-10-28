<?php
//Activamos el almacenamiento del Buffer
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
  
                <div class="content-header">
                  <h1>Gastos e Ingresos <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#agregarmaspagos"> Agregar mas pagos</button></h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                      <div class="row">
                  
                        <div class="col-lg-4">
                        <form action="../reportes/reportegastosagrupado.php" method="POST" target="_blank">
                        <div class="input-group mb-5">
                          <button class="btn btn-outline-secondary" type="submit" id="btnReportelistado" style="margin: 0 auto;">Reporte de Egreso</button>
                          <input type="date" class="form-control" name="fechagasto" id="fechagasto">
                        </div>
                        </form>  

                        </div>
                
                        <div class="col-lg-4">

                          <form action="../reportes/reporteingresosagrupado.php" method="POST" target="_blank">
                          <div class="input-group mb-5">
                            <button class="btn btn-outline-secondary" type="submit" id="btnReportelistado" style="margin: 0 auto;">Reporte de Ingreso</button>
                            <input type="date" class="form-control" placeholder="" name="fechaingreso" id="fechaingreso">
                          </div>                  
                          </form>

                        </div>
                  

                      </div>

                             
                      <div class="table-responsive" id="listadoregistros">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                  
                                  <thead>
                                    <th>Id</th>
                                    <th>Tipo</th>
                                    <th>Tipo de Documento</th>
                                    <th>Número</th>
                                    <th>Nombre o Razón</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Egreso</th>
                                    <th>Ingreso</th>
                                    <th>Eliminar</th>
                                  </thead>
                                  <tbody>                            
                                  </tbody>
                         
                                </table>
                          </div>
                      </div>
                    </div>
                  </div>



                </div><!-- /.row -->
        


          <div class="modal fade text-left" id="agregarmaspagos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">Añade nuevos ingresos/egresos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                    <input type="hidden" name="idinsumo" id="idinsumo">
                      <div class="row">
                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Fecha de registro:</label>
                          <input type="date" name="fecharegistro" id="fecharegistro" class="form-control" onchange="listar();">
                        </div>
                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Tipo:</label>
                          <select  class="form-select" name="tipodato" id="tipodato"  onchange="foco0()">
                                    <option value='ingreso' selected>INGRESO</option>
                                    <option value='gasto'>EGRESO</option>
                          </select>
                        </div>
                        <div class="mb-3 col-lg-12">
                          <label for="recipient-name" class="col-form-label">Naturaleza del pago:</label>
                          <div class="input-group mb-1">
                  
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#ModalNcategoria" style="margin: 0 auto;">Agregar</button>
                            <select class="form-select" name="categoriai" id="categoriai" required data-live-search="true" onchange="foco0()"></select>
                          </div>
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Documento:</label>
                          <select  class="form-select" name="documnIDE" id="documnIDE">
                                    <option value='DNI' selected>DNI</option>
                                    <option value='RUC'>RUC</option>
                                    <option value='LICENCIA'>LICENCIA</option>
                          </select>
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Número:</label>
                          <input type="text" name="numDOCIDE" id="numDOCIDE" class="form-control">
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="message-text" class="col-form-label">Acredor:</label>
                          <select  class="form-select" name="acredor" id="acredor">
                                    <!-- <option value='fido' selected>Usuarios</option> -->
                          </select>
                        </div>

                        <div class="mb-3 col-lg-6">
                          <label for="recipient-name" class="col-form-label">Monto:</label>
                          <input type="text" class="form-control" name="monto" id="monto" placeholder="0.00" onkeypress="return NumCheck(event, this)" required="true"> 
                          <label id="mensaje" style="color: green;"> </label>
                        </div>

                        <div class="mb-3 col-lg-12">
                          <label for="recipient-name" class="col-form-label">Detalles | Glosa:</label>
                          <input type="text" class="form-control" name="descripcion" id="descripcion" onkeyup="mayus(this)"  onkeypress="foco1(event)" required="true" onfocus="true">
                        </div>


                      </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cancelar</span>
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary ml-1">
                      <i class="bx bx-check d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Guardar</span>
                    </button>
                  </div>
                  </form>
                </div>
              </div>
            </div>




 
          <div class="modal fade text-left" id="ModalNcategoria" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">Agregar movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formnewcate" id="formnewcate" method="POST">
                      <div class="row">
                        <div class="mb-3 col-lg-12">
                          <label for="descripcion-name" class="col-form-label">Descripción:</label>
                          <input type="hidden" name="idalmacen" id="idalmacen">
                          <input type="text" class="form-control" name="descripcioncate" id="descripcioncate" autofocus="true" onkeyup="mayus(this);">
                        </div>
                
                      </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-target="#agregarmaspagos" data-bs-toggle="modal" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cancelar</span>
                    </button>
                    <button id="btnGuardarncate" type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
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
    <script type="text/javascript" src="scripts/insumos.js"></script>
    <?php
}
ob_end_flush();
?>