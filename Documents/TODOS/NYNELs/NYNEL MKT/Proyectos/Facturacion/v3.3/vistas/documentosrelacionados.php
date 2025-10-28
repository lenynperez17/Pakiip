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

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>


        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="">        
                <!-- Main content -->
                <section class="">
                      <div class="content-header">
                          <h1>Facturas / boleta anulados</h1>
                      </div>
                    <div class="row">
                    <div class="card">

                    <div class="card-body">

                        <div class="row">

                

                            <!-- /.box-header -->
                            <!-- centro -->

                            <div class="panel-body table-responsive" id="listadoregistros">
                              <table border="0" cellspacing="5" cellpadding="5">
                                  <tbody>
                                <div class="row justify-content-center text-center">

                      
                                    <div class="mb-3 col-lg-2">
            <label> Año: </label>
            <select class="form-control" name="ano" id="ano" onchange="listarDocRec()">

              <option value="2017">2017</option>
              <option value="2018">2018</option>
              <option value="2019">2019</option>
              <option value="2020">2020</option>
              <option value="2021">2021</option>
              <option value="2022">2022</option>
              <option value="2023">2023</option>
              <option value="2024">2024</option>
              <option value="2025">2025</option>
              <option value="2026">2026</option>
              <option value="2027">2027</option>
              <option value="2028">2028</option>
              <option value="2029">2029</option>
            </select>
            <input type="hidden" name="ano_1" id="ano_1">
          </div>

          <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">

         <div class="mb-3 col-lg-2">
            <label> Mes: </label>
            <select class="form-control" name="mes" id="mes" onchange="listarDocRec()">
              <option value="00">todos</option>
              <option value="1">Enero</option>
              <option value="2">Febrero</option>
              <option value="3">Marzo</option>
              <option value="4">Abril</option>
              <option value="5">Mayo</option>
              <option value="6">Junio</option>
              <option value="7">Julio</option>
              <option value="8">Agosto</option>
              <option value="9">Septiembre</option>
              <option value="10">Octubre</option>
              <option value="11">Noviembre</option>
              <option value="12">Diciembre</option>
            </select>
            <input type="hidden" name="mes_1" id="mes_1">
          </div> 
          <div class="mb-3 col-lg-2" >
                            <label>TIPO DE DOCUMENTO</label>
                              <select name="tipocomprobante" id="tipocomprobante" class="form-control" onchange="listarDocRec()">
                                <option value="01">FACTURA</option>
                                <option value="03">BOLETA</option>
                              </select>
                                </div>
          </div>
                              </tbody>


               

                                  <div class="table-responsive" id="listadoregistros">
                                      <table id="tbllistadoDR" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                                      <thead style="text-align:center;">
                                          <th>Fecha emisión</th>
                                          <th>Serie/Número</th>
                                          <th>Cliente</th>
                                          <th>R.U.C.</th>
                                          <th>Subtotal</th>
                                          <th>IGV</th>
                                          <th>Total</th>
                                          <th>Fecha baja</th>
                                          <th>Baja/Nota</th>
                                          <th>Vendedor</th>
                                          <th>...</th>
                                      </thead>
                                      <tbody style="text-align:center;">
                                      </tbody>

                                  </table>
                                  </div>

                    
                            </div>


                             <!-- Modal -->
          <div class="modal fade" id="ModalDocRel">
            <div class="modal-dialog" style="width: 50% !important;">
              <div class="modal-content">
                  <div class="modal-header">
                </div>
                  <div class="table-responsive">
                  <table id="tblaconsultadr" class="table table-striped table-bordered table-condensed table-hover" style="font-size: 14px">
                    <thead>
                        <th>Documento</th>
                        <th>Serie/número</th>
                        <th>Fecha emisión</th>
                        <th>Motivo</th>
                        <th>Subtotal</th>
                        <th>IGV</th>
                        <th>Total</th>
                    </thead>
                    <tbody>
               
                    </tbody>
                    <tfoot>
                      <th>Documento</th>
                        <th>Serie/número</th>
                        <th>Fecha emisión</th>
                        <th>Motivo</th>
                       <th>Subtotal</th>
                        <th>IGV</th>
                        <th>Total</th>
                    </tfoot>
                  </table>
                </div>
                  <div class="modal-footer">
                  <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>
                </div>        
             </div>
           </div>
          </div>



 

  

        <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>

    <script type="text/javascript" src="scripts/documentorelacionado.js"></script>


  <?php
}
ob_end_flush();
?>