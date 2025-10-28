<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['compras'] == 1) {

    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-wrapper">        
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                      <div class="col-md-12">
                          <div class="box">
                            <div class="box-header with-border">
                                  <h1 class="box-title">INSUMOS DIARIOS</h1>
                              <div class="box-tools pull-right">
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <!-- centro -->
                   

                   
                            <div class="panel-body" style="height: 300px;" id="formularioregistros">
                                <form name="formulario" id="formulario" method="POST">
                                  <input type="hidden" name="idinsumo" id="idinsumo">

                                  <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                     <label>Fecha de registro:</label>
                                    <input type="date" name="fecharegistro" id="fecharegistro" >
                    
                                     <label>Categoría insumo:</label>
                                    <div class="input-group mb-3">
                                  <select  class=" select-picker" name="categoriai" id="categoriai" required data-live-search="true"  onchange="foco0()">
                                  </select>
                                  <div class="input-group-append">
                                    <a data-toggle="modal" href="#ModalNcategoria" class="btn btn-success">
                                      <i class="fa fa-plus"></i></a>
                                  </div>
                                </div>


                                    <label>Descripción:</label>
                                    <input type="text" name="descripcion" id="descripcion" onkeyup="mayus(this)"  onkeypress="foco1(event)" required onfocus="true">
        
                      
                                    <label>Precio:</label>
                                    <input type="text" class="" name="precio" id="precio" placeholder="0.00"  
                                    onkeypress="return NumCheck(event, this)" required> 
                            
                                        <label id="mensaje" style="color: green;"> </label>
                                      </div>

                              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="panel-body table-responsive" id="listadoregistros">
                                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover" style="font-size: 12px">
                                  <thead>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Eliminar</th>
                                  </thead>
                                  <tbody>                            
                                  </tbody>
                         
                                </table>
                            </div>
                                       </div>
                     
                      <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                          <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                          <button class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i>  Cancelar</button>
                        </div>

                                </form>

                      <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <form action="../reportes/reportegastosagrupado.php" method="POST" target="_blank">
                          <button class="btn btn-primary" type="submit" id="btnReportelistado"><i class="fa fa-new"></i>Reporte gastos agrupado</button>
                  

                          </form>
                        </div>


                            </div>
                            <!--Fin centro -->
                          </div><!-- /.box -->
                      </div><!-- /.col -->
                  </div><!-- /.row -->
              </section><!-- /.content -->

            </div><!-- /.content-wrapper -->
          <!--Fin-Contenido-->




           <!-- Modal -->
          <div class="modal fade" id="ModalNcategoria">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">Nueva categoría</h4>
                </div>
                <div class="modal-body">
                   <form name="formnewcate" id="formnewcate" method="POST">
                    <input type="hidden" id="idcategoria">
                      <div class="form-group col-lg-8 col-md-4 col-sm-6 col-xs-12">
                                  <label>Descripción: </label>

                                   <div class="input-group mb-3">
                                  <input type="text" name="descripcioncate" id="descripcioncate" autofocus="true"
                                  onkeyup="mayus(this);">
                                  <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit" id="btnGuardarncate">
                                      <i class="fa fa-save"></i> 
                                    </button>
                                  </div>
                                  </div>

                                  </div>
                       
              
                            
                 
                                </form>
                      </div>
                  <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cerrar</button>  
                  </div> 
              </div> <!-- Fin content -->
            </div> <!-- Fin dialog -->
          </div>  <!-- Fin modal -->
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