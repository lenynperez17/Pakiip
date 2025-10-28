
// function listarValidarComprobantes()
// {
//         "ajax":
//                 {
//                     url: '../ajax/ventas.php?op=listarValidarComprobantesSiempre=',
//                     type : "get",
//                     dataType : "json",
//                     error: function(e){
//                     console.log(e.responseText);
//                     }
//                 },

//          "rowCallback":
//          function( row, data ) {

//         },


// setInterval( function () {
// tabla.ajax.reload(null, false);
// }, 5000 );
// }



$('body').on("keydown", function(e) {
            if (e.ctrlKey && e.shiftKey && e.which === 83) {
                alert("You pressed Ctrl + Shift + s");
                e.preventDefault();
            }else if(e.which===112){
                location.href ="factura";
            // }else if(e.which===80){
            //     $("#myModalArt").modal('show');
            //     $("#itemno").val('1')
            //     iit=$("#itemno").val()
            //     listarArticulos();
            //     e.preventDefault();
            // }else if(e.which===78){
            //     mostrarform(true);
            //     e.preventDefault();
            }else if(e.which===115){
                location.href ="boleta";
            }
        });




        setTimeout(function(){
        location.reload();
        },60000);
