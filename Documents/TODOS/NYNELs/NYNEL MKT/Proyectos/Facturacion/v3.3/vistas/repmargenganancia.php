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
            <div class="content-start transition">
              <div class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Reporte de margen de ganancia bruta</h1>
                </div>

                <div class="row">

                  <div class="col-12 col-md-6 col-lg-12">
                    <div class="card">
                      <form name="formulario" id="formulario" action="../reportes/repmargenganancia.php" method="POST"
                        target="_blank">

                        <div class="card-body">
                          <div class="row">

                            <div class="mb-3 col-lg-2">
                              <label>Opción</label>
                              <select class="form-control" id="opcion1" name="opcion1">
                                <!-- <option value="xcodigo">Por código</option> -->
                                <option value="general">Todos</option>
                              </select>
                            </div>
                            <div hidden class="mb-3  col-lg-2">
                              <label>Seleccionar almacén</label>
                              <select class="form-control" id="almacenlista" onchange="actualizarartialma()"> </select>
                            </div>
                            <div hidden class="mb-3  col-lg-4">
                              <label>Artículo</label>
                              <select class="form-control" id="codigoInterno" name="codigoInterno"> </select>
                            </div>
                            <div class="mb-3 col-lg-2">
                              <label>Año</label>
                              <select class="form-control" name="ano" id="ano" onchange="calcularmargeng()">

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
                            <div class="mb-3 col-lg-2">
                              <label>Mes</label>
                              <select class="form-control" name="mes" id="mes" onchange="calcularmargeng()">
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

                          </div>

                          <div class="table-responsive" id="listadoregistros">
                            <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                              <thead>
                                <tr>
                                  <th>ARTÍCULO</th>
                                  <th>TOTAL VENTAS S/</th>
                                  <th>TOTAL COMPRA S/</th>
                                  <th>GANANCIA S/</th>
                                  <th>%</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>

                                </tr>
                              </tbody>
                            </table>

                          </div>

                        </div>


                        <div class="card-footer text-right">
                          <button hidden type="button" id="btncalcular" onclick="calcularmargeng()"
                            class="btn btn-primary">Calcular</button>
                          <button class="btn btn-primary" type="submit" id="btnGenerar">Generar Reporte</button>
                          <button class="btn btn-danger" onclick="cancelarform()" type="button">Cancelar</button>
                        </div>
                      </form>
                    </div>
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
      <script type="text/javascript" src="scripts/repmargenganancia.js"></script>

      <?php
}
ob_end_flush();
?>