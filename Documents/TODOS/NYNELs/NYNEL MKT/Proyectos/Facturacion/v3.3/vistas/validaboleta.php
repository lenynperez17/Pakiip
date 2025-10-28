<?php
//Activamos el almacenamiento en el buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Ventas'] == 1) {
    ?>


        <!--Contenido-->
              <!-- Content Wrapper. Contains page content -->
              <div class="content-start transition">        
                <!-- Main content -->
                <section class="container-fluid dashboard">
                <div class="content-header">
                          <h1>Validar boletas del sistema</h1>
                      </div>
                      <div class="row">
                    <div class="card">

                    <div class="card-body">

                        <div class="row">


           <div class="panel-body" id="listadoregistros">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
                  <div class="row justify-content-center text-center">

         <div class="mb-3 col-lg-2">
            <label> Año: </label>
            <select class="form-control" name="ano" id="ano" onchange="listarValidar()">
              <option value="2017">2017</option>
              <option value="2018">2018</option      <option value="2019">2019</option>>

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
            <label> Mes: </label>
         <select class="form-control" name="mes" id="mes" onchange="listarValidar()">
              <option value="'01','02','03','04','05','06','07','08','09','10', '11','12'">todos</option>
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
          </div> 

          <div class="mb-3 col-lg-2">
            <label> Día: </label>
            <select class="form-control" name="dia" id="dia" onchange="listarValidar()">
              <option value="0">Todos</option>
              <option value="1">01</option>
              <option value="2">02</option>
              <option value="3">03</option>
              <option value="4">04</option>
              <option value="5">05</option>
              <option value="6">06</option>
              <option value="7">07</option>
              <option value="8">08</option>
              <option value="9">09</option>
              <option value="10">10</option>
              <option value="11">11</option>
              <option value="12">12</option>
              <option value="13">13</option>
              <option value="14">14</option>
              <option value="15">15</option>
              <option value="16">16</option>
              <option value="17">17</option>
              <option value="18">18</option>
              <option value="19">19</option>
              <option value="20">20</option>
              <option value="21">21</option>
              <option value="22">22</option>
              <option value="23">23</option>
              <option value="24">24</option>
              <option value="25">25</option>
              <option value="26">26</option>
              <option value="27">27</option>
              <option value="28">28</option>
              <option value="29">29</option>
              <option value="30">30</option>
              <option value="31">31</option>
      
            </select>
            <input type="hidden" name="mes_1" id="mes_1">
          </div>

          <div class="mb-3 col-lg-2">
            <label>Forma de envío: </label>
            <select class="form-control" name="fenvio" id="fenvio" onchange="cambiotipoenvio()">
              <option value="0">Automatico</option>
              <option value="1" selected="true">Manual</option>
            </select>
            <input type="hidden" name="mes_1" id="mes_1">
          </div>  

          <div class="mb-3 col-lg-2">
            <label> Marcar: </label>
            <select class="form-control" name="marcar" id="marcar" onchange="marcartn()">
              <option value="0">todos</option>
              <option value="1" selected="true">ninguno</option>
            </select>
            <input type="hidden" name="mes_1" id="mes_1">
          </div>  

 
  
        <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">
        </div>
        <div class="row justify-content-center text-center">
          <div class="mb-3 col-lg-2" >
            <button class="btn btn-danger" id="formaenvio" onclick="tipoenvio()" style="display: none;">
              <i class="fa fa-check-circle"></i></button>

            <button class="btn btn-success" id="refrescartabla" onclick="refrescartabla()"><i class="fa fa-repeat"></i></button>
          </div>
          </div>
            </tbody>
          </table>

                                <div class="table-responsive" id="listadoregistros">
                                      <table id="tbllistado" class="table table-striped" style="font-size: 14px; max-width: 100%; !important;">
                                      <thead style="text-align:center;">
                                            <th >..</th>
                                          <th>Fecha  </th>
                                          <th>Cliente</th>
                                          <th >Vendedor</th>
                                          <th>Boleta</th>
                                          <th>Total</th>
                                          <th></th>
                                          <th></th>
                                          <th>Estado</th>
                                          <th>Sunat</th>
                                          <th>-</th>
                                      </thead>
                                      <tbody style="text-align:center;">
                                      </tbody>

                                  </table>
                                  </div>
             
                            </div>


          <div class="panel-body"  id="formularioregistros">
            <form name="formulario" id="formulario" method="POST" >




                         

                                </form>

                                <div class="modal fade" id="modalPreviewXml" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-lg" style="width: 70% !important;" >
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title">ARCHIVO XML DE BOLETA</h4>
                  <a name="bajaxml" id="bajaxml" download><span class="fa fa-font-pencil">DESCARGAR XML </span></a>
                </div>
        
            <iframe name="modalxml" id="modalxml" border="0" frameborder="0"  width="100%" style="height: 800px;" marginwidth="1" 
            src="">
            </iframe>

                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                </div>        
              </div>
            </div>
          </div>  
          <!-- Fin modal -->

                              </div>
                            <!--Fin centro -->
                          </div><!-- /.box -->
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


    <script type="text/javascript" src="scripts/validaboleta.js"></script>


  <?php
}
ob_end_flush();
?>