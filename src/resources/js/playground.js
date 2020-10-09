$( document ).ready(function() {

    let editor;

    require.config({ paths: { 'vs': 'https://unpkg.com/monaco-editor/min/vs' }});
    window.MonacoEnvironment = { getWorkerUrl: () => proxy };

    let proxy = URL.createObjectURL(new Blob([`
        self.MonacoEnvironment = {
            baseUrl: 'https://unpkg.com/monaco-editor/min/'
        };
        importScripts('https://unpkg.com/monaco-editor/min/vs/base/worker/workerMain.js');
    `], { type: 'text/javascript' }));

    suggestions = suggestions.concat([
        {
            label: 'sprig',
            insertText: 'sprig',
            kind: 1,
        }
    ]);

    require(["vs/editor/editor.main"], function () {
        monaco.languages.registerCompletionItemProvider('twig', {
            provideCompletionItems: function() {
                return {
                    suggestions: suggestions,
                };
            }
        });

        editor = monaco.editor.create($('#editor')[0], {
            language: 'twig',
            value: $('#input').val(),
            minimap: {
                enabled: false
            },
        });
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

        $('#playground')[0].dispatchEvent(new Event('refresh'));
    });

    $('#output-toggle').click(function(event) {
        event.preventDefault();
        $(this).toggleClass('submit');

        $('.playground #playground').toggle();
        $('.playground #sourcecode').toggle();
    });
});
