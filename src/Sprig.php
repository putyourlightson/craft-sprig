<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use putyourlightson\sprig\services\ComponentsService;
use putyourlightson\sprig\services\RequestService;
use putyourlightson\sprig\twigextensions\SprigTwigExtension;
use putyourlightson\sprig\variables\SprigVariable;
use yii\base\Event;

/**
 * @property ComponentsService $components
 * @property RequestService $request
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;
        self::$sprigVariable = new SprigVariable();

        $this->setComponents([
            'components' => ComponentsService::class,
            'request' => RequestService::class,
        ]);

        $this->_registerTwigExtensions();
        $this->_registerVariables();
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
}
