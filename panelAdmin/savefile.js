var phpupload = "upload.php";
var arrimgs = ['image/png','image/jpeg','image/jpeg','image/jpeg','image/gif','image/bmp','image/vnd.microsoft.icon','image/tiff','image/tiff','image/svg+xml','image/svg+xml'];
var arrothers = [];//['application/pdf'];
function saveFile(input,nombre) { 
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var tipo = input.files[0].type;//dame el MIME Type
           
            if(arrimgs.indexOf(tipo)<0 && arrothers.indexOf(tipo)<0){
                alert('The type of file is not supported');
                return;
            }            
            $('#'+nombre+'-preview').attr('src', e.target.result);
            uploadFile(input.files[0],nombre);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function uploadFile(file,nombre){
    // Uploading - for Firefox, Google Chrome and Safari
    var xhr = new XMLHttpRequest();
    // Update progress bar
    xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
            var percentComplete = evt.loaded * 100 / evt.total;
            $('#'+nombre+'-progressbar').html(percentComplete.toFixed(2)+'%');
        } else {
          // Unable to compute progress information since the total size is unknown
          $('#'+nombre+'-progressbar').html('unknown size');
        }
    }, false);
    xhr.onreadystatechange = function (evt) {
        if (xhr.readyState === 4) { 
            if(xhr.status === 200){
                if(xhr.responseText === 'error'){    
                    alert('Error loading the file');
                }else{
                    $('#'+nombre+'-progressbar').html('Complete 100%');
                    $('#'+nombre).val(xhr.responseText);
                    if (typeof yourFunctionName === 'function') { 
                        finishload(); 
                    }
                }
                fileLock = false;
            }else{
                alert('Error loading the file');
            }                
      }
    };    
    xhr.open("post", phpupload, true);
    var formData = new FormData();  
    formData.append('file',file); 
    xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');  
    fileLock = true;//bloquear otra accion mientras archivo sube
    xhr.send(formData);// Send the file (doh)            
}