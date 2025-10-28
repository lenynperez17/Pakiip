<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['acceso'] == 1) {
    ?>

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-start">
      <!-- Main content -->
      <section class="container-fluid">
        <div class="row">
          <div class=" form-group col-lg-12 col-md-12 col-lg-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">CONFIGURACIÓN DE RUTAS DE ACCESO Y ALMACENAMIENTO.</h3>
                <button class="btn btn-info" id="btnagregar" onclick='mostrarform(true)'><i class="fa fa-newspaper-o"></i>
                  Nuevo</button>

              </div>

              <div class="panel-body table-responsive" id="listadoregistros">
                <table border="0" cellspacing="5" cellpadding="5">
                  <tbody>
                  </tbody>
                </table>
                <table id="tbllistado" class="table table-striped table-bordered table-condensed  table-hover">
                  <thead>
                    <th>...</th>
                    <th>Data</th>
                    <th>Firma</th>
                    <th>Envio</th>
                    <th>Respuesta</th>
                    <th>Resp. Descomprimida</th>
                  </thead>
                  <tbody>
                  </tbody>

                </table>
              </div>



              <div class="panel-body" id="formularioregistros">
                <form name="formulario" id="formulario" method="post">

                  <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
                  <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i></button>
                    <button id="btnCancelar" class="btn btn-danger" data-toggle="tooltip" title="Cancelar"
                      onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"> Cancelar</i></button>
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <input type="hidden" name="idruta" id="idruta">
                    <label>Ruta de carpeta DATA</label>
                    <select name="rutadata" id="rutadata">
                      <option value="C:/sfs/data/">C:/sfs/data/</option>
                      <option value="C:/sfs/data/">C:/sfs/data/</option>
                      <option value="..\sfs\data\">..\sfs\data\</option>
                      <option value="../sfs/data/">../sfs/data/</option>
                      <option value="../sfs/sucursales/data/">../sfs/sucursales/data/</option>
                    </select>

                    <!-- <input type="text" name="rutadata" id="rutadata" value="../sfs/data/" class="">  -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta DATA ALTERNA </label>
                    <select name="rutadatalt" id="rutadatalt">
                      <option value="C:/sfs/dataalterna/">C:/sfs/dataalterna/</option>
                      <option value="..\sfs\dataalterna\">..\sfs\dataalterna\</option>
                      <option value="../sfs/dataalterna/">../sfs/dataalterna/</option>
                      <option value="../sfs/sucursales/dataalterna/">../sfs/sucursales/dataalterna/</option>
                    </select>
                    <!-- <input type="text" name="rutadatalt" id="rutadatalt" value="../sfs/dataalterna/" class="">  -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta FIRMA</label>
                    <select name="rutafirma" id="rutafirma">
                      <option value="C:/sfs/firma/">C:/sfs/firma/</option>
                      <option value="..\sfs\firma\">..\sfs\firma\</option>
                      <option value="../sfs/firma/">../sfs/firma/</option>
                      <option value="../sfs/sucursales/firma/">../sfs/sucursales/firma/</option>
                    </select>
                    <!-- <input type="text" name="rutafirma" id="rutafirma" value="../sfs/firma/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta ENVIO</label>
                    <select name="rutaenvio" id="rutaenvio">
                      <option value="C:/sfs/envio/">C:/sfs/envio/</option>
                      <option value="..\sfs\envio\">..\sfs\envio\</option>
                      <option value="../sfs/envio/">../sfs/envio/</option>
                      <option value="../sfs/sucursales/envio/">../sfs/sucursales/envio/</option>
                    </select>
                    <!-- <input type="text" name="rutaenvio" id="rutaenvio" value="../sfs/envio/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta RPTA</label>
                    <select name="rutarpta" id="rutarpta">
                      <option value="C:/sfs/rpta/">C:/sfs/rpta/</option>
                      <option value="..\sfs\rpta\">..\sfs\rpta\</option>
                      <option value="../sfs/rpta/">../sfs/rpta/</option>
                      <option value="../sfs/sucursales/rpta/">../sfs/sucursales/rpta/</option>
                    </select>
                    <!-- <input type="text" name="rutarpta" id="rutarpta" value="../sfs/rpta/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta BAJA</label>
                    <select name="rutabaja" id="rutabaja">
                      <option value="C:/sfs/baja/">C:/sfs/baja/</option>
                      <option value="..\sfs\baja\">..\sfs\baja\</option>
                      <option value="../sfs/baja/">../sfs/baja/</option>
                      <option value="../sfs/sucursales/baja/">../sfs/sucursales/baja/</option>
                    </select>
                    <!-- <input type="text" name="rutabaja" id="rutabaja" value="../sfs/baja/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta RESUMEN CONTINGENCIAS</label>
                    <select name="rutaresumen" id="rutaresumen">
                      <option value="C:/sfs/resumen/">C:/sfs/resumen/</option>
                      <option value="..\sfs\resumen\">..\sfs\resumen\</option>
                      <option value="../sfs/resumen/">../sfs/resumen/</option>
                      <option value="../sfs/sucursales/resumen/">../sfs/sucursales/resumen/</option>
                    </select>
                    <!-- <input type="text" name="rutaresumen" id="rutaresumen" value="../sfs/resumen/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta DESCARGAS</label>
                    <select name="rutadescargas" id="rutadescargas">
                      <option value="C:/sfs/descargas/">C:/sfs/descargas/</option>
                      <option value="..\sfs\descargas\">..\sfs\descargas\/</option>
                      <option value="../sfs/descargas/">../sfs/descargas//</option>
                      <option value="../sfs/sucursales/descargas/">../sfs/sucursales/descargas/</option>
                    </select>
                    <!-- <input type="text" name="rutadescargas" id="rutadescargas" value="../sfs/descargas/" class=""> -->
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de carpeta PLE</label>
                    <select name="rutaple" id="rutaple">
                      <option value="C:/sfs/ple/">C:/sfs/ple/</option>
                      <option value="..\sfs\ple\">..\sfs\ple\</option>
                      <option value="../sfs/ple/">../sfs/ple/</option>
                      <option value="../sfs/sucursales/ple/">../sfs/sucursales/ple/</option>
                    </select>
                    <!-- <input type="text" name="rutaple" id="rutaple" value="../sfs/ple/" class=""> -->

                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de respuestas descomprimidas</label>
                    <select name="unziprpta" id="unziprpta">
                      <option value="C:/sfs/unziprpta/">C:/sfs/unziprpta/</option>
                      <option value="..\sfs\unziprpta\">..\sfs\unziprpta\</option>
                      <option value="../sfs/unziprpta/">../sfs/unziprpta/</option>
                      <option value="../sfs/sucursales/unziprpta/">../sfs/sucursales/unziprpta/</option>
                    </select>
                    <!-- <input type="text" name="unziprpta" id="unziprpta" value="../sfs/unziprpta/" placeholder="../sfs/unziprpta/" class=""> -->

                  </div>



                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de imagenes de art.</label>
                    <select name="rutaarticulos" id="rutaarticulos">
                      <option value="C:/sfs/files/articulos/">C:/sfs/files/articulos/</option>
                      <option value="..\files\articulos\">..\files\articulos\</option>
                      <option value="../files/articulos/">../files/articulos/</option>
                      <option value="../sfs/sucursales/files/articulos/">../sfs/sucursales/files/articulos/</option>
                    </select>
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta imagen logo</label>
                    <select name="rutalogo" id="rutalogo">
                      <option value="C:/sfs/files/logo/">C:/sfs/files/logo/</option>
                      <option value="..\files\logo\">..\files\logo\</option>
                      <option value="../files/logo/">../files/logo/</option>
                      <option value="../sfs/sucursales/files/logo/">../sfs/sucursales/files/logo/</option>
                    </select>
                  </div>

                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta de imagenes usuarios.</label>
                    <select name="rutausuarios" id="rutausuarios">
                      <option value="C:/sfs/files/usuarios/">C:/sfs/files/usuarios/</option>
                      <option value="..\files\usuarios\">..\files\usuarios\</option>
                      <option value="../files/usuarios/">../files/usuarios/</option>
                      <option value="../sfs/sucursales/usuarios/">../sfs/sucursales/usuarios/</option>
                    </select>
                  </div>


                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta salida facturas.</label>
                    <select name="salidafacturas" id="salidafacturas">
                      <option value="C:/sfs/facturasPDF/">C:/sfs/facturasPDF/</option>
                      <option value="..\facturasPDF\">..\facturasPDF\</option>
                      <option value="../facturasPDF/">../facturasPDF/</option>
                      <option value="../sfs/sucursales/facturasPDF/">../sfs/sucursales/facturasPDF/</option>
                    </select>
                  </div>


                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Ruta salida boletas.</label>
                    <select name="salidaboletas" id="salidaboletas">
                      <option value="C:/sfs/boletasPDF/">C:/sfs/boletasPDF/</option>
                      <option value="..\boletasPDF\">..\boletasPDF\</option>
                      <option value="../boletasPDF/">../boletasPDF/</option>
                      <option value="../sfs/sucursales/boletasPDF/">../sfs/sucursales/boletasPDF/</option>
                    </select>
                  </div>




                  <div class="form-group col-lg-4 col-md-12 col-lg-12 col-xs-12">
                    <label>Empresa</label>
                    <select name="empresa" id="empresa" class="form-control"></select>

                  </div>


                  <!--  <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                            <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i></button>
                            <a href="escritorio.php">
                            <button class="btn btn-danger" type="button"><i class="fa fa-arrow-circle-left"></i></button><a>
                    </div> -->




                </form>


              </div>
              <!-- <h5>*Configurar las rutas dependiendo si va trabajar de forma local o utilizará un Hosting, ingrese correctamente las rutas.</h5> -->

            </div>

          </div><!-- /.col -->
        </div><!-- /.row -->

      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->

    <!--Fin-Contenido-->
    <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/rutas.js"></script>
  <?php
}
ob_end_flush();
?>