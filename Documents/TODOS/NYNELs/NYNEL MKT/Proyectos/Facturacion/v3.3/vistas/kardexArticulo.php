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
            <!-- <link rel="stylesheet" href="carga.css" > -->
            <div class="loader" id="ld" name="ld"></div>
            <div class="content-start transition">

              <!-- Main content -->
              <section class="container-fluid dashboard">
                <div class="content-header">
                  <h1>Kardex de artículo</h1>
                  <p>El rango de fechas debe ser del mismo año, no funciona con años diferentes.</p>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">


                        <div class="panel-body" id="formularioconsultas">

                          <form action="../reportes/kardexArticulo.php" method="POST" target="_blank">

                            <div class="row justify-content-center text-center ">

                              <script>
                                $(document).ready(function () {
                                  var fechaActual = new Date().toISOString().split('T')[0];
                                  $("#fecha1").val(fechaActual);
                                  $("#fecha2").val(fechaActual);
                                });

                              </script>

                              <div class="mb-3 col-lg-3">
                                <label> Fecha 1: </label>
                                <input type="date" name="fecha1" id="fecha1" class="form-control" onchange="">
                              </div>

                              <div class="mb-3 col-lg-3">
                                <label> Fecha 2: </label>
                                <input type="date" name="fecha2" id="fecha2" class="form-control" onchange="">
                              </div>


                              <div style="color:black;" class="mb-3 col-lg-3" id="consuxaño">

                                CÓDIGO <input type="checkbox" name="chk1" id="chk1" onclick="selectO();" data-toggle="tooltip"
                                  title="Escoger opción"> TODOS
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                  <select class="form-control select-picker" data-live-search="true" id="codigoInterno"
                                    name="codigoInterno"> </select>
                                </div>
                              </div>
                              <!-- <input type="hidden" name="xocdTods" id="xocdTods"> -->

                              <input type="hidden" name="fechas" id="fechas" value="01,02,03,04,05,06,07,08,09,10,11,12">
                              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <button class="btn btn-info" type="button" name="calcular" onclick="calcularkardexentrefechas();">1
                                  CALCULAR</button>
                                <!-- <button class="btn btn-primary" type="submit" name="reporte" > 2 REPORTE</button> -->
                                <button class="btn btn-danger" onclick="resetearvalores()" type="button"><i class="fa fa-exit"></i>
                                  RESETEAR VALORES</button>
                                <button class="btn btn-success" onclick="mostraractual()" type="button">MOSTRAR ACTUAL</button>
                              </div>

                            </div>

                          </form>


                          <div class="table-responsive" id="listadoregistros">
                            <table>
                              <tbody>

                              </tbody>
                            </table>
                            <table id="tbllistadokardex" class="table table-striped" style="width: 100% !important;">
                              <thead>
                                <th>Código artículo </th>
                                <th>Nombre </th>
                                <th>Año</th>
                                <th>Costo Compra</th>
                                <th>Saldo inicial</th>
                                <th>Total compra</th>
                                <th>Total venta</th>

                                <th>Valor inicial</th>

                                <th>Stock Actual</th>
                                <th>Costo final</th>
                                <th>Valor final</th>
                                <th>...</th>


                              </thead>
                              <tbody>
                              </tbody>

                            </table>
                          </div>


                          <!--   <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <a href="../reportes/kardexArticulo.php?param1=$codigo"> <button class="btn btn-info"> Reporte </button>
            </a>

            </div> -->

                        </div>
                        <!--Fin centro -->
                      </div><!-- /.box -->
                    </div><!-- /.col -->
                  </div><!-- /.row -->
              </section><!-- /.content -->

            </div><!-- /.content-wrapper -->
            <!--Fin-Contenido-->
            <?php

    //$codigoInterno=$_POST['codigoInterno'];
    //$fechas=$_POST['fechas'];
    //$mes=$_POST['mes'];
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