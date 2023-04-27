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

        // Follows the “Custom Data for HTML Language Service” spec
        // https://github.com/microsoft/vscode-html-languageservice/blob/main/docs/customData.md
        $path = Craft::getAlias('@putyourlightson/sprig/plugin/autocompletes/data/sprig-support.json');
        $json = Json::decodeFromFile($path);
        $attributes = $json['globalAttributes'];
        $valueSets = $json['valueSets'];
        $attributeDocs = [];

        foreach ($attributes as $attribute) {
            $name = $attribute['name'];
            $docs = $attribute['description'] . "\n\n";
            $references = $attribute['references'] ?? [];
            $links = [];
            foreach ($references as $reference) {
                $links[] = '[' . $reference['name'] . '](' . $reference['url']  . ')';
            }
            $docs .= implode(' | ', $links);
            $attributeDocs[$name] = $docs;

            $value = $name == 'sprig' ? $name : $name . '=""';

            CompleteItem::create()
                ->label($value)
                ->insertText($value)
                ->sortText($name)
                ->detail($detail)
                ->documentation($docs)
                ->kind(CompleteItemKind::FieldKind)
                ->add($this);
        }

        foreach ($valueSets as $valueSet) {
            $name = $valueSet['name'];
            $values = $valueSet['values'] ?? [];
            foreach ($values as $value) {
                $value = $name . '="' . $value['name'] . '"';
                CompleteItem::create()
                    ->label($value)
                    ->insertText($value)
                    ->sortText($value)
                    ->detail($detail)
                    ->documentation($attributeDocs[$name] ?? '')
                    ->kind(CompleteItemKind::FieldKind)
                    ->add($this);
            }
        }
    }
}
