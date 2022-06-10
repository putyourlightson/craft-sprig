<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin;

use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use nystudio107\twigfield\events\RegisterTwigfieldAutocompletesEvent;
use nystudio107\twigfield\services\AutocompleteService;
use putyourlightson\sprig\plugin\autocompletes\SprigApiAutocomplete;
use putyourlightson\sprig\plugin\models\SettingsModel;
use putyourlightson\sprig\plugin\services\PlaygroundService;
use putyourlightson\sprig\Sprig as SprigCore;
use yii\base\Event;

/**
 * @property-read PlaygroundService $playground
 * @property-read SettingsModel $settings
 */
class Sprig extends Plugin
{
    const SPRIG_TWIG_FIELD_TYPE = 'SprigField';

    /**
     * @var Sprig
     */
    public static Sprig $plugin;

    /**
     * @inheritdoc
     */
    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public string $schemaVersion = '1.0.1';

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

        $this->_registerEventHandlers();

        SprigCore::bootstrap();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new SettingsModel();
    }

    /**
     * Registers CP routes.
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['sprig'] = 'sprig/playground/index';
                $event->rules['sprig/<id:\d+>'] = 'sprig/playground/index';
                $event->rules['sprig/<slug:([^\/]*)?>'] = 'sprig/playground/index';
            }
        );
    }

    /**
     * Registers event handlers
     */
    private function _registerEventHandlers()
    {
        // Register our autocompletes
        Event::on(AutocompleteService::class, AutocompleteService::EVENT_REGISTER_TWIGFIELD_AUTOCOMPLETES, static function (RegisterTwigfieldAutocompletesEvent $e) {
            if ($e->fieldType === self::SPRIG_TWIG_FIELD_TYPE) {
                $e->types[] = CraftApiAutocomplete::class;
                $e->types[] = SprigApiAutocomplete::class;
            }
        });
    }
}
