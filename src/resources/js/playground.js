var sprigInclude = true;

var editor = CodeMirror.fromTextArea($('#component')[0], {
    lineNumbers: true,
    lineWrapping: true,
    mode: 'twig',
});

htmx.on('htmx:configRequest', function(event) {
    $('.spinner').show();

    event.detail.headers['Sprig-Playground-Component'] = editor.getValue();
    event.detail.headers['Sprig-Playground-Variables'] = $('#variables').val();

    if (sprigInclude) {
        event.detail.headers['HX-Request'] = 'false';
        sprigInclude = false;
    }
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
