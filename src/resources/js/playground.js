$(document).ready(function() {

    let editor;

    require.config({ paths: { 'vs': resourcesUrl + '/lib/monaco-editor/min/vs' }});
    window.MonacoEnvironment = { getWorkerUrl: () => proxy };

    let proxy = URL.createObjectURL(new Blob([`
        self.MonacoEnvironment = {
            baseUrl: '` + resourcesUrl + `/lib/monaco-editor/min/'
        };
        importScripts('` + resourcesUrl + `/lib/monaco-editor/min/vs/base/worker/workerMain.js');
    `], { type: 'text/javascript' }));

    require(["vs/editor/editor.main"], function () {
        monaco.languages.registerCompletionItemProvider('twig', {
            provideCompletionItems: getCompletionItems,
        });

        editor = monaco.editor.create($('#editor')[0], {
            language: 'twig',
            value: $('#input').val(),
            wordWrap: true,
            scrollBeyondLastLine: false,
            lineNumbersMinChars: 4,
            fontSize: 14,
            fontFamily: 'SFMono-Regular, Consolas, "Liberation Mono", Menlo, Courier, monospace',
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

    $('#save').click(function(event) {
        event.preventDefault();

        $('input[name=name').val(prompt('Enter a name for this playground.'));
        $('input[name=component').val(editor.getValue());
        $('input[name=variables').val($('#input-variables').val());

        $(this).closest('form').submit();
    });

    let shareHud;

    $('#share').click(function(event) {
        event.preventDefault();

        if (shareHud) {
            shareHud.show();
        }
        else {
            let textarea = '<textarea id="share-url" class="text fullwidth" rows="3"></textarea>';

            shareHud = new Garnish.HUD($('#share'), textarea, {
                onShow: $.proxy(function() {
                    let url = $('#share').attr('data-url') + '?component=' + btoa(encodeURIComponent(editor.getValue())) + '&variables=' + btoa(encodeURIComponent($('#input-variables').val()));

                    console.log(encodeURIComponent(editor.getValue()));

                    $('#share-url').val(url).select();
                    document.execCommand('copy');
                    Craft.cp.displayNotice('Copied to clipboard.');
                }),
            });
        }
    });
});

function getCompletionItems()
{
    let suggestions = [
        {
            label: 'sprig',
            insertText: 'sprig',
            kind: monaco.languages.CompletionItemKind.Function,
            sortText: '_sprig',
        },
    ];

    let suggestionLabels = [
        's-action=""',
        's-method=""', 's-method="post"',
        's-confirm=""',
        's-include=""',
        's-indicator=""',
        's-params=""',
        's-prompt=""',
        's-push-url=""',
        's-select=""',
        's-swap=""', 's-swap="innerHTML"', 's-swap="outerHTML"', 's-swap="beforebegin"', 's-swap="afterbegin"', 's-swap="beforeend"', 's-swap="afterend"',
        's-swap-oob=""',
        's-target=""',
        's-trigger=""', 's-trigger="click"', 's-trigger="change"', 's-trigger="submit"',
        's-vars=""',
    ];

    for (let i = 0; i < suggestionLabels.length; i++) {
        suggestions[i + 1] = {
            label: suggestionLabels[i],
            insertText: suggestionLabels[i],
            kind: monaco.languages.CompletionItemKind.Field,
        };
    }

    return {
        suggestions: suggestions,
    };
}
