var fecha = new Date();
var ano = fecha.getFullYear();
var mes=fecha.getMonth();
var dia=fecha.getDate();

$("#ano").val(ano);
$("#mes").val(mes+1);
$("#dia").val(dia);

baja();
//$("#myModalClave").modal("show");


listarValidar();

//Función Listar
function listarValidar()
{

    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();


    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        
        

        buttons: [                
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdf'
                ],

        "ajax":
                {
                    url: '../ajax/articuloscreditex.php?op=listado',
                    type : "get",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
                },

         "rowCallback": 
         function( row, data ) {
            
        },

        "bDestroy": true,
        "iDisplayLength": 8,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();

$("#marcar").val("1");
}



function baja()
{
 var f=new Date();
 cad=f.getHours()+":"+f.getMinutes()+":"+f.getSeconds(); 

bootbox.prompt({
    title: "Ingrese la clave",
    inputType: 'text',
    callback: function (result) {
        if(result)
        {
            $.post("../ajax/articuloscreditex.php?op=clave&clave1="+result, function(data, status){
                data = JSON.parse(data);        
                //bootbox.alert(e);
                if (data=="1") {
                    $("#myModalClave").modal("show");
                    }else{
                        alert("Clave erronea");
                      baja();  
                }
                
            }); 
        }
    }

});

}







function refrescartabla()
{
tabla.ajax.reload();
$("#marcar").val("1");
}


