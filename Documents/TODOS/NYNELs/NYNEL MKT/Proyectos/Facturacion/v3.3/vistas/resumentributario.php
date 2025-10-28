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

        <!-- <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'> -->

        <style>
            .dt-buttons {
                float: left !important;
                position: relative;
                top: 20px;

                .btn.btn-sm {
                    padding: 0.26rem 0.5rem;
                    border-radius: 0.25rem;
                    font-size: 0.75rem;
                    margin-left: 2px;
                }
            }
        </style>

        <div class="content-start transition">
            <!-- Main content -->
            <section class="container-fluid dashboard">
                <div class="content-header">
                    <h5>RESUMEN TRIBUTARIO DE BOLETAS/FACTURAS</h5>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">

                                <form name="formulario" id="formulario">
                                    <input type="hidden" name="idempresa" id="idempresa"
                                        value="<?php echo $_SESSION['idempresa']; ?>">
                                    <div class="row">

                                        <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                            <label> Fecha Inicio: </label>
                                            <input type="date" name="FechaDesdeIni" id="FechaDesdeIni" class="form-control">
                                        </div>

                                        <div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                            <label> Fecha Fin: </label>
                                            <input type="date" name="FechaHastaFin" id="FechaHastaFin" class="form-control">
                                        </div>


                                        <div hidden class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                            <label> Moneda: </label>
                                            <select class="form-control" name="tmonedaa" id="tmonedaa">
                                                <option value="PEN">PEN</option>
                                                <option value="USD">USD</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div hidden class=" form-group col-lg-12 justify-content-center text-center mt-3 mb-3">
                                        <button class="btn btn-danger btn-sm" type="button" id="btnReportePDF"
                                            data-toggle="tooltip" title="Reporte pdf por mes">Reporte PDF
                                        </button>
                                    </div>

                                    <div hidden class=" form-group col-lg-12 justify-content-center text-center mt-3 mb-3">
                                        <button class="btn btn-success btn-sm" type="button" id="btnReporteExcel"
                                            data-toggle="tooltip" title="Reporte pdf por mes">Reporte Excel
                                        </button>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="panel-body table-responsive" id="listadoTributario">
                                            <table id="tbllistadoVentas"
                                                class="table table-striped table-bordered table-condensed table-hover"
                                                style="width: 1900px !important">
                                                <thead>
                                                    <th>FECHA</th>
                                                    <th>TIPO CPE</th>
                                                    <th>SERIE/NUMERO</th>
                                                    <th>RUC/DNI</th>
                                                    <th>RZ/NOMBRE</th>
                                                    <th>OP. GRAVADAS</th>
                                                    <th>OP. GRATUITAS</th>
                                                    <th>OP. EXONERADAS</th>
                                                    <th>OP. INAFECTAS</th>
                                                    <th>TOTAL DSCTO</th>
                                                    <th>IGV</th>
                                                    <th>IMP. BOLSA</th>
                                                    <th>TOTAL</th>
                                                    <th>ESTADO</th>
                                                    <th>RESPUESTA SUNAT</th>
                                                    <th>OBSERVACION</th>
                                                </thead>
                                                <!-- <tfoot>
                                                    <tr>
                                                        <th>Totales</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot> -->
                                                <tbody>
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>

                            </div>

                            </form>

                            <!--Fin centro -->
                        </div><!-- /.box -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </section><!-- /.content -->

        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.4/xlsx.full.min.js"></script>

        

        < <?php
    } else {
        require 'noacceso.php';
    }
    require 'footer.php';
    ?>
        <script type=" text/javascript" src="scripts/scriptresumentributario.js"></script>
        <!-- <script   type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script> -->
        <?php
}
ob_end_flush();
?>