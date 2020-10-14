<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use Craft;
use craft\helpers\Html;
use craft\helpers\Template;
use putyourlightson\sprig\records\PlaygroundRecord;
use putyourlightson\sprig\services\ComponentsService;
use putyourlightson\sprig\Sprig;
use Twig\Markup;

class SprigVariable
{
    /**
     * @var string
     */
    public $htmxVersion = '0.1.2';

    /**
     * @var string
     */
    public $hyperscriptVersion = '0.0.2';

    /**
     * Returns a script tag to the htmx source file.
     *
     * @param array $attributes
     * @return Markup
     */
    public function getScript(array $attributes = []): Markup
    {
        return $this->_getScript('htmx', $this->htmxVersion, $attributes);
    }

    /**
     * Returns a script tag to the hyperscript source file.
     *
     * @param array $attributes
     * @return Markup
     */
    public function getHyperscript(array $attributes = []): Markup
    {
        return $this->_getScript('hyperscript', $this->hyperscriptVersion, $attributes);
    }

    /**
     * Returns whether this is a Sprig request.
     *
     * @return bool
     */
    public function getIsRequest(): bool
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Request', false, true) == 'true';
    }

    /**
     * Returns whether this is a Sprig include.
     *
     * @return bool
     */
    public function getIsInclude(): bool
    {
        return !$this->getIsRequest();
    }

    // TODO: remove in 1.0.0
    /**
     * Returns whether this is a Sprig request.
     *
     * @return bool
     * @deprecated Use [[SprigVariable::getIsRequest()]] instead.
     */
    public function getRequest(): bool
    {
        Craft::$app->getDeprecator()->log('SprigVariable::getRequest()', 'The “sprig.request” template variable has been deprecated and will be removed in version 1.0.0. Use “sprig.isRequest” instead.');

        return $this->getIsRequest();
    }

    // TODO: remove in 1.0.0
    /**
     * Returns whether this is a Sprig include.
     *
     * @return bool
     * @deprecated Use [[SprigVariable::getIsInclude()]] instead.
     */
    public function getInclude(): bool
    {
        Craft::$app->getDeprecator()->log('SprigVariable::getInclude()', 'The “sprig.include” template variable has been deprecated and will be removed in version 1.0.0. Use “sprig.isInclude” instead.');

        return $this->getIsInclude();
    }

    /**
     * Returns the URL that the Sprig component was loaded from.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Current-URL', '', true);
    }

    /**
     * Returns the value entered by the user when prompted via `s-prompt` or `hx-prompt`.
     *
     * @return string
     */
    public function getPrompt(): string
    {
        return Craft::$app->getRequest()->getHeaders()->get('HX-Prompt', '', true);
    }

    /**
     * Returns the name of the element that triggered the request.
     *
     * @return string
     */
    public function getTrigger(): string
    {
        $headers = Craft::$app->getRequest()->getHeaders();

        return $headers->get('HX-Trigger-Name', '', true);
    }

    // Undocumented variables (subject to change)
    // =========================================================================

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

    /**
     * Returns the htmx attributes.
     *
     * @return array
     */
    public function getHtmxAttributes(): array
    {
        return ComponentsService::HTMX_ATTRIBUTES;
    }

    /**
     * Returns a script tag to a source file.
     *
     * @param string $name
     * @param string $version
     * @param array $attributes
     * @return Markup
     */
    private function _getScript(string $name, string $version, array $attributes = []): Markup
    {
        $url = 'https://unpkg.com/'.$name.'.org@'.$version;

        if (Craft::$app->getConfig()->env == 'dev') {
            $path = '@putyourlightson/sprig/resources/js/'.$name.'-'.$version.'.js';
            $url = Craft::$app->getAssetManager()->getPublishedUrl($path, true);
        }

        $script = Html::jsFile($url, $attributes);

        return Template::raw($script);
    }
}
