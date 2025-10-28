
$("#frmsubidajson").on("submit",function(e)
    {
        SubirJson(e);  
        
    });




//Iniciamos nuestra función jquery.
// $(function(){
// 	$('#enviar').submit(SubirJson); //Capturamos el evento click sobre el boton con el id=enviar	y ejecutamos la función seleccionado.
// });

function SubirJson(e){	
		e.preventDefault(); //No se activará la acción predeterminada del evento
		var archivos = document.getElementById("archivos");//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
		var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
		//Creamos una instancia del Objeto FormDara.
		var archivos = new FormData();
		/* Como son multiples archivos creamos un ciclo for que recorra la el arreglo de los archivos seleccionados en el input
		Este y añadimos cada elemento al formulario FormData en forma de arreglo, utilizando la variable i (autoincremental) como 
		indice para cada archivo, si no hacemos esto, los valores del arreglo se sobre escriben*/
		for(i=0; i<archivo.length; i++){
		archivos.append('archivo'+i,archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
		}

		/*Ejecutamos la función ajax de jQuery*/		
		$.ajax({
			url:'../ajax/subir.php', //Url a donde la enviaremos
			type:'POST', //Metodo que usaremos
			contentType:false, //Debe estar en false para que pase el objeto sin procesar
			data:archivos, //Le pasamos el objeto que creamos con los archivos
			processData:false, //Debe estar en false para que JQuery no procese los datos a enviar
			cache:false //Para que el formulario no guarde cache
		}).done(function(msg){//Escuchamos la respuesta y capturamos el mensaje msg
			MensajeFinal(msg)
		});







	}

function MensajeFinal(msg){
	$('.mensage').html(msg);//A el div con la clase msg, le insertamos el mensaje en formato  thml
	$('.mensage').show('slow');//Mostramos el div.
}




	
// 	$(document).ready (function() {
//     $.ajax({
//         url: "../sfs/contingencia/20100088917-01-F999-52.json",
//         success : function(data) {
//             //var o = JSON.parse(data);//A la variable le asigno el json decodificado
//             $('#tbllistado').dataTable( {
//               //  data : o,
//                 columns: [
//                      { "data": "tipOperacion" },
//            			 { "data": "fecEmision" },
//            			 { "data": "horEmision" },
//            			 { "data": "fecVencimiento" }          
//                 ],
//             });
//         }       
//     });
// });