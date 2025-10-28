<?php
//Activamos el almacenamiento en el buffer
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
              <!-- Content Wrapper. Contains page content -->
              <div class="">        
                <!-- Main content -->
                <section class="">
                <div class="content-header">
                          <h1>Validar comprobantes</h1>
                      </div>
                    <div class="row" style="background: white;">

                    <div class="col-md-12">

                      <div class="card">

                          <div class="card-body">

                          <div class="row justify-content-center text-center">

  

        <div class="mb-3 col-lg-2">
            <label> Fecha 1: </label>
            <input type="date" name="fecha1" id="fecha1" class="form-control" onchange="listarcomprobantes()">


             </div>

              <div class="mb-3 col-lg-2">
            <label> Fecha 2: </label>
            <input type="date" name="fecha2" id="fecha2" class="form-control" onchange="listarcomprobantes()">
              </div>



         <div class="mb-3 col-lg-2">
            <label> Tipo de comprobante: </label>
            <select class="form-control" name="tipocomprobante" id="tipocomprobante" onchange="listarcomprobantes()">

              <option value="00">TODOS</option>
              <option value="01">FACTURA</option>
              <option value="03">BOLETA</option>
              <!-- <option value="07">NOTA DE CREDITO</option> -->
      
            </select>
    
          </div>

         <div class="mb-3 col-lg-2">
            <label> Estado: </label>
            <select class="form-control" name="sttcompro" id="sttcompro" onchange="listarcomprobantes()">
      
              <option value="01,03,04,05">TODOS</option>
              <option value="05">ACEPTADOS</option>
              <option value="04">FIRMADOS</option>
              <option value="01">EMITIDOS</option>
              <option value="03">ANULADOS</option>
            </select>    
          </div> 


          <div class="mb-3 col-lg-2" >
          <button class="btn btn-danger" onclick="listarcomprobantes()" style="top: 12px;">Consultar</button>
          </div>

        </div>
    

      
        <div class="panel-body" id="listadoregistros">
        <div class="table-responsive" id="listadoregistros">
            <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
            <thead style="text-align:center;">
                                    <!-- <th hidden >Impresión</th> -->
                                    <th>Fecha  </th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Factura</th>
                                    <th>Total</th>
                            
                                    <th>Estado</th>
                                    <th>Sunat</th>
            </thead>
            <tbody style="text-align:center;">
            </tbody>

        </table>
        </div>
    
                    
                          </div>


  

                                <!-- Modal -->
          <div class="modal fade" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 70% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                  <h4 class="modal-title">ARCHIVO XML DE FACTURA</h4>
                  <a name="bajaxml" id="bajaxml" download><span class="fa fa-font-pencil">DESCARGAR XML </span></a>
                </div>
        
            <iframe type="application/xml" name="modalxml" id="modalxml" border="0" frameborder="0"  width="100%" style="height: 800px;" marginwidth="1">
            </iframe>

                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                </div>        
              </div>
            </div>
          </div>  
          <!-- Fin modal -->


          <!-- Modal -->
          <div class="modal fade" id="modalPreview2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 100% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">COMPROBANTE</h4>
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
  

                            <!--Fin centro -->
                          </div><!-- /.box -->
                      </div><!-- /.col -->
                  </div><!-- /.row -->
              </section><!-- /.content -->
 
            </div><!-- /.content-wrapper -->
          <!--Fin-Contenido-->
 
   





        <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>


    <script type="text/javascript" src="scripts/consultacomprobantes.js"></script>


  <?php
}
ob_end_flush();
?>