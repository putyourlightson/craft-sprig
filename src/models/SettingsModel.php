<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\models;

use Craft;
use craft\base\Model;

class SettingsModel extends Model
{
    /**
     * @var bool Whether the playground should be enabled.
     */
    public bool $enablePlayground = true;

    /**
     * @var bool Whether the playground should be enabled when `devMode` is disabled
     */
    public bool $enablePlaygroundWhenDevModeDisabled = false;

    /**
     * Returns whether the playground can be accessed.
     */
    public function getCanAccessPlayground(): bool
    {
        return $this->enablePlayground && ($this->enablePlaygroundWhenDevModeDisabled || Craft::$app->getConfig()->getGeneral()->devMode);
    }
}
