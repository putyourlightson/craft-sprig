<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\base;

use Craft;
use craft\base\Component as BaseComponent;

abstract class Component extends BaseComponent implements ComponentInterface
{
    /**
     * The path to the template that the `render` method should render.
     *
     * @var string|null
     */
    protected $_template;

    /**
     * @inheritdoc
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
     * Pushes the URL into the history stack.
     * https://htmx.org/reference#response_headers
     *
     * @param string $url
     */
    public static function pushUrl(string $url)
    {
        Craft::$app->getResponse()->getHeaders()->set('HX-Push', $url);
    }

    /**
     * Redirects the browser to the URL.
     * https://htmx.org/reference#response_headers
     *
     * @param string $url
     */
    public static function redirect(string $url)
    {
        Craft::$app->getResponse()->getHeaders()->set('HX-Redirect', $url);
    }

    /**
     * Refreshes the browser.
     * https://htmx.org/reference#response_headers
     *
     * @param bool $refresh
     */
    public static function refresh(bool $refresh = true)
    {
        Craft::$app->getResponse()->getHeaders()->set('HX-Refresh', $refresh ? 'true' : '');
    }

    /**
     * Triggers client-side events.
     * https://htmx.org/headers/x-hx-trigger/
     *
     * @param array|string $events
     * @param string $on
     */
    public static function triggerEvents($events, string $on = 'load')
    {
        if (is_array($events)) {
            $events = implode(' ', $events);
        }

        $headerMap = [
            'load' => 'HX-Trigger',
            'swap' => 'HX-Trigger-After-Swap',
            'settle' => 'HX-Trigger-After-Settle',
        ];

        $header = $headerMap[$on] ?? null;

        if ($header) {
            Craft::$app->getResponse()->getHeaders()->set($header, $events);
        }
    }
}
