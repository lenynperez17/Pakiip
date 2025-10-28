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
        <div class="content-start transition ">
        <div class="container-fluid dashboard">

              <div class="content-header">
                <h1>Compras</h1>
                <p>Genera el reporte de todas tus compras</p>
              </div>
            
              <div class="row"> 

              <div class="card">
              <div class="card-body">
              <form name="formulario" id="formulario" action="../reportes/RegistroCompras.php" method="POST" target="_blank">

                              <div class="row ">

                              <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                                    <div class="mb-3 col-lg-3">
                                      <label>Año</label>
                                      <select class="form-control" name="ano" id="ano" onchange="regcompras()">

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
                                    <div class="mb-3 col-lg-3">
                                      <label>Mes</label>
                                      <select class="form-control" name="mes" id="mes" onchange="regcompras()">
                                        <option value="00">Ninguno</option>
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
                                      <input type="hidden" name="mes_1" id="mes_1">
                                    </div>

                                    <div class="mb-3 col-lg-3">
                                      <label>Moneda</label>
                                      <select class="form-control" name="moneda" id="moneda" onchange="regcompras()">
                                        <option value="USD">DOLARES</option>
                                        <option value="PEN">SOLES</option>
                                      </select>
                                  </div>

                                    <div class="mt-1 col-lg-3 card-footer">
                                    <button class="btn btn-primary" id="imprimir" onclick="return enviarCompras();">Submit</button>
                                    </div>
                          
                          
                     

                                  </div>

                                  <div class="table-responsive">
                                    <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                                      <thead>
                                        <tr>
                                          <th>DÍA</th>
                                          <th>TIP. DOCU</th>
                                          <th>SERIE</th>
                                          <th>NÚMERO</th>
                                          <th>RUC</th>
                                          <th>PROVEEDOR</th>
                                          <th>VALOR AFECTO</th>
                                          <th>IGV</th>
                                          <th>TOTAL</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <tr>

                                        </tr>
                                      </tbody>
                                      <tfoot>
                                        <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th>TOTALES</th>
                                          <th></th>
                                          <th></th>
                                          <th></th> 
                                        </tfoot>
                                    </table>

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
    <script type="text/javascript" src="scripts/inventario.js"></script>



    <?php
}
ob_end_flush();
?>