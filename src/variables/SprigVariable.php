<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use Craft;
use craft\helpers\Html;
use craft\helpers\Template;
use putyourlightson\sprig\Sprig;
use Twig\Markup;

class SprigVariable
{
    /**
     * Returns a new component.
     *
     * @param string $template
     * @param array $variables
     * @param array $attributes
     * @return Markup
     */
    public function getComponent(string $template, array $variables = [], array $attributes = []): Markup
    {
        return Sprig::$plugin->components->create($template, $variables, $attributes);
    }

    /**
     * Returns a script tag to the htmx source file using unpkg.com.
     *
     * @param array $attributes
     * @return Markup
     */
    public function getScript(array $attributes = []): Markup
    {
        $path = '@putyourlightson/sprig/resources/js/htmx-0.0.7.1.js';
        $url = Craft::$app->assetManager->getPublishedUrl($path, true);

        $script = Html::jsFile($url, $attributes);

        return Template::raw($script);
    }

    /**
     * Returns whether this is a Sprig include.
     *
     * @return bool
     */
    public function getInclude(): bool
    {
        return !Sprig::$plugin->getIsRequest();
    }

    /**
     * Returns whether this is a Sprig request.
     *
     * @return bool
     */
    public function getRequest(): bool
    {
        return Sprig::$plugin->getIsRequest();
    }

    /**
     * Returns the element that triggered the request.
     *
     * @return array
     */
    public function getTrigger(): array
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        return [
            'id' => $headers->get('HX-Trigger', '', true),
            'name' => $headers->get('HX-Trigger-Name', '', true),
        ];
    }
    /**
     * Returns the target element.
     *
     * @return array
     */
    public function getTarget(): array
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        return [
            'id' => $headers->get('HX-Target', '', true),
        ];
    }

    /**
     * Returns the URL of the browser.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Current-URL', '', true);
    }

    /**
     * Returns value entered by the user when prompted via `hx-prompt`.
     *
     * @return string
     */
    public function getPrompt(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Prompt', '', true);
    }

    /**
     * Returns the original target of the event that triggered the request.
     *
     * @return array
     */
    public function getEventTarget(): array
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        return [
            'id' => $headers->get('HX-Event-Target', '', true),
        ];
    }

    /**
     * Returns the active element.
     *
     * @return array
     */
    public function getElement(): array
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        return [
            'id' => $headers->get('HX-Active-Element', '', true),
            'name' => $headers->get('HX-Active-Element-Name', '', true),
            'value' => $headers->get('HX-Active-Element-Value', '', true),
        ];
    }
}
