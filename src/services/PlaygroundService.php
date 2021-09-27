<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigplugin\services;

use craft\base\Component;
use putyourlightson\sprigplugin\models\PlaygroundModel;
use putyourlightson\sprigplugin\records\PlaygroundRecord;

/**
 * @property-read PlaygroundModel[] $all
 */
class PlaygroundService extends Component
{
    /**
     * Returns a saved playground.
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
     * Returns all saved playgrounds.
     *
     * @return PlaygroundModel[]
     */
    public function getAll(): array
    {
        $records = PlaygroundRecord::find()
            ->orderBy('dateCreated DESC')
            ->all();

        $playgrounds = [];

        foreach ($records as $record) {
            $playground = new PlaygroundModel();
            $playground->setAttributes($record->getAttributes(), false);
            $playgrounds[] = $playground;
        }

        return $playgrounds;
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
