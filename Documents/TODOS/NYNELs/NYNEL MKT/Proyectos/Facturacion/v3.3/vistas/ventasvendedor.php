<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Contabilidad'] == 1) {
    require_once "../modelos/Venta.php";
    $venta = new Venta();

    $rspta = $venta->totalVentasVendedorAno($_SESSION['idempresa']);
    $vendedor = '';
    $totalv = '';
    while ($reg = $rspta->fetch_object()) {
      $vendedor = $vendedor . '"' . $reg->vendedorsitio . '",';
      $totalv = $totalv . $reg->totalv . ',';
    }
    $vendedor = substr($vendedor, 0, -1);
    $totalv = substr($totalv, 0, -1);

    ?>

            <div class="content-start transition">
              <div class="container-fluid dashboard">
                <!-- <div class="content-header">
          <h1>Ventas por vendedor</h1>
        </div> -->



                <div class="row">
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-header">
                        <h4 class="card-title">Ventas por vendedor del año actual</h4>
                      </div>
                      <div class="card-body">
                        <div class="chartjs-size-monitor">
                          <div class="chartjs-size-monitor-expand">
                            <div class=""></div>
                          </div>
                          <div class="chartjs-size-monitor-shrink">
                            <div class=""></div>
                          </div>
                        </div>
                        <canvas id="ventas" style="display: block; width: 735px; height: 367px;" class="chartjs-render-monitor"
                          width="735" height="367"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-header">
                        <h4 class="card-title">Filtros</h4>
                        <button type="button" id="reporteVendedorMensual" class="btn btn-success">Reporte mensual</button>
                        <button type="button" id="reporteVendedorDiario" class="btn btn-primary">Reporte diario</button>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="mb-3 col-lg-4">
                            <label>Vendedor</label>
                            <select class="form-control" name="vendedorsitio" id="vendedorsitio"
                              onchange="listarVentasVendedor()"></select>
                          </div>
                          <div class="mb-3 col-lg-2">
                            <label>Mes</label>
                            <select class="form-control" name="mes" id="mes" onchange="listarVentasVendedor()">
                              <option value="00">Niguno</option>
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
                          <div class="mb-3 col-lg-2">
                            <label>Día</label>
                            <select class="form-control" name="dia" id="dia" onchange="listarVentasVendedor()">
                              <option value="00">Ninguno</option>
                            </select>
                          </div>

                          <div class="mb-3 col-lg-4">
                            <label>Año</label>
                            <select class="form-control" name="ano" id="ano" onchange="listarVentasVendedor()">

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

                          <div class="mb-3 col-lg-4">
                            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                            <label>Total Boleta</label>
                            <h4 style="font-size:30px;">
                              <strong id="Tboleta">0.00</strong>
                            </h4>
                          </div>


                          <div class="mb-3 col-lg-4">
                            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                            <label>Total Factura</label>
                            <h4 style="font-size:30px;">
                              <strong id="Tfactura">0.00</strong>
                            </h4>
                          </div>

                          <div class="mb-3 col-lg-4">
                            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                            <label>Total Nota de venta</label>
                            <h4 style="font-size:30px;">
                              <strong id="Tnota">0.00</strong>
                            </h4>
                          </div>

                        </div>

                      </div>
                    </div>
                  </div>
                </div>





              </div><!-- /.row -->


            </div><!-- End Container-->
            </div><!-- End Content-->

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>



            <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
      <!-- <script src="../public/js/chart.min.js"></script> -->
      <script src="../public/js/Chart.bundle.min.js"></script>
      <script type="text/javascript" src="scripts/ventasvendedor.js"></script>

      <script type="text/javascript">
        function reloadPage() {
          location.reload(true)
        }
        var fecha = new Date();
        var ano = fecha.getFullYear();

        var ctx = document.getElementById("ventas").getContext('2d');
        var ventas = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: [<?php echo $vendedor; ?>],
          datasets: [{
            label: 'Ventas por vendedor del año ' + ano,
            data: [<?php echo $totalv; ?>],
            backgroundColor: [
              'rgba(54, 162, 235, 0.2)',
              'rgba(255, 99, 132, 0.2)',
              'rgba(255, 206, 86, 0.2)',
              'rgba(75, 192, 192, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(255, 159, 64, 0.2)',
              'rgba(255, 99, 132, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(255, 206, 86, 0.2)',
              'rgba(75, 192, 192, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(255,99,132,1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 159, 64, 1)',
              'rgba(255,99,132,1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              }
            }]
          }
        }
      });
    </script>

    <?php


}
ob_end_flush();
?>