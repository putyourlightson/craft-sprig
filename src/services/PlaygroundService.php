<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use putyourlightson\sprig\plugin\models\PlaygroundModel;
use putyourlightson\sprig\plugin\records\PlaygroundRecord;

/**
 * @property-read PlaygroundModel[] $samples
 * @property-read PlaygroundModel[] $saved
 */
class PlaygroundService extends Component
{
    /**
     * @const string
     */
    public const SAMPLES_DIR_PATH = '@putyourlightson/sprig/plugin/templates/_samples/';

    /**
     * Returns a saved playground.
     */
    public function get(int $id): ?PlaygroundModel
    {
        $record = PlaygroundRecord::findOne(['id' => $id]);

        if ($record === null) {
            return null;
        }

        $playground = new PlaygroundModel();
        $playground->setAttributes($record->getAttributes(), false);

        return $playground;
    }

    /**
     * Returns sample playgrounds.
     *
     * @return PlaygroundModel[]
     */
    public function getSamples(): array
    {
        $index = Craft::getAlias(self::SAMPLES_DIR_PATH . 'index.json');

        if ($index === false) {
            return [];
        }

        $json = @file_get_contents($index);

        if ($json === false) {
            return [];
        }

        $samplesConfig = Json::decodeIfJson($json);

        if (!is_array($samplesConfig)) {
            return [];
        }

        $samples = [];

        foreach ($samplesConfig as $config) {
            $playground = new PlaygroundModel($config);
            $componentPath = Craft::getAlias(self::SAMPLES_DIR_PATH . $playground->component);

            if ($componentPath === false) {
                continue;
            }

            $content = @file_get_contents($componentPath);

            if ($content === false) {
                continue;
            }

            if ($playground->validate()) {
                $playground->component = $content;
                $samples[$playground->slug] = $playground;
            }
        }

        return $samples;
    }

    /**
     * Returns saved playgrounds.
     *
     * @return PlaygroundModel[]
     */
    public function getSaved(): array
    {
        /** @var PlaygroundRecord[] $records */
        $records = PlaygroundRecord::find()
            ->orderBy(['dateCreated' => SORT_DESC])
            ->all();

        $saved = [];

        foreach ($records as $record) {
            $playground = new PlaygroundModel();
            $playground->setAttributes($record->getAttributes(), false);
            $saved[] = $playground;
        }

        return $saved;
    }

    /**
     * Saves a playground.
     */
    public function save(string $name, string $component, string $variables): int
    {
        $record = new PlaygroundRecord([
            'name' => $name,
            'component' => $component,
            'variables' => $variables,
        ]);

        $record->save();

        if ($record->name == '') {
            $record->name = 'Playground ' . $record->id;
            $record->save();
        }

        return $record->id;
    }

    /**
     * Updates a playground.
     */
    public function update(int $id, string $component, string $variables)
    {
        $record = PlaygroundRecord::findOne(['id' => $id]);

        if ($record === null) {
            return;
        }

        $record->component = $component;
        $record->variables = $variables;
        $record->save();
    }

    /**
     * Deletes a playground.
     */
    public function delete(int $id)
    {
        PlaygroundRecord::deleteAll(['id' => $id]);
    }
}
