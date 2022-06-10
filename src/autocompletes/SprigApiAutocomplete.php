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
    // Public Static Methods
    // =========================================================================

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
        CompleteItem::create()
            ->label('s-action=""')
            ->insertText('s-action=""')
            ->kind(CompleteItemKind::FieldKind)
            ->add(self::class);
        // Example code you can used too, delete below
        $completeItems = [];
        foreach (self::COMPLETE_ITEMS as $completeItem) {
            self::addCompleteItem($completeItem);
        }

    }
}
