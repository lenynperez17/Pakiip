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
          <h1>Libros electrónicos (PLE - VENTAS)</h1>
        </div>

        <div class="row">

          <div class="col-xl-6 col-md-8 col-sm-12">
            <div class="card">

              <form name="formulario" id="formulario" action="../modelos/Ple.php" method="POST">

                <div class="card-body">
                  <div class="row">
                    <div class="mb-3 col-lg-6">
                      <label>Año</label>
                      <select class="form-control" name="ano" id="ano" onchange="">
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
                        <option value="2030">2030</option>
                        <option value="2031">2031</option>
                        <option value="2032">2032</option>
                        <option value="2033">2033</option>
                        <option value="2034">2034</option>
                        <option value="2035">2035</option>
                        <option value="2036">2036</option>
                        <option value="2037">2037</option>
                        <option value="2038">2038</option>
                        <option value="2039">2039</option>
                        <option value="2040">2040</option>

                      </select>
                      <input type="hidden" name="ano_1" id="ano_1">
                    </div>
                    <div class="mb-3 mb-3 col-lg-6">
                      <label>Mes</label>
                      <select class="form-control" name="mes" id="mes" onchange="">
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
                    <div class="mb-3 col-lg-6">
                      <label>Tipo</label>
                      <select class="form-control" name="tipo" id="tipo" onchange="">
                        <option value="ventas" onfocus="">Ventas</option>
                      </select>
                      <input type="hidden" name="tipo_1" id="tipo_1">
                    </div>
                    <div class="mb-3 col-lg-6">
                      <label>origen</label>
                      <select class="form-control" name="destino" id="destino">
                        <option value="01">LOCAL</option>
                        <option value="02">REMOTO</option>
                      </select>
                      <input type="hidden" name="mes_1" id="mes_1">
                    </div>
                  </div>

                </div>
                <div class="card-footer text-right">
                  <button type="submit" id="btngenerar" class="btn btn-primary">Generar</button>
                </div>
              </form>

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
  <!-- <script type="text/javascript" src="scripts/inventario.js"></script> -->
  <?php
}
ob_end_flush();
?>