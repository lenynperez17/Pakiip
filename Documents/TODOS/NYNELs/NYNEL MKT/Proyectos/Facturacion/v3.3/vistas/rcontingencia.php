<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['acceso'] == 1) {
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
                                  <h1 class="box-title"> RESUMEN DE CONTINGENCIA. 
                                 </h1>
                                <div class="box-tools pull-right">
                                </div>
                            </div>

                            <!-- /.box-header -->
                            <!-- centro -->


          <div class="panel-body" style="height: 400px;" id="formularioregistros">
               <form name="formulario" id="formulario" method="POST" >

                    <h1 style="font-size: 22px"> Cabecera del archivo a crear.</h1><br>

        <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">

          <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
         <input type="text"  name="rucemisor" id="rucemisor"  value="" placeholder="Numero de RUC de la empresa" size="11" class="form-control">
        </div>

        <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
         <input type="hidden" name="rf" id="rf" value="RF" class="form-control">
         <input type="input"  name="fechag" id="fechag" class="form-control" >
        <input type="hidden" name="ncor" id="ncor" value="01">
        </div>

        <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
        <label>FORMATO: RUCEMPRESA-RF-FECHARESUMEN-01.txt</label>
        </div>

        </div>

        <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
         <label>Motivo de contingencia</label>
         <select  class="form-control" name="motivoc" id="motivoc" >
          <option value="1">Conexión internet.</option>
          <option value="2">Fallas de fluido eléctrico.</option>
          <option value="3">Desastres naturales.</option>
          <option value="4">Robo.</option>
          <option value="5">Fallas en el sistema de facturación.</option>
          <option value="6">Venta Itinerante.</option>
          <option value="7">Otros.</option>
        </select>

        </div>
        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Fecha emisión de comprobante</label>
        <input type="input"  class="form-control" name="fechacomp" id="fechacomp" placeholder="DD/MM/YYYY ">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Tipo comprobante de pago</label>
        <input type="input"  class="form-control" name="tipocp" id="tipocp" placeholder="01 o 03 o 07 o 08">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Número de serie del comprobante de pago</label>
        <input type="input"  class="form-control" name="seriecp" id="seriecp" placeholder="001">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Número correlativo del comprobante de pago</label>
        <input type="input"  class="form-control" name="numerocp" id="numerocp" placeholder="000000">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Número final de rangos de tickets</label>
        <input type="input"  class="form-control" name="numeroft" id="numeroft" value="0">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Tipo de documento identidad de cliente</label>
        <input type="input"  class="form-control" name="tipodc" id="tipodc" placeholder="0 o 1 o 6 o 7">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Número de documento identidad de cliente</label>
        <input type="input"  class="form-control" name="numerodc" id="numerodc" placeholder="###########" size="15">
        </div>

        <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">
          <label>Apellidos y nombres o denominación o razón social del cliente</label>
        <input type="input"  class="form-control" name="nombrecli" id="nombrecli" placeholder="Cliente" >
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Total valor de venta de ope. grav. </label>
        <input type="input"  class="form-control" name="totalvvg" id="totalvvg" placeholder="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Total valor de venta de ope. exo. </label>
        <input type="input"  class="form-control" name="totalvve" id="totalvve" value="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Total valor de venta de ope. inaf. </label>
        <input type="input"  class="form-control" name="totalvoi" id="totalvoi" value="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Impuesto selectivo al consumo - ISC </label>
        <input type="input"  class="form-control" name="isc" id="isc" value="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Impuesto general a las ventas - IGV </label>
        <input type="input"  class="form-control" name="igv" id="igv" placeholder="0.00">
        </div>


        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Otros tributos y cargos que no forma parte. </label>
        <input type="input"  class="form-control" name="otrosc" id="otrosc" value="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Importe total del comprobante de pago. </label>
        <input type="input"  class="form-control" name="total" id="total" placeholder="0.00">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Tipo de comp. de pago que se modifica. </label>
        <input type="input"  class="form-control" name="tipocpm" id="tipocpm" placeholder="01 o 03 o 07 o 08">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Serie del comp. de pago que se modifica. </label>
        <input type="input"  class="form-control" name="seriecpm" id="seriecpm" placeholder="001">
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
          <label>Número de comp. de pago que se modifica. </label>
        <input type="input"  class="form-control" name="numerocpm" id="numerocpm" placeholder="##########">
        </div>

        <br>
                            
                                   
                                    <button id="btnAgregar" type="button" class="btn btn-primary" onclick="agregarComprobante()">Agregar comprobante <span class="fa fa-plus"></span> </button>
                           

                                  <br>
                                  <br>
         <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                  <div class="table-responsive">
                                    <table id="detalles" class="table table-striped table-hover table-bordered" >
                              <thead style="background-color:#35770c; color: #fff; text-align: justify;">

                                            <th >-</th>
                                            <th >Motivo</th>
                                            <th >Fecha Emisión</th>
                                            <th >Tipo comp.</th>
                                            <th >Serie</th>
                                            <th >Número</th>
                                            <th >Num. final</th>
                                            <th >Tipo doc. cli.</th>
                                            <th >Num. doc. cli.</th>
                                            <th >Nombre o razón s.</th>
                                            <th >T. v. grav.</th>
                                            <th >T. v. exo.</th>
                                            <th >T. v. ina.</th>
                                            <th >Imp. ISC</th>
                                            <th >IGV</th>
                                            <th >Ot. carg.</th>
                                            <th >Imp. total</th>
                                            <th >Tip. doc. mod.</th>
                                            <th >Ser. doc. mod.</th>
                                            <th >Num. doc. mod.</th>
                                    
                              </thead>

                                        <tbody>
                                        </tbody>
                                    </table>
                                  </div>
                                  </div>

                            </div>



    

                                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    </a><button class="btn btn-primary" type="submit" id="btnGuardar" ><i class="fa fa-save"></i></button>
 
                                    <button id="btnCancelar" class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i></button>

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
 
  
 
  

        <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>

    <script type="text/javascript" src="scripts/rcontingencia.js"></script>


    <!-- <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.js"></script> -->

  <?php
}
ob_end_flush();
?>