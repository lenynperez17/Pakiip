<?php
//Activamos el almacenamiento en el buffer
ob_start();

// Inicializar sesión de forma centralizada (se hace automáticamente en Conexion.php)
require_once "../config/Conexion.php";

// Verificar autenticación
if (!isset($_SESSION["nombre"])) {
    header("Location: ../vistas/login.php");
    exit();
} else {
    require 'header.php';



    if ($_SESSION['Dashboard'] == 1) {





        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        $rsptac = $consulta->totalcomprahoy($_SESSION['idempresa']);
        $regc = $rsptac->fetch_object();
        $totalc = $regc->total_compra;



        $rsptav = $consulta->totalventahoycotizacion($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        $totalvcotihoy = $regv->total_venta_coti_hoy;



        $rsptav = $consulta->totalventahoyFactura($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totalvfacturahoy = $regv->total_venta_factura_hoy ?? 0;



        $rsptav = $consulta->totalventahoyBoleta($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totalvboletahoy = $regv->total_venta_boleta_hoy ?? 0;



        $rsptav = $consulta->totalventahoyNotapedido($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totalvnpedidohoy = $regv->total_venta_npedido_hoy ?? 0;

        // Calcular venta total del día (suma de boletas, facturas y notas de venta)
        $totalventas = $totalvfacturahoy + $totalvboletahoy + $totalvnpedidohoy;





        $rsptav = $consulta->totalventahoyFacturaServicio($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        $totalvfacturaServiciohoy = $regv->total_venta_factura_hoy;



        $rsptav = $consulta->totalventahoyBoletaServicio($_SESSION['idempresa']);
        $regv = $rsptav->fetch_object();
        $totalvboletaServiciohoy = $regv->total_venta_boleta_hoy;


        //dash card

        //total categoria
        $rsptav = $consulta->totalcategoriaActiva();
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totalcategoriaActiva = $regv->total ?? 0;

        $rsptav = $consulta->totaUsuarioRegistrados();
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totaUsuarioRegistrados = $regv->total ?? 0;

        $rsptav = $consulta->totaArticulosRegistrados();
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totaArticulosRegistrados = $regv->total ?? 0;

        $rsptav = $consulta->totaClientesRegistrados();
        $regv = $rsptav->fetch_object();
        // Convertir null a 0 para evitar warning deprecado en PHP 8.3+
        $totaClientesRegistrados = $regv->total ?? 0;



        //Productos mas vendidos
        $rsptav = $consulta->productosmasvendidos();
        $regv = $rsptav->fetch_object();
        $productosmasvendidos = isset($regv->total) ? $regv->total : 0;

        //clientes top
        $rsptaClientesTop = $consulta->ClientesTop();





        //Tipo de cambio
        date_default_timezone_set('America/Lima');
        $hoy = date('Y/m/d');
        $hoy2 = date('Y-m-d');

        $rsptatc = $consulta->mostrartipocambio($hoy);
        $regtc = $rsptatc->fetch_object();

        if (!isset($regtc)) {
            $idtipocambio = "";
            $fechatc = "";
            $tccompra = "";
            $tcventa = "";
            $dfecha = "";

        } else {
            $idtipocambio = $regtc->idtipocambio;
            $fechatc = $regtc->fecha;
            $tccompra = $regtc->compra;
            $tcventa = $regtc->venta;


            // if ($fechatc==$hoy2) {
            //      $dfecha="readonly";
            //    }else{
            //      $dfecha="";
            //  }


            if ($fechatc == '') {
                $dfecha = "";
            }
        }
        //Tipor de cambio

        //Caja

        date_default_timezone_set('America/Lima');

        $hoy = date('Y/m/d');

        $hoy2 = date('Y-m-d');



        $rsptatc = $consulta->mostrarcaja($hoy, $_SESSION['idempresa']);

        $regtc = $rsptatc->fetch_object();



        if (!isset($regtc)) {

            $idcaja = "";

            $idcajai = "";

            $idcajas = "";

            $fecha = "";

            $montoi = "0";

            $montof = "0";

            $dfecha = "";

            $estado = "";

            $cajaestado = "";

            $mensajecaja = "ABRIR CAJA";

            $hb = "";

            $color = "";

            $btn = "";

        } else {

            $idcaja = $regtc->idcaja;

            $idcajai = $regtc->idcaja;

            $idcajas = $regtc->idcaja;

            $fecha = $regtc->fecha;

            $montoi = $regtc->montoi;

            $montof = $regtc->montof;

            $estado = $regtc->estado;



            if ($fecha == $hoy2) {

                $dfecha = "readonly";

            } else {

                $dfecha = "";



            }



            if ($estado == '') {

                $mensajecaja = 'ABRIR CAJA';

            }



            if ($estado == '1') {

                $mensajecaja = 'CERRAR CAJA';

                $hb = "";

                $cajaestado = 'ABIERTA';

                $color = 'green';

                $btn = "";

            } else {

                $mensajecaja = 'ABRIR CAJA';

                $hb = "readonly";

                $cajaestado = 'CERRADA';

                $color = 'red';

                $btn = "disabled";

            }











        }

        //Tipor de caja





        //Datos para mostrar el gráfico de barras de las compras

        $compras10 = $consulta->comprasultimos_10dias($_SESSION['idempresa']);

        $fechasc = '';

        $totalesc = '';

        $mes = '';

        while ($regfechac = $compras10->fetch_object()) {

            $fechasc = $fechasc . '"' . $regfechac->fecha . '",';

            $totalesc = $totalesc . $regfechac->total . ',';

            $mes = $mes . $regfechac->mes . ',';

        }

        //Quitamos la última coma

        $fechasc = substr($fechasc, 0, -1);

        $totalesc = substr($totalesc, 0, -1);

        $mes = substr($mes, 0, -1);



        //Datos para mostrar el gráfico de barras de las ventas

        $ventas12 = $consulta->ventasultimos_12meses($_SESSION['idempresa']);

        $fechasv = '';

        $totalesv = '';

        while ($regfechav = $ventas12->fetch_object()) {

            $fechasv = $fechasv . '"' . $regfechav->fecha . '",';

            $totalesv = $totalesv . $regfechav->total . ',';

        }

        //Quitamos la última coma

        $fechasv = $fechasv;

        $totalesv = $totalesv;





        $consultaSTs = $consulta->consultaestados();

        $estado = '';

        $totalestado = '';

        $stEmitido = 0;

        $stFirmado = 0;

        $stAceptado = 0;

        $stAnulado = 0;

        $stNota = 0;

        $stFisico = 0;

        while ($regestados = $consultaSTs->fetch_object()) {

            $estadoD = $regestados->estado;

            $totalestadoD = $regestados->totalestados;

            switch ($estadoD) {

                case '1':

                    $stEmitido = $totalestadoD;

                    break;

                case '5':

                    $stAceptado = $totalestadoD;
                    ;

                    break;

                case '5':

                    $stAceptado = $totalestadoD;
                    ;

                    break;

                case '3':

                    $stAnulado = $totalestadoD;
                    ;

                    break;

                case '4':

                    $stFirmado = $totalestadoD;
                    ;

                    break;

                case '6':

                    $stFisico = $totalestadoD;
                    ;

                    break;



                default:

                    # code...

                    break;

            }

        }





        $consultaSTsCoti = $consulta->consultaestadoscotizaciones();

        $estadoC = '';

        $totalestadoDCoti = '';

        $stEmitidoCoti = 0;

        $stAceptadoCoti = 0;

        while ($regestadosCoti = $consultaSTsCoti->fetch_object()) {

            $estadoDCoti = $regestadosCoti->estado;

            $totalestadoDCoti = $regestadosCoti->totalestados;

            switch ($estadoDCoti) {

                case '1':

                    $stEmitidoCoti = $totalestadoDCoti;

                    break;

                case '5':

                    $stAceptadoCoti = $totalestadoDCoti;
                    ;

                    break;

                default:

                    break;

            }

        }







        $consultaSTsOs = $consulta->consultaestadosdocumentoC();

        $estadoC = '';

        $totalestadoDcobranza = '';

        $stEmitidoDcobranza = 0;

        $stAceptadoddcobranza = 0;

        while ($regestadosDcobranza = $consultaSTsOs->fetch_object()) {

            $estadoDCoti = $regestadosDcobranza->estado;

            $totalestadoDcobranza = $regestadosDcobranza->totalestados;

            switch ($estadoDCoti) {

                case '1':

                    $stEmitidoDcobranza = $totalestadoDcobranza;

                    break;

                case '5':

                    $stAceptadoddcobranza = $totalestadoDcobranza;
                    ;

                    break;

                default:

                    break;

            }

        }



        $lunes = '0.00';
        $martes = '0.00';
        $miercoles = '0.00';
        $jueves = '0.00';
        $viernes = '0.00';
        $sabado = '0.00';
        $domingo = '0.00';

        $horaLunes = '';
        $horaMartes = '';
        $horaMiercoles = '';
        $horaJueves = '';
        $horaViernes = '';
        $horaSabado = '';
        $horaDomingo = '';

        $consultadiase = $consulta->ventasdiasemana($_SESSION['idempresa']);

        while ($regdiase = $consultadiase->fetch_object()) {

            $nrodia = $regdiase->dia;

            switch ($nrodia) {
                case '1':
                    $domingo = $regdiase->ventasdia;
                    $horaDomingo = $regdiase->horaactualizacion;
                    break;
                case '2':
                    $lunes = $regdiase->ventasdia;
                    $horaLunes = $regdiase->horaactualizacion;

                    break;
                case '3':
                    $martes = $regdiase->ventasdia;
                    $horaMartes = $regdiase->horaactualizacion;
                    break;
                case '4':
                    $miercoles = $regdiase->ventasdia;
                    $horaMiercoles = $regdiase->horaactualizacion;
                    break;
                case '5':
                    $jueves = $regdiase->ventasdia;
                    $horaJueves = $regdiase->horaactualizacion;
                    break;
                case '6':
                    $viernes = $regdiase->ventasdia;
                    $horaViernes = $regdiase->horaactualizacion;
                    break;
                case '7':
                    $sabado = $regdiase->ventasdia;
                    $horaSabado = $regdiase->horaactualizacion;
                    break;
            }
        }




        require_once "../modelos/Factura.php";

        $factura = new Factura();

        $datos = $factura->datosemp($_SESSION['idempresa']);

        $datose = $datos->fetch_object();



        ?>



                                                            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

                                                            <!--Contenido-->



                                                              <!-- Modal ABRIR / CERRAR CAJA -->

                                                             <div class="modal fade" id="modalcaja">



                                                                <div class="modal-dialog" style="width: 60% !important;">

                                                                  <div class="modal-content">



                                                                      <div class="modal-header">CAJA</div>



                                                                  <form name="formulariocaja" id="formulariocaja" method="POST">

                                                                      <div  id="montoscajamodal" name="montoscajamodal">

                                                                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">

                                                                         Fecha del día: <input type="date" name="fechacaja" id="fechacaja" value="<?php echo $fecha; ?>" class=""  <?php echo $dfecha; ?>     >

                                                                         <input type="hidden" name="idcaja" id="idcaja" value="<?php echo $idcaja; ?>" >

                                                                         <input type="hidden" name="estado" id="estado" value="<?php echo $mensajecaja; ?>" >

                                                                      </div>



                                                                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">

                                                                         Monto inicial del día:  <input type="text" name="montoi" id="montoi" placeholder="Monto inicial" value=" <?php echo $montoi; ?> " class=""  <?php echo $hb; ?> onkeypress="return NumCheck(event, this)">

                                                                      </div>

                                                                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">

                                                                          Monto final del día: <input type="text" name="montof" id="montof" placeholder="Monto final" value=" <?php echo $montof; ?> " class=""  <?php echo $hb; ?>  onkeypress="return NumCheck(event, this)">

                                                                      </div>





                                                              <div class="form-group col-lg-4 col-md-6 col-sm-6 col-xs-12">

                                                                <button class="btn btn-primary" type="submit" id="btngrabar" name="btngrabar">

                                                                    <i class="fa fa-save"></i> <?php echo $mensajecaja; ?>

                                                                      </button>

                                                              </div>

 
                                                              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">

                                                               <a href="#ingresocaja" data-toggle="modal"> <button class="btn btn-primary" type="submit" id="btningreso" name="btningreso" <?php echo $btn; ?>>

                                                                    <i class="fa fa-save"></i> INGRESO

                                                                      </button></a>

                                                              </div>



                                                              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">

                                                                 <a href="#salidacaja" data-toggle="modal">  <button class="btn btn-danger" type="submit" id="btnsalida" name="btnsalida" <?php echo $btn; ?> >

                                                                    <i class="fa fa-save"></i> SALIDA

                                                                      </button></a>

                                                              </div>











                                                              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">

                                                                 <a href="#cajaconsulta" data-toggle="modal">  <button class="btn btn-primary">

                                                                    <i class="fa fa-eye"></i> CONSULTA

                                                                      </button></a>

                                                              </div>



                                                              <div class="form-group col-lg-2 col-md-6 col-sm-6 col-xs-12">

                                                                CAJA: <label style="font-size: 18px; color:<?php echo $color; ?>;"> <?php echo $cajaestado; ?> </label>

                                                              </div>



                                                              </div>



                                                              <div class="form-group col-lg-12 col-md-6 col-sm-6 col-xs-12">



                                                              </div>



                                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                                                              FECHA DE CAJAS ANTERIORES.

                                                                <table id="tbllistadocaja"  class="table table-striped table-bordered table-condensed table-hover" style="font-size: 14px;">

                                                                                      <thead >

                                                                                        <th>Id</th>

                                                                                        <th>Fecha</th>



                                                                                        <th>Inicial </th>

                                                                                        <th>Final</th>

                                                                                      </thead>

                                                                                      <tbody >



                                                                                      </tbody>

                                                                      </table>

                                                                    </div>







                                                                    </form>



                                                                      <div class="modal-footer">

                                                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" ><i class="fa fa-close"> </i>   Cerrar</button>

                                                                    </div>

                                                                 </div>

                                                               </div>

                                                              </div>






                                                               <!-- Modal ABRIR / CERRAR CAJA -->

                                                             <div class="modal fade" id="modalfechas">

                                                                <div class="modal-dialog" style="width: 40% !important;">

                                                                  <div class="modal-content">

                                                                      <div class="modal-header">Fechas anteriores</div>

                                                          <div class="modal-footer">

                                                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>



                                                                    </div>

                                                                 </div>

                                                               </div>

                                                              </div>







                                                                <!-- Modal REPORTE ---------------------------------------------->

                                                             <div class="modal fade" id="cajaconsulta">

                                                                <div class="modal-dialog" style="width: 80% !important;">

                                                                  <div class="modal-content">

                                                                      <div class="modal-header">ingresos y salidas</div>

                                                                    <form name="formulariois" id="formulariois" action="../reportes/.php" method="POST" target="_blank">

                                                            <input type="hidden" name="idempresa" id="idempresa" value="<?php echo $_SESSION['idempresa']; ?>">

                                                                <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">

                                                                <label> Año: </label>

                                                                <select class="" name="ano" id="ano" onchange="listarValidar()">



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







                                                             <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">

                                                                <label> Mes: </label>

                                                                <select class="" name="mes" id="mes" onchange="listarValidar()">

                                                                  <option value="0">todos</option>

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





                                                              <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">

                                                                <label> Día: </label>

                                                                <select class="" name="dia" id="dia" onchange="listarValidar()">

                                                                  <option value="01">01</option>

                                                                  <option value="02">02</option>

                                                                  <option value="03">03</option>

                                                                  <option value="04">04</option>

                                                                  <option value="05">05</option>

                                                                  <option value="06">06</option>

                                                                  <option value="07">07</option>

                                                                  <option value="08">08</option>

                                                                  <option value="09">09</option>

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



                                                            <!-- <div class="form-group col-lg-2 col-md-4 col-sm-6 col-xs-12">

        <button class="btn btn-primary" type="submit" id="btnconsulta"  data-toggle="tooltip" title="Consultar" onclick="return enviar();" ><i class="fa fa-print" ></i> Reporte

        </button>

</div> -->



                                                            <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">

                                                            </div>





                                                              <!-- centro -->



                                                                    <table id="tbllistadocajavalidar" class="table table-striped table-bordered table-condensed table-hover" style="font-size: 12px;">

                                                                                <thead>

                                                                                        <th>FECHA</th>

                                                                                        <th>MONTO</th>

                                                                                        <th>CONCEPTO</th>

                                                                                        <th>TIPO</th>

                                                                                      </thead>

                                                                                      <tbody>

                                                                                      </tbody>

                                                                                    </table>







                                                                  </form>

                                                                      <div class="modal-footer">

                                                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>



                                                                    </div>

                                                                 </div>

                                                               </div>

                                                              </div>







                                                              <!-- Modal ABRIR / INGRESO CAJA -->

                                                             <div class="modal fade" id="ingresocaja">

                                                                <div class="modal-dialog" style="width: 40% !important;">

                                                                  <div class="modal-content">

                                                                      <div class="modal-header" style="font-size: 18px; color: green;"  >Ingreso</div>

                                                                  <form name="formularioicaja" id="formularioicaja" method="POST">

                                                                    <div name="idcajaingreso" id="idcajaingreso">

                                                                      <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">

                                                                         <input type="hidden" name="idcajai" id="idcajai" value="<?php echo $idcajai; ?>" >

                                                                      </div>

                                                                      <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">

                                                                         Concepto:  <textarea name="concepto" id="concepto" placeholder="Monto inicial" class=""  rows="5" cols="100" autofocus onkeyup="mayus(this)"></textarea>

                                                                      </div>

                                                                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">

                                                                          Monto: <input type="text" name="monto" id="monto" placeholder="Monto" class=""  onkeypress="return NumCheck(event, this)" >

                                                                      </div>

                                                                    </div>



                                                              <div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">

                                                                <button class="btn btn-primary" type="submit" id="btngrabar" name="btngrabar">

                                                                    <i class="fa fa-save"></i> GRABAR

                                                                      </button>

                                                              </div>

                                                                    </form>

                                                                      <div class="modal-footer">

                                                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>

                                                                    </div>

                                                                 </div>

                                                               </div>

                                                              </div>





                                                               <!-- Modal ABRIR / SALIDA CAJA -->

                                                             <div class="modal fade" id="salidacaja">

                                                                <div class="modal-dialog" style="width: 40% !important;">

                                                                  <div class="modal-content">

                                                                      <div class="modal-header" style="font-size: 18px; color: red;"  >SALIDA</div>

                                                                  <form name="formularioscaja" id="formularioscaja" method="POST">

                                                                    <div name="idcajasalida" id="idcajasalida">

                                                                      <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">

                                                                         <input type="hidden" name="idcajas" id="idcajas" value="<?php echo $idcajas; ?>" >

                                                                      </div>

                                                                      <div class="form-group col-lg-12 col-md-4 col-sm-6 col-xs-12">

                                                                         Concepto:  <textarea name="concepto" id="concepto" placeholder="Monto inicial" class=""  rows="5" cols="100" autofocus onkeyup="mayus(this)"></textarea>

                                                                      </div>

                                                                      <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">

                                                                          Monto: <input type="text" name="monto" id="monto" placeholder="Monto" class=""  onkeypress="return NumCheck(event, this)" >

                                                                      </div>

                                                                    </div>



                                                              <div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12">

                                                                <button class="btn btn-primary" type="submit" id="btngrabar" name="btngrabar">

                                                                    <i class="fa fa-save"></i> GRABAR

                                                                      </button>

                                                              </div>

                                                                    </form>

                                                                      <div class="modal-footer">

                                                                      <button type="button" class="btn btn-danger btn-ver" data-dismiss="modal" >Cerrar</button>

                                                                    </div>

                                                                 </div>

                                                               </div>

                                                              </div>



                                                             <!-- Modal tipo de cambio -->
                                                             <div class="modal fade" id="modalTcambio" tabindex="-1" aria-labelledby="modalTcambio" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="modalTcambio">Tipo de cambio desde SUNAT</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form name="formulariotcambio" id="formulariotcambio" method="POST">
                                                                                <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12" hidden>
                                                                                    Fecha: <input type="date" name="fechatc" id="fechatc" value="<?php echo $fechatc; ?>" class=""
                                                                                        <?php echo $dfecha; ?> onchange="consultartcambio();" readonly="true">
                                                                                    <input type="hidden" name="idtcambio" id="idtcambio" value="<?php echo $idtipocambio; ?>">
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="mb-3 col-lg-6">
                                                                                        <label for="recipient-name" class="col-form-label">Compra:</label>
                                                                                        <input type="text" class="form-control" name="compra" id="compra" value=" <?php echo $tccompra; ?> ">
                                                                                    </div>
                                                                                    <div class="mb-3 col-lg-6">
                                                                                        <label for="message-text" class="col-form-label">Venta:</label>
                                                                                        <input type="text" class="form-control" name="venta" id="venta"
                                                                                            value=" <?php echo $tcventa; ?> ">
                                                                                    </div>
                                                                                </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button hidden class="btn btn-success" type="button" id="btnguarconsultar" name="btnguarconsultar"
                                                                                onclick="consultartcambio();">
                                                                                <i class="fa fa-find"></i>TC sunat
                                                                            </button>
                                                                            <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button> -->
                                                                            <button type="submit" id="btnguardartcambio" name="btnguardartcambio" value="btnguardartcambio"
                                                                                class="btn btn-primary">Guardar</button>
                                                                        </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>

                                
       

                                <!-- Start::page-header -->

                                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                   
                                </div>

                                 <!-- End::page-header -->

                                <!-- Start::row-1 -->
                                <div class="row">
                                    <div class="col-xxl-9 col-xl-12">
                                        <div class="row">
                                            <div class="col-xl-4">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="card custom-card crm-highlight-card">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <div class="fw-semibold fs-18 text-fixed-white mb-2">¿Más Información?</div>
                                                                        <span class="d-block fs-12 text-fixed-white"><span class="op-7">Sistema</span> de <span class="fw-semibold text-warning">Facturación Electrónica</span> <span class="op-7">y gestión de tu empresa</span>.</span>
                                                                        <span class="d-block fw-semibold mt-1"><a class="text-fixed-white" href="https://wa.link/nzpd55" target="_blank"><u>COMPRAR</u></a></span>
                                                                    </div>
                                                                    <div>
                                                                        <div id="crm-main"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12">
                                                    <div class="card custom-card">
                                                        <div class="card-header  justify-content-between">
                                                            <div class="card-title">
                                                                Clientes potenciales
                                                            </div>
                                                            <div class="dropdown">
                                                                <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fe fe-more-vertical"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Lista clientes</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <style>
                                                            .truncate {
                                                                white-space: nowrap;
                                                                overflow: hidden;
                                                                text-overflow: ellipsis;
                                                                max-width: 200px; /* Ajusta este valor según tus necesidades */
                                                                display: inline-block;
                                                            }

                                                            .scrollable {
                                                                overflow-y: auto;
                                                                max-height: 290px; /* Ajusta este valor según tus necesidades */
                                                            }

                                                        </style>
       
                                                        <?php
                                                        echo '<div class="card-body scrollable">';

                                                        echo '<ul class="list-unstyled crm-top-deals mb-0">';
                                                        while ($regCliente = $rsptaClientesTop->fetch_object()) {
                                                            echo '<li>';
                                                            echo '    <div class="d-flex align-items-top flex-wrap">';
                                                            echo '        <div class="me-2">';
                                                            echo '            <span class="avatar avatar-sm avatar-rounded">';
                                                            // Aquí puedes colocar una imagen del cliente o usar un avatar por defecto
                                                            echo '                <img src="../assets/images/faces/10.jpg" alt="">';
                                                            echo '            </span>';
                                                            echo '        </div>';
                                                            echo '        <div class="flex-fill">';
                                                            echo '            <p class="fw-semibold mb-0 truncate" title="' . $regCliente->nombrecliente . '">' . $regCliente->nombrecliente . '</p><br>';
                                                            echo '            <span class="text-muted fs-12 truncate" title="' . $regCliente->detallecliente . '">' . $regCliente->detallecliente . '</span>';
                                                            echo '        </div>';
                                                            echo '        <div class="fw-semibold fs-15"> s/ ' . $regCliente->totalgastado . '</div>';
                                                            echo '    </div>';
                                                            echo '</li>';
                                                        }
                                                        echo '</ul>';
                                                        echo '</div>'
                                                            ?>
                                        
                                                    </div>
                                                </div>
                                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                    <div class="card custom-card">
                                                        <div class="card-header justify-content-between">
                                                            <div class="card-title">Gráfico de compras</div>
                                                            <div class="dropdown">
                                                                <a href="javascript:void(0);" class="p-2 fs-12 text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Ver más<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Dia</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Semana</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Año</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div class="card-body py-0 ps-0">
                                                            <div id="crm-profits-earned"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8">
                                                <div class="row">
                                                    <div class="col-xxl-6 col-lg-6 col-md-6">
                                                        <div class="card custom-card hrm-main-card primary">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-top justify-content-center ecommerce-icon secondary px-0">
                                                                    <div>
                                                                        <span class="rounded p-3 bg-secondary-transparent">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="svg-white secondary" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0,0h24v24H0V0z" fill="none"/><g><path d="M19.5,3.5L18,2l-1.5,1.5L15,2l-1.5,1.5L12,2l-1.5,1.5L9,2L7.5,3.5L6,2v14H3v3c0,1.66,1.34,3,3,3h12c1.66,0,3-1.34,3-3V2 L19.5,3.5z M15,20H6c-0.55,0-1-0.45-1-1v-1h10V20z M19,19c0,0.55-0.45,1-1,1s-1-0.45-1-1v-3H8V5h11V19z"/><rect height="2" width="6" x="9" y="7"/><rect height="2" width="2" x="16" y="7"/><rect height="2" width="6" x="9" y="10"/><rect height="2" width="2" x="16" y="10"/></g></svg>
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-fill ms-3">
                                                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                                            <div>
                                                                                <p class="text-muted mb-0">Total Boletas</p>
                                                                                <h4 class="fw-semibold mt-1">S/<?php echo number_format($totalvboletahoy, 2); ?></h4>
                                                                            </div>
                                                                            <!--<div id="crm-total-customers"></div>-->
                                                                        </div>
                                                                        <div>
                                                                                <span class="fs-12 mb-0"><span class="badge bg-success-transparent text-success mx-1">Venta diaria</span></span>
                                                                            </div>
                                
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-lg-6 col-md-6">
                                                        <div class="card custom-card hrm-main-card primary">
                                                            <div class="card-body">
                                                                
                                                                <div class="d-flex align-items-top justify-content-center ecommerce-icon px-0">
                                                                    <div>
                                                                        <span class="rounded p-3 bg-primary-transparent">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="svg-white primary" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/><path d="M18,6h-2c0-2.21-1.79-4-4-4S8,3.79,8,6H6C4.9,6,4,6.9,4,8v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V8C20,6.9,19.1,6,18,6z M12,4c1.1,0,2,0.9,2,2h-4C10,4.9,10.9,4,12,4z M18,20H6V8h2v2c0,0.55,0.45,1,1,1s1-0.45,1-1V8h4v2c0,0.55,0.45,1,1,1s1-0.45,1-1V8 h2V20z"/></g></svg>
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-fill ms-3">
                                                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                                            <div>
                                                                                <p class="text-muted mb-0">Total Facturas</p>
                                                                                <h4 class="fw-semibold mt-1">S/<?php echo number_format($totalvfacturahoy, 2); ?></h4>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-12 mb-0"><span class="badge bg-success-transparent text-success mx-1">Venta diaria</span></span>
                                                                            </div>
                                                                        </div>
                                                                       
                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-lg-6 col-md-6">
                                                        <div class="card custom-card hrm-main-card primary">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-top justify-content-center ecommerce-icon success px-0">
                                                                    <div>
                                                                        <span class="rounded p-3 bg-success-transparent">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="svg-white success" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2m0 10c2.7 0 5.8 1.29 6 2H6c.23-.72 3.31-2 6-2m0-12C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 10c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-fill ms-3">
                                                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                                            <div>
                                                                                <p class="text-muted mb-0">Total Nota de Venta</p>
                                                                                <h4 class="fw-semibold mt-1">S/<?php echo number_format($totalvnpedidohoy, 2); ?></h4>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-12 mb-0"><span class="badge bg-success-transparent text-success mx-1">Venta diaria</span></span>
                                                                            </div>
                                                                           
                                                                        </div>
                                                                       
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-lg-6 col-md-6">
                                                        <div class="card custom-card hrm-main-card primary">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-top justify-content-between">
                                                                    <div>
                                                                        <span class="avatar avatar-md avatar-rounded bg-warning">
                                                                            <i class="ti ti-cash fs-16"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-fill ms-3">
                                                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                                            <div>
                                                                                <p class="text-muted mb-0">Venta Total</p>
                                                                                <h4 class="fw-semibold mt-1">S/<?php echo number_format($totalventas, 2); ?></h4>
                                                                            </div>
                                                                            
                                                                            <div>
                                                                                <span class="fs-12 mb-0"><span class="badge bg-success-transparent text-success mx-1">Venta diaria</span></span>
                                                                            </div>
                                                                           
                                                                        </div>
                                                                        
                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="card custom-card">
                                                            <div class="card-header justify-content-between">
                                                                <div class="card-title">
                                                                    Gráfico de ventas
                                                                </div>
                                                                <div class="dropdown">
                                                                    <a href="javascript:void(0);" class="p-2 fs-12 text-muted" data-bs-toggle="dropdown"
                                                                        aria-expanded="false">
                                                                        Ver todo<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Día</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Mes</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Año</a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="content-wrapper">
                                                                    <div id="crm-revenue-analytics"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <style>
                  tbody {
                    display: block; /* Esto hace que el tbody se comporte como un div */
                    max-height: 225px; /* ajusta esto según tu necesidad */
                    overflow-y: scroll;
                }

                thead, tbody tr {
                    display: table; 
                    width: 100%; 
                    table-layout: fixed; 
                }

                </style>
                                            <div class="col-xl-12">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            Top productos más vendidos
                                                        </div>
                                                       
                                                    </div>
                                                    <style>
                                                        .form-container { width: 100%; margin: 40px 0;}
                                                        .table { width: 100%; min-width: 960px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); display: table; font-size: 14px;}
                                                        .table .input-field { float: none; padding: 0 5px; height: 30px; font-size: 14px;}
                                                        .table-row { display: table-row; background: #f6f6f6;}
                                                        .table-row:nth-of-type(odd) { background: #e9e9e9;}
                                                        .table-row.header { font-weight: 900; color: #ffffff; background: #ea6153;}
                                                        .cell { padding: 8px 12px; display: table-cell;}
                                                        .table ul { margin: 0; padding: 0;}
                                                        .table ul li { list-style: none; display: inline-block;}
                                                        .table ul li a { opacity: .8; margin-right: 3px; height: 16px; float: left;}
                                                        .table ul li a:hover, .table ul li a:focus { opacity: 1;}
                                                        .table ul li a img { max-width: 16px;}

                                                        .svg-icon { width: 16px; height: 16px;}
                                                        .svg-icon path, .svg-icon polygon, .svg-icon rect { fill: #1332bf;}
                                                        .svg-icon circle { stroke: #09208a; stroke-width: 1;}

                                                        @media only screen and (max-width: 900px){
                                                            .container { padding: 0 50px;}
                                                            .form-container { width: 100%; overflow-x: scroll; overflow-y: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);}
                                                            .table { box-shadow: 0 0 0;}
                                                        }

                                                        @media only screen and (max-width: 360px){
                                                            .container { padding: 0 10px;}
                                                        }
                                                    </style>
                                                    <div class="card-body">

                                                  
                                                    
                                                        
                                                                <div class="table-responsive">
                                                            <table class="table text-nowrap table-hover border  table-bordered">

                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Código</th>
                                                                        <th scope="col">Nombre</th>
                                                                        <th scope="col">Imagen</th>
                                                                        <th scope="col">Estado</th>
                                                                        <th scope="col">Uni. vendidas</th>
                                                                        <th scope="col">Total ventas</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                                $rsptav = $consulta->productosmasvendidos();
                                                                while ($regv = $rsptav->fetch_object()) {
                                                                    echo "<tr>";
                                                                    echo "<td class='text-left'>{$regv->codigo}</td>";
                                                                    echo "<td class='text-left'>{$regv->nombre}</td>";
                                                                    $imagenPath = (empty($regv->imagen) || $regv->imagen == null) ? 'simagen.png' : $regv->imagen;
                                                                    echo "<td class='text-center'><img src='../files/articulos/{$imagenPath}' alt='Imagen del producto' class='img-thumbnail' style='max-width: 40px;'></td>";
                                                                    $estadoClase = ($regv->estado == 1) ? 'estado-activo' : 'estado-inhabilitado';
                                                                    $estadoTexto = ($regv->estado == 1) ? '<span class="badge bg-success-transparent">Activo</span>' : '<span class="badge bg-danger-transparent">Inhabilitado</span>';
                                                                    echo "<td class='text-center'><span class='{$estadoClase}'>{$estadoTexto}</span></td>";
                                                                    echo "<td class='text-right'>" . intval($regv->total_unidades_vendidas) . "</td>";
                                                                    echo "<td class='text-right'>{$regv->total_ventas}</td>";
                                                                    echo "</tr>";
                                                                }
                                                                ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-xl-12">
                                        <div class="row">
                                            <div class="col-xxl-12 col-xl-12">
                                                <div class="row">
                                                    <div class="col-xl-12 col-xl-6">
                                                        <div class="card custom-card">
                                                            <div class="card-header justify-content-between">
                                                                <div class="card-title">
                                                                   General
                                                                </div>
                                                                <!--<div class="dropdown">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown">
                                                                        <i class="fe fe-more-vertical"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu">
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Día</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Mes</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Año</a></li>
                                                                    </ul>
                                                                </div>-->
                                                            </div>
                                                            <div class="card-body p-0 overflow-hidden">
                                                                <div class="leads-source-chart d-flex align-items-center justify-content-center">
                                                                    <canvas id="leads-source" class="chartjs-chart w-100 p-4"></canvas>
                                                                    <div class="lead-source-value">
                                                                        <span class="d-block fs-14">Total Items</span>
                                                                        <span id="total-items-value" class="d-block fs-25 fw-bold"></span>
                                                        

                                                                    </div>
                                                                </div>
                                                                <div class="row row-cols-12 border-top border-block-start-dashed">
                                                                    <div class="col p-0">
                                                                        <div class="ps-4 py-3 pe-3 text-center border-end border-inline-end-dashed">
                                                                            <span class="text-muted fs-12 mb-1 crm-lead-legend mobile d-inline-block">Categorias
                                                                            </span>
                                                                            <div><span class="fs-16 fw-semibold"><?php echo number_format($totalcategoriaActiva); ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col p-0">
                                                                        <div class="p-3 text-center border-end border-inline-end-dashed">
                                                                            <span class="text-muted fs-12 mb-1 crm-lead-legend desktop d-inline-block">Artículos
                                                                            </span>
                                                                            <div><span class="fs-16 fw-semibold"><?php echo number_format($totaArticulosRegistrados); ?></span></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col p-0">
                                                                        <div class="p-3 text-center border-end border-inline-end-dashed">
                                                                            <span class="text-muted fs-12 mb-1 crm-lead-legend laptop d-inline-block">Usuarios
                                                                            </span>
                                                                            <div><span class="fs-16 fw-semibold"><?php echo number_format($totaUsuarioRegistrados); ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col p-0">
                                                                        <div class="p-3 text-center">
                                                                            <span class="text-muted fs-12 mb-1 crm-lead-legend tablet d-inline-block">Clientes
                                                                            </span>
                                                                            <div><span class="fs-16 fw-semibold"><?php echo number_format($totaClientesRegistrados); ?></span></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-12 col-xl-6">
                                                    <div class="card custom-card">
                                                        <div class="card-header justify-content-between">
                                                            <div class="card-title">
                                                                Estado comprobantes
                                                            </div>
                                                            <div class="dropdown">
                                                                <a href="javascript:void(0);" class="p-2 fs-12 text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Ver más<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Día</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Quincenal</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Mensual</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <h4 class="fw-bold mb-0"><?php echo ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico); ?></h4>
                                                                <div class="ms-2">
                                                                    <!-- Puedes agregar algún comentario sobre el cambio de los valores respecto a la última semana, mes, etc. -->
                                                                </div>
                                                            </div>
                                                            <div class="progress-stacked progress-animate progress-xs mb-4">
                                                                <!-- Aquí vamos a mostrar los valores en porcentaje en las barras de progreso -->
                                                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($stEmitido / ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico)) * 100; ?>%" aria-valuenow="<?php echo $stEmitido; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo ($stFirmado / ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico)) * 100; ?>%" aria-valuenow="<?php echo $stFirmado; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($stAceptado / ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico)) * 100; ?>%" aria-valuenow="<?php echo $stAceptado; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($stAnulado / ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico)) * 100; ?>%" aria-valuenow="<?php echo $stAnulado; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($stFisico / ($stEmitido + $stFirmado + $stAceptado + $stAnulado + $stFisico)) * 100; ?>%" aria-valuenow="<?php echo $stFisico; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <ul class="list-unstyled mb-0 pt-2 crm-deals-status">
                                                            <li class="success">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>Sunat Aceptados</div>
                                                                        <div class="fs-12 text-muted"><?php echo $stAceptado; ?> comprobantes</div>
                                                                    </div>
                                                                </li>
                                                                <li class="primary">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>Emitidos</div>
                                                                        <div class="fs-12 text-muted"><?php echo $stEmitido; ?> comprobantes</div>
                                                                    </div>
                                                                </li>
                                                                <li class="info">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>Firmados</div>
                                                                        <div class="fs-12 text-muted"><?php echo $stFirmado; ?> comprobantes</div>
                                                                    </div>
                                                                </li>
                                                
                                                                <li class="warning">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>Anulados</div>
                                                                        <div class="fs-12 text-muted"><?php echo $stAnulado; ?> comprobantes</div>
                                                                    </div>
                                                                </li>
                                                                <li class="danger">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>Físicos</div>
                                                                        <div class="fs-12 text-muted"><?php echo $stFisico; ?> comprobantes</div>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>


                                                    <div class="col-xxl-12 col-xl-6">
                                                        <div class="card custom-card">
                                                            <div class="card-header justify-content-between">
                                                                <div class="card-title">
                                                                    Venta x día de la semana
                                                                </div>
                                                                <div class="dropdown">
                                                                    <a href="javascript:void(0);" class="p-2 fs-12 text-muted" data-bs-toggle="dropdown"
                                                                        aria-expanded="false">
                                                                        Ver mas<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Día</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Semanal</a></li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0);">Mensual</a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div>
                                                                    <ul class="list-unstyled mb-0 crm-recent-activity">
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-primary-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span class="fw-semibold">Total día LUNES : </span><span><a href="javascript:void(0);" class="text-primary fw-semibold"> <?php echo $lunes; ?> soles</a></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaLunes; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-secondary-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día MARTES : <span class="fw-semibold"><?php echo $martes; ?> soles</span></span>
                                                                                    <span class="d-block fs-12 text-muted"></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaMartes; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-success-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día MIERCOLES: <span class="text-success fw-semibold"><?php echo $miercoles; ?> soles</span> <span class="avatar avatar-xs bg-purple-transparent avatar-rounded ms-1"><i class="ri-add-fill text-purple fs-12"></i></span></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaMiercoles; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-pink-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día JUEVES: <strong><?php echo $jueves; ?> soles</strong></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaJueves; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-warning-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día VIERNES: <strong><?php echo $viernes; ?> soles </strong></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaViernes; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-info-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día SÁBADO:<span class="text-info fw-semibold"> <?php echo $sabado; ?> soles </span></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaSabado; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        <li class="crm-recent-activity-content">
                                                                            <div class="d-flex align-items-top">
                                                                                <div class="me-3">
                                                                                    <span class="avatar avatar-xs bg-dark-transparent avatar-rounded">
                                                                                        <i class="bi bi-circle-fill fs-8"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="crm-timeline-content">
                                                                                    <span>Total día DOMINGO <strong> <?php echo $domingo; ?> soles</strong> <i class="ri-checkbox-circle-line text-success fs-16 align-middle"></i></span>
                                                                                </div>
                                                                                <div class="flex-fill text-end">
                                                                                    <span class="d-block text-muted fs-11 op-7"><?php echo $horaDomingo; ?></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                        
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End::row-1 -->

    
                                
                                          


                                                              <!--Fin-Contenido-->


                                                              <div class="modal fade" id="ModalNnotificacion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                                                                <div class="modal-dialog modal-lg" style="width: 50% !important;" >
                                                                  <div class="modal-content">
                                                                    <div class="modal-header">
                                                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                                       <!-- <img src="../public/images/notificacion.png"> -->
                                                                       <h1 class="modal-title" id="fechaaviso">ALERTAS DEL DÍA DE HOY</h1>
                                                                    </div>

                                                                        <form name="formularionnotificacion" id="formularionnotificacion" method="POST">
                                                                              <input type="hidden" name="fechaaviso" id="fechaaviso">
                                                                          <div class="table-responsive" id="">
                                                                        <table id="listanotificaciones" class="table table-sm table-striped table-bordered table-condensed table-hover nowrap">
                                                                                      <thead>
                                                                                        <th>Notificación</th>
                                                                                        <th>Documento</th>
                                                                                        <th>Cliente</th>
                                                                                        <th >Proxima aviso</th>
                                                                                        <th >---</th>
                                                                                      </thead>
                                                                                      <tbody>
                                                                                      </tbody>
                                                                                    </table>
                                                                            </div>

                                                                 <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                                  <!--   <button class="btn btn-primary" type="button" id="btnguardarnnotificacion" name="btnguardarnnotificacion" value="">
          <i class="fa fa-save"></i> OK
          </button> -->

                                                                      <button type="button" class="btn btn-danger" data-dismiss="modal">OK</button>
                                                                 </div>

                                                                    <div class="modal-footer">
                                                                    </div>
                                                                    </form>



                                                                  </div>
                                                                </div>
                                                              </div>






                                                              <div class="modal fade" id="ModalComprobantes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                                                                <div class="modal-dialog modal-lg" style="width: 30% !important;" >
                                                                  <div class="modal-content">
                                                                    <div class="modal-header">
                                                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                                       <!-- <img src="../public/images/notificacion.png"> -->
                                                                       <h1 class="modal-title" id="fechaaviso">COMPROBANTES PENDIENTES</h1>
                                                                    </div>

                                                                        <table id="listacomprobantes" class="table table-sm table-striped table-bordered table-condensed table-hover nowrap">
                                                                                      <thead>
                                                                                        <th>Fecha</th>
                                                                                        <th>Estado</th>
                                                                                        <th>Cantidad</th>
                                                                                        <th>Comprobante</th>
                                                                                      </thead>
                                                                                      <tbody>
                                                                                      </tbody>

                                                                                    </table>



                                                                 <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                                      <button type="button" class="btn btn-danger" data-dismiss="modal">OK</button>
                                                                 </div>

                                                                    <div class="modal-footer">
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

                                <!-- JSVector Maps JS -->
                                <script src="../assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
                                <!-- JSVector Maps MapsJS -->
                                <script src="../assets/libs/jsvectormap/maps/world-merc.js"></script>
                                <!-- Apex Charts JS -->
                                <script src="../assets/libs/apexcharts/apexcharts.min.js"></script>
                                <!-- Chartjs Chart JS -->
                                <script src="../assets/libs/chart.js/chart.min.js"></script>
                                <script type="text/javascript" src="scripts/caja.js"></script>
                                <!-- CRM-Dashboard -->
                                <script src="../assets/js/crm-dashboard.js"></script>
                             
  
                              <script>
                                  var totaArticulosRegistrados = <?php echo $totaArticulosRegistrados; ?>;
                                  var totalcategoriaActiva = <?php echo $totalcategoriaActiva; ?>;
                                  var totaUsuarioRegistrados = <?php echo $totaUsuarioRegistrados; ?>;
                                  var totaClientesRegistrados = <?php echo $totaClientesRegistrados; ?>;

                                  var totalSumado = totaArticulosRegistrados + totalcategoriaActiva + totaUsuarioRegistrados + totaClientesRegistrados;
                                  document.getElementById('total-items-value').textContent = totalSumado.toLocaleString();

                                  Chart.defaults.elements.arc.borderWidth = 0;
                                  Chart.defaults.datasets.doughnut.cutout = '85%';
                                  var chartInstance = new Chart(document.getElementById("leads-source"), {
                                      type: 'doughnut',
                                      data: {
                                          datasets: [{
                                              label: 'My First Dataset',
                                              data: [totaClientesRegistrados,totaArticulosRegistrados, totalcategoriaActiva, totaUsuarioRegistrados, ],
                                              backgroundColor: [
                                                'rgb(38, 191, 148)',       // Verde
                                                  'rgb(35, 183, 229)',      // Morado
                                                  'rgb(132, 90, 223)',      // Celeste
                                                  'rgb(245, 184, 73)',      // Amarillo
                                              ]
                                        }]
                                      },
                                      plugins: [{
                                          afterUpdate: function (chart) {
                                              const arcs = chart.getDatasetMeta(0).data;

                                              arcs.forEach(function (arc) {
                                                  arc.round = {
                                                      x: (chart.chartArea.left + chart.chartArea.right) / 2,
                                                      y: (chart.chartArea.top + chart.chartArea.bottom) / 2,
                                                      radius: (arc.outerRadius + arc.innerRadius) / 2,
                                                      thickness: (arc.outerRadius - arc.innerRadius) / 2,
                                                      backgroundColor: arc.options.backgroundColor
                                                  }
                                              });
                                          },
                                          afterDraw: (chart) => {
                                              const {
                                                  ctx,
                                                  canvas
                                              } = chart;

                                              chart.getDatasetMeta(0).data.forEach(arc => {
                                                  const startAngle = Math.PI / 2 - arc.startAngle;
                                                  const endAngle = Math.PI / 2 - arc.endAngle;

                                                  ctx.save();
                                                  ctx.translate(arc.round.x, arc.round.y);
                                                  ctx.fillStyle = arc.options.backgroundColor;
                                                  ctx.beginPath();
                                                  ctx.arc(arc.round.radius * Math.sin(endAngle), arc.round.radius * Math.cos(endAngle), arc.round.thickness, 0, 2 * Math.PI);
                                                  ctx.closePath();
                                                  ctx.fill();
                                                  ctx.restore();
                                              });
                                          }
                                      }]
                                  });
                            </script>





                              <script type="text/javascript">
                              function reloadPage () {
                              location.reload (true)
                              }

                                toastr.options = {
                                              closeButton: false,
                                              debug: false,
                                              newestOnTop: false,
                                              progressBar: false,
                                              rtl: false,
                                              positionClass: 'toast-bottom-full-width',
                                              preventDuplicates: false,
                                              onclick: null
                                          };


                              showComprobantes();

                              $("#formularionnotificacion").on("submit",function(e)
                                  {
                                      guardaryeditarnotificacion(e);
                                  });


                              var now = new Date();
                              var day = ("0" + now.getDate()).slice(-2);
                              var month = ("0" + (now.getMonth() + 1)).slice(-2);
                              var fechahoy = now.getFullYear()+"-"+(month)+"-"+(day);
                              $("#fechaaviso").val(fechahoy);

                              function guardaryeditarnotificacion(e)
                              {
                                  e.preventDefault(); //
                                  var formData = new FormData($("#formularionnotificacion")[0]);


                                  $.ajax({
                                      //url: "../ajax/ventas.php?op=editarnotificacion",
                                      type: "POST",
                                      data: formData,
                                      contentType: false,
                                      processData: false,
                                      success: function(datos)
                                      {
                                            //toastr.success(datos);
                                            //tabla.ajax.reload();
                                      }

                                  });
                                  $("#ModalNnotificacion").modal('hide');
                                  $("#ModalComprobantes").modal('hide');
                              }



                              $(document).ready(function()
                              {
                                    showNotification();
                                    setTimeout(function ()
                                    {
                                        $("#ModalNnotificacion").modal('hide');
                                         //showComprobantes();
                                    }, 8000);

                              });








                              function nextM(idnotificacion)
                              {

                                  $.post("../ajax/ventas.php?op=avanzar", {idnotificacion : idnotificacion}, function(e){
                                          toastr.success(e);
                                        });

                              }


                              function showNotification() {
                                 tabla=$('#listanotificaciones').dataTable(
                                  {
                                      "aProcessing": true,
                                      "aServerSide": true,
                                      dom: 'Bfrtip',
                                      searching:false,
                                      buttons: [],
                                      "ajax":
                                              {
                                                  url: '../ajax/ventas.php?op=notificaciones&fechanoti='+fechahoy,
                                                  type : "get",
                                                  dataType : "json",
                                                  error: function(e){
                                                  console.log(e.responseText);
                                                  }
                                              },

                                       "rowCallback":
                                       function( row, data ) {
                                        if (data) {
                                          $("#ModalNnotificacion").modal('show');
                                        }
                                      },

                                      "bDestroy": true,
                                      "iDisplayLength": 5,//Paginación
                                      "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
                                  }).DataTable();

                              };







                              function showComprobantes()
                               {
                                 tabla=$('#listacomprobantes2').dataTable(
                                  {
                                      "aProcessing": true,
                                      "aServerSide": true,
                                      "bPaginate": false,
                                      "paging": false,
                                      "bInfo": false,
                                      dom: 'Bfrtip',
                                      searching:false,
                                      lengthChange: false,


                                      buttons: [],
                                      "ajax":
                                              {
                                                  url: '../ajax/ventas.php?op=ComprobantesPendientes',
                                                  type : "get",
                                                  dataType : "json",
                                                  error: function(e){
                                                  console.log(e.responseText);
                                                  }
                                              },

                                       "rowCallback":
                                       function( row, data ) {
                                        if (data) {
                                          //$("#ModalComprobantes").modal('show');
                                        }
                                      },

                                      "fnDrawCallback":
                                      function(oSettings) {
                                        $('.dataTables_paginate').hide();
                                      },

                                      "bDestroy": true,
                                      "iDisplayLength": 20,//Paginación
                                      "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
                                  }).DataTable();

                              }

                     


                              function estadoNoti()
                              {
                                  var estanoti = document.getElementById("estadonoti").checked;
                                  if (estanoti==true) {
                                      $("#selestado").val("1");
                                  }else{
                                      $("#selestado").val("0");
                                  }
                              }



                            // Prepare the data for ApexCharts
                        var categories = [<?php echo $fechasv; ?>];
                        var dataValues = [<?php echo $totalesv; ?>];

                        var dataPoints = categories.map(function(category, index) {
                            return {
                                x: category,
                                y: dataValues[index]
                            };
                        });

                        var options = {
                            series: [{
                                type: 'bar',
                                name: 'Gráfico de venta x mes',
                                data: dataPoints,
                                colors: ['#081A51', 'rgba(54, 162, 235, 0.2)', 'green', 'rgba(255, 99, 132, 0.2)', 'orange']
                            }],
                            chart: {
                                height: 350,
                                animations: {
                                    speed: 500
                                },
                                dropShadow: {
                                    enabled: true,
                                    enabledOnSeries: undefined,
                                    top: 8,
                                    left: 0,
                                    blur: 3,
                                    color: '#000',
                                    opacity: 0.1
                                }
                            },
                            colors: ["rgb(132, 90, 223)", "rgba(35, 183, 229, 0.85)", "rgba(119, 119, 142, 0.05)"],
                            dataLabels: {
                                enabled: false
                            },
                            grid: {
                                borderColor: '#f1f1f1',
                                strokeDashArray: 3
                            },
                            stroke: {
                                curve: 'smooth',
                                width: [2, 2, 0],
                                dashArray: [0, 5, 0]
                            },
                            xaxis: {
                                categories: categories,
                                axisTicks: {
                                    show: false
                                }
                            },
                            yaxis: {
                                labels: {
                                    formatter: function(value) {
                                        return "$" + value;
                                    }
                                }
                            },
                            tooltip: {
                                y: [{
                                    formatter: function(e) {
                                        return void 0 !== e ? "$" + e.toFixed(0) : e
                                    }
                                }]
                            },
                            legend: {
                                show: true,
                                customLegendItems: ['Gráfico de venta x mes'],
                                inverseOrder: true
                            },
                            title: {
                                text: 'Gráfico de venta x mes',
                                align: 'left',
                                style: {
                                    fontSize: '.8125rem',
                                    fontWeight: 'semibold',
                                    color: '#8c9097'
                                }
                            },
                            markers: {
                                hover: {
                                    sizeOffset: 5
                                }
                            }
                        };

                        document.getElementById('crm-revenue-analytics').innerHTML = '';
                        var chart = new ApexCharts(document.querySelector("#crm-revenue-analytics"), options);
                        chart.render();

                        function revenueAnalytics() {
                            chart.updateOptions({
                                colors: ["rgba(" + myVarVal + ", 1)", "rgba(35, 183, 229, 0.85)", "rgba(119, 119, 142, 0.05)"],
                            });
                        }
                
                

                        var categoriesc = [<?php echo $fechasc; ?>];
        var dataValuesc = [<?php echo $totalesc; ?>];

        var options1 = {
            series: [{
                name: 'Gráfico de compra x mes',
                type: 'bar',
                data: dataValuesc,
                colors: ['#081A51', 'green', 'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'orange']
            },
            {
                name: 'Promedio',
                type: 'line',
                data: dataValuesc,
                color: '#FF5733',  // Color específico para "Average". Puedes ajustarlo según tus necesidades.
                marker: {
                    lineWidth: 2,
                    lineColor: '#FF5733', // Ajusta este color según lo que necesites.
                    fillColor: 'white'
                }
            }],
            chart: {
                type: 'bar',
                height: 180,
                toolbar: {
                    show: false
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            },
            colors: ["rgb(132, 90, 223)", "#e4e7ed"],
            plotOptions: {
                bar: {
                    columnWidth: '60%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2
            },
            legend: {
                show: true,
                position: 'top'
            },
            yaxis: {
                title: {
                    text: 'Reporte de compras',
                    style: {
                        color: '#adb5be',
                        fontSize: '13px',
                        fontFamily: 'poppins, sans-serif',
                        fontWeight: 600
                    }
                }
            },
            xaxis: {
                categories: categoriesc,
                axisBorder: {
                    show: true,
                    color: 'rgba(119, 119, 142, 0.05)'
                },
                axisTicks: {
                    show: true,
                    color: 'rgba(119, 119, 142, 0.05)',
                    width: 6
                },
                labels: {
                    rotate: -90
                }
            }
        };

        document.getElementById('crm-profits-earned').innerHTML = '';
        var chart1 = new ApexCharts(document.querySelector("#crm-profits-earned"), options1);
        chart1.render();

        function crmProfitsearned() {
            chart1.updateOptions({
                colors: ["rgba(" + myVarVal + ", 1)", "#ededed"],
            });
        }


                

            





                              </script>


                              <?php

}

ob_end_flush();

?>
