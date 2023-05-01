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
        $path = Craft::getAlias('@putyourlightson/sprig/plugin/autocompletes/sprig-support.json');
        $json = Json::decodeFromFile($path);
        $attributes = $json['globalAttributes'];
        $valueSets = $json['valueSets'];

        foreach ($attributes as $attribute) {
            $name = $attribute['name'];
            $value = $name == 'sprig' ? $name : $name . '=""';
            $docs = $this->_getDocs($attribute);

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
                $docs = $this->_getDocs($value);
                $value = $name . '="' . $value['name'] . '"';

                CompleteItem::create()
                    ->label($value)
                    ->insertText($value)
                    ->sortText($value)
                    ->detail($detail)
                    ->documentation($docs)
                    ->kind(CompleteItemKind::FieldKind)
                    ->add($this);
            }
        }
    }

    private function _getDocs(array $value): string
    {
        $docs = $value['description'] . "\n\n";

        $references = $value['references'] ?? [];
        $links = [];
        foreach ($references as $reference) {
            $links[] = '[' . $reference['name'] . '](' . $reference['url'] . ')';
        }
        $docs .= implode(' | ', $links);

        return $docs;
    }
}
