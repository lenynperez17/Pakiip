<?php
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['Contabilidad'] == 1) {
    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

        <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Ventas por cliente</h1>
                </div>

                <div class="row">

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body"> 

                        <div class="panel-body" id="formularioconsultas">
                           <form name="formulario" id="formulario" action="../reportes/ventasxcliente.php" method="POST" target="_blank">

                           <div class="row justify-content-center text-center">

                           <div class="mb-3 col-lg-3">
                            <input type="radio" name="tipo" value="RUC" checked onclick="limpiarFac()">&nbsp;<label>RUC</label>&nbsp;&nbsp;&nbsp;&nbsp; 
                              <input type="radio" name="tipo" value="RUCAG" onclick="limpiarFac()">&nbsp;<label>RUC AGRUPADO</label>
                              &nbsp;&nbsp;&nbsp;&nbsp;
                              <input type="radio" name="tipo" value="DNI" onclick="limpiarBol()">&nbsp;<label>DNI</label>
                            </div>
                  

                           <div class="input-group mb-3 col-lg-3">

                            <select class="form-control selectpicker" data-live-search="true" name="nruc" id="nruc" onchange="listarventasxruc()"></select>
                            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                            <button class="btn btn-primary" type="submit" id="btnconsulta" > <i class="fa fa-print" data-toggle="tooltip" title="Consultar">Reporte </i></button>                    
                           </div>

                           </div>
                           <div class="row justify-content-center text-center">

                           <div class="mb-3 col-lg-3">
                           <label> Mes: </label>
                              <select class="form-control" name="mesr" id="mesr" onchange="listarventasxruc()">
                                <option value="'01','02','03','04','05','06','07','08','09','10', '11','12'">Todos los meses</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                              </select>
                           </div>

                           <div class="mb-3 col-lg-3">
                           <label> Año: </label>
                                  <select class="form-control" name="anor" id="anor" onchange="listarventasxruc()">
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
                           </div>
                    
                           </div>

                           </form>

                        </div>

                        <div class="table-responsive">
                          <table id="tablar" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    <th>Nùmero</th>
                                    <th>Fecha emisiòn</th>
                                    <th>Código</th>
                                    <th>Artículo</th>
                                    <th>Cantidad</th>
                                    <th>Gravada</th>
                                    <th>Igv</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>

                              </tr>
                            </tbody>
                          </table>

                        </div>
                      </div>
                    </div>
                  </div>



                </div><!-- /.row -->


              </div><!-- End Container-->
            </div><!-- End Content-->


        <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';

  ?>
    <script type="text/javascript" src="scripts/ventasxcliente.js"></script>
    <?php

}
ob_end_flush();
?>
