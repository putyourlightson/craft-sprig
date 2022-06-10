<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\autocompletes;

use nystudio107\twigfield\base\Autocomplete;
use nystudio107\twigfield\models\CompleteItem;
use nystudio107\twigfield\types\AutocompleteTypes;
use nystudio107\twigfield\types\CompleteItemKind;

class SprigApiAutocomplete extends Autocomplete
{
    public const SPRIG_ATTRIBUTES= [
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

    /**
     * @inerhitDoc
     */
    public static function getAutocompleteName(): string
    {
        return 'SprigApiAutocomplete';
    }

    /**
     * @inerhitDoc
     */
    public static function getAutocompleteType(): string
    {
        return AutocompleteTypes::TwigExpressionAutocomplete;
    }

    /**
     * Core function that generates the autocomplete array
     */
    public static function generateCompleteItems(): void
    {
        CompleteItem::create()
            ->label('sprig')
            ->insertText('sprig')
            ->kind(CompleteItemKind::FunctionKind)
            ->sortText('__sprig')
            ->add(self::class);

        foreach (self::SPRIG_ATTRIBUTES as $attribute) {
            CompleteItem::create()
                ->label($attribute)
                ->insertText($attribute)
                ->kind(CompleteItemKind::FieldKind)
                ->sortText($attribute)
                ->add(self::class);
        }
    }
}
