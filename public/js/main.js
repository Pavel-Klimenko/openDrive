$( document ).ready(function() {
    $("#newFile").change(function(){ //file select event
        $("#uploadFileForm").submit(); //form submission
    });

    $( "#createFolder" ).click(function() {
        createFolder($('#currentPath').val());
    });

    $(".choose-tariff").click(function() {
        let tariff = $(this).data('tariff');

        if ($(this).data('tariff') == 'free') {
            document.location.href = '/register?tariff=' + tariff;
        } else {
            alert('This tariff is unavailable now');
        }
    });
});


function createFolder(folderPath) {
    console.log(folderPath);
    $('#popupCreateFolder').modal("show");
    $('.inputs input[name=FOLDER_PATH]').val(folderPath);
}

function in_array(needle, haystack) {
    var found = 0;
    for (var i=0, len=haystack.length;i<len;i++) {
        if (haystack[i] == needle) return i;
        found++;
    }
    return -1;
}