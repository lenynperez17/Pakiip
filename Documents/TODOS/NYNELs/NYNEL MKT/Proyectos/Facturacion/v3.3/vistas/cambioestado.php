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
                  <div class="content-start transition">        
                    <!-- Main content -->
                    <section class="container-fluid dashboard">
                        <div class="row">
                          <div class="col-md-12">
                              <div class="">

                                <div class="box-header with-border">
                                      <h1 class="box-title">CAMBIAR ESTADOS NOTAS DE PEDIDO</h1>
                                    <div class="box-tools pull-right">
                                    </div>
                                </div>


                                <!-- /.box-header -->
                                <!-- centro -->
                    


              <div class="panel-body" style="height: 400px;" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                          
                            <div class="form-group  col-lg-2 col-sm-12 col-md-12 col-xs-12">
                                        <a data-toggle="modal" href="#myModalcomprobantes">           
                                          <button id="btnAgregarArt" type="button" class="btn btn-primary btn-sm"> Buscar comprobantes</button>
                                        </a>
                            </div>

                            <div class="form-group col-lg-2 col-sm-12 col-md-12 col-xs-12">
                            <select class="" name="chestado" id="chestado">
                                          <option value="0">SELECCIONE ESTADO</option>
                                          <option value="5">CANCELADO</option>
                                          <option value="3" >ANULADO</option>
                                          <option value="1">EMITIDO</option>
                            </select>
                          </div>



                              <br>    
                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  </div>
                              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                      <div class="table-responsive">
                                        <table id="detalles" class="table">
                                  <thead>
                                        <th>Opciones</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Comprobante</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                      </thead>
                                          <tfoot>
                                          </tfoot>
                        
                                            <tbody>
                                            </tbody>
                                        </table>
                                      </div>
                          </div>

                                        <div class="form-group col-lg-8 col-md-12 col-sm-12 col-xs-12">
                                        </div>
                                      <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-6">
                                        <button class="btn btn-primary btn-sm" type="submit" id="btnGuardar" data-toggle="tooltip" title="Guardar boleta" ><i class="fa fa-save"></i> cambiar estado </button>
 
                                        <button id="btnCancelar" class="btn btn-danger btn-sm" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left" data-toggle="tooltip" title="Cancelar"></i> Cancelar</button>
                                      </div>

                                    </form>
                                </div>
                                <!--Fin centro -->
                              </div><!-- /.box -->
                          </div><!-- /.col -->
                      </div><!-- /.row -->
                  </section><!-- /.content -->
 
                </div><!-- /.content-wrapper -->
              <!--Fin-Contenido-->
 
  
             <!-- Modal -->
              <!-- Fin modal -->



              <!-- Modal -->
              <div class="modal fade" id="myModalcomprobantes" >
                <div class="modal-dialog"  style="background-color: rgba(0,0,0,0.7);">
                  <div class="modal-content"  >
                    <div class="table-responsive">
                      <table id="tblcomprobantes" class="table table-striped table-bordered  table-hover">
                        <thead>
                            <th>Agregar</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Comprobante</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        </tfoot>
                      </table>
                    </div>

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
      <script type="text/javascript" src="scripts/cambioestado.js"></script>


    <?php
}
ob_end_flush();
?>