var sprigInclude = true;

htmx.on('htmx:configRequest', function(event) {
    $('.spinner').show();

    event.detail.headers['Sprig-Playground-Component'] = encodeURIComponent($('#component').val());
    event.detail.headers['Sprig-Playground-Variables'] = $('#variables').val();

    if (sprigInclude) {
        event.detail.headers['HX-Request'] = 'false';
        sprigInclude = false;
    }

    console.log(event.detail.headers);
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

    sprigInclude = true;

    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

