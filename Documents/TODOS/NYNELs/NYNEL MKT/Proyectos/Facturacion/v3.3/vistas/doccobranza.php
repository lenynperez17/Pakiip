<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  $swsession = 0;
  header("Location: ../vistas/login.php");
} else {
  $swsession = 1;
  require 'header.php';

  if ($_SESSION['Ventas'] == 1) {
    ?>

            <!--Contenido-->
                  <!-- Content Wrapper. Contains page content -->
                  <link rel="stylesheet" href="style.css">
                  <div class="content-start transition">        
                    <!-- Main content -->
                    <section class="container-fluid dashboard">
                        <div class="row">
                          <div class="col-md-12">
                              <div class="box">

                                <div class="box-header with-border">
                                <h1 class="box-title"> DOCUMENTO DE COBRANZA  
                <button class="btn btn-info btn-sm" id="btnagregar" onclick= 'mostrarform(true)'><i class="fa fa-newspaper-o"></i> Nuevo</button>   
     
                                      </h1>
                                    <div class="box-tools pull-right">
                                    </div>
                                    <div>
                                    </div>
                                </div>


                                <!-- /.box-header -->
                                <!-- centro -->
                <div class="panel-body table-responsive" id="listadoregistros">
                                    <table id="tbllistado" class="table table-striped table-bordered table-condensed  table-hove"  >
                                      <thead>
                                        <th>Opciones</th>
                                        <th>Fecha emision</th>
                                        <th>Número</th>
                                        <th>Cliente</th>
                                        <th>Tipo moneda</th>
                                        <th>Total</th>
                                        <th>T. cambio</th>
                                        <th>Total c/t.c.</th>
                                        <th>Observación</th>
                                        <th>Estado</th>

                                      </thead>
                                      <tbody>                            
                                      </tbody>
                          
                                    </table>
                  </div>


              <div class="panel-body"  id="formularioregistros">
            <form name="formulario" id="formulario" method="POST">

            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" >
                 <label>Serie</label>
                 <select  class=""  name="serie" id="serie"  onchange="incrementarNum();"  ></select>
                     <input type="hidden" name="idnumeracion" id="idnumeracion" >
                     <input type="hidden" name="SerieReal" id="SerieReal" >
                     <input type="hidden" name="tipo_documento_dc" id="tipo_documento_dc" value="01">
                </div>

                <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                <label>Número</label> <input type="text" style="font-size: 12pt;" name="numero_doccobranza" id="numero_doccobranza" class="" required="true" readonly style="font-size: 22px" >
                </div>


            <!--Campos para guardar cotizacion-->
                <input type="hidden" name="idccobranza" id="idccobranza" >
                <!--Datos de empresa Estrella-->
                <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                <input type="hidden" name="numeracion" id="numeracion" value="">
                <!--Datos del cliente-->
                <input type="hidden" name="idcliente" id="idcliente">
                <!--Datos del cliente-->

                <input type="hidden" name="hora" id="hora">

                  <div class="row">
                                      <div class="form-group  col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                <label>Fecha ccobranza:</label>
                                  <input type="date"  class="" name="fecha_emision" id="fecha_emision" required="true" disabled="true" >
                                      </div>

                                      <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                       <label>Moneda:</label>
                                       <select class="" name="tipo_moneda" id="tipo_moneda"   onchange="tipodecambiosunat();" >
                                         <option value="none">SELECIONE</option>
                                         <option value="USD">DOLARES</option>
                                         <option value="PEN">SOLES</option>
                                       </select>
                                     </div>  

                                  <div class="form-group col-lg-1 col-md-4 col-sm-6 col-xs-12">
                                       <label>T. camb:</label>
                                        <input type="text" name="tcambio" id="tcambio" class="" readonly="true" >
                                  </div>

                                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                       <label>Condición:</label>
                                        <select class="" name="condicion" id="condicion">
                                          <option value="">SELECCIONAR</option>
                                          <option value="contado">CONTADO</option>
                                          <option value="credito">CREDITO</option>
                                        </select>
                                  </div>

                          
                    </div> 


                                  <div class="row">
                          
                          <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                           <select class="" id="tipo_doc_cli"  name="tipo_doc_cli" onchange="cambiotipoDoc()"> 
                            <option  value="none">TIPO DOCUMENTO</option>
                            <option  value="dni">DNI</option>
                            <option value="ruc">RUC</option>
                           </select>
                            </div>

                      <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" id="divruc">
                           <input type="text" class="" name="numero_documento2" id="numero_documento2" maxlength="11" placeholder="RUC DE CLIENTE-ENTER"  onkeypress="agregarClientexRuc(event)" onchange="agregarClientexRucChange()">
                            </div>

                       <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12"  id="divdni" >
                          <input type="text" class="" name="numero_documento" 
                       id="numero_documento_dni" maxlength="15" placeholder="Número de DNI"  onfocus="focusTest(this);"   onkeypress="agregarClientexDni(event)" > 
                        </div>

                                      <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <input type="text" class="" name="razon_social2" id="razon_social2" required="true"  placeholder="RAZÓN SOCIAL">
                                      </div>


                                      <div class="form-group col-lg-8 col-md-6 col-sm-6 col-xs-12">
                                        <input type="text" class="" name="domicilio_fiscal2" id="domicilio_fiscal2" required="true" placeholder="DIRECCIÓN CLIENTE" >
                                      </div>

                                      <div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                        <input type="text" class="" name="correocli" id="correocli" required="true"  placeholder="CORREO CLIENTE" onkeypress="return focusbotonarticulo(event, this)" onfocus="focusTest(this)">
                                      </div>  

                                       <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12" >
                                        <textarea name="observacion" id="observacion" placeholder="OBSERVACIÓN" rows="3"></textarea>
                                      </div>

                                      <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                                       <select class="" name="tipodoccobranza" id="tipodoccobranza" onchange="seleccionTipoCot();">
                                         <option value="none">SELECCIONE TIPO</option>
                                         <option value="servicio">SERVICIO</option>
                                         <!-- <option value="producto">PRODUCTO</option> -->
                                       </select>
                                     </div>



                    <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                   <select class=""  name="vendedorsitio" id="vendedorsitio" onchange="focusruccliente()">
                   </select>
                 </div>

                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">     
                      <select class="" name="nombre_tributo_4_p" id="nombre_tributo_4_p" onchange="tributocodnon()" >TRIBUTO</select>
                       </div>



                                    <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12" id="divproductos" >
                                        <a data-toggle="modal" href="#myModalArt">           
                                        <button id="btnAgregarArt" type="button" class="btn btn-primary" > 
                                           Productos 
                                        </button>
                                        </a>  
                                    </div>

                                    <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12" id="divservicios">
                                        <a data-toggle="modal" href="#myModalServ">           
                                        <button id="btnAgregarArt" type="button" class="btn btn-primary" > 
                                           Servicios 
                                        </button>
                                        </a>  
                                    </div>

                        

                              </div>

                        
                                    <div class="row">
                          
                                    </div>


            <!-- TABLA DETALLE DE PRODUCTO  ===========================================================  --> 
            <div class="form-group col-lg-12 col-md-8 col-sm-6 col-xs-12">
                  <div class="table-responsive"  id="detallesproductoDIV">
                      <table id="detallesproducto" class="table table-striped table-hover table-bordered"  >
                                  <thead style="background-color:#35770c; color: #fff; text-align: justify;">
                                                <th >Sup.</th>
                                                <th >Item</th>
                                                <th >Artículo</th>
                                                <th >Cantidad</th>
                                                <th >Cód.</th>
                                                <th >U.M.</th>
                                                <th >Prec. u.</th>
                                                <th >Val. u.</th>
                                                <th >Importe</th>
                                    
                                  </thead>


                                          <tfoot style="vertical-align: center;">

                                 
                                            <tr>
                                       <td><td></td><td></td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Tarifa </th>

                                                <th style="font-weight: bold; background-color:#A5E393;"></th>
                                      
                                                  <h4 id="tarifa">0.00</h4>

                                                </td>
                                                </tr> 

                                

                                             <tr>
                                        <td><td></td><td></td><td></td><td></td><td></td>

                                                <th  style="font-weight: bold; vertical-align: center; background-color:#A5E393;">INPUESTO</th>

                                                <th style="font-weight: bold; background-color:#A5E393; vertical-align: center;"></th>

                                                  <h4 id="igv_">0.00</h4>

                                    
                                                </td>
                                                </tr> 


                                
                                                 <tr>
                                       <td><td></td><td></td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Subtotal </th>

                                                <th style="font-weight: bold; background-color:#A5E393;"></th>
                                      
                                                  <h4 id="subtotal">0.00</h4>

                                                </td>
                                                </tr> 

                                
                                                 <tr>
                                       <td><td></td><td></td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Otros </th>

                                                <th style="font-weight: bold; background-color:#A5E393;">
                                                  <input type="text" name="otrosp" id="otrosp" size="4" value="" onfocus="focusTest(this);" onblur="modificarSubototalesServicio()">
                                                  </th>
                                      
                                                </td>
                                                </tr> 
                          
                             
                                    <tr>      
                                      <td><td></td><td></td><td></td><td></td><td></td>
                                                <th style="font-weight: bold; vertical-align: center; background-color:#A5E393;">Total </th>

                                                <th style="font-weight: bold; background-color:#A5E393;">

                                                  <h4 id="total" style="font-weight: bold; vertical-align: center; background-color:#A5E393;">0.00</h4>
                                                  </th>

                                <input type="hidden" name="total_final_producto" id="total_final_producto">
                                <input type="hidden" name="subtotal_cotizacion_producto" id="subtotal_cotizacion_producto"> 
                                <input type="hidden" name="total_igv_producto" id="total_igv_producto">
                    
                                                </td>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                            </tbody>
                                        </table>
                                      </div>
            

              <!-- TABLA DETALLE DE PRODUCTO  ===========================================================  -->

                        <div class="table-responsive"  id="detallesservicioDIV">
                      <table id="detallesservicio" class="table table-striped table-hover table-bordered"   >
                                  <thead style="background-color:#35770c; color: #fff; text-align: justify;">
                                                <th >Opciones</th>
                                                <th >Item</th>
                                                <th >Servicio</th>
                                                <th >Código</th>
                                                <th >Precio</th>
                                                <th >Importe</th>
                                    
                                  </thead>


                                          <tfoot style="vertical-align: center;">

                                            <!--TARIFA-->
                                            <tr>
                                       <td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Tarifa </th>

                                                <th style="font-weight: bold; background-color:#A5E393;">
                                                  <h4 id="tarifaS">0.00</h4>
                                                  </th>

                                                </td>
                                                </tr> 


                                                <!--SUBTOTAL-->
                                                 <tr>
                                      <td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Neta </th>

                                                <th style="font-weight: bold; background-color:#A5E393;">
                                                  <h4 id="subtotal_servicio">0.00</h4>
                                                  </th>

                                                </td>
                                                </tr> 


                                                   <!--IGV-->
                                       <tr>
                                        <td><td></td><td></td><td></td>

                                                <th  style="font-weight: bold; vertical-align: center; background-color:#A5E393;">igv 18% </th>

                                                <th style="font-weight: bold; background-color:#A5E393; vertical-align: center;">
                                                  <h4 id="igv_servicio">0.00</h4>
                                                    </th>
                                    
                                                </td>
                                                </tr> 


                                

                                                 <!--Deduccion-->
                                                 <tr>
                                        <td><td></td><td></td><td></td>

                                                <th style="font-weight: bold;  background-color:#A5E393;">Otros </th>

                                                <th style="font-weight: bold; background-color:#A5E393;">
                                                  <input type="text" name="otross" id="otross" size="4" value="0.00" onfocus="focusTest(this);" onchange="modificarSubototalesServicio();">
                                                </th>
                                                </td>
                                                </tr> 

                                    
                             
                                    

                                         <!--TOTAL--> 
                                              <tr>      
                                       <td><td></td><td></td><td></td>
                                                <th style="font-weight: bold; vertical-align: center; background-color:#A5E393;">Total </th> <!--Datos de 

impuestos-->  <!--IGV-->
                                                <th style="font-weight: bold; background-color:#A5E393;">

                                                <h4 id="total_servicio" style="font-weight: bold; vertical-align: center; background-color:#A5E393;">0.00</h4>
                                                </th>
                                <input type="hidden" name="total_final_servicio" id="total_final_servicio">
                                <input type="hidden" name="subtotal_doccobranza_servicio" id="subtotal_doccobranza_servicio"> 
                                <input type="hidden" name="total_igv_servicio" id="total_igv_servicio">
                                                </td>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                            </tbody>
                                        </table>
                                      </div>

      
                  </div>
    

                <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-6">
                <button class="btn btn-primary" type="submit" id="btnGuardarDoccobranza" data-toggle="tooltip" title="Guardar" > Guardar</button>


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













              <!-- Modal generar nueva factura-->
              <div class="modal fade" id="myModalnfac" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" role="Documento" style="overflow-y: scroll;">
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">NUEVA FACTURA</h4>
                    </div>
        
            <div class="panel-body"  id="formularioregistros">
                <form name="formularionfactura" id="formularionfactura" method="POST" autocomplete="off">
                  <input type="hidden" name="idempresa2" id="idempresa2" value="<?php echo $_SESSION['idempresa']; ?>">
     
                 <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12" >
                 <label>Serie</label>
                     <select  class=""  name="seriefactura" id="seriefactura"  onchange="incrementarNumfactura();"  ></select>
                         <input type="hidden" name="idnumeracionf" id="idnumeracionf" >
                         <input type="hidden" name="SerieRealfactura" id="SerieRealfactura" >
                  </div>


                <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                <label>Número</label> 
                <input type="text" style="font-size: 12pt;" name="numero_factura" id="numero_factura" class="form-control" required="true" readonly style="font-size: 22px" >
                </div>


                <div class="form-group  col-lg-2 col-md-4 col-sm-6 col-xs-12">
                  <label>Fecha doc. cobr.:</label>
                   <input type="hidden" name="horaf" id="horaf">
                    <input type="date"  style="font-size: 12pt;"  class="" name="fechadc" id="fechadc"  readonly="">
      
                 </div>

                   <div class="form-group  col-lg-2 col-md-4 col-sm-6 col-xs-12">
                 <label>Fecha operación:</label>
       
                   <input type="date"  style="font-size: 12pt;"  class="" name="fecemifa" id="fecemifa" required="true" >
                 </div>

                 <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                      <label>Moneda:</label>
                         <select class="form-control" name="tipo_moneda_factura" id="tipo_moneda_factura" readonly>
                              <option value="PEN">SOLES</option>
                              <option value="USD">DOLARES</option>
                         </select>
                  </div>

                  <div class="form-group col-lg-1 col-md-4 col-sm-6 col-xs-12">
                      <label>T. camb:</label>
                      <input type="text" name="tcambiofactura" id="tcambiofactura" class=""  readonly >
                  </div>

                  <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                    <label>RUC:</label>
                    <input type="hidden" name="idclientef" id="idclientef">
                    <input type="hidden" name="tipodocucli" id="tipodocucli">
                    <input type="text" class="" name="numero_documento_factura" id="numero_documento_factura" readonly >
                  </div>


                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Nombre comercial:</label>
                      <input type="text" class="" name="razon_socialnfactura" id="razon_socialnfactura" readonly>
                  </div>

                  <div class="form-group col-lg-8 col-md-6 col-sm-6 col-xs-12">
                    <label>Dirección</label>
                    <input type="text" class="" name="domicilionfactura" id="domicilionfactura" readonly>
                  </div>


                  <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
                      <label>Condición:</label>
                        <input type="text" class="" name="condicionnfactura" id="condicionnfactura" readonly>
                  </div>

                   <div class="form-group  col-lg-4 col-sm-2 col-md-2 col-xs-12">
                    <label>Correo cliente:</label>
                  <input type="text" name="correocliente" id="correocliente" class="" >
                  </div>


    

                  <div class="form-group  col-lg-4 col-sm-2 col-md-2 col-xs-12">
                                      <label>Forma de pago:</label>
                                     <select class=""  name="tipopago" id="tipopago" onchange="contadocredito()">
                                        <option value="nn">SELECCIONE LA FORMA DE PAGO</option>
                                        <option value="Contado" selected>CONTADO</option>
                                        <option value="Credito">CRÉDITO</option>
                                      </select>
                  </div>

                <div id="tipopagodiv" style="display: none;" > 

                                    <div class="form-group col-lg-2 col-sm-2 col-md-2 col-xs-12">
                                      <label>N de cuotas:</label>
                      <div class="input-group mb-3">
                        <input name="ccuotas" id="ccuotas" onchange="focusnroreferencia()" class="" value="1"  onkeypress="return NumCheck(event, this)">
                                      <div class="input-group-append">
                                        <a data-toggle="modal" href="#modalcuotas" class="btn btn-success">
                                          <i class="fa fa-table" data-toggle="tooltip" title="Mostrar cuotas"></i>
                                        </a>
                                      </div>

                                      <div class="input-group-append">
                                        <a data-toggle="modal" onclick="borrarcuotas()" class="btn btn-success">
                                          <i class="fa fa-trash" data-toggle="tooltip" title="Limpiar cuotas"></i></a>
                                      </div>
                                    </div>
                                    </div>

                    </div> 


                    <div class="form-group  col-lg-4 col-sm-2 col-md-2 col-xs-12">
                      <label>Nro de referencia:</label>
                  <input type="text" name="nroreferenciaf" id="nroreferenciaf" class="" style="color: blue;" placeholder="Nro de referencia de cuenta u otro">
                </div> 


                <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                          <label>Pago tarj.:</label>
                            <img src="../files/articulos/tarjetadc.png" data-toggle="tooltip" title="Pago por tarjeta"> <input type="checkbox" name="tarjetadc" id="tarjetadc" 
                            onclick="activartarjetadc();">
                            <input type="hidden" name="tadc"  id="tadc" >
                          </div>


                          <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                            <label>Pago Transf.:</label>
                            <img src="../files/articulos/transferencia.png" data-toggle="tooltip" title="Pago por transferencia"> <input type="checkbox" name="transferencia" id="transferencia" 
                            onclick="activartransferencia();">
                            <input type="hidden" name="trans"  id="trans" >
                          </div>

    


            <div class="form-group  col-lg-12 col-sm-12 col-md-12 col-xs-12">
                <div class="row">
                          
                    </div>

                 <div class="table-responsive">
                        <table id="detallefactura" class="table table-striped table-hover table-bordered" >
                        </table>
              </div>
            </div>

                      <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-6">
                      <button class="btn btn-primary" type="submit" id="btnGuardar" data-toggle="tooltip" title="Guardar factura" >
                          <i class="fa fa-save"></i> Guardar</button>
           
                          <button id="btnCancelar" class="btn btn-danger" data-toggle="tooltip" title="Cancelar" data-dismiss="modal"
                      type="button"><i class="fa fa-arrow-circle-left"> Cancelar</i></button>
                      </div>


                      <!-- Fin modal -->
              <div class="modal fade" id="modalcuotas" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog" style="width: 70% !important;" >
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">CUOTAS Y FECHAS DE PAGO</h4>
                    </div>
        
                      <h2 id="totalcomp"></h2>
        

            <div class="table-responsive">
              <table class="table table-sm table-striped table-bordered table-condensed table-hover nowrap">
            <tr>
              <td>CUOTAS</td>
              <td>
                    <div >
                    <label>Monto de cuotas</label>
                      <div id="divmontocuotas" >
                      </div>
                      </div>
              </td>

              <td>
                <div >
                    <label>Fechas de pago</label>
                      <div id="divfechaspago" >
          
                      </div>
                    </div>
              </td>
            </tr>



            </table>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                      <!-- <button type="button" class="btn btn-success" onclick="mesescontinuos()" >Meses continuos</button> -->

                    </div>      
          
                  </div>
     
                </div>
              </div>  
              <!-- Fin modal -->


                </form>
            </div> 
                      <div class="modal-footer">

                      </div> 

                  </div>
                </div>
              </div>  



              <!-- Modal tipo de cambio -->
             <div class="modal fade" id="modalTcambio">
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                      <div class="modal-header"> 
                      <button type="button" class="close"  aria-hidden="true">&times;</button>

                        Tipo de cambio
                      </div>
                  <form name="formulariotcambio" id="formulariotcambio" method="POST">
                          <iframe border="0" frameborder="0" height="300" width="100%" src="https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias"></iframe>
                    </form>

                      <div class="modal-footer">
                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>
          

                    </div>        
                 </div>
               </div>
              </div>
 
  
             <!-- Modal -->
              <div class="modal fade" id="myModalCli" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" role="Documento" >
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">Seleccione un cliente</h4>
                    </div>
                    <div class="table-responsive">

                      <table id="tblaclientes" class="table table-striped table-bordered table-condensed table-hover" width=-5px>
                        <thead>
                            <th >Opciones</th>
                            <th >Nombre</th>
                            <th >RUC</th>
                            <th >Dirección</th>
                
                        </thead>
                        <tbody>
               
                        </tbody>
                        <tfoot>
                            <th>Opciones</th>
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Dirección</th>

                        </tfoot>
                      </table>

                    </div>

                      <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Salir
                      </button>

                      <a data-toggle="modal" href="#ModalNcliente">
                      <button type="button" class="btn btn-primary" data-dismiss="modal" 
                      onclick=""><i class="fa fa-user" data-toggle="tooltip" title="Nuevo cLiente"></i> Nuevo cliente</button>  
                      </a>

                      </div> 

                  </div>
                </div>
              </div>  
              <!-- Fin modal -->


              <!-- Modal -->
              <div class="modal fade" id="ModalNcliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title ">Nuevo cliente</h4>
                    </div>
                    <div class="modal-body">


            <div class="container">
                  <form role="form" method="post" name="busqueda" id="busqueda" >
                      <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                        <input type="number" class="" name="nruc" id="nruc" placeholder="Ingrese RUC o DNI" pattern="([0-9][0-9][0-9][0-9][0-9][0-9]

[0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])" autofocus>
                      </div>
                       <button type="submit" class="btn btn-success" name="btn-submit" id="btn-submit" value="burcarclientesunat">
                          <i class="fa fa-search"></i> Buscar SUNAT
                        </button>
                      </form>
              </div>

                       <form name="formularioncliente" id="formularioncliente" method="POST">
                                      <div class="">
                                        <input type="hidden" name="idpersona" id="idpersona">
                                        <input type="hidden" name="tipo_persona" id="tipo_persona" value="cliente">
                                      </div>

                                      <div class="form-group col-lg-1 col-md-12 col-sm-12 col-xs-12">
                                        <label>Tipo Doc.:</label>
                                        <select  class=" select-picker" name="tipo_documento" id="tipo_documento" required>                       
                                        <option value="6"> RUC </option>
                                        </select>
                                      </div>

                                        <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                                          <label>N. Doc.:</label>
                                          <input type="text" class="" name="numero_documento3" id="numero_documento3" maxlength="20" 

            placeholder="Documento"  onkeypress="return focusRsocial(event, this)"  >
                                        </div>

             
                                        <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                                        <label>Razón social:</label>
                                        <input type="text" class="" name="razon_social" id="razon_social" maxlength="100" placeholder="Razón social" 

            required onkeypress="return focusDomi(event, this)">
                                        </div>


                                        <div class="form-group col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                        <label>Domicilio:</label>
                                        <input type="text" class="" name="domicilio_fiscal" id="domicilio_fiscal" maxlength="100" 

            placeholder="Domicilio fiscal" required onkeypress="focustel(event, this)" >
                                        </div>

                           

                   <div class="form-group col-lg-2 col-md-12 col-sm-12 col-xs-12">
                     <input type="number" class="" name="telefono1" id="telefono1" maxlength="15" placeholder="Teléfono 1" pattern="([0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9])" onkeypress="return focusemail(event, this)">
                    </div>

                          <div class="form-group col-lg-3 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" class="" name="email" id="email" maxlength="50" placeholder="CORREO"  onkeypress="return focusguardar(event, this)">
                            </div>

            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                  <button class="btn btn-primary" type="submit" id="btnguardarncliente" name="btnguardarncliente" value="btnGuardarcliente">
                    <i class="fa fa-save"></i> Guardar
                  </button>
            </div>

               <!--  <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
    <iframe border="0" frameborder="0" height="450" width="100%" marginwidth="1" 
    src="https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias">
    </iframe>
    </div>    -->

                     </form>

            <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>

               <script src="scripts/ajaxview.js"></script>
                <script>
            //============== original ===========================================================
                  $(document).ready(function(){
                    $("#btn-submit").click(function(e){
                      var $this = $(this);
                      e.preventDefault();
            //============== ORIGINAL FACTURA ===========================================================

            var tipodo=$("#tipo_doc_cli").val();

            if (tipodo=="ruc")
              {
                var documento=$("#nruc").val();
                   $.post("../ajax/factura.php?op=listarClientesfacturaxDoc&doc="+documento, function(data,status)
                    {
                   data=JSON.parse(data);
                   if (data != null){
                    alert("Ya esta registrado cliente, se agregarán sus datos!");
                   $('#idpersona').val(data.idpersona);
                   $('#numero_documento2').val(data.numero_documento);
                   $("#razon_social2").val(data.razon_social);
                   $('#domicilio_fiscal2').val(data.domicilio_fiscal);
                   $('#correocli').val(data.email);
                   document.getElementById("btnAgregarArt").style.backgroundColor= '#367fa9';
                   document.getElementById("btnAgregarArt").focus();
                   $("#ModalNcliente").modal('hide');
                    }
                    else
                    {
                //============== original ===========================================================
              var numero = $('#nruc').val(),
              url_s = "../ajax/factura.php?op=consultaRucSunat&nroucc="+numero;
              parametros = {'action':'getnumero','numero':numero}
              if (numero == '') {
                alert("El campo documento esta vacio ");
                $.ajaxunblock();
              }else{
                $.ajax({
                    type: 'POST',
                    url: url_s,
                    dataType:'json',
                    beforeSend: function(){
                    },  
                    complete:function(data){
        
                    },
                    success: function(data){
                      $('.before-send').fadeOut(500);
                      if(!jQuery.isEmptyObject(data.error)){
                        alert(data.error);
                      }else{
              
                          $("#numero_documento3").val(data.numeroDocumento);
                          $('#razon_social').val(data.nombre);
                          $('#domicilio_fiscal').val(data.direccion);
                          $('#iddistrito').val(data.distrito);
                          $('#idciudad').val(data.provincia);
                          $('#iddepartamento').val(data.departamento);
                      }
                      $.ajaxunblock();
                    },
                    error: function(data){
                        alert("Problemas al tratar de enviar el formulario");
                        $.ajaxunblock();
                    }
                }); 
              }
            document.getElementById("btnguardarncliente").focus();
            //============== original ===========================================================
                            }
            //============== original ===========================================================
                        });
            //============== original FACTURA=====================================================

            }else{


            var documento=$("#nruc").val();
                    $.post("../ajax/doccobranza.php?op=listarClientesboletaxDoc&doc="+documento, function(data,status)
                    {
                   data=JSON.parse(data);
                   if (data != null){
                    alert("Ya esta registrado cliente, se agregarán sus datos!");
                   $('#idpersona').val(data.idpersona);
                   $("#razon_social").val(data.nombres);
                   $('#domicilio_fiscal').val(data.domicilio_fiscal);
                   document.getElementById("btnAgregarArt").style.backgroundColor= '#367fa9';
                   document.getElementById("btnAgregarArt").focus();
                   $("#ModalNcliente").modal('hide');
                    }
                    else
                    {
                //============== original ===========================================================
              var numero = $('#nruc').val(),
              url_s = "../ajax/boleta.php?op=consultaDniSunat&nrodni="+numero;
              parametros = {'action':'getnumero','numero':numero}
              if (numero == '') {
                alert("El campo documento esta vacio ");
                $.ajaxunblock();
              }else{
                $.ajax({
                    type: 'POST',
                    url: url_s,
                    dataType:'json',
                    beforeSend: function(){
                    },  
                    complete:function(data){
        
                    },
                    success: function(data){
                      $('.before-send').fadeOut(500);
                      if(!jQuery.isEmptyObject(data.error)){
                        alert(data.error);
                      }else{
                          $('#numero_documento3').val("");
                          $('#razon_social').val("");
                          $('#domicilio_fiscal').val("");
                          $("#idpersona").val("N");
                          $("#numero_documento3").val(data.numeroDocumento);
                          $('#razon_social').val(data.nombre);
                      }
                      $.ajaxunblock();
                    },
                    error: function(data){
                        alert("Problemas al tratar de enviar el formulario");
                        $.ajaxunblock();
                    }
                }); 
              }
            document.getElementById("btnguardarncliente").focus();
            //============== original ===========================================================
                            }
            //============== original ===========================================================
                        });
            //============== original FACTURA=====================================================


            }


          
                    });
                  });
                </script>


                </div>


                      <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i>
                      Cerrar</button>  
                      </div> 

                  </div>
                </div>
              </div>  
              <!-- Fin modal -->

              <!-- Modal -->
              <div class="modal fade" id="myModalArt" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">Seleccione un Artículo</h4>
                    </div>
                    <div class="table-responsive">
                      <table id="tblarticulos" class="table table-striped table-bordered table-condensed table-hover" >
                        <thead>
                            <th>Opciones</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>U.M.</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Imagen</th>
                        </thead>
                        <tbody>
               
                        </tbody>
                        <tfoot>
                          <th>Opciones</th>
                          <th>Nombre</th>
                          <th>Código</th>
                          <th>U.M.</th>
                
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Imagen</th>
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



              <!-- Modal  Servicio o inmueble-->
             <div class="modal fade" id="myModalServ" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">Seleccione un bien o servicio</h4>
                    </div>
                    <div class="table-responsive">
                      <table id="tblservicios" class="table table-striped table-bordered table-condensed table-hover" >
                        <thead>
                            <th>Opciones</th>
                            <th>Descripción</th>
                            <th>Código</th>
                            <th>Valor</th>
                        </thead>
                        <tbody>
               
                        </tbody>
                        <tfoot>
                            <th>Opciones</th>
                            <th>Descripción</th>
                            <th>Código</th>
                            <th>Precio</th>
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



                <!-- Modal -->
              <div class="modal fade" id="myModalArt_" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog" style="width: 100% !important;">
                  <div class="modal-content">

                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">Seleccione el tipo de précioooo</h4>
                      <select class="" id="preciotipo"  name="preciotipo" onchange="listarArticulos_()" style="background-color: #85d197;">
                      <option value='1'>PRECIO PÚBLICO</option>
                      <option value='2'>PRÉCIO POR MAYOR</option>
                      <option value='3'>PRÉCIO DISTRIBUIDOR</option>
                      </select>


                      <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()"><i class="fa fa-refresh fa-spin fa-1x fa-fw"></i>
                        <span class="sr-only"></span>Actualizar</button>

                      </div>

                    <div class="table-responsive">
                      <table id="tblarticulos_" class="table table-striped table-bordered table-condensed table-hover" >
                        <thead>
                            <th>Opciones</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>U.M.</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Imagen</th>
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

            <input type="hidden" name="idultimocom" id="idultimocom">

              <!-- Modal -->
              <!-- <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
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
  </div>   -->
              <!-- Fin modal -->


              <!-- Modal VISTA PREVIA IMPRESION -->
              <div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog modal-lg" style="width: 90% !important;" >
                  <div class="modal-content" >
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title">SELECCIONE EL FORMATO DE IMPRESIÓN</h4>
                    </div>
            
                    <div class="text-center">

                      <a onclick="preticket()">
                        <div  class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                          <img src="../files/vistaprevia/ticket.jpg" >
                        </div>
                      </a>

                      <a onclick="prea42copias()">
                        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                          <img src="../files/vistaprevia/a42copias.jpg">
                        </div>
                      </a>

                      <a onclick="prea4completo()">
                        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                          <img src="../files/vistaprevia/a4completo.jpg">
                        </div>
                      </a>

          
          
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                          ENVIAR POR CORREO FACTURA N°: <h3 style="" id="ultimocomprobante"> </h3> AL CORREO: <h3 style="" id="ultimocomprobantecorreo"></h3>
                          <a onclick="enviarcorreoprew()">
                          <img src="../public/images/mail.png"> 
                          </a>
                          </div>
                    <button class="btn btn-info" name="estadoenvio" id="estadoenvio" value="ESTADO ENVIO A SUNAT" onclick="estadoenvio()">Estado envio</button> 
                    <h3 id="estadofact">Documento emitido</h3>

                          <h3 id="estadofact2" style="color: red;"> Recuerde que para enviar por correo debe hacer la vista previa para que se generen los archivos PDF.</h3>

                           <h4>Recuerde que puede enviar los comprobantes por correo. Cuide el planeta.</h4> <img src="../files/vistaprevia/hoja.jpg">
          
                      </div>
          


                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-dismiss="modal" >Cerrar</button>
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
                      <h4 class="modal-title">FACTURA</h4>
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



  




  

                                    </form>

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
      <script type="text/javascript" src="scripts/doccobranza.js"></script>

    <?php
}
ob_end_flush();
?>