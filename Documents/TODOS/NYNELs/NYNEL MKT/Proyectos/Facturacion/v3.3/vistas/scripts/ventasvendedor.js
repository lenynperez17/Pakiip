var fecha = new Date();
var ano = fecha.getFullYear();
//var mes=fecha.getFullMonth();
$("#ano").val(ano);
//$("#mes").val(mes);

$idempresa=$("#idempresa").val();

// Carga de combo para vendedores =====================
    $.post("../ajax/vendedorsitio.php?op=selectVendedorsitio&idempresa="+$idempresa, function(r){
            $("#vendedorsitio").html(r);
            //$('#vendedorsitio').selectpicker('refresh');
    });




function listarVentasVendedor()
{

    var $vendedor=$("#vendedorsitio option:selected").val();
    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();
    var Tsaldo = 0.00;

    if ($mes=='00') {
        //Si la consulta es por vendedor, año.
        $.post('../ajax/ventas.php?op=ventaVendedorFacturaAno&vendedor='+$vendedor+'&ano='+$ano+'&idempresa='+$idempresa, function(dataf,status){
                    $("#Tfactura").html(dataf);
                    
            });
        
        $.post('../ajax/ventas.php?op=ventaVendedorBoletaAno&vendedor='+$vendedor+'&ano='+$ano+'&idempresa='+$idempresa, function(datab,status){
                    $("#Tboleta").html(datab);
                    
            });

            
    }else if ($dia=='00'){
    
        //Si la consulta es por vendedor, año y mes
        $.post('../ajax/ventas.php?op=ventaVendedorFactura&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(dataf,status){
                    $("#Tfactura").html(dataf);
                    //Tsaldo=Tsaldo+parseFloat(dataf);
                    Tsaldo=Tsaldo+0;
                    $("#Tsaldo").html(Tsaldo);
                    
            });
        
        $.post('../ajax/ventas.php?op=ventaVendedorBoleta&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(datab,status){
                    $("#Tboleta").html(datab);
                    //Tsaldo=Tsaldo+parseFloat(datab);
                    Tsaldo=Tsaldo+0;
                    $("#Tsaldo").html(Tsaldo);
                    
            });

            
    
        $.post('../ajax/ventas.php?op=ventaVendedorNota&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(datan,status){
                    $("#Tnota").html(datan);
                    Tsaldo=Tsaldo+parseFloat(datan);
                    $("#Tsaldo").html(Tsaldo.toFixed(2));
                    
            });
        
        $.post('../ajax/ventas.php?op=compraVendedor&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(datac,status){
                    $("#Tcompra").html(datac);
                    Tsaldo=Tsaldo-parseFloat(datac);
                    $("#Tsaldo").html(Tsaldo);
                    
            });
        $.post('../ajax/ventas.php?op=gastoVendedor&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(datag,status){
                    $("#Tgasto").html(datag);
                     Tsaldo=Tsaldo-parseFloat(datag);
                    $("#Tsaldo").html(Tsaldo);
                    
            });
        $.post('../ajax/ventas.php?op=planillaVendedor&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&idempresa='+$idempresa, function(datap,status){
                    $("#Tplanilla").html(datap);
                     Tsaldo=Tsaldo-parseFloat(datap);
                    $("#Tsaldo").html(Tsaldo.toFixed(2));
                    
            }); 
        //Tsaldo=Tsaldo+parseInt(Tfactura.value); //$("#Tfactura").val();  //$("#mes option:selected").val();
        //Tsaldo=Tsaldo+parseInt($("#Tboleta").text()); //$("#Tboleta").val();  //$("#mes option:selected").val();
        //tsaldo=parseInt($("#Tboleta").innerHTML());
        //$("#Tsaldo").html(Tsaldo);
    }else{

        $.post('../ajax/ventas.php?op=ventaVendedorBoletadia&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa, function(datab,status){
            $("#Tboleta").html(datab);
            //Tsaldo=Tsaldo+parseFloat(datab);
            Tsaldo=Tsaldo+0;
            $("#Tsaldo").html(Tsaldo);
        });


        $.post('../ajax/ventas.php?op=ventaVendedorFacturadia&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa, function(dataf,status){
            $("#Tfactura").html(dataf);
            //Tsaldo=Tsaldo+parseFloat(datab);
            Tsaldo=Tsaldo+0;
            $("#Tsaldo").html(Tsaldo);
        });


        $.post('../ajax/ventas.php?op=ventaVendedorNotaxdia&vendedor='+$vendedor+'&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa, function(datan,status){
            $("#Tnota").html(datan);
            Tsaldo=Tsaldo+parseFloat(datan);
            $("#Tsaldo").html(Tsaldo.toFixed(2));
        });


        
    }

    

}




$(document).ready(function() {
    $('#mes').change(function() {
        var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var month = $(this).val() - 1; // los índices de los arrays en JavaScript empiezan en 0
        var days = daysInMonth[month];

        // Si el mes es febrero, comprueba si es un año bisiesto
        if (month === 1) {
            var year = $('#ano').val();
            if ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0) {
                days = 29;
            }
        }

        var options = '<option value="00">Ninguno</option>';
        for (var i = 1; i <= days; i++) {
            var day = (i < 10) ? '0' + i : i; // agrega un cero a los días de un solo dígito
            options += '<option value="' + day + '">' + i + '</option>';
        }
        $('#dia').html(options);
    });
});



    $('#reporteVendedorMensual').on('click', function () {
        var vendedor = $('#vendedorsitio').val();
        var totalFactura = $('#Tfactura').text();
        var totalBoleta = $('#Tboleta').text();
        var totalNota = $('#Tnota').text();
        var ano = $('#ano').val();
        var mes = $('#mes').val();

        if (!vendedor || mes == '00') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, selecciona un mes válido...',
            });
            return;
        }

        var doc = new jsPDF();

        // Añadir fecha y hora actuales
        var now = new Date();
        var fecha = now.toLocaleDateString();
        var hora = now.toLocaleTimeString();

        // Sumar los totales
        var montoTotal = parseFloat(totalFactura) + parseFloat(totalBoleta) + parseFloat(totalNota);

        doc.setFontSize(22);
        doc.text('REPORTE DE VENDEDOR MENSUAL', 105, 30, null, null, 'center');
        doc.setFontSize(16);
        doc.text('Fecha: ' + fecha + '      Hora: ' + hora, 105, 40, null, null, 'center');

        doc.setFontSize(12);
        doc.text('Vendedor: ' + vendedor, 20, 60);
        doc.text('Total Factura: ' + totalFactura, 20, 70);
        doc.text('Total Boleta: ' + totalBoleta, 20, 80);
        doc.text('Total Nota: ' + totalNota, 20, 90);

        doc.setFontSize(14);
        doc.text('MONTO DE VENTA TOTAL: ' + montoTotal.toFixed(2), 20, 110);

        // Crear un nombre de archivo basado en el vendedor, mes y año
        var fileName = 'reporte_' + vendedor + '_' + mes + '_' + ano + '.pdf';

        doc.save(fileName);
    });


    $('#reporteVendedorDiario').on('click', function () {
        var vendedor = $('#vendedorsitio').val();
        var totalFactura = $('#Tfactura').text();
        var totalBoleta = $('#Tboleta').text();
        var totalNota = $('#Tnota').text();
        var ano = $('#ano').val();
        var mes = $('#mes').val();
        var dia = $('#dia').val();
    
        if (!vendedor || dia == '00') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, selecciona un un día válido.',
            });
            return;
        }
    
        var doc = new jsPDF();
    
        // Añadir fecha y hora actuales
        var now = new Date();
        var fecha = now.toLocaleDateString();
        var hora = now.toLocaleTimeString();
    
        // Sumar los totales
        var montoTotal = parseFloat(totalFactura) + parseFloat(totalBoleta) + parseFloat(totalNota);
    
        doc.setFontSize(22);
        doc.text('REPORTE DE VENDEDOR DIARIO', 105, 30, null, null, 'center');
        doc.setFontSize(16);
        doc.text('Fecha: ' + fecha + '      Hora: ' + hora, 105, 40, null, null, 'center');
    
        doc.setFontSize(12);
        doc.text('Vendedor: ' + vendedor, 20, 60);
        doc.text('Total Factura: ' + totalFactura, 20, 70);
        doc.text('Total Boleta: ' + totalBoleta, 20, 80);
        doc.text('Total Nota: ' + totalNota, 20, 90);
    
        doc.setFontSize(14);
        doc.text('MONTO DE VENTA TOTAL: ' + montoTotal.toFixed(2), 20, 110);
    
        // Crear un nombre de archivo basado en el vendedor, mes y año
        var fileName = 'reporte_' + vendedor + '_' + dia + '_' + mes + '_' + ano + '.pdf';
    
        doc.save(fileName);
    });
    


