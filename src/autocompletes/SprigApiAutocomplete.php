<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\autocompletes;

use Craft;
use nystudio107\twigfield\base\Autocomplete;
use nystudio107\twigfield\models\CompleteItem;
use nystudio107\twigfield\types\AutocompleteTypes;
use nystudio107\twigfield\types\CompleteItemKind;

class SprigApiAutocomplete extends Autocomplete
{
    public const SPRIG_ATTRIBUTES = [
        's-action' => [
            'values' => [
                's-action=""',
            ],
            'description' => 'Sends an action request to the provided controller action.',
        ],
        's-boost' => [
            'values' => [
                's-boost=""',
                's-boost="true"',
            ],
            'description' => 'Boosts normal anchors and form tags to use AJAX instead.',
        ],
        's-confirm' => [
            'values' => [
                's-confirm=""',
                's-confirm="Are you sure?"',
            ],
            'description' => 'Shows a `confim()` dialog before issuing a request.',
        ],
        's-disable' => [
            'values' => [
                's-disable',
            ],
            'description' => 'Disables htmx processing for an element and its children.',
        ],
        's-disinherit' => [
            'values' => [
                's-disinherit=""',
                's-disinherit="*"',
            ],
            'description' => 'Allows you to control attribute inheritance.',
        ],
        's-encoding' => [
            'values' => [
                's-encoding=""',
                's-encoding="multipart/form-data"',
            ],
            'description' => 'Allows you to change the request encoding.',
        ],
        's-ext' => [
            'values' => [
                's-ext=""',
            ],
            'description' => 'Enables an htmx extension for an element and all its children.',
        ],
        's-headers' => [
            'values' => [
                's-headers=""',
            ],
            'description' => 'Allows you to add to the headers that will be submitted with an AJAX request.',
        ],
        's-history-elt' => [
            'values' => [
                's-history-elt=""',
            ],
            'description' => 'Allows you to specify the element that will be used to snapshot and restore page state during navigation.',
        ],
        's-include' => [
            'values' => [
                's-include=""',
            ],
            'description' => 'Includes additional element values in AJAX requests.',
        ],
        's-indicator' => [
            'values' => [
                's-indicator=""',
            ],
            'description' => 'The element to put the `htmx-request` class on during the AJAX request.',
        ],
        's-listen' => [
            'values' => [
                's-listen=""',
            ],
            'description' => 'Allows you to specify one or more components (as CSS selectors, separated by commas) that when refreshed, should trigger a refresh on the current element.',
        ],
        's-method' => [
            'values' => [
                's-method=""',
                's-method="post"',
            ],
            'description' => 'Forces the request to be of the type provided.',
        ],
        's-params' => [
            'values' => [
                's-params=""',
            ],
            'description' => 'Filters the parameters that will be submitted with a request.',
        ],
        's-preserve' => [
            'values' => [
                's-preserve="true"',
            ],
            'description' => 'Ensures that an element remains unchanged even when the component is re-rendered.',
        ],
        's-prompt' => [
            'values' => [
                's-prompt=""',
            ],
            'description' => 'Shows a prompt before submitting a request.',
        ],
        's-push-url' => [
            'values' => [
                's-push-url=""',
            ],
            'description' => 'Pushes a URL into the URL bar and creates a new history entry.',
        ],
        's-replace' => [
            'values' => [
                's-replace=""',
            ],
            'description' => 'Specifies the element to be replaced.',
        ],
        's-request' => [
            'values' => [
                's-request=""',
            ],
            'description' => 'Allows you to configure various aspects of the request.',
        ],
        's-select' => [
            'values' => [
                's-select=""',
            ],
            'description' => 'Selects a subset of the server response to process.',
        ],
        's-swap' => [
            'values' => [
                's-swap=""',
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
            'values' => [
                's-swap-oob=""',
            ],
            'description' => 'Marks content in a response as being “Out of Band”, i.e. swapped somewhere other than the target.',
        ],
        's-sync' => [
            'values' => [
                's-sync=""',
            ],
            'description' => 'Allows you to synchronize AJAX requests between multiple elements.',
        ],
        's-target' => [
            'values' => [
                's-target=""',
                's-target="this"',
            ],
            'description' => 'Specifies the target element to be swapped.',
        ],
        's-trigger' => [
            'values' => [
                's-trigger=""',
                's-trigger="click"',
                's-trigger="change"',
                's-trigger="submit"',
            ],
            'description' => 'Specifies the event that triggers the request.',
        ],
        's-val' => [
            'values' => [
                's-val-x="1"',
                's-val-y="2"',
            ],
            'description' => 'Provides a more readable way of populating the `s-vals` attribute.',
        ],
        's-vals' => [
            'values' => [
                's-vals=""',
            ],
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
            ->detail($detail)
            ->documentation(Craft::t('sprig', 'Adding the sprig attribute to an element makes it reactive.'))
            ->kind(CompleteItemKind::FieldKind)
            ->sortText('_sprig')
            ->add($this);

        foreach (self::SPRIG_ATTRIBUTES as $name => $attribute) {
            $docs = '[' . $name . '](' . self::BASE_DOCS_URL . $name . ') | ' . Craft::t('sprig', $attribute['description']);

            foreach ($attribute['values'] as $value) {
                CompleteItem::create()
                    ->label($value)
                    ->insertText($value)
                    ->detail($detail)
                    ->documentation($docs)
                    ->kind(CompleteItemKind::FieldKind)
                    ->add($this);
            }
        }
    }
}
