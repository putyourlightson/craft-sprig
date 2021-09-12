$(document).ready(function()
{
    //--- Monaco editor ---//

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

        import(resourcesUrl + "/js/autocomplete.js");

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


    //--- htmx ---//

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

    //--- Buttons ---//

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

    $('#action-form').submit(function(event) {
        $(this).find('textarea[name=component]').val(editor.getValue());
        $(this).find('textarea[name=variables]').val($('#input-variables').val());

        if ($(this).find('input[name=action]:last').val() == 'sprig/playground/save') {
            let name = prompt('Enter a name for this playground.');

            if (name) {
                $(this).find('input[name=name]').val(name);
                return;
            }
        }
        else {
            return;
        }

        event.preventDefault();
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
        {"detail":"string: English","kind":4,"label":"siteName"},{"detail":"string: http:\/\/localhost:8000\/","kind":4,"label":"siteUrl"},{"detail":"string: plugindev","kind":4,"label":"systemName"},{"detail":"boolean: 1","kind":4,"label":"devMode"},{"detail":"integer: 4","kind":14,"label":"SORT_ASC"},{"detail":"integer: 3","kind":14,"label":"SORT_DESC"},{"detail":"integer: 0","kind":14,"label":"SORT_REGULAR"},{"detail":"integer: 1","kind":14,"label":"SORT_NUMERIC"},{"detail":"integer: 2","kind":14,"label":"SORT_STRING"},{"detail":"integer: 5","kind":14,"label":"SORT_LOCALE_STRING"},{"detail":"integer: 6","kind":14,"label":"SORT_NATURAL"},{"detail":"integer: 8","kind":14,"label":"SORT_FLAG_CASE"},{"detail":"integer: 1","kind":14,"label":"POS_HEAD"},{"detail":"integer: 2","kind":14,"label":"POS_BEGIN"},{"detail":"integer: 3","kind":14,"label":"POS_END"},{"detail":"integer: 4","kind":14,"label":"POS_READY"},{"detail":"integer: 5","kind":14,"label":"POS_LOAD"},{"detail":"boolean: 1","kind":4,"label":"isInstalled"},{"detail":"string: http:\/\/localhost:8000\/login","kind":4,"label":"loginUrl"},{"detail":"string: http:\/\/localhost:8000\/logout","kind":4,"label":"logoutUrl"},

    ];

    let suggestionLabels = [
        's-action=""',
        's-method=""', 's-method="post"',
        's-boost=""', 's-boost="true"',
        's-confirm=""', 's-confirm="Are you sure?"',
        's-disable=""',
        's-encoding=""', 's-encoding="multipart/form-data"',
        's-headers=""',
        's-history-elt=""',
        's-include=""',
        's-indicator=""',
        's-params=""',
        's-preserve=""', 's-preserve="true"',
        's-prompt=""',
        's-push-url=""',
        's-request=""',
        's-select=""',
        's-swap=""', 's-swap="innerHTML"', 's-swap="outerHTML"', 's-swap="beforebegin"', 's-swap="afterbegin"', 's-swap="beforeend"', 's-swap="afterend"',
        's-swap-oob=""',
        's-target=""', 's-target="this"',
        's-trigger=""', 's-trigger="click"', 's-trigger="change"', 's-trigger="submit"',
        's-val-x="1"', 's-val-y="2"',
    ];

    for (let i = 0; i < suggestionLabels.length; i++) {
        suggestions[i + suggestions.length] = {
            label: suggestionLabels[i],
            insertText: suggestionLabels[i],
            kind: monaco.languages.CompletionItemKind.Field,
        };
    }

    return {
        suggestions: suggestions,
    };
}
