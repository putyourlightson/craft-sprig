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
    $('#sourcecode').val(html_beautify($('#playground').html()));
});

htmx.on('htmx:responseError', function(event) {
    $('.spinner').hide();
    $('#playground').html(`
        <h2 class="error">` + event.detail.xhr.status + ' ' + event.detail.xhr.statusText + `</h2>
        <p>View the full response in the network tab of your browser dev tools.</p>
    `);
    $('#sourcecode').val('');
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
