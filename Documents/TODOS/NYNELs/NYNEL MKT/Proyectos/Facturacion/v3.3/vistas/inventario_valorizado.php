<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['almacen'] == 1) {
    ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <div class="content-start transition">
        <div class="container-fluid dashboard">
              <div class="content-header">
                <h1>Inventario valorizado</h1>
                <p>Genera el reporte muy rapidamente</p>
              </div>
            
                    <div class="row">

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="card">
                           <form name="formulario" id="formulario" action="../reportes/InventarioValorizado.php" method="POST" target="_blank">
                              <div class="card-header">
                                <h4>Opciones de busqueda</h4>
                              </div>
                              <div class="card-body">
                                <div class="mb-3" style="display: block ruby;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="opcion1" value="general" onclick="if(this.checked == true){entrada.disabled=true }">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                General
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="opcion1" value="codigointerno" onclick="if(this.checked == true){entrada.disabled=false  } else { entrada.disabled=true }">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Código
                                            </label>
                                        </div>
                                
                            
                                </div>
                                <div class="row">
                                <div class="mb-3 col-lg-6">
                                  <label>Código</label>
                                  <input type="text" class="form-control" name="entrada" id="entrada">
                                </div>
                                <div class="mb-3  col-lg-6">
                                  <label>Año</label>
                                  <select class="form-control" name="ano" id="ano" >

                                    <option value="0">AÑO</option>
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
             
                              </div>
                              <div class="card-footer text-right">
                                <button class="btn btn-primary" type="submit" id="btnGenerar">Generar</button>
                                <button class="btn btn-danger" type="button" onclick="cancelarform()">Cancelar</button>
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

    <!-- <script type="text/javascript" src="scripts/inventario.js"></script> -->
    <?php
}
ob_end_flush();
?>