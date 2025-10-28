<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['inventarios'] == 1) {
    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-wrapper">        
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                      <div class="col-md-12">
                          <div class="">

                            <div class="box-header with-border">
                                  <h1 class="box-title"> ARTÍCULOS COMPRAS
                            
                                  </h1>
                                <div class="box-tools pull-right">
                                </div>
                            </div>


    
            <!-- Modal -->
          <div class="modal fade" id="myModalClave" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" style="width: 100% !important;">
              <div class="modal-content">
                <div class="modal-header">
          
                   
                  </div>

                  <div class="table-responsive">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover" WIDTH="100%">
                                  <thead>
                                    <th >Artículo</th>
                                    <th>Nombre</th>
                                    <th>Fecha compra</th>
                                    <th >Valor uni. $</th>
                                    <th>IGV uni. $</th>
                                    <th>Total $</th>
                                  </thead>
                                  <tbody>                            
                                  </tbody>
                                </table>
                              </div>
        
                <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>        
              </div>
            </div>
          </div>  
          <!-- Fin modal -->


                        

                    
                            <!--Fin centro -->
                          </div><!-- /.box -->
                      </div><!-- /.col -->
                  </div><!-- /.row -->
              </section><!-- /.content -->
 
            </div><!-- /.content-wrapper -->
          <!--Fin-Contenido-->
 
   

        <?php
  } else {
    //require 'noacceso.php';
  }
  require 'footer.php';
  ?>


    <script type="text/javascript" src="scripts/articuloscreditex.js"></script>


  <?php
}
ob_end_flush();
?>