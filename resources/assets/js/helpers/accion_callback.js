function validateForm(){
    var obj=$("#header").val();
    var caso=0;
    if (obj.length>1){
        var result=isJsonH();
        if (result!='0'){
            $("#header").addClass('invalido');
            $("#resultHeader").text("Formato requerido / json");
            caso=1;
        }else{
            $("#header").removeClass('invalido');
            $("#resultHeader").text("");   
        }
    }
    if (caso==0){
        javascript:$('#plantillaForm').submit();
    }
}
 
function isJsonH(){
    try {
        JSON.parse($("#header").val());
    }catch (e){
        return 1;
    }        
    return 0;
}