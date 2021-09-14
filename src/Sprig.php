<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use putyourlightson\sprig\models\SettingsModel;
use putyourlightson\sprig\services\ComponentsService;
use putyourlightson\sprig\services\PlaygroundService;
use putyourlightson\sprig\services\RequestService;
use putyourlightson\sprig\twigextensions\SprigTwigExtension;
use putyourlightson\sprig\variables\SprigVariable;
use yii\base\Event;

/**
 * @property ComponentsService $components
 * @property RequestService $request
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
     * @var SprigVariable
     */
    public static $sprigVariable;

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
        self::$sprigVariable = new SprigVariable();

        $this->hasCpSection = $this->settings->enablePlayground;

        $this->setComponents([
            'components' => ComponentsService::class,
            'request' => RequestService::class,
            'playground' => PlaygroundService::class,
        ]);

        $this->_registerTwigExtensions();
        $this->_registerVariables();
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
     * Registers Twig extensions
     */
    private function _registerTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new SprigTwigExtension());
    }

    /**
     * Registers variables.
     */
    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('sprig', self::$sprigVariable);
            }
        );
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
            }
        );
    }
}
