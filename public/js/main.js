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


        console.log(options);


        console.log($(this).data('test'));

        console.log(key);

        window.location.href = '/download/';



        //$('#res').html('Выбрана команда: <strong>' + key + '</strong>');
    }
});


});
