<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigplugin;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use putyourlightson\sprigplugin\models\SettingsModel;
use putyourlightson\sprigplugin\services\PlaygroundService;
use yii\base\Event;

/**
 * @property PlaygroundService $playground
 * @property SettingsModel $settings
 */
class Sprig extends Plugin
{
    /**
     * @var Sprig
     */
    public static $plugin;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->hasCpSection = $this->settings->enablePlayground;

        $this->setComponents([
            'playground' => PlaygroundService::class,
        ]);

        // Register the controller map manually since it is named differently to not conflict with Sprig core.
        if (!Craft::$app->request->isConsoleRequest) {
            Craft::$app->controllerMap['sprig-plugin'] = self::class;
        }

        $this->_registerCpRoutes();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new SettingsModel();
    }

    /**
     * Registers CP routes.
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['sprig'] = 'sprig-plugin/playground/index';
                $event->rules['sprig/<id:\d+>'] = 'sprig-plugin/playground/index';
            }
        );
    }
}
