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

        function ShowAutocompletion(obj) {
            // Disable default autocompletion for javascript
            monaco.languages.typescript.javascriptDefaults.setCompilerOptions({ noLib: true  });

            // Helper function to return the monaco completion item type of a thing
            function getType(thing, isMember) {
                isMember =  (isMember == undefined) ? (typeof isMember == "boolean") ? isMember : false : false; // Give isMember a default value of false

                switch ((typeof thing).toLowerCase()) {
                    case "object":
                        return monaco.languages.CompletionItemKind.Class;

                    case "function":
                        return (isMember) ? monaco.languages.CompletionItemKind.Method : monaco.languages.CompletionItemKind.Function;

                    default:
                        return (isMember) ? monaco.languages.CompletionItemKind.Property : monaco.languages.CompletionItemKind.Variable;
                }
            }

            // Register object that will return autocomplete items
            monaco.languages.registerCompletionItemProvider('twig', {
                // Run this function when the period or open parenthesis is typed (and anything after a space)
                triggerCharacters: ['.', '('],

                // Function to generate autocompletion results
                provideCompletionItems: function(model, position, token) {
                    // Split everything the user has typed on the current line up at each space, and only look at the last word
                    var last_chars = model.getValueInRange({startLineNumber: position.lineNumber, startColumn: 0, endLineNumber: position.lineNumber, endColumn: position.column});
                    var words = last_chars.replace("\t", "").split(" ");
                    var active_typing = words[words.length - 1]; // What the user is currently typing (everything after the last space)

                    // If the last character typed is a period then we need to look at member objects of the obj object
                    var is_member = active_typing.charAt(active_typing.length - 1) == ".";

                    // Array of autocompletion results
                    var result = [];

                    // Used for generic handling between member and non-member objects
                    var last_token = obj;
                    var prefix = '';

                    if (is_member) {
                        // Is a member, get a list of all members, and the prefix
                        var parents = active_typing.substring(0, active_typing.length - 1).split(".");
                        last_token = obj[parents[0]];
                        prefix = parents[0];

                        // Loop through all the parents the current one will have (to generate prefix)
                        for (var i = 1; i < parents.length; i++) {
                            if (last_token.hasOwnProperty(parents[i])) {
                                prefix += '.' + parents[i];
                                last_token = last_token[parents[i]];
                            } else {
                                // Not valid
                                return result;
                            }
                        }

                        prefix += '.';
                    }

                    // Get all the child properties of the last token
                    for (var prop in last_token) {
                        // Do not show properites that begin with "__"
                        if (last_token.hasOwnProperty(prop) && !prop.startsWith("__")) {
                            // Get the detail type (try-catch) incase object does not have prototype
                            var details = '';
                            try {
                                details = last_token[prop].__proto__.constructor.name;
                            } catch (e) {
                                details = typeof last_token[prop];
                            }

                            // Create completion object
                            var to_push = {
                                label: prefix + prop,
                                kind: getType(last_token[prop], is_member),
                                detail: details,
                                insertText: prop
                            };

                            // Change insertText and documentation for functions
                            if (to_push.detail.toLowerCase() == 'function') {
                                to_push.insertText += "(";
                                to_push.documentation = (last_token[prop].toString()).split("{")[0]; // Show function prototype in the documentation popup
                            }

                            // Add to final results
                            result.push(to_push);
                        }
                    }

                    return {
                        suggestions: result
                    };
                }
            });
        }

        ShowAutocompletion({
            Person: {
                name: "",
                age: 0,
                gender: {
                    some: "woof",
                    furry: "moron"
                }
            }
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
