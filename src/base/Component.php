<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\base;

use Craft;
use craft\base\Component as BaseComponent;
use putyourlightson\sprig\Sprig;

abstract class Component extends BaseComponent implements ComponentInterface
{
    /**
     * The events to send in the `HX-Trigger` response header.
     * @see https://htmx.org/headers/x-hx-trigger/
     *
     * @var mixed
     */
    public $events;

    /**
     * The path to the template that the `render` method should render.
     *
     * @var string|null
     */
    protected $template;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Sprig::$plugin->componentsService->setResponseEvents($this->events);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if ($this->template !== null) {
            return Craft::$app->getView()->renderTemplate($this->template, $this->getAttributes());
        }

        return '';
    }
}
