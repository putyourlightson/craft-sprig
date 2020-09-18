htmx.on('htmx:beforeRequest', function(event) {
    event.detail.xhr.setRequestHeader('Sprig-Playground-Component', encodeURIComponent($('#component').val()));
    event.detail.xhr.setRequestHeader('Sprig-Playground-Variables', $('#variables').val());
});

htmx.on('htmx:afterSwap', function(event) {
    $('#sourcecode').val($('#playground').html());
});

$('#run').click(function() {
    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

