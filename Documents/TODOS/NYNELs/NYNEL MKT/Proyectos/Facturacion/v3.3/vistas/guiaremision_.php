<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

//$ms=$_POST['ms'];

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['almacen'] == 1) {
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
                                  <h1 class="box-title"> GUÍA DE REMISIÓN    
                                    <button class="btn btn-success" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-newspaper-o"></i> NUEVO</button>
                                  </h1>
                                <div class="box-tools pull-right">
                                </div>
                            </div>


                            <!-- /.box-header -->
                            <!-- centro -->
                            <div class="panel-body table-responsive" id="listadoregistros">
                                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                                  <thead>
                                    <th>OPCIONES</th>
                                    <th>FECHA E.</th>
                                    <th>NÚMERO</th>
                                    <th>DESTINATARIO</th>
                                    <th>COMPROBANTE</th>
                                    <th>ESTADO</th>
                                  </thead>
                                  <tbody>                            
                                  </tbody>
                                  <tfoot>
                                   <th>OPCIONES</th>
                                    <th>FECHA E.</th>
                                    <th>NÚMERO</th>
                                    <th>DESTINATARIO</th>
                                    <th>COMPROBANTE</th>
                                    <th>ESTADO</th>
                                  </tfoot>
                                </table>
                            </div>

        <div  class="panel-body" style="height: 400px;" id="formularioregistros">
    
          <form  name="formulario" id="formulario" method="POST">

    
        <div   class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
         <label >SERIE</label>
          <select class="form-control" name="serie" id="serie" onchange="incrementarNum()">
          </select>
             <input type="hidden" name="idnumeracion" id="idnumeracion" >
             <input type="hidden" name="SerieReal" id="SerieReal" >
        </div>


                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                    <label>NÚMERO</label>
                  <input type="text" name="numero_guia" id="numero_guia" class="form-control" required="true" >

                  </div>

        <!--Campos para guardar comprobante Factura-->
            <input type="hidden" name="idcomprobante" id="idcomprobante" >
            <input type="hidden" name="idguia" id="idguia" >
            <input type="hidden" name="tipo_documento" id="tipo_documento" value="01">
            <input type="hidden" name="numeracion" id="numeracion" value="">
            <input type="hidden" name="ocompra" id="ocompra" value="">

            <!--Datos del cliente-->
            <input type="hidden" name="idpersona" id="idpersona" required="true">
            <input type="hidden" name="tipo_documento_cliente" id="tipo_documento_cliente">
            <!--Datos del cliente-->


                        
                                  <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <label>Fecha operación:</label>
                                    <input type="date"  style="font-size: 12pt;"  class="form-control" name="fecha_emision" id="fecha_emision" required="true" >
                                  </div>

                        
                                <div class="form-group col-lg-5 col-md-4 col-sm-6 col-xs-12">
                                     <label>Punto de partida:</label> 
                                     <input type="text" class="form-control" name="ppartida" id="ppartida" maxlength="100" placeholder=""  required="true" value="PROLOG. ÚNANUE N° 1418 - LA VICTORIA" onkeypress="mayus(this);" >
                                 </div>


                      
                                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                    <label>Comprobante:</label>
                                    <a data-toggle="modal" href="#myModalComprobante">           
                                      <button id="btnAgregarComp" type="button" class="btn btn-primary"> <span class="fa fa-plus"></span></button>
                                    </a>
                                  </div>


                                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                    <label>Nro. Comprobante:</label> <!--Datos del cliente-->
                                    <input type="text" class="form-control" name="numero_comprobante" id="numero_comprobante" maxlength="8" placeholder=""  width="50x" required="true">
                                  </div>


                                 <div class="form-group col-lg-8 col-md-4 col-sm-6 col-xs-12">
                                     <label>Punto de llegada:</label> 
                                     <input type="text" class="form-control" name="pllegada" id="pllegada" maxlength="100" placeholder=""  required="true" onkeypress="mayus(this);"  >
                                 </div>

                                 <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                     <label>Destinatario:</label> 
                                     <input type="text" class="form-control" name="destinatario" id="destinatario" maxlength="100" placeholder=""  required="true" onkeypress="mayus(this);"  >
                                 </div>

                                 <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                     <label>Número de RUC:</label> 
                                     <input type="text" class="form-control" name="nruc" id="nruc" maxlength="11" placeholder=""  required="true" onkeypress=" return NumCheck(event,this)" >
                                 </div>

                         


                              <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                              <label>Motivo :</label><br> 
                            
                                      Venta: &nbsp;&nbsp;<input type="checkbox" name="motivo" id="motivo" value="venta" class="form-group" >&nbsp;&nbsp;&nbsp;&nbsp;
                              
                                      Consignación: &nbsp;&nbsp; <input type="checkbox" name="motivo" id="motivo"  value="consignacion"> &nbsp;&nbsp;&nbsp;&nbsp;
                        
                                      Transformación:&nbsp;&nbsp;<input type="checkbox" name="motivo" id="motivo"  value="transformacion">&nbsp;&nbsp;&nbsp;&nbsp;
                            
                                      Venta Sujeta:&nbsp;&nbsp; <input type="checkbox" name="motivo" id="motivo" value="venta sujeta">&nbsp;&nbsp;&nbsp;&nbsp;
                             
                                      Otros: &nbsp;&nbsp;<input type="checkbox" name="motivo" id="motivo" value="otros" >&nbsp;&nbsp;&nbsp;&nbsp;
                              
                                      Devolución:&nbsp;&nbsp; <input type="checkbox" name="motivo" id="motivo" value="devolucion"  >&nbsp;&nbsp;&nbsp;&nbsp;
                             
                                      Recojo de bienes: &nbsp;&nbsp;<input type="checkbox" name="motivo" id="motivo" value="rocojo de bienes"  >&nbsp;&nbsp;&nbsp;&nbsp;
                              
                                      Compra: &nbsp;&nbsp;<input type="checkbox" name="motivo" id="motivo" value="compra" >&nbsp;&nbsp;&nbsp;&nbsp;

                                       Entre establecimientos: &nbsp;&nbsp; <input type="checkbox" name="motivo"  id="motivo" value="traslado entre establecimientos">

                              </div>
 
                      <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                                    <table id="detalles" class="table table-striped table-bordered table-condensed table-hover">
                              <thead style="background-color:#35770c; color: #fff;">
                                            <th>CANT.</th>
                                            <th>CÓDIGO</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>U. MED.</th>
                                            <th>PESO TOTAL</th>
                                            <!-- <th style="background-color:#9fde90bf; color: #fff; text-align: center; color: #000000;">IGV</th>
                                    <th style="background-color:#9fde90bf; color: #fff; color: #000000;">Total item</th> -->
                              </thead>
                                  <TFOOT>
                            
                                  </TFOOT>
                                                  
                                        <tbody>
                                        </tbody>
                                    </table>
                        </div>



    

                          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    </a><button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i> EMITIR GUÍA</button>
 
                                    <button id="btnCancelar" class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i>CANCELAR</button>
     
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
         <div class="modal fade" id="myModalComprobante" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" style="width: 65% !important;">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">Seleccione un comprobante</h4>
                </div>
                <div class="modal-body">

                  <table id="tblacomprobante" class="table table-striped table-bordered table-condensed table-hover" width=-5px>
                    <thead>
                        <th >Opciones</th>
                        <th >Num. Documento</th>
                        <th >Razon Social</th>
                        <th >Domicilio</th>
                        <th >Numero comprobante</th>
                        <th >Neto</th>
                        <th >IGV</h>
                        <th >Totalt</th>
                
                    </thead>
                    <tbody>
               
                    </tbody>
                    <tfoot>
                        <th >Opciones</th>
                        <th >Num. Documento</th>
                        <th >Razon Social</th>
                        <th >Domicilio</th>
                        <th >Numero comprobante</th>
                        <th >Neto</th>
                        <th >IGV</th>
                        <th >Total</th>

                    </tfoot>
                  </table>

                </div>

                  <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cerrar</button>  
          
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
    <script type="text/javascript" src="scripts/guiaremision.js"></script>

  <?php
}
ob_end_flush();
?>