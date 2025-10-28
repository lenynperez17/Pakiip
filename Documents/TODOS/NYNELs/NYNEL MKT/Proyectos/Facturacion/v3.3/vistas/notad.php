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
        <link rel="stylesheet" href="style.css">
              <!-- Content Wrapper. Contains page content -->
              <div class="content-start transition">        
                <!-- Main content -->
                <section class="container-fluid dashboard">
                <div class="content-header">
                    <h1>Nota de débito <button class="btn btn-info btn-sm" id="btnagregar"
                            onclick="mostrarform(true)">Nuevo</button> <button class="btn btn-success btn-sm"
                            id="refrescartabla" onclick="refrescartabla()">Refrescar</button></h1>
                    
                </div>
                    <div class="row" style="background:white;">
                      <div class="col-md-12">
                          <div class="">


                          <div class="table-responsive" id="listadoregistros">
                            <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                                <thead style="text-align:center;">
                                   <th >-</th>
                                   <th>Número</th>
                                   <th>Fecha</th>
                                   <th>Descripción</th>
                                   <th>Comprobante modificado</th>
                                   <th>Cliente</th>
                                   <th>Subtotal</th>
                                   <th>Igv</th>
                                   <th>Total</th>
                                   <th>Estado</th>
                                   <th>Opciones</th>
                                </thead>
                                <tbody style="text-align:center;">
                                </tbody>

                            </table>
                        </div>
                                         

                          <div class="panel-body" id="formularioregistros">
                            <form name="formulario" id="formulario" method="POST">
                            <!-- <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                    <label style="color: red;" > *Para notas de crédito a boletas debe seleccionar la serie B.  </label>
                    <label style="color: red;" > *Recuerde que devolución por item solo es para facturas y boletas de productos mas no servicios.</label>
                    </div> -->

                            <div class="row">

                                  <div class="col-md-4">
                                      <div class="card">
                                          <div class="card-body">
                                              <div class="row">

                                                <div class="mb-3 col-lg-6">
                                                      <label for="recipient-name" class="col-form-label">Serie:</label>
                                                      <select class="form-control" name="serie" id="serie" onchange="incremetarNum();"></select>
                                                      <input type="hidden" name="idnumeracion" id="idnumeracion" >
                                                      <input type="hidden" name="SerieReal" id="SerieReal" >
                                                      <input type="hidden" name="idempresa" id="idempresa"  value="<?php echo $_SESSION['idempresa']; ?>" >
                                                </div>

                                                <div class="mb-3 col-lg-6">
                                                    <label for="recipient-name" class="col-form-label">Número:</label>
                                                    <input type="text" name="numero_nc" id="numero_nc" class="form-control" required="true" readonly>
                                                </div>

                                                 <div class="mb-3 col-lg-6">
                                                        <label for="recipient-name" class="col-form-label">Fecha operación:</label>
                                                        <input type="date" class="form-control" name="fecha_emision" id="fecha_emision" required="true" disabled="true">
                                                 </div>

                                                 <div class="mb-3 col-lg-6">
                                                        <label for="recipient-name" class="col-form-label">Motivo nota débito</label>
                                                        <select class="form-control" name="codigo_nota" id="codigo_nota" onchange="cambiotiponota()" ></select>
                                                        <input type="hidden" name="codtiponota" id="codtiponota">
                                                        <input type="hidden" name="nomcodtipo" id="nomcodtipo">
                                                 </div>

                                                 <div class="mb-3 col-lg-6">
                                                        <label for="recipient-name" class="col-form-label">Tipo de comprobante:</label>
                                                        <select class="form-control" name="tipo_doc_mod" id="tipo_doc_mod" onchange="cambio()"  >
                                                        <option value="01">FACTURAS</option>
                                                        <option value="03">BOLETA</option>
                                                        <option value="04">FACTURAS SERVICIO</option>
                                                        <option value="05">BOLETA SERVICIO</option>
                                                        </select>
                                                  </div>

          

                                              </div>
                                          </div>
                                      </div>
                                  </div>

                                  <div class="col-md-8">
                                      <div class="card">
                                          <div class="card-body">

                                          <button style="margin-left:0px;" data-bs-toggle="modal" data-bs-target="#myModalComprobante" id="btnAgregarcomp" type="button" class="btn btn-primary btn-sm"><i class="fa fa-search" data-toggle="tooltip" title="Buscar Comprobante"></i> Buscar Comprobante</button>
                                  
                                          <input type="text" class="" name="numero_comprobante" id="numero_comprobante"  required="true" readonly="true" >
              

                                          <div class="row justify-content-center text-center">

                                          <div class="mb-3 col-lg-2">
                                              <label for="recipient-name" class="col-form-label">R.U.C.</label>
                                              <input type="text" class="form-control" name="numero_documento_cliente" id="numero_documento_cliente" maxlength="15" placeholder=""  required="true" readonly="true" style="background-color: #DAF7A6; font-weight:bold;" readonly="true" >
                                          </div>

                                          <div class="mb-3 col-lg-2">
                                              <label for="recipient-name" class="col-form-label">Fecha boleta</label>
                                              <input type="text" class="form-control" name="fecha1" id="fecha1" placeholder=""  required="true" style="background-color: #DAF7A6; font-weight:bold;" readonly="true" >
                                          </div>

                                          <div class="mb-3 col-lg-4">
                                              <label for="recipient-name" class="col-form-label">Cliente</label>
                                              <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="8" placeholder=""  width="50x" required="true" style="background-color: #DAF7A6; font-weight:bold;" readonly="true">
                                          </div>

                                
                                  

                                          </div>

                              
                                                                                 
                                                   <div id="pdescu">
                                                    <div class="row justify-content-center text-center">

                                                    <div class="mb-3 col-lg-3">
                                                        <label for="recipient-name" class="col-form-label">% Interés</label>
                                                        <input type="text" class="form-control" name="pdescuento" id="pdescuento" maxlength="3" placeholder="%" style="background-color: #FFFF99; font-weight:bold;" onkeypress="return NumChek(event, this);" onchange="calDescuento()" >
                                                    </div>

                                                    <div class="mb-3 col-lg-3">
                                                        <label for="recipient-name" class="col-form-label">Op. Gravada</label>
                                                        <input type="text" class="form-control" name="subtotaldesc" id="subtotaldesc" maxlength="3" placeholder="Subtotal"  style="background-color: #FFFF99; font-weight:bold;" readonly >
                                                    </div>

                                                    <div class="mb-3 col-lg-3">
                                                        <label for="recipient-name" class="col-form-label">IGV</label>
                                                        <input type="text" class="form-control" name="igvdescu" id="igvdescu" maxlength="3" placeholder="IGV"  style="background-color: #FFFF99; font-weight:bold;" readonly >
                                                    </div>

                                                    <div class="mb-3 col-lg-3">
                                                        <label for="recipient-name" class="col-form-label">Importe total</label>
                                                        <input type="text" class="form-control" name="totaldescu" id="totaldescu" maxlength="3" placeholder="Total"  style="background-color: #FFFF99; font-weight:bold; font-size: 14px;" readonly >
                                                    </div>

                                                    <div hidden class="mb-3 col-lg-3">
                                                        <label for="recipient-name" class="col-form-label">Vendedor</label>
                                                        <select class="form-control" name="vendedorsitio" id="vendedorsitio"></select>
                                                    </div>

                                                    <div class="mb-3 col-lg-12">
                                      
                                                      <textarea rows="2" cols="50" class="form-control" placeholder="Escribir todas las obervaciones necesarias" name="desc_motivo" id="desc_motivo" style="background-color: #DAF7A6; font-weight:bold;"> </textarea>
                                                  </div>
              
                                                    </div>
                      
                                
                                          </div>

                                         
                                        </div>

                                             
                                        </div>




                                

                                  
                                    
                                          </div>
                                      </div>
                                  </div>

                                  <div class="form-group col-lg-12">
                                        <!--Campos para guardar comprobante Nota de credito-->
                                        <input type="hidden" name="idnota" id="idnota" >
                                        <input type="hidden" name="idcomprobante" id="idcomprobante" >
                                        <input type="hidden" name="tipocomprobante" id="tipocomprobante" >
                                        <input type="hidden" name="nombre" id="nombre"  value="NOTA DE DEBITO">
                                        <input type="hidden" name="codigo_nota" id="codigo_nota" value="08">
                                        <input type="hidden" name="tipo_moneda" id="tipo_moneda" value="PEN">
                                        <input type="hidden" name="sum_ot_car" id="sum_ot_car" value="0">
                                        <input type="hidden" name="total_val_venta_oi" id="total_val_venta_oi" value="0">
                                        <input type="hidden" name="total_val_venta_oe" id="total_val_venta_oe" value="0">
                                        <input type="hidden" name="sum_isc" id="sum_isc" value="0">
                                        <input type="hidden" name="sum_ot" id="sum_ot" value="0">
                                        <!--Datos del cliente-->
                                        <input type="hidden" name="idpersona" id="idpersona" required="true">
                                        <input type="hidden" name="tipo_documento_cliente" id="tipo_documento_cliente">

                                        <input type="hidden" name="fecha2" id="fecha2">
                                        <input type="hidden" name="hora2" id="hora2">
                                        <!--Datos del cliente-->
                                  </div>

                                  <div class="col-md-12">
                                              <div class="card">
                                                 <div class="card-body">

                                                 <div class="table-responsive">
                                                 <table id="detalles" class="table table-striped" style="text-align:center;">
                                                        <thead align="center" style="background:#081A51; color: white; ">
                                                            <th style="color:white;">+</th>
                                                            <th style="color:white;">Item</th>
                                                            <th style="color:white;">Código</th>
                                                            <th style="color:white;">Descripción</th>
                                                            <th style="color:white;">Cantidad</th>
                                                            <th style="color:white;">Va. Uni.</th>
                                                            <th style="color:white;">Pre. Uni.</th>
                                                            <th style="color:white;">Valor uni.</th>
                                                            <th style="color:white;">Igv</th>
                                                   

                                                        </thead>
                                                
                                                
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                  <div id="totales">
                                                    <div class="card-body">
                                                      <h4 class="card-title">Detalle del comprobante</h4>
                                                      <p class="card-text">
                                                                      <th style="font-weight: bold; background-color:#FFB887;">
                                                                      <div style="display:flex;">
                                                                      <label for="">Subtotal : </label>
                                                                      <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" id="subtotal"> 0.00</h6>
                                                                      <input type="hidden" name="subtotal_comprobante" id="subtotal_comprobante"></th> 
                                                                      </div>
                                                                      <div style="display:flex;">
                                                                      <label for="">IGV : </label>
                                                                      <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" id="igv_"> 0.00</h6>
                                                                      <input type="hidden" name="total_igv" id="total_igv">
                                                                      </div>
                                                                          
                                                                      <div style="display:flex;">
                                                                      <label for="">Total: </label>
                                                                      <h6 style="font-weight: bold; margin: 0 auto; top: 10px; margin-top: 4px; margin-right: 0px;" id="total"> 0.00</h6>
                                                                      <input type="hidden" name="total_final" id="total_final">
                                                                      </div>
                                                              
                                                              
                                                                      </div>
                                                                                                      
                                                                          </th><!--Datos de impuestos--> <!--TOTAL-->
                                                                          <!-- </th> -->

                                                                          <div class="form-group col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                                          </a><button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save" data-toggle="tooltip" title="Guardar"></i> Guardar</button>
                                                  
                                                  
                                                          <button id="btnCancelar" class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left" data-toggle="tooltip" title="Cancelar"></i> Cancelar</button>
                                  
                                                      </p>
                                                    </div>
                                            
                                                  </div>

                                          

                                                               

                                          
                          

                                          
                                                </div>

                                        

                                                  </div>
                                              </div>
                                        </div>

                                        

                                    

                            
                                </form>

                              </div>

                            </div>

    
                    
            
              </section><!-- /.content -->
 
            </div><!-- /.content-wrapper -->
          <!--Fin-Contenido-->
 
  

          <div class="modal fade text-left" id="myModalComprobante" tabindex="-1" role="dialog" aria-labelledby="myModalComprobante" aria-hidden="true">
              <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="myModalComprobante">Agrega el comprobante a afectar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  <button class="btn btn-success btn-sm" id="refrescartabla" onclick="refrescartabla()"><i class="fa fa-repeat"></i> Actualizar</button>
                  <div class="table-responsive" id="listadoregistros">
                            <table id="tblacomprobante" class="table table-striped" style="font-size: 14px; max-width: 100%; !important; width: 100% !important;">
                                <thead style="text-align:center;">
                                <th >Opciones</th>
                                <th >Ruc cliente</th>
                                <th >Fecha emisión</th>
                                <th >Razon Social</th>
                                <th >Domicilio</th>
                                <th >Numero comprobante</th>
                                <th >Neto</th>
                                <th >IGV</h>
                                <th >Totalt</th>
                                </thead>
                                <tbody style="text-align:center;">
                                </tbody>
                                <tfoot>
                               </tfoot>
                            </table>
                        </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                      <i class="bx bx-x d-block d-sm-none"></i>
                      <span class="d-none d-sm-block">Cerrar</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

 


  


          <!-- Modal -->
          <div class="modal fade" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 70% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">NOTA DE CREDITO</h4>
                </div>
        
            <iframe name="modalxml" id="modalxml" border="0" frameborder="0"  width="100%" style="height: 800px;" marginwidth="1" 
            src="">
            </iframe>

                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
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
    <script type="text/javascript" src="scripts/notad.js"></script>

  <?php
}
ob_end_flush();
?>