/**
 * Created by infinety on 8/3/16.
 */
$(document).ready(function(){



    $('#uploadModal').on('show.bs.modal', function (e) {
        console.log('daa');
        uploadMethod();
    });


     uploadMethod = function(){

        //Uploader
        $('#files_container').dmUploader({
            url: url_upload,
            dataType: 'json',
            allowedTypes: ['image/*|application/*|text/plain'],
            extraData: function(){
                return {'folder' : path_folder }
            },
            onInit: function () {
                $.gallery.addLog("Init");
            },
            onDrag: function () {
                //console.log("DragOn");
                $('#files_container').addClass('upload-div');
            },
            onDragExit: function () {
                //console.log("DragOFF");
                $('#files_container').removeClass('upload-div');
            },
            onBeforeUpload: function (id) {
                $('#files_container').removeClass('upload-div');
            },
            onNewFile: function (id, file) {

                $.gallery.addFile('#files_container', id, file);

                //Only if is image
                if(file.type.match('image')){
                    /*** Begins Image preview loader ***/
                    if (typeof FileReader !== "undefined") {

                        var reader = new FileReader();

                        // Last image added
                        var img = $('#file-'+id).find('.img-responsive').eq(0);

                        reader.onload = function (e) {
                            img.attr('src', e.target.result);
                        }

                        reader.readAsDataURL(file);

                    } else {
                        // Hide/Remove all Images if FileReader isn't supported
                        $('#images-container').find('.img-responsive').remove();
                    }
                }
                /*** Ends Image preview loader ***/

            },
            onComplete: function () {
                //var filter = $('.filter').data('filter');
                //var sort = $("#sort-by").val();
                //getData(path_folder, sort, filter);
            },
            onUploadProgress: function (id, percent) {
                var percentStr = percent + '%';
                //console.log(percentStr);
                $.gallery.updateFileProgress(id, percentStr);
            },
            onUploadSuccess: function (id, data) {
                //Referesh this folder
                if(data.error){
                    new PNotify({
                        title: 'Error',
                        text: data.error,
                        type: 'error'
                    });
                    console.log(id);
                    $("#file-"+id).remove();
                }
                //Refresh this folder
                $(".refresh").trigger( "click" );
            },
            onUploadError: function (id, message) {
                //                $.gallery.addLog(message);
                console.log("dada");
            },
            onFileTypeError: function (file) {
                //                $.gallery.addLog(file);
            },
            onFileSizeError: function (file) {
                //                $.gallery.addLog(file);
            },
            onFallbackMode: function (message) {
                //                $.gallery.addLog(message);
                console.log("error");
            }
        });

    };


});