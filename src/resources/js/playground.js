htmx.on('htmx:beforeRequest', function(event) {
    $('.spinner').show();
    event.detail.xhr.setRequestHeader('Sprig-Playground-Component', encodeURIComponent($('#component').val()));
    event.detail.xhr.setRequestHeader('Sprig-Playground-Variables', $('#variables').val());
});

htmx.on('htmx:afterSwap', function(event) {
    $('.spinner').hide();
    $('#sourcecode').val('');

    var sourcecode = $('#playground').html();

    if (sourcecode.indexOf('id="sprig-error"') === -1) {
        $('#sourcecode').val(sourcecode);
    }
});

$('#run').click(function() {
    $('.playground .btn.submit').removeClass('submit');

    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

