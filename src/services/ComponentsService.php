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
use Twig\Markup;
use yii\base\Exception;

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
     * @const array
     */
    const HTMX_ATTRIBUTES = ['boost', 'confirm', 'delete', 'error-url', 'ext', 'get', 'history-elt', 'include', 'indicator', 'params', 'patch', 'post', 'prompt', 'push-url', 'put', 'select', 'sse', 'swap-oob', 'swap', 'target', 'trigger', 'ws'];

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
        $componentObject = $this->createObject($value, $variables);

        if ($componentObject) {
            $type = 'component';
            $renderedContent = $componentObject->render();
        }
        else {
            $type = 'template';

            if (!Craft::$app->getView()->doesTemplateExist($value)) {
                throw new Exception(Craft::t('sprig', 'Unable to find the component or template “{value}”.', [
                    'value' => $value,
                ]));
            }

            $renderedContent = Craft::$app->getView()->renderTemplate($value, $variables);
        }

        $renderedContent = $this->parseTagAttributes($renderedContent);

        $content = Html::hiddenInput('sprig:'.$type, Craft::$app->getSecurity()->hashData($value));

        foreach ($variables as $name => $value) {
            $content .= Html::hiddenInput('sprig:variables['.$name.']', $value);
        }

        $content .= Html::tag('div', $renderedContent, [
            'hx-target' => 'this',
            'class' => 'component',
        ]);

        $id = $attributes['id'] ?? StringHelper::randomString();

        $attributes = array_merge(
            [
                'id' => $id,
                'hx-include' => '#'.$id.' *',
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
        $dom->loadHTML($html);

        $csrf = false;

        /** @var DOMElement $element */
        foreach ($dom->getElementsByTagName('*') as $element) {
            $verb = '';
            $params = [];

            if ($element->hasAttribute('sprig')) {
                $verb = 'get';

                if ($this->getElementAttribute($element, 'method') == 'post') {
                    $verb = 'post';
                    $csrf = true;
                }

                $action = $this->getElementAttribute($element, 'action');

                if ($action) {
                    $params['sprig:action'] = Craft::$app->getSecurity()->hashData($action);
                }
            }

            if ($verb) {
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
