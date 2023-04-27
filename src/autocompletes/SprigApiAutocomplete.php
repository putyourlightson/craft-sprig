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
        $path = Craft::getAlias('@putyourlightson/sprig/plugin/autocompletes/sprig-attributes.json');
        $attributes = Json::decodeFromFile($path);
        foreach ($attributes as $attribute) {
            $name = $attribute['name'];
            $docs = $attribute['description'] . "\n\n";
            $references = $attribute['references'] ?? [];
            foreach ($references as $reference) {
                $docs .= '[' . $reference['name'] . ' &raquo;](' . $reference['url']  . ')' . PHP_EOL . PHP_EOL;
            }

            $hasValue = $attribute['hasValue'] ?? false;
            $versions = $hasValue ? [$name . '=""'] : [$name];
            $values = $attribute['values'] ?? [];
            foreach ($values as $value) {
                $versions[] = $name . '="' . $value['name'] . '"';
            }

            foreach ($versions as $version) {
                CompleteItem::create()
                    ->label($version)
                    ->insertText($version)
                    ->sortText($name)
                    ->detail($detail)
                    ->documentation($docs)
                    ->kind(CompleteItemKind::FieldKind)
                    ->add($this);
            }
        }
    }
}
