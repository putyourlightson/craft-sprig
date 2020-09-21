var editor = CodeMirror.fromTextArea($('#input')[0], {
    lineNumbers: true,
    lineWrapping: true,
    mode: 'twig',
});

htmx.on('htmx:configRequest', function(event) {
    $('.spinner').show();

    event.detail.headers['Sprig-Playground-Component'] = editor.getValue();
    event.detail.headers['Sprig-Playground-Variables'] = $('#input-variables').val();

    if ($('#playground').html() == '') {
        event.detail.headers['HX-Request'] = 'false';
    }
});

htmx.on('htmx:afterSwap', function(event) {
    $('.spinner').hide();
    $('#sourcecode').val(html_beautify(event.detail.xhr.responseText));

    $('#output-variables').val(event.detail.xhr.getResponseHeader('Sprig-Playground-Variables'));
});

htmx.on('htmx:responseError', function(event) {
    $('.spinner').hide();
    $('#playground').html(`
        <h2 class="error">` + event.detail.xhr.status + ' ' + event.detail.xhr.statusText + `</h2>
        <p>View the full response in the network tab of your browser dev tools.</p>
    `);
    $('#sourcecode').val('');
});

$('#create').click(function(event) {
    event.preventDefault();
    $(this).removeClass('submit');

    $('#playground').html('');
    $('#sourcecode').val('');
    $('#output-variables').val('');

    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

$('#output-toggle').click(function(event) {
    event.preventDefault();
    $(this).toggleClass('submit');

    $('.playground #playground').toggle();
    $('.playground #sourcecode').toggle();
});
