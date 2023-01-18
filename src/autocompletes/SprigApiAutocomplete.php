<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\autocompletes;

use Craft;
use nystudio107\codeeditor\base\Autocomplete;
use nystudio107\codeeditor\models\CompleteItem;
use nystudio107\codeeditor\types\AutocompleteTypes;
use nystudio107\codeeditor\types\CompleteItemKind;

class SprigApiAutocomplete extends Autocomplete
{
    public const SPRIG_ATTRIBUTES = [
        's-action' => [
            'description' => 'Sends an action request to the provided controller action.',
        ],
        's-boost' => [
            'options' => [
                's-boost="true"',
            ],
            'description' => 'Boosts normal anchors and form tags to use AJAX instead.',
        ],
        's-confirm' => [
            'options' => [
                's-confirm="Are you sure?"',
            ],
            'description' => 'Shows a `confim()` dialog before issuing a request.',
        ],
        's-disable' => [
            'description' => 'Disables htmx processing for an element and its children.',
        ],
        's-disinherit' => [
            'options' => [
                's-disinherit="*"',
            ],
            'description' => 'Allows you to control attribute inheritance.',
        ],
        's-encoding' => [
            'options' => [
                's-encoding="multipart/form-data"',
            ],
            'description' => 'Allows you to change the request encoding.',
        ],
        's-ext' => [
            'description' => 'Enables an htmx extension for an element and all its children.',
        ],
        's-headers' => [
            'description' => 'Allows you to add to the headers that will be submitted with an AJAX request.',
        ],
        's-history' => [
            'options' => [
                's-history="false"',
            ],
            'description' => 'Prevents sensitive data being saved to the history cache.',
        ],
        's-history-elt' => [
            'description' => 'Allows you to specify the element that will be used to snapshot and restore page state during navigation.',
        ],
        's-include' => [
            'description' => 'Includes additional element values in AJAX requests.',
        ],
        's-indicator' => [
            'description' => 'The element to put the `htmx-request` class on during the AJAX request.',
        ],
        's-listen' => [
            'description' => 'Allows you to specify one or more components (as CSS selectors, separated by commas) that when refreshed, should trigger a refresh on the current element.',
        ],
        's-method' => [
            'options' => [
                's-method="post"',
            ],
            'description' => 'Forces the request to be of the type provided.',
        ],
        's-params' => [
            'description' => 'Filters the parameters that will be submitted with a request.',
        ],
        's-preserve' => [
            'description' => 'Ensures that an element remains unchanged even when the component is re-rendered.',
        ],
        's-prompt' => [
            'description' => 'Shows a prompt before submitting a request.',
        ],
        's-push-url' => [
            'description' => 'Pushes a URL into the URL bar and creates a new history entry.',
        ],
        's-replace' => [
            'description' => 'Specifies the element to be replaced.',
        ],
        's-replace-url' => [
            'description' => 'Allows you to replace the current URL of the browser location history.',
        ],
        's-request' => [
            'description' => 'Allows you to configure various aspects of the request.',
        ],
        's-select' => [
            'description' => 'Selects a subset of the server response to process.',
        ],
        's-select-oob' => [
            'description' => 'Selects one or more elements from a server response to swap in via an “Out of Band” swap.',
        ],
        's-swap' => [
            'options' => [
                's-swap="innerHTML"',
                's-swap="outerHTML"',
                's-swap="beforebegin"',
                's-swap="afterbegin"',
                's-swap="beforeend"',
                's-swap="afterend"',
            ],
            'description' => 'Controls how the response content is swapped into the DOM.',
        ],
        's-swap-oob' => [
            'description' => 'Marks content in a response as being “Out of Band”, i.e. swapped somewhere other than the target.',
        ],
        's-sync' => [
            'description' => 'Allows you to synchronize AJAX requests between multiple elements.',
        ],
        's-target' => [
            'options' => [
                's-target="this"',
            ],
            'description' => 'Specifies the target element to be swapped.',
        ],
        's-trigger' => [
            'options' => [
                's-trigger="click"',
                's-trigger="change"',
                's-trigger="submit"',
            ],
            'description' => 'Specifies the event that triggers the request.',
        ],
        's-val' => [
            'options' => [
                's-val:x="1"',
                's-val:y="2"',
            ],
            'description' => 'Provides a more readable way of populating the `s-vals` attribute.',
        ],
        's-validate' => [
            'options' => [
                's-boost="validate"',
            ],
            'description' => 'Forces an element to validate itself before it submits a request.',
        ],
        's-vals' => [
            'description' => 'Adds to the parameters that will be submitted with the request.',
        ],
    ];

    public const BASE_DOCS_URL = 'https://putyourlightson.com/plugins/sprig#';

    /**
     * @inheritdoc
     */
    public $name = 'SprigApiAutocomplete';

    /**
     * @inheritdoc
     */
    public $type = AutocompleteTypes::GeneralAutocomplete;

    /**
     * @inheritdoc
     */
    public function generateCompleteItems(): void
    {
        $detail = Craft::t('sprig', 'Sprig Attribute');

        CompleteItem::create()
            ->label('sprig')
            ->insertText('sprig')
            ->sortText('_sprig')
            ->detail($detail)
            ->documentation(Craft::t('sprig', 'Adding the sprig attribute to an element makes it reactive.'))
            ->kind(CompleteItemKind::FieldKind)
            ->add($this);

        foreach (self::SPRIG_ATTRIBUTES as $name => $attribute) {
            $docs = '[' . $name . '](' . self::BASE_DOCS_URL . $name . ') | ' . Craft::t('sprig', $attribute['description']);
            $values = array_merge([$name], $attribute['options'] ?? []);

            foreach ($values as $value) {
                CompleteItem::create()
                    ->label($value)
                    ->insertText($value)
                    ->sortText($name)
                    ->detail($detail)
                    ->documentation($docs)
                    ->kind(CompleteItemKind::FieldKind)
                    ->add($this);
            }
        }
    }
}
