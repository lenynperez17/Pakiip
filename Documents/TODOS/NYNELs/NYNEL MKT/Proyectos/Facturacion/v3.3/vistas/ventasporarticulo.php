<?php

//Activamos el almacenamiento del Buffer

ob_start();
session_start();



if (!isset($_SESSION["nombre"])) {

  header("Location: ../vistas/login.php");

} else {

  require 'header.php';



  if ($_SESSION['almacen'] == 1) {

    ?>

        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

        <!--Contenido-->

              <!-- Content Wrapper. Contains page content -->

              <div class="content-wrapper">        

                <!-- Main content -->

                <section class="content">
                    <div class="row">
                      <div class="col-md-12">
                          <div class="box">
                            <div class="box-header with-border">
                                  <h1 class="box-title" style="position: left;">VENTAS POR ARTÌCULOS</h1>
                                <div class="box-tools pull-right">
                                </div>
                            </div>

                            <!-- /.box-header -->

                            <!-- centro -->

        <div class="panel-body" id="formularioconsultas">
        <form  action="../reportes/kardexArticulo.php" method="POST" target="_blank"  autocomplete="off">

          <!-- <form name="formEnviar" id="formEnviar"> -->
            <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
            <label> Año: </label>
            <select class="" name="ano" id="ano" >
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


            <div class="form-group  col-lg-2 col-sm-12 col-md-12 col-xs-12">
                        <h4 class="modal-title">Almacen</h4>
                        <select class="form-control" id="almacenlista"  onchange="actualizarartialma()" >
                        </select>
           </div>

           <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">
            <label>Seleccion de código: </label>
            <select name="opcion" id="opcion"  >
              <option value="0">Por código</option>
              <option value="1">Todos</option>
            </select>
              </div>


           <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <label>Código: </label>
            <select class="form-control select-picker"  data-live-search="true" id="codigoInterno" name="codigoInterno"  
            onchange="mostrarumedidas()"> </select>
           </div>



        <input type="hidden" name="fechas" id="fechas" value="01,02,03,04,05,06,07,08,09,10,11,12" >
                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" type="submit" name="reporte" > 2 REPORTE</button>
                    <button class="btn btn-danger" onclick="resetearvalores()" type="button"><i class="fa fa-exit"></i> RESETEAR VALORES</button>
                    </div>
        </form>

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

