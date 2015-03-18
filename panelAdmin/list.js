 var tablaRegistro;
 var parametro;
 var funcion;
(function($, window, document, undefined) {
    'use strict';
    tablaRegistro = document.getElementById('tabla').value;    
    parametro = document.getElementById('parametro').value;    
    listar();
    console.log("works!");
})(jQuery, this, this.document);                            
function listar(){
    funcion = "gestion_"+tablaRegistro;
    loading('div-listado','../');
    $.post('proceso.php',{funcion:funcion,accion: 'listar',parametro:parametro}, function(data) {
       $('#div-listado').html(data);
       $('.fancybox').fancybox();
       $('#table-list').DataTable();
       $('#div-lote').slideDown();
    });      
}    
function editarStatusRegistro(id,nombre,accion,bu){
    idRegistro = id;
    nombreRegistro = nombre; 
    accionStatus = accion;
    beforeupdate = bu;

    if(accion === 'activo'){
         $('#tituloStatus').html('Activar Registro');
         $('#modal-body-statusRegistro').html('<p>Are you sure that you want to activate the record <b>'+nombre+'</b></p>');
         $('#btnStatusRegistro').html('Activar');
         $('#btnStatusRegistro').removeClass('btn btn-success');
         $('#btnStatusRegistro').removeClass('btn btn-warning');
         $('#btnStatusRegistro').addClass('btn btn-success');
    }else{
         $('#tituloStatus').html('Inactivar Registro');
         $('#modal-body-statusRegistro').html('<p>Are you sure that you want to delete the record <b>'+nombre+'</b></p>');
         $('#btnStatusRegistro').html('Inactivar');         
         $('#btnStatusRegistro').removeClass('btn btn-warning');
         $('#btnStatusRegistro').removeClass('btn btn-success');
         $('#btnStatusRegistro').addClass('btn btn-warning');
    }
    $('#statusRegistro').modal();
    $('#btnStatusRegistro').removeAttr('disabled');
    $('#modal-body-statusRegistro').show();
    $('#responseStatus').html('');             
}
function statusRegistro(){                
    $('#btnStatusRegistro').attr('disabled','');
    $('#modal-body-statusRegistro').hide();
    loading('responseStatus','../');
    $.ajax({
        cache: false,
        type: 'post',
        url: 'proceso.php',
        data:{
            funcion:'cambiarStatus',
            accion:accionStatus,
            id:idRegistro,
            tabla:tablaRegistro,
            beforeupdate:beforeupdate
        },
        success: function(response) {
            $('#responseStatus').html(response);
            listar(); 
        }
    }); 
}  
function seleccionar_visibles(b){
   $("#table-list > tbody > tr ").filter(':visible').find('.check-lote').prop('checked',b);               
}
function eliminar_seleccion(){
    var val = confirm('¿Esta seguro que desea eliminar todos los registros seleccionados?');
    if(val) {
        var s = '';
        $('.check-lote').filter(':checked').each(function( ) {
            s += $(this).val()+',';
        });           

        $.post('procesocomun.php',{funcion:'eliminar_lote',tabla:tablaRegistro,iditems:s}, function(data) {                        
            listar();
        });                   
    }
}
function condicion_seleccion(condicion){
    var val = confirm('¿Esta seguro que desea cambiar la condicion a '+condicion+' de todos los registros seleccionados?');
    if(val) {
        var s = '';
        $('.check-lote').filter(':checked').each(function( ) {
            s += $(this).val()+',';
        });           
        $.post('../lib/_procesocomun.php',{funcion:'condicion_lote',condicion:condicion,tabla:tablaRegistro,iditems:s}, function(data) {                        
            listar();
        });                   
    }
}


