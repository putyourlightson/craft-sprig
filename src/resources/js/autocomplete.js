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
                    var to_push = last_token[prop].__completions;

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

function setWithExpiry(key, value, ttl) {
    const now = new Date()
    // `item` is an object which contains the original value
    // as well as the time when it's supposed to expire
    const item = {
        value: value,
        expiry: now.getTime() + ttl,
    }
    localStorage.setItem(key, JSON.stringify(item))
}

function getWithExpiry(key) {
    const itemStr = localStorage.getItem(key)
    // if the item doesn't exist, return null
    if (!itemStr) {
        return null
    }
    const item = JSON.parse(itemStr)
    const now = new Date()
    // compare the expiry time of the item with the current time
    if (now.getTime() > item.expiry) {
        // If the item is expired, delete the item from storage
        // and return null
        localStorage.removeItem(key)
        return null
    }
    return item.value
}

function getCompletionItems() {
    // Try to grab the menu from our local storage cache if possible
    var responseVars = getWithExpiry('sprig-autocomplete-cache');
    if (responseVars === null) {
        // Grab the globals set Reference Tags from our controller
        var request = new XMLHttpRequest();
        request.open('GET', Craft.getActionUrl('sprig/autocomplete/index'), true);
        console.log('request.open');
        request.onload = function () {
            if (request.status >= 200 && request.status < 400) {
                console.log('status returned');
                responseVars = JSON.parse(request.responseText);
                console.log('JSON parsed');
                setWithExpiry('sprig-autocomplete-cache', responseVars, 1);
                console.log('stored in local storage');
                ShowAutocompletion(responseVars);
                console.log('autocomplete shown');
            } else {
            }
        };
        request.send();
        console.log('request.send()');
    }

    return responseVars;
}

getCompletionItems();
