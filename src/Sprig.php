<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin;

use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use putyourlightson\sprig\Sprig as SprigCore;
use putyourlightson\sprig\plugin\models\SettingsModel;
use putyourlightson\sprig\plugin\services\PlaygroundService;
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

        $this->_registerCpRoutes();

        SprigCore::bootstrap();
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
                $event->rules['sprig'] = 'sprig/playground/index';
                $event->rules['sprig/<id:\d+>'] = 'sprig/playground/index';
                $event->rules['sprig/<slug:([^\/]*)?>'] = 'sprig/playground/index';
            }
        );
    }
}
