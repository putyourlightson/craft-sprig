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
     * The client-side events to trigger in a response.
     * @see https://htmx.org/headers/x-hx-trigger/
     *
     * @var mixed
     */
    protected $_events;

    /**
     * The URL to push into the history stack.
     * @see https://htmx.org/reference#response_headers
     *
     * @var mixed
     */
    protected $_url;

    /**
     * The path to the template that the `render` method should render.
     *
     * @var string|null
     */
    protected $_template;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Sprig::$plugin->components->setResponseHeaders([
            'events' => $this->_events,
            'url' => $this->_url,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if ($this->_template !== null) {
            return Craft::$app->getView()->renderTemplate($this->_template, $this->getAttributes());
        }

        return '';
    }
}
