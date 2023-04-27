<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\autocompletes;

use Craft;
use craft\helpers\Json;
use nystudio107\codeeditor\base\Autocomplete;
use nystudio107\codeeditor\models\CompleteItem;
use nystudio107\codeeditor\types\AutocompleteTypes;
use nystudio107\codeeditor\types\CompleteItemKind;

class SprigApiAutocomplete extends Autocomplete
{
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

        $path = Craft::getAlias('@putyourlightson/sprig/plugin/autocompletes/sprig-attributes.json');
        $attributes = Json::decodeFromFile($path);
        foreach ($attributes as $attribute) {
            $name = $attribute['name'];
            $docs = $attribute['description'] . "\n\n";
            $links = $attribute['links'] ?? [];
            foreach ($links as $link) {
                $docs .= '[' . $link['text'] . ' &raquo;](' . $link['url']  . ')' . PHP_EOL . PHP_EOL;
            }

            $hasValue = $attribute['hasValue'] ?? false;
            $values = $hasValue ? [$name . '=""'] : [$name];
            $options = $attribute['options'] ?? [];
            foreach ($options as $option) {
                if (is_array($option)) {
                    $values[] = $option['name'] . '="' . $option['value'] . '"';
                } else {
                    $values[] = $name . '="' . $option . '"';
                }
            }

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
