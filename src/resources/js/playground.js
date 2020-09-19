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
    $('#sourcecode').val($('#playground').html());
});

$('#create').click(function() {
    $('.playground .create.submit').removeClass('submit');

    sprigInclude = true;

    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

$('.output-toggle').click(function() {
    $('.playground .content-pane').show();
    $(this).closest('.content-pane').hide();
});

