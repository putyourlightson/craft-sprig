<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\services;

use Craft;
use craft\base\Component;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use DOMDocument;
use DOMElement;
use putyourlightson\sprig\base\ComponentInterface;
use putyourlightson\sprig\Sprig;
use Twig\Markup;
use yii\web\BadRequestHttpException;

class ComponentsService extends Component
{
    /**
     * @const string
     */
    const COMPONENT_NAMESPACE = 'sprig\\components\\';

    /**
     * @const string
     */
    const RENDER_CONTROLLER_ACTION = 'sprig/components/render';

    /**
     * @const string[]
     */
    const HTMX_ATTRIBUTES = ['boost', 'confirm', 'delete', 'ext', 'get', 'history-elt', 'include', 'indicator', 'params', 'patch', 'post', 'prompt', 'push-url', 'put', 'select', 'sse', 'swap-oob', 'swap', 'target', 'trigger', 'vars', 'ws'];

    /**
     * Creates a new component.
     *
     * @param string $value
     * @param array $variables
     * @param array $attributes
     * @return Markup
     */
    public function create(string $value, array $variables = [], array $attributes = []): Markup
    {
        $allVariables = array_merge(
            $variables,
            Sprig::$plugin->request->getVariables()
        );

        $componentObject = $this->createObject($value, $allVariables);

        if ($componentObject) {
            $type = 'component';
            $renderedContent = $componentObject->render();
        }
        else {
            $type = 'template';

            if (!Craft::$app->getView()->doesTemplateExist($value)) {
                throw new BadRequestHttpException(Craft::t('sprig', 'Unable to find the component or template “{value}”.', [
                    'value' => $value,
                ]));
            }

            $renderedContent = Craft::$app->getView()->renderTemplate($value, $allVariables);
        }

        $content = $this->parseTagAttributes($renderedContent);

        $vars = [];
        $vars['sprig:'.$type] = Craft::$app->getSecurity()->hashData($value);

        foreach ($variables as $name => $value) {
            $vars['sprig:variables['.$name.']'] = Craft::$app->getSecurity()->hashData($value);
        }

        // Ensure ID does not start with a digit, otherwise a JS error will be thrown
        $id = $attributes['id'] ?? 'component-'.StringHelper::randomString(6);

        $attributes = array_merge(
            [
                'id' => $id,
                'hx-target' => 'this',
                'hx-include' => '#'.$id.' *',
                'hx-trigger' => 'refresh',
                'hx-get' => UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION),
                'hx-vars' => $this->parseVars($vars),
            ],
            $attributes
        );

        return Template::raw(
            Html::tag('div', $content, $attributes)
        );
    }

    /**
     * Creates a new component object with the provided variables.
     *
     * @param string $component
     * @param array $variables
     * @return object|null
     */
    public function createObject(string $component, array $variables = [])
    {
        $componentClass = self::COMPONENT_NAMESPACE.$component;

        if (!class_exists($componentClass)) {
            return null;
        }

        $componentObject = new $componentClass;

        if (!($componentObject instanceof ComponentInterface)) {
            return null;
        }

        // Only populate variables that exist as properties on the class
        foreach ($variables as $name => $value) {
            if (property_exists($componentObject, $name)) {
                $componentObject->$name = $value;
            }
        }

        return $componentObject;
    }

    /**
     * Parses tag attributes in the provided HTML.
     *
     * @param string $html
     * @return string
     */
    public function parseTagAttributes(string $html): string
    {
        if (empty(trim($html))) {
            return $html;
        }

        // Prevent XML errors from being thrown
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        // Force UTF-8 encoding
        // https://stackoverflow.com/a/8218649/1769259
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        $csrf = false;

        /** @var DOMElement $element */
        foreach ($dom->getElementsByTagName('*') as $element) {
            if ($element->hasAttribute('sprig')) {
                $verb = 'get';
                $params = [];

                // Make the check case-insensitive
                if (strtolower($this->getElementAttribute($element, 'method')) == 'post') {
                    $verb = 'post';
                    $csrf = true;
                }

                $action = $this->getElementAttribute($element, 'action');

                if ($action) {
                    $params['sprig:action'] = Craft::$app->getSecurity()->hashData($action);
                    $element->setAttribute('hx-vars', $this->parseVars([
                        'sprig:action' => Craft::$app->getSecurity()->hashData($action)
                    ]));
                }

                $element->setAttribute('hx-'.$verb,
                    UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION, $params)
                );
            }

            foreach (self::HTMX_ATTRIBUTES as $attribute) {
                $value = $this->getElementAttribute($element, $attribute);

                if ($value) {
                    $element->setAttribute('hx-'.$attribute, $value);
                }
            }
        }

        /**
         * Generate output by concatenating all child elements of the body tag.
         * https://stackoverflow.com/a/38079328/1769259
         */
        $output = '';

        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $output .= $dom->saveHTML($node);
        }

        if ($csrf) {
            $output = Html::csrfInput().$output;
        }

        return $output;
    }

    /**
     * Parses variables for the `hx-vars` attribute.
     *
     * @param array $values
     * @return string
     */
    public function parseVars(array $values): string
    {
        // JSON encode, then remove braces
        $variables = [];

        foreach ($values as $name => $value) {
            // Wrap name and value in single quotes so it can be used within a HTML attribute
            $variables[] = "'".$name."':'".$value."'";
        }

        return implode(',', $variables);
    }

    /**
     * Returns an element attribute value.
     *
     * @param DOMElement $element
     * @param string $attribute
     * @return string
     */
    public function getElementAttribute(DOMElement $element, string $attribute): string
    {
        $prefixes = ['s', 'sprig'];

        foreach ($prefixes as $prefix) {
            $value = $element->getAttribute($prefix.'-'.$attribute);

            if ($value) {
                return $value;
            }
        }

        return '';
    }
}
