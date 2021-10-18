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
    const SAMPLES_DIR_PATH = '@putyourlightson/sprig/plugin/templates/_samples/';

    /**
     * Returns a saved component.
     *
     * @param int $id
     * @return PlaygroundModel|null
     */
    public function get(int $id)
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
     * Returns sample components.
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
     * Returns saved components.
     *
     * @return PlaygroundModel[]
     */
    public function getSaved(): array
    {
        $records = PlaygroundRecord::find()
            ->orderBy('dateCreated DESC')
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
     *
     * @param string $name
     * @param string $component
     * @param string $variables
     * @return int
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
            $record->name = 'Playground '.$record->id;
            $record->save();
        }

        return $record->id;
    }

    /**
     * Updates a playground.
     *
     * @param int $id
     * @param string $component
     * @param string $variables
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
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        PlaygroundRecord::deleteAll(['id' => $id]);
    }
}
