<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $component
 * @property string $variables
 */
class PlaygroundRecord extends ActiveRecord
{
     /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprig_playgrounds}}';
    }
}
