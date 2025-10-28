var fecha = new Date();
var ano = fecha.getFullYear();
$("#ano").val(ano);

var mes = fecha.getMonth();
$("#mes").val(mes + 1);

// Lo primero que noto es que deberías llamar a calcularmargeng() al final
// Esto asegura que todas las llamadas y asignaciones previas se hayan completado.
// calcularmargeng();

// Declarando "alma" como una variable global para que pueda ser accesible en diferentes funciones.
var alma;

$.post("../ajax/inventarios.php?op=selectAlm", function(r) {
    $("#almacenlista").html(r);
    // Si no necesitas refresh de selectpicker, simplemente mantenlo comentado.
    // $('#almacenlista').selectpicker('refresh');
    
    alma = $("#almacenlista").val();

    $.post("../ajax/articulo.php?op=comboarticulomg&anor=" + ano + "&aml=" + alma, function(r) {
        $("#codigoInterno").html(r);
        // No estás haciendo nada con $("#codigoInterno").value; por lo tanto, puedes omitirlo o borrarlo.
    });
});

function actualizarartialma() {
    alma = $("#almacenlista").val();
    $.post("../ajax/articulo.php?op=comboarticulomg&anor=" + ano + "&aml=" + alma, function(r) {
        $("#codigoInterno").html(r);
        // Si no necesitas refresh de selectpicker, simplemente mantenlo comentado.
        // $("#codigoInterno").selectpicker('refresh');
    });
}

function calcularmargeng() {
    var opcion = $("#opcion1").val();
    var idarticulo = $("#codigoInterno").val();
    var ano = $("#ano").val();
    var mes = $("#mes").val();

    var tabla = $('#tbllistado').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [],
        "ajax": {
            url: '../ajax/repmargenganancia.php?op=calcularmargen&idart=' + idarticulo + '&ano=' + ano + '&mes=' + mes + '&opt=' + opcion,
            type: "post",
            dataType: "json",
            error: function(e) {
                console.log(e.responseText);
            }
        },
        "bDestroy": true,
        "iDisplayLength": 5,
        "order": [[0, "asc"]]
    }).DataTable();

    // ¿Estás seguro de que necesitas recargar la tabla aquí? Si es una inicialización, probablemente no necesitas esta línea.
    // tabla.ajax.reload();
}

// Ahora sí, llamamos a calcularmargeng().
calcularmargeng();
