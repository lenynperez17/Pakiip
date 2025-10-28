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

    <link href="../public/css/html5tooltips.css" rel="stylesheet">
    <link href="../public/css/html5tooltips.animation.css" rel="stylesheet">

    <!--Contenido-->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="">
              <div class="box-header with-border">
                <h1 class="box-title">PLATOS: <button class="btn btn-primary" id="btnagregar" onclick="mostrarform(true)"><i
                      class="fa fa-newspaper-o"></i> Agregar platos</button>


                  <a data-toggle="modal" href="#ModalNcategoria" class="btn btn-success">
                    <i class="fa fa-newspaper-o"></i> Nueva Categoría</a>

                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <!-- /.box-header -->
              <!-- centro -->
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                  <thead>

                    <th>Opciones</th>
                    <th>Código</th>
                    <th>Nombre plato</th>
                    <th>Précio</th>
                    <th>Foto</th>
                    <th>Estado</th>

                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>

                  </tfoot>
                </table>
              </div>




              <div class="panel-body" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">

                  <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <table>
                      <tr>

                        <input type="hidden" id="idplato" name="idplato">
                        <td>

                          <!-- <label>Tipo</label>
              <input type="text" name="tipop" id="tipop"> -->
                          <!-- <select   name="tipop" id="tipop">
                <option value="1">Entrada</option>
                <option value="2">Fondo</option>
                <option value="3">Postre</option>

              </select> -->



                          <label>Categoría</label>
                          <select class=" select-picker" name="idcategoria" id="idcategoria" required
                            data-live-search="true" onchange="focuscodprov()">
                          </select>

                        </td>
                      </tr>

                      <tr>
                        <td>

                          <label>Código:</label>
                          <input type="text" class="" name="codigo" id="codigo" maxlength="100" placeholder="Código"
                            required="" onkeyup="mayus(this)" onkeypress="return focusnomb(event, this)"
                            data-tooltip="Información de este campo"
                            data-tooltip-more="Aquí ingrese si su artículo tiene un código que viene desde el proveedor, es opcional, si no tiene un codigo puede poner un . o -"
                            data-tooltip-stickto="right" data-tooltip-maxwidth="200" data-tooltip-animate-function="foldin"
                            data-tooltip-color="green">

                        </td>
                      </tr>


                      <tr>
                        <td>

                          <label>Descripción / Nombre:</label>
                          <input type="text" class="" name="nombre" id="nombre" maxlength="100" placeholder="Nombre"
                            required="true" onkeyup="mayus(this);" onkeypress=" return focusum(event, this)"
                            data-tooltip="Información de este campo"
                            data-tooltip-more="Descripción del artículo,  aparecera en el detalle del comprobante."
                            data-tooltip-stickto="top" data-tooltip-maxwidth="200" data-tooltip-animate-function="foldin"
                            data-tooltip-color="green">

                        </td>
                      </tr>


                      <tr>
                        <td>

                          <label>Precio venta (S/.):</label>
                          <input type="text" class="" name="precio" id="precio" onkeypress="return codigoi(event, this)"
                            data-tooltip="Información de este campo"
                            data-tooltip-more="El precio que se muestra en los ocmprobantes, incluye IGV."
                            data-tooltip-stickto="right" data-tooltip-maxwidth="200" data-tooltip-animate-function="foldin"
                            data-tooltip-color="green">

                        </td>
                      </tr>
                    </table>
                  </div>

                  <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                    <table>
                      <td>
                        <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">
                          <label>Imagen:</label>
                          <img src="../public/images/sinplato.png" width="700px" height="700px" id="imagenmuestra">
                          <br>
                          <input type="file" class="" name="imagen" id="imagen" value="">
                          <input type="hidden" name="imagenactual" id="imagenactual">
                        </div>
                      </td>
                    </table>
                  </div>





                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>
                      Guardar</button>

                    <button class="btn btn-danger" onclick="cancelarform()" type="button"><i
                        class="fa fa-arrow-circle-left"></i> Cancelar</button>
                  </div>



                </form>
              </div>

              <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <a href="../files/plantilla/plantilla.zip"> Descargar plantilla <img src="../public/images/excel.png"
                    height="2px"> </a>
              </div>

              <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <a data-toggle="modal" href="#Modalcargardatos">
                  <i class="fa fa-import"></i> Importar artículos a la base de datos <img
                    src="../public/images/importar.png" height="2px"></a>
              </div>
              <!--Fin centro -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->
    <!--Fin-Contenido-->




    <!-- Modal -->
    <div class="modal fade" id="Modalcargardatos">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
              <h4 class="modal-title">Subir plantilla</h4>
            </div>

            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
              <img src="../public/images/importar2.png" height="50px">
            </div>
          </div>
          <div class="modal-body">
            <form action='../modelos/importarArticulo.php' method='post' enctype="multipart/form-data">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                Importar Archivo : <input class="" type='file' name='sel_file' size='20'>
              </div>

              <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                <br>
              </div>


              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <input type='submit' name='submit' value='Cargar a tabla artículos' class="btn btn-primary">
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Cerrar</button>
          </div>

        </div>
      </div>
    </div>
    <!-- Fin modal -->



    <!-- Modal -->
    <div class="modal fade" id="ModalNcategoria">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Nueva categoría</h4>
          </div>
          <div class="modal-body">
            <form name="formnewcategoria" id="formnewcategoria" method="POST">


              <input type="hidden" name="idfamilia" id="idfamilia">


              <div class="form-group col-lg-8 col-md-4 col-sm-6 col-xs-12">
                <input type="text" name="nombreCategoria" id="nombreCategoria" placeholder="Nombre de categoria"
                  autofocus="true" onkeyup="mayus(this);" size="30" class="">

                <button class="btn btn-primary" type="submit" id="btnGuardarcliente"><i class="fa fa-save"></i>
                  Guardar</button>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->


    <!-- Modal -->
    <div class="modal fade" id="ModalNalmacen">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Nuevo almacen</h4>
          </div>
          <div class="modal-body">
            <form name="formnewalmacen" id="formnewalmacen" method="POST">


              <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                <label>Nombre: </label>
                <input type="text" name="nombrea" id="nombrea" placeholder="Escribir el nombre" autofocus="true"
                  onkeyup="mayus(this);" size="30" class="">
                <input type="hidden" name="idempresa2" id="idempresa2" value="<?php echo $_SESSION['idempresa']; ?>">

              </div>


              <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                <label>Dirección: </label>
                <input type="text" name="direc" id="direc" placeholder="Escribir la dirección" autofocus="true"
                  onkeyup="mayus(this);" size="30" class="">
              </div>




              <button class="btn btn-primary" type="submit" id="btnGuardarcliente"><i class="fa fa-save"></i>
                Guardar</button>

            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Cerrar</button>
          </div>

        </div> <!-- Fin content -->
      </div> <!-- Fin dialog -->
    </div> <!-- Fin modal -->
    <!-- Fin modal -->

    <!-- Modal -->
    <div class="modal fade" id="ModalNumedida">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Nueva unidad medida</h4>
          </div>
          <div class="modal-body">
            <form name="formnewumedida" id="formnewumedida" method="POST">


              <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                <label>Nombre: </label>
                <input type="text" name="nombreu" id="nombreu" placeholder="Escribir el nombre" autofocus="true"
                  onkeyup="mayus(this);" size="30" class="" onchange=" unidadvalor()">
              </div>


              <div class="form-group col-lg-6 col-md-4 col-sm-6 col-xs-12">
                <label>Abreviatura: </label>
                <input type="text" name="abre" id="abre" placeholder="Escribir la abreviatura" autofocus="true"
                  onkeyup="mayus(this);" size="30" class="">
              </div>




              <button class="btn btn-primary" type="submit" id="btnGuardarumedida"><i class="fa fa-save"></i>
                Guardar</button>

            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Cerrar</button>
          </div>

        </div> <!-- Fin content -->
      </div> <!-- Fin dialog -->
    </div> <!-- Fin modal -->
    <!-- Fin modal -->

    <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="../public/js/JsBarcode.all.min.js"></script>
  <script type="text/javascript" src="../public/js/jquery.PrintArea.js"></script>
  <script type="text/javascript" src="scripts/platos.js"></script>

  <script src="../public/js/html5tooltips.js"></script>

  <!-- <script type="text/javascript" src="scripts/jquery-1.2.6.min.js"></script> -->
  <?php
}
ob_end_flush();
?>