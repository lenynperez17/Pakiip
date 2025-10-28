function focusAgrArt(e)
{
    if(e.keyCode===13  && !e.shiftKey){
       document.getElementById('clavea').focus();
    }
}



function empresa()
{
  empresa=$("#empresaConsulta").val();
  $.post("../ajax/enlacebd.php?op=verificarempresa",{"dbase": empresa});
}


function enter(e, field) {
    // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46
    key = e.keyCode ? e.keyCode : e.which
  
    if(e.keyCode===13  && !e.shiftKey)
      {
         document.getElementById('serienumero').focus();
      }
  
     }

     function focusTest(el)
     {
        el.select();
     }