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
     * https://htmx.org/headers/x-hx-trigger/
     *
     * @var mixed
     */
    protected $_events;

    /**
     * The URL to push into the history stack.
     * https://htmx.org/reference#response_headers
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

    /**
     * Returns whether this is a Sprig request.
     *
     * @return bool
     */
    public static function getIsRequest(): bool
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Request', false, true) == 'true';
    }

    /**
     * Returns whether this is a Sprig include.
     *
     * @return bool
     */
    public static function getIsInclude(): bool
    {
        return !self::getIsRequest();
    }

    /**
     * Returns the ID of the active element.
     *
     * @return string
     */
    public static function getElement(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Active-Element', '', true);
    }

    /**
     * Returns the name of the active element.
     *
     * @return string
     */
    public static function getElementName(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Active-Element-Name', '', true);
    }

    /**
     * Returns the value of the active element.
     *
     * @return string
     */
    public static function getElementValue(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Active-Element-Name', '', true);
    }

    /**
     * Returns the ID of the original target of the event that triggered the request.
     *
     * @return string
     */
    public static function getEventTarget(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Event-Target', '', true);
    }

    /**
     * Returns the value entered by the user when prompted via `s-prompt` or `hx-prompt`.
     *
     * @return string
     */
    public static function getPrompt(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Prompt', '', true);
    }

    /**
     * Returns the ID of the target element.
     *
     * @return string
     */
    public static function getTarget(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Target', '', true);
    }

    /**
     * Returns the ID of the element that triggered the request.
     *
     * @return string
     */
    public static function getTrigger(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Trigger', '', true);
    }

    /**
     * Returns the name of the element that triggered the request.
     *
     * @return string
     */
    public static function getTriggerName(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Trigger-Name', '', true);
    }

    /**
     * Returns the URL that the Sprig component was loaded from.
     *
     * @return string
     */
    public static function getUrl(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Current-URL', '', true);
    }

    /**
     * Triggers client-side events.
     *
     * @param string|array $events
     */
    public static function triggerEvents($events)
    {
        if (is_array($events)) {
            $events = implode(' ', $events);
        }

        Craft::$app->getResponse()->getHeaders()->set('HX-Trigger', $events);
    }

    /**
     * Pushes the URL into the history stack.
     *
     * @param string $url
     */
    public static function pushUrl(string $url)
    {
        Craft::$app->getResponse()->getHeaders()->set('HX-Push', $url);
    }
}
