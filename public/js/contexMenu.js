$( document ).ready(function() {
    //folder context menu
    $.contextMenu({
        selector: '.context-menu-folders',
        items: {delete:  {name: 'Delete completely'}},
        callback: function(menuAction, options) {
            let folderPath = $(this).data('folder-path');
            let folderName = $(this).data('folder-name');
            if (menuAction === 'delete') {
                deleteFolder(folderPath, folderName)
            }
        }
    });

    getExchangeBuffer(function (response) {
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
                //let formattedFilePath = $('#formattedCurrentPath').val();
                let filePath = $('#currentPath').val();

                if (menuAction === 'copy' || menuAction === 'move') {
                    copyFile(filePath, fileName, menuAction);
                } else if (menuAction === 'paste') {
                    if (filePath === response.file_path) {
                        alert('You can`t paste file to the same folder');
                    } else {
                        pasteFile(filePath);
                    }
                } else if (menuAction === 'rename') {
                    renameFile(filePath, fileName);
                } else if (menuAction === 'download') {
                    downLoadFile($(this).data('file-url'));
                } else if (menuAction === 'props') {
                    showFileProps(fileName, fileLocation, fileSize);
                } else if (menuAction === 'delete') {
                    deleteFile(filePath, fileName, 'N');
                } else if (menuAction === 'delete_completely') {
                    deleteFile(filePath, fileName, 'Y');
                } else if (menuAction === 'restore') {
                    restoreFileFromBasket(fileName);
                }
            }
        });


        //empty folder
        if (response.status == 'BUFFER') {
            $.contextMenu({
                selector: '.context-menu-empty-folder',
                items: {paste:  {name: 'Paste'}},
                callback: function(menuAction, options) {
                    let filePath = $('#currentPath').val();
                    if (menuAction === 'paste') {
                        pasteFile(filePath);
                    }
                }
            });
        }

    });
});


function pasteFile(filePath) {
    let data = {filePath: filePath};
    menuActionAjaxRequest('/file-paste/', data);
}

function copyFile(filePath, fileName, action) {
    let data = {
        filePath: filePath,
        fileName: fileName,
        action: action
    };
    menuActionAjaxRequest('/file-copy/', data);
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
    $('.modal-body').append(`<p><b>Name: </b>${fileName}</p>`);
    $('.modal-body').append(`<p><b>Location: </b>${fileLocation}</p>`);
    $('.modal-body').append(`<p><b>Size: </b>${fileSize}</p>`);
}

function deleteFolder(folderPath, folderName) {
    let data = {
        folderPath: folderPath,
        folderName: folderName,
    };
    menuActionAjaxRequest('/folder-delete/', data);
}

function deleteFile(filePath, fileName, deleteCompletely) {
    let data = {
        filePath: filePath,
        fileName: fileName,
        deleteCompletely: deleteCompletely,
    };
    menuActionAjaxRequest('/file-delete/', data);
}

function restoreFileFromBasket(fileName) {
    let data = {fileName: fileName};
    menuActionAjaxRequest('/file-restore/', data);
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
    });

    request.done(function (msg) {
        handleData(msg);
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function menuActionAjaxRequest(url, data) {
    var request = $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        async: true,
        data: data
    });

    request.done(function (msg) {
        console.log(msg);
        location.reload();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}