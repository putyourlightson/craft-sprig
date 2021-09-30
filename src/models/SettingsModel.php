<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\models;

use craft\base\Model;

class SettingsModel extends Model
{
    /**
     * @var bool Whether the playground should be enabled.
     */
    public $enablePlayground = true;
}
