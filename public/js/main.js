$( document ).ready(function() {
    console.log( "ready!" );

/*
    $( ".disk-item" ).click(function() {
        alert( "Handler for .click() called." );
    });
*/


$.contextMenu({
    selector: '.context-menu',
    items: {
        download:  {name: 'Download'},
        copy:  {name: 'Copy'},
        replace: {name: 'Replace'},
        rename:  {name: 'Rename'},
        props:  {name: 'Properties'}
    },

    callback: function(key, options) {

        let fileName = $(this).data('file-name');
        let fileLocation = $(this).data('file-url');
        let fileSize = $(this).data('file-size');



        console.log(key);

        if (key === 'rename') {


            let filePath = $('#currentPath').val();


            console.log(filePath);

            $('#popupFileRename').modal("show");



            $('.rename_inputs input[name=FILE_PATH]').val(filePath + '/');
            $('.rename_inputs input[name=FILE_OLD_NAME]').val(fileName);
            $('.rename_inputs input[name=FILE_NEW_MAME]').val(fileName);

            //downLoadFile($(this).data('file-url'));
        }


        if (key === 'download') {
            downLoadFile($(this).data('file-url'));
        }


        if (key === 'props') {
            $('.modal-body').empty();
            $('#popupFileProps').modal("show");
            $('.modal-body').append(`<p><b>File name: </b>${fileName}</p>`);
            $('.modal-body').append(`<p><b>File location: </b>${fileLocation}</p>`);
            $('.modal-body').append(`<p><b>File size: </b>${fileSize}</p>`);
        }





    }

});


});



function downLoadFile(url) {
    var link_url = document.createElement("a");
    link_url.download = url.substring((url.lastIndexOf("/") + 1), url.length);
    link_url.href = url;
    document.body.appendChild(link_url);
    link_url.click();
    document.body.removeChild(link_url);
    delete link_url;
}
