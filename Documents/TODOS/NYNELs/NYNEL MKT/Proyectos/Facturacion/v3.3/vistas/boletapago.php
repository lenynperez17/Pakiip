<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['RRHH'] == 1) {

    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-start transition">        
                <!-- Main content -->
                <section class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Boleta de pago <button class="btn btn-primary btn-sm" onclick="mostrarform(true)"> Nueva boleta</button></h1>
                </div>
                    <div class="row" style="background:white;">
                      <div class="col-md-12">
                          <div class="box">
               
            

                            <div class="table-responsive" id="listadoregistros">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                              <th>Id</th>
                                    <th>N° Boleta</th>
                                    <th>Empleado</th>
                                    <th>Mes</th>
                                    <th>Año</th>
                                    <th>Total días</th>
                                    <th>Total horas</th>
                                    <th>Total bruto</th>
                                    <th>Total Dcto</th>
                                    <th>Sueldo Pagar</th>
                                    <th>Empresa</th>
                                    <th>Opciones</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>

                              </tr>
                            </tbody>
                          </table>

                        </div>

              

                   
                            <div class="panel-body" style="height: 300px;" id="formularioregistros">
                                <form name="formulario" id="formulario" method="POST">
                                <div class="card">
                                          <div class="card-body">
                                  <input type="hidden" name="idboletaPago" id="idboletaPago">
                                  <input type="hidden" name="nrobol" id="nrobol">
                                  <input type="hidden" id="idserie" name="idserie">
                                  <input type="hidden" id="nboleta2" name="nboleta2">

                                <div  style="border:2px solid grey; border-radius: 15px; align-content: right;">
                                    <h1 style="width: 144px; font-size:18px; margin-top:-16px; margin-left:10px; background:white;">N° BOLETA</h1>
                                      <h1 id="nboleta" name="nboleta"></h1>
                                   </div>
                                   <br>

                                  <div class="row" style="border:2px solid grey; border-radius: 15px;">
                                    <h1 style="width: 144px; font-size:18px; margin-top:-12px; margin-left:7px; background:white;">DATOS EMPLEADO </h1>

                          


                                  <div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <label>Empresa:</label>
                                   <select  class="select-picker form-control" name="empresa" id="empresa" onchange="cargarempleadocombo();" >
                                  </select>
                                  </div>
                      
                              <div id="datosempleado" name="datosempleado"  class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                  <label>Empleado:</label>
                                   <select  class="select-picker form-control" name="idempleado" id="idempleado"  data-live-search="true" onchange="seleccionempleado();">
                                   </select>
                           </div>


                                 <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                    <label>Nombres:</label>
                                    <input type="text" class="form-control" name="nombreemple" id="nombreemple" readonly> 
                                  </div>

                                  <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                    <label>Apellidos:</label>
                                    <input type="text" class="form-control" name="apeemple" id="apeemple" readonly> 
                                  </div>

                                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Puesto:</label>
                                    <input type="text" class="form-control" name="ocupacione" id="ocupacione" readonly> 
                                  </div>

                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>DNI/CE:</label>
                                    <input type="text" class="form-control" name="docide" id="docide" readonly required="true"> 
                                   </div>

                                   <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Tipo de remuneración:</label>
                                    <input type="text" class="form-control" name="tiporemun" id="tiporemun" readonly> 
                                   </div>



                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Mes:</label>
                                    <select class="form-control" id="mes"  name="mes" >
                                      <option value="01">ENERO</option>
                                      <option value="02">FEBRERO</option>
                                      <option value="03">MARZO</option>
                                      <option value="04">ABRIL</option>
                                      <option value="05">MAYO</option>
                                      <option value="06">JUNIO</option>
                                      <option value="07">JULIO</option>
                                      <option value="08">AGOSTO</option>
                                      <option value="09">SEPTIEMBRE</option>
                                      <option value="10">OCTUBRE</option>
                                      <option value="11">NOVIEMBRE</option>
                                      <option value="12">DICIEMBRE</option>
                                    </select>
                                  </div>


                                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Año:</label>
                                    <select class="form-control" id="ano"  name="ano" >
                                      <option value="2021">2021</option>
                                      <option value="2022">2022</option>
                                      <option value="2023">2023</option>
                                      <option value="2024">2024</option>
                                      <option value="2025">2025</option>
                                      <option value="2026">2026</option>
                                      <option value="2027">2027</option>
                                      <option value="2028">2028</option>
                                      <option value="2029">2029</option>
                                      <option value="2030">2030</option>
                                      <option value="2031">2031</option>
                                      <option value="2032">2032</option>
                                    </select>
                                  </div>


                                  <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Fecha de ingreso:</label>
                                    <input type="date" class="form-control" name="fechai" id="fechai" readonly>
                                  </div>


                                    <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                    <label>Nro CUAPP:</label>
                                    <input type="text" class="form-control" name="cuapp" id="cuapp" readonly> 
                                  </div>


                          
                        
                                </div>


                                <br>

                                <div class="row" style="border:2px solid grey; border-radius: 15px;">
                                    <h1 style="width: 144px; font-size:18px; margin-top:-12px; margin-left:7px; background:white;">REMUNERCIÓN </h1>


                                    <table style="color:black;">
                                      <th>
                                      <tr>
                                        <td >CONCEPTO</td><td></td><td></td><td>IMPORTE</td>
                                      </tr>
                                    </th>

                          <tr>
                          <td>SUELDO MENSUAL</td>
                           <td><input class="form-control" type="text" name="cdias" id="cdias" placeholder="días" onchange="calculohorastrabajadas();"></td>
                           <td><input class="form-control" type="text" name="choras" id="choras" placeholder="horas"></td>
                           <td><input class="form-control" type="text" class="" name="sueldomensu" id="sueldomensu" onchange="calculartbruto();"></td>
                          </tr>


                          <tr>
                          <td>HORAS EXTRAS</td>
                           <td><select id="cchoras" name="cchoras" class="form-control" onchange="calculohorastrabajadas();">
                            <option value="1.00">1</option>   
                            <option value="2.00">2</option>   
                            <option value="3.00">3</option>   
                            <option value="4.00">4</option>   
                            <option value="5.00">5</option>    
                          </select></td>
                           <td><input class="form-control" type="text" name="horasex" id="horasex" ></td>
                           <td><input type="text" class="form-control" name="hextras" id="hextras" onchange="calculartbruto();"></td>
                          </tr>


                          <tr>
                          <td colspan="3" align="right">ASIGNACIÓN FAMILIAR</td>
                           <td><input type="text" class="form-control" name="asigfam" id="asigfam" onchange="calculartbruto();"></td>
                          </tr>


                          <tr>
                          <td colspan="3" align="right">SOBRE TIEMPO TRAB. NOC.</td>
                           <td><input type="text" class="form-control" name="sobrthr" id="sobrthr" onchange="calculartbruto();"></td>
                          </tr>


                          <tr>
                          <td>CONCEPTO ADICIONAL</td>
                           <td colspan="2"><input type="text" class="form-control" name="conceptoadicional" id="conceptoadicional" onchange="calculartbruto();"></td>
                           <td><input type="text" class="form-control" name="importeconcepto" id="importeconcepto" onchange="calculartbruto();" value="0.00"></td>
                          </tr>



                          <tr>
                          <td></td>
                           <td></td>
                           <td align="right">TOTAL S/</td>
                           <td><input type="text" class="form-control" name="totalsbru" id="totalsbru" readonly></td>
                          </tr>


                                    </table>
                                  </div>

                                  <br>


                                  <div class="row" style="border:2px solid grey; border-radius: 15px;padding: 10px;">
                                    <h1 style="width: 144px; font-size:18px; margin-top:-12px; margin-left:7px; background:white;">DESCUENTOS </h1>


                                    <table style="color:black;">
                                      <th>
                                      <tr>
                                        <td>CONCEPTO</td><td>TASA %</td><td>IMPORTE</td>
                                        <td>SEGURO: <label style="font-size: 14px;" name="nombreseg" 
                                          id="nombreseg"></label></td>
                                      </tr>
                                    </th>

                          <tr>
                          <td>Aporte Oblig. AFP</td>
                           <td><input type="text" class="form-control" name="tasaafp" id="tasaafp" placeholder="Tasa" readonly></td>
                           <td><input type="text" class="form-control" name="importetasa" id="importetasa" readonly ></td>
                           <td></td>
                          </tr>


                          <tr>
                          <td>Invalidez y sobrevivencia</td>
                           <td><input type="text" class="form-control" name="tasais" id="tasais"  readonly></td>
                           <td><input type="text" class="form-control" name="importetasais" id="importetasais" readonly ></td>
                           <td></td>
                          </tr>



                          <tr>
                          <td>Comisión AFP</td>
                           <td><input type="text" class="form-control" name="tasacomi" id="tasacomi"  readonly></td>
                           <td><input type="text" class="form-control" name="importetasacomi" id="importetasacomi" readonly></td>
                           <td></td>
                          </tr>


                           <tr>
                          <td colspan="2" align="right">Retención 5ta Categ.</td>
                           <td><input type="text" class="form-control" name="importe5t" id="importe5t" onchange="calculardctos();" ></td>
                           <td></td>
                          </tr>


                          <tr>
                          <td>SIST. NAC. PENSIÓN</td>
                           <td><input type="text" class="form-control" name="tasasnp" id="tasasnp"  readonly></td>
                           <td><input type="text" class="form-control" name="importesnp" id="importesnp"  readonly></td>
                           <td></td>
                          </tr>




                          <tr>
                          <td>TOTAL DCTOS:</td>
                           <td><input type="text" class="form-control" name="totaldescu" id="totaldescu" readonly></td>
                           <td align="right"><h2>SALDO A PAGAR S/</h2></td>
                           <td><input type="text" class="form-control" name="saldopagar" id="saldopagar" readonly style="color: blue; font-size: 18px;"   ></td>
                          </tr>

                                    </table>
                                  </div>

                                  <br>

                                  <div class="row" style="border:2px solid grey; border-radius: 15px;">
                                    <h1 style="width: 144px; font-size:18px; margin-top:-12px; margin-left:7px; background:white;">FECHA DE PAGO </h1>
                                     <table style="color:black;">
                                      <th>
                                      <tr>
                                        <td><input class="form-control" type="date" name="fechapagoboleta"  id="fechapagoboleta"> </td><td></td><td></td><td></td>
                                      </tr>
                                      <tr>
                                        <td><h2 id="fechapago" name="fechapago"> </h2></td>
                                      </tr>
                                    </th>
                                  </table>
                                  </div>




                                  <br>


                                  <div class="row" style="border:2px solid grey; border-radius: 15px;">
                                    <h1 style="width: 200px; font-size:18px; margin-top:-12px; margin-left:7px; background:white;">APORTES DE LA EMPRESA: </h1>


                                    <table style="color:black;">
                                      <th>
                                      <tr>
                                        <td>CONCEPTO</td><td>TASA %</td><td>IMPORTE</td><td></td>
                                      </tr>
                                    </th>

                          <tr>
                          <td>ESSALUD</td>
                           <td><input type="text" class="form-control" name="tasaessa" id="tasaessa"  value="9.00"  readonly></td>
                           <td><input type="text" class="form-control" name="importeessa" id="importeessa" readonly ></td>
                           <td></td>
                          </tr>


                          <tr>
                          <td>TOTAL S/:</td>
                           <td><input type="text" class="form-control" name="totalessa" id="totalessa" readonly></td>
                           <td></td>
                           <td></td>
                          </tr>

                                    </table>
                                  </div>

                                  <br>
                        




                                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>

                                     <button class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                                    </div>
                                
                                 </form>

                            </div>

                         
                            </div>
                            <!--Fin centro -->
                          </div><!-- /.box -->
                      </div><!-- /.col -->
                  <!--</div> /.row -->
              </section><!-- /.content -->

            </div><!-- /.content-wrapper -->
          <!--Fin-Contenido-->


          <!-- Modal -->
          <div class="modal fade" id="modalPreview2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 100% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">BOLETA DE PAGO</h4>
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
    <script type="text/javascript" src="scripts/boletapago.js"></script>
    <?php
}
ob_end_flush();
?>