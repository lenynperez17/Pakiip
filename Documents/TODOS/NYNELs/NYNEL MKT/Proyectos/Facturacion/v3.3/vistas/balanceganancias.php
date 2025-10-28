<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['inventarios'] == 1) {
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
                <div class="content-header">
                  <h1>Balance de ganancias y pérdidas</h1>
                </div>

                <div class="row">

                <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">

                        <div class="row">
                            <div class="col-lg-4">
                                <label for="message-text" class="col-form-label">Selecciona un vendedor:</label>
                                <select  class="form-select" name="vendedorsitio" id="vendedorsitio" onchange="listarVentasVendedor()">
                
                                </select>
                            </div>
                            <div class="col-lg-4">
                            <label for="message-text" class="col-form-label">Filtra por año:</label>
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

                            <div class="col-lg-4">
                            <label for="message-text" class="col-form-label">Filtra por mes:</label>
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

                        </div>

                      </div>
                    </div>
                  </div>

                <div class="col-md-6 col-lg-3">
                <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-inbox icon-home bg-primary text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total de Facturas</p>
                            <h5 id="Tfactura">0.00</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-clipboard-list icon-home bg-success text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total de Boletas</p>
                            <h5 id="Tboleta">0.00</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
          
                                                 
                  <div class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-chart-bar  icon-home bg-info text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total Nota de Venta</p>
                            <h5 id="Tnota">0.00</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-chart-bar  icon-home bg-info text-danger"></i>
                          </div>
                          <div class="col-8">
                  
                                              
                            <p>Total Compras</p>
                            <h5 id="Tcompra">0.00</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-clipboard-list icon-home bg-success text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total Gasto</p>
                            <h5 id="Tgasto">S/<?php echo number_format($totalvboletahoy, 2); ?></h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
          
                
                  <div class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-inbox icon-home bg-primary text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total Planilla</p>
                            <h5 class="Tplanilla">0.00</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div hidden class="col-md-6 col-lg-3">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-4 d-flex align-items-center">
                            <i class="fas fa-id-card  icon-home bg-warning text-light"></i>
                          </div>
                          <div class="col-8">
                            <p>Total en saldos</p>
                            <h5 id="Tsaldo">0.00</h5>
                          </div>
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

        <script type="text/javascript" src="scripts/ventasvendedor.js"></script>

    

    <?php

}
ob_end_flush();
?>