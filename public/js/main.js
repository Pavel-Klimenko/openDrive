$( document ).ready(function() {

    $("#newFile").change(function(){ // событие выбора файла
        $("#uploadFileForm").submit(); // отправка формы
    });


    $( "#createFolder" ).click(function() {
        let folderPath = $('#currentPath').val();
        createFolder(folderPath);
    });




    let arrHideGoBack = ['/get-files/user_files', '/basket/'];
    if (in_array(window.location.pathname, arrHideGoBack) !== -1) {
        $('#goBackButton').hide();
    } else {
        $('#goBackButton').show();
    }



    //folder context menu
    $.contextMenu({
        selector: '.context-menu-folders',
        items: {delete:  {name: 'Delete completely'}},
        callback: function(menuAction, options) {
            let folderPath = $(this).data('folder-path');
            let folderName = $(this).data('folder-name');

/*            console.log(folderPath);
            console.log(folderName);
            console.log(menuAction);*/

            if (menuAction === 'delete') {
                deleteFolder(folderPath, folderName)
            }
        }
    });


    getExchangeBuffer(function (response) {
        console.log(response);

        let contextMenu = {
                download:  {name: 'Download'},
                rename:  {name: 'Rename'},
            };

        if (response.status == 'BUFFER_NONE') {
            contextMenu['copy'] = {name: 'Copy'};
            contextMenu['move'] = {name: 'Move'};
        } else if(response.status == 'BUFFER') {
            contextMenu['paste'] = {name: 'Paste'};
        }

        contextMenu['props'] = {name: 'Properties'};
        contextMenu['delete'] = {name: 'Delete'};


        //Inside the basket
        let pathName = window.location.pathname;
        if (pathName.includes('/basket/')) {
             contextMenu = {
                restore:  {name: 'Restore'},
                delete_completely:  {name: 'Delete completely'},
            };
        }


        $.contextMenu({
            selector: '.context-menu',
            items: contextMenu,
            callback: function(menuAction, options) {


                let fileName = $(this).data('file-name');
                let fileLocation = $(this).data('file-url');
                let fileSize = $(this).data('file-size');
                let formattedFilePath = $('#formattedCurrentPath').val();
                let filePath = $('#currentPath').val();


                if (menuAction === 'copy' || menuAction === 'move') {
                    copyFile(filePath, fileName, menuAction);
                }


                if (menuAction === 'paste') {
                    pasteFile(filePath);
                }


                if (menuAction === 'rename') {
                    renameFile(filePath, fileName);
                }


                if (menuAction === 'download') {
                    downLoadFile($(this).data('file-url'));
                }


                if (menuAction === 'props') {
                    showFileProps(fileName, fileLocation, fileSize);
                }


                if (menuAction === 'delete') {
                    deleteFile(filePath, fileName, 'N');
                }


                if (menuAction === 'delete_completely') {
                    deleteFile(filePath, fileName, 'Y');
                }

                if (menuAction === 'restore') {
                    restoreFileFromBasket(fileName);
                }

            }

        });

    });

});


//TODO AJAX вынести в отдельный метод

function createFolder(folderPath) {
    console.log(folderPath);
    $('#popupCreateFolder').modal("show");
    $('.inputs input[name=FOLDER_PATH]').val(folderPath);
}

function pasteFile(filePath) {
    var request = $.ajax({
        url: "/file-paste/",
        type: "POST",
        dataType: "json",
        async: true,
        data: {filePath: filePath}
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function copyFile(filePath, fileName, action) {
    var request = $.ajax({
        url: "/file-copy/",
        type: "POST",
        dataType: "json",
        async: true,
        data: {
            filePath: filePath,
            fileName: fileName,
            action: action
        }
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function renameFile(filePath, fileName) {
    $('#popupFileRename').modal("show");
    $('.rename_inputs input[name=FILE_PATH]').val(filePath + '/');
    $('.rename_inputs input[name=FILE_OLD_NAME]').val(fileName);
    $('.rename_inputs input[name=FILE_NEW_MAME]').val(fileName);
}

function showFileProps(fileName, fileLocation, fileSize) {
    $('.modal-body').empty();
    $('#popupFileProps').modal("show");
    $('.modal-body').append(`<p><b>File name: </b>${fileName}</p>`);
    $('.modal-body').append(`<p><b>File location: </b>${fileLocation}</p>`);
    $('.modal-body').append(`<p><b>File size: </b>${fileSize}</p>`);
}

function deleteFolder(folderPath, folderName) {
    var request = $.ajax({
        url: "/folder-delete/",
        type: "POST",
        dataType: "json",
        async: true,
        data: {
            folderPath: folderPath,
            folderName: folderName,
        }
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function deleteFile(filePath, fileName, deleteCompletely) {
    var request = $.ajax({
        url: "/file-delete/",
        type: "POST",
        dataType: "json",
        async: true,
        data: {
            filePath: filePath,
            fileName: fileName,
            deleteCompletely: deleteCompletely,
        }
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function restoreFileFromBasket(fileName) {
    var request = $.ajax({
        url: "/file-restore/",
        type: "POST",
        dataType: "json",
        async: true,
        data: {fileName: fileName}
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function downLoadFile(url) {
    var link_url = document.createElement("a");
    link_url.download = url.substring((url.lastIndexOf("/") + 1), url.length);
    link_url.href = url;
    document.body.appendChild(link_url);
    link_url.click();
    document.body.removeChild(link_url);
    delete link_url;
}

function getExchangeBuffer(handleData) {
    var request = $.ajax({
        url: "/get-exchange-buffer",
        type: "POST",
        dataType: "json",
        async: true,
        //data: {skipSlot: skipSlot, full: full}
    });

    request.done(function (msg) {
        handleData(msg);
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function in_array(needle, haystack) {
    var found = 0;
    for (var i=0, len=haystack.length;i<len;i++) {
        if (haystack[i] == needle) return i;
        found++;
    }
    return -1;
}