htmx.on('htmx:beforeRequest', function(event) {
    event.detail.xhr.setRequestHeader('Sprig-Playground-Component', encodeURIComponent($('#component').val()));
    event.detail.xhr.setRequestHeader('Sprig-Playground-Variables', $('#variables').val());
});

htmx.on('htmx:afterSwap', function(event) {
    $('#sourcecode').val('');

    var sourcecode = $('#playground').html();

    if (sourcecode.indexOf('id="sprig-error"') === -1) {
        $('#sourcecode').val(sourcecode);
    }
});

$('#run').click(function() {
    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

