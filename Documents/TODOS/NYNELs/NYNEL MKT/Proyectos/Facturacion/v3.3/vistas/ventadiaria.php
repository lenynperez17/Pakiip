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
            
              <div class="content-header">
                <h1>Ingreso Diario</h1>
                <p>Guarda tus ingresos x dia</p>
              </div>
            
                    <div class="row">

                        <div class="card">
                            <div class="card-body">
                            <form action="../reportes/reporteingresosdiario.php" method="POST" target="_blank">
                          <button class="btn btn-primary" type="submit" id="btnReportelistado"><i class="fa fa-paper"></i>Reporte ingresos agrupado</button>
                  

                          </form>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3">
                          <div class="card">
                    
                          <form name="formulario" id="formulario" method="POST">
                           <input type="hidden" name="idventa" id="idventa">
                              <div class="card-body">
                                <div class="mb-3">
                                  <label>Fecha de registro ingreso</label>
                                  <input type="date" name="fecharegistroingreso" class="form-control" id="fecharegistroingreso" >
                                </div>
                                <div class="mb-3">
                                  <label>Tipo de ingreso</label>
                                  <select  class="form-select" name="tipo" id="tipo" required data-live-search="true"  onchange="foco0()">
                                  <option value='efectivod'>EFECTIVO DIA</option>
                                  <option value='efectivon'>EFECTIVO NOCHE</option>
                                  <option value='efectivot'>EFECTIVO TOTAL</option>
                                  <option value='tarjeta'>TARJETA</option>
                                  <option value='ingresop'>INGRESO PERSONAL</option>
                                  </select>
                                </div>
                                <div class="mb-3">
                                  <label>Total</label>
                                  <input type="text" name="total" id="total" class="form-control" onkeypress="return NumCheck(event, this)"  onchange="foco1()">
                                  <label id="mensaje" style="color: green;"> </label>
                                </div>
                       
                              </div>
                              <div class="card-footer text-right">
                                <button type="submit" id="btnGuardar" class="btn btn-primary">Guardar</button>
                                <button type="reset" class="btn btn-info">Limpiar</button>
                              </div>
                   
                        </div>
                    </div>

                    <div class="col-12 col-md-9 col-lg-9">
                        <div class="card">
                        <div class="card-body">
                        <div class="table-responsive"  id="listadoregistros">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    <th>Fecha de ingreso</th>
                                    <th>Tipo</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>

                              </tr>
                            </tbody>
                          </table>

                        </div>
                    
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
    <script type="text/javascript" src="scripts/ventadiaria.js"></script>
    <?php
}
ob_end_flush();
?>