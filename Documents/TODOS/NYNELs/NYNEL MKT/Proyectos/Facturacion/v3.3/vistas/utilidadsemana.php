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
                  <h1>Utilidad Semanal </h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                      <div class="row">
                  
                        <div class="col-lg-3">

                        <form name="formularioutilidad" id="formularioutilidad" method="POST">
                        <input type="hidden" name="idutilidad" id="idutilidad">
                          <div class="input-group mb-3">
                            <button class="btn btn-outline-secondary" disabled style="margin: 0 auto;">Fecha 1</button>
                            <input type="date" class="form-control" name="fecha1" id="fecha1">
                          </div> 

                          </div>
                  
                          <div class="col-lg-3">

                            <div class="input-group mb-3">
                              <button class="btn btn-outline-secondary" disabled style="margin: 0 auto;">Fecha 2</button>
                              <input type="date" class="form-control" name="fecha2" id="fecha2">
                            </div>                  
                    

                          </div>

                          <div class="col-lg-2">

                            <div class="input-group mb-3">
                              <button class="btn btn-outline-primary" type="submit" id="btnCalcular" onclick="calcularutilidad()" style="margin: 0 auto;" value="Calcular">Calcular Utilidad</button>
                              <input hidden type="date" class="form-control" name="fecha2" id="fecha2">
                            </div>                  
                    

                          </div>
                  
                        </form>


              

                        <div class="col-lg-3">
                        <form action="../reportes/reportemensualgastosing.php" method="POST" target="_blank">
                        
                   
                           <div class="input-group mb-3">
                            <button class="btn btn-danger" type="submit" id="btnreportemensual" style="margin: 0 auto;" value="Calcular">Reporte Mensual</button>
                            <select class="form-select" name="mes" id="mes">
                          
                            <option value="1">Enero</option>
                                                  <option value="2">Febrero</option>
                                                  <option value="3">Marzo</option>
                                                  <option value="4">Abril</option>
                                                  <option value="5">Mayo</option>
                                                  <option value="6">Junio</option>
                                                  <option value="7">Julio</option>
                                                  <option value="8">Agosto</option>
                                                  <option value="9">Septiembre</option>
                                                  <option value="10">Octubre</option>
                                                  <option value="11">Noviembre</option>
                                                  <option value="12">Diciembre</option>
                                                </select>                
                           </div>                  

                           <label id="mensaje" style="color: green;"> </label>
                          </div>

                          </form>
                      </div>

                      <div class="table-responsive"  id="formularioregistros">
                          <table id="tbllistadouti" class="table table-striped" style="width: 100% !important;">
                                  <thead>
                                    <th>Id</th>
                                    <th>Fecha 1</th>
                                    <th>Fecha 2 </th>
                                    <th>Gastos</th>
                                    <th>Ingresos</th>
                                     <th>Utilidad</th>
                                      <th>%</th>
                                      <th>Estado</th>
                                      <th>Eliminar/actualizar</th>
                                      <th>Reporte</th>
                            

                                  </thead>
                                  <tbody>                            
                                  </tbody>
                         
                                </table>
                          </div>
                      </div>
                    </div>
                  </div>



                </div><!-- /.row -->
        


     

          <!-- Modal -->
          <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 100% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">VISTA PREVIA REPORTE</h4>
                </div>
        
            <iframe name="modalCom" id="modalCom" border="0" frameborder="0"  width="100%" style="height: 800px;" marginwidth="1" 
            src="">
            </iframe>

                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>        
              </div>
            </div>
          </div>  
          <!-- Fin modal -->


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