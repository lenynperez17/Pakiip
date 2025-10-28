<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['almacen'] == 1) {
    ?>

        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-wrapper">        
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                      <div class="col-md-12">
                          <div class="box">

                            <div class="box-header with-border">
                                  <h1 class="box-title"> ORDEN DE SERVICIO    
                                    <button class="btn btn-info" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-newspaper-o"></i> Nuevo</button>
                                  </h1>
                                <div class="box-tools pull-right">
                                </div>
                            </div>

                            <!-- /.box-header -->
                            <!-- centro -->

                            <div class="panel-body table-responsive" id="listadoregistros">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
            </tbody>
          </table>
                                <table id="tbllistado" class="table table-striped table-bordered table-condensed  table-hover"  >
                                  <thead>
                                    <th >Opciones</th>
                                    <th>Fecha Emisión</th>
                                    <th>Número de orden</th>
                                    <th>Proveedor</th>
                                    <th >Forma de pago</th>
                                    <th >Forma de entrega</th>
                                    <th>Fecha de entrega</th>
                                    <th>Total</th>
                                  </thead>
                                  <tbody>                            
                                  </tbody>
                          
                                </table>
                            </div>


          <div class="panel-body"  id="formularioregistros">
            <form name="formulario" id="formulario" method="POST" >

            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" >
             <label>Serie</label>
             <select autofocus class=""  name="serie" id="serie"  onchange="incrementarNum();"  ></select>
                 <input type="hidden" name="idnumeracion" id="idnumeracion" >
                 <input type="hidden" name="hora" id="hora">
                 <input type="hidden" name="SerieReal" id="SerieReal" >
                 <input type="hidden" name="idempresa" id="idempresa" value="1">
            </div>

            <div class="form-group col-lg-1 col-md-6 col-sm-6 col-xs-12">
            <label>Número</label> <input type="text"  name="numero" id="numero" class="" required="true" >
            </div>

             <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
              <label>Proveedor:</label>
               <select autofocus  name="idproveedor" id="idproveedor"  class="form-control">
               </select>
              </div>
        <!--Campos para guardar comprobante Orden-->
            <input type="hidden" name="idorden" id="idorden" >

        <!--DETALLE-->
                    <div class="row">
                                  <div class="form-group  col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <label>Fecha emisión:</label>
                                    <input type="date"   name="fechaemision" id="fechaemision" required="true" >
                                  </div>

                                  <div class="form-group  col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <label>Forma de pago:</label>
                                    <select type="text"  class="" name="formapago" id="formapago" required="true">
                                      <option value="CONTADO"> CONTADO </option>
                                      <option value="CHEQUE"> CHEQUE </option>
                                    </select>
                                  </div>

                                  <div class="form-group  col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <input type="text"  class="" name="formaentrega" id="formaentrega"  placeholder="Forma de entrega" >
                                  </div>

                                  <div class="form-group  col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <input type="date"  class="" name="fechaentrega" id="fechaentrega" required="true" placeholder="Fecha de entrega" >
                                  </div>

                                  <div class="form-group  col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <input type="text"  class="" name="anotaciones" id="anotaciones"  placeholder="Anotaciones" >
                                  </div>

                     
                              <div class="form-group col-lg-4 col-sm-2 col-md-2 col-xs-12">
                                <input type="text" name="codigob" id="codigob" class="" onkeypress="agregarArticuloxCodigo(event)" onkeyup="mayus(this);" placeholder="Digite el código y presione ENTER">
                                </div>   

                                <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                    <a data-toggle="modal" href="#myModalArt">           
                                    <button id="btnAgregarArt" type="button" class="btn btn-primary" > 
                                       ARTÍCULO <span class="fa fa-shopping-cart"></span>
                                    </button>
                                    </a>  
                                </div>
                      </div> 
                          
                        
                        <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                                  <div class="table-responsive">
                                    <table id="detalles" class="table table-striped table-hover table-bordered" >
                              <thead style="background-color:#35770c; color: #fff; text-align: justify;">

                                            <th >Sup.</th>
                                            <th >Item</th>
                                            <th style="width: 400px;" >Descripción</th>
                                            <th >Cant.</th>
                                            <th >U.M.</th>
                                            <th >Precio unitario</th>
                                            <th >Precio total</th>
                                    
                              </thead>


                                      <tfoot style="vertical-align: center;">

                                        <!--SUBTOTAL-->
                                             <tr>
                                  <td><td></td><td></td><td></td><td></td>

                                            <th style="font-weight: bold;  background-color:#A5E393;">Subtotal </th>

                                            <th style="font-weight: bold; background-color:#A5E393;">
                                      
                                              <h4 id="subtotal">0.00</h4>

                                            </td>
                                            </tr> 

                                        <!--IGV-->
                                   <tr>
                                   <td><td></td><td></td><td></td><td></td>

                                            <th  style="font-weight: bold; vertical-align: center; background-color:#A5E393;">igv 18% </th>

                                            <th style="font-weight: bold; background-color:#A5E393; vertical-align: center;">

                                              <h4 id="igv_">0.00</h4>

                                            </th>
                                            </td>
                                            </tr> 
                                     <!--TOTAL-->       
                                  <tr>
                                    <td><td></td><td></td><td></td><td></td>
                                            <th style="font-weight: bold; vertical-align: center; background-color:#A5E393;">Total </th> <!--Datos de impuestos-->  <!--IGV-->
                                            <th style="font-weight: bold; background-color:#A5E393;">

                                              <h4 id="total" style="font-weight: bold; vertical-align: center; background-color:#A5E393;">0.00</h4>
                   
                            <input type="hidden" name="subtotal_factura" id="subtotal_factura"> 
                            <input type="hidden" name="total_igv" id="total_igv">
                            <input type="hidden" name="total_final" id="total_final">
                            <input type="hidden" name="pre_v_u" id="pre_v_u"></th><!--Datos de impuestos-->  <!--TOTAL-->
                                            </td>
                                            </tr>


                                        </tfoot>
                        
                                        <tbody>
                                        </tbody>
                                    </table>
                                  </div>

                                    </div>
                              </div>
                            </div>

                                    <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-6">
                                    </a><button class="btn btn-primary" type="submit" id="btnGuardar" data-toggle="tooltip" title="Guardar orden" ><i class="fa fa-save"></i> Guardar</button>
 
                                    <button id="btnCancelar" class="btn btn-danger" data-toggle="tooltip" title="Cancelar" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"> Cancelar</i></button>

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
          <div class="modal fade" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" style="width: 100% !important;">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">Seleccione un Artículo</h4>
                </div>
                <div class="table-responsive">
                  <table id="tblarticulos" class="table table-striped table-bordered table-condensed table-hover" style="font-size: 12px">
                    <thead>
                        <th>Opciones</th>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>U.M.</th>
                        <th>Costo compra</th>
                        <th>Stock</th>
                        <th>...</th>
                    </thead>
                    <tbody>
               
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Nombre</th>
                      <th>Código</th>
                      <th>U.M.</th>
                      <th>Costo compra</th>
                      <th>Stock</th>
                      <th>...</th>
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


    <script type="text/javascript" src="scripts/ordenservicio.js"></script>


  <?php
}
ob_end_flush();
?>