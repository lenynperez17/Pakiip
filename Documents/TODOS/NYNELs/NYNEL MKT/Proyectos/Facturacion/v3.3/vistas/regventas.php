<?php

//Activamos el almacenamiento del Buffer
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
        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-start transition">        
                <!-- Main content -->
                <section class="container-fluid dashboard">
                <div class="content-header">
                  <h1>REGISTRO DE VENTAS TOTALES POR DÍA/MES/AÑO</h1>
                </div>
                    <div class="row">
                      <div class="col-md-12">
                      <div class="card">
                      <div class="card-body">




                    
        <div class="panel-body"  id="formularioregistros">
          <form name="formulario" id="formulario" action="../reportes/RegistroVentasAgrupado.php" method="POST" target="_blank">
            <div class="row justify-content-center text-center">

    
            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
            <label> Año: </label>
            <select class="form-control" name="ano" id="ano" onchange="regvenagruxdia()">

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

         <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
            <label> Mes: </label>
            <select class="form-control" name="mes" id="mes" onchange="regvenagruxdia()">
              <option value="00">Ninguno</option>
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
            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
  
          </div> 



            <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">
            <label> Moneda: </label>
            <select class="form-control" name="tmonedaa" id="tmonedaa" onchange="regventas()">
              <option value="PEN">PEN</option>
              <option value="USD">USD</option>
           </select>
          </div> 



        <div class="form-group col-lg-2 col-md-6 col-sm-4 col-xs-12" style="top: 18px;position: relative;">
          <button type="submit" class="btn btn-danger btn-sm" id="btnagregarPDF" 
          onclick="this.form.action='../reportes/RegistroVentasAgrupado.php'">REPORTE PDF</button>
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-4 col-xs-12" style="top: 18px;position: relative;">
          <button type="submit" class="btn btn-success btn-sm" id="btnagregarEXCEL"
          onclick="this.form.action='../reportes/RegistroVentasxdiaExcel.php'">REPORTE EXCEL DÍA</button>
        </div>

        <div class="form-group col-lg-2 col-md-6 col-sm-4 col-xs-12" style="top: 18px;position: relative;">
        <button type="submit" class="btn btn-success btn-sm" id="btnagregarEXCEL"
          onclick="this.form.action='../reportes/ventasexcelano.php'">REPORTE EXCEL AÑO</button>
        </div>


          <!-- centro -->
          <div class="table-responsive" id="listadoregistros">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                                  <thead>
                                    <th>FECHA</th>
                                    <th>DOCUMENTO</th>
                                    <th>VALOR AFECTO</th>
                                    <th>IGV</th>
                                    <th>TOTAL</th>
                                    <th>TIPO</th>
                                  </thead>
                                  <tbody>
                                  </tbody>
                              <tfoot style="background-color:black;">
                                    <th>TOTALES</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                              </tfoot>
                                </table>
                            </div>
                  
                          </div>
                       </form>
                     </div>
                            <!--Fin centro -->
                          </div><!-- /.box -->
                      </div><!-- /.col -->
                  </div><!-- /.row -->
              </section><!-- /.content -->

            </div>




        <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
    <script type="text/javascript" src="scripts/inventario.js"></script>



    <?php
}
ob_end_flush();
?>