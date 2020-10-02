<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\web\View;
use DOMDocument;
use DOMElement;
use IvoPetkov\HTML5DOMDocument;
use putyourlightson\sprig\base\ComponentInterface;
use putyourlightson\sprig\errors\InvalidVariableException;
use putyourlightson\sprig\Sprig;
use Twig\Markup;
use yii\base\Model;
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
        $vars = [];

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        $vars['sprig:siteId'] = Craft::$app->getSecurity()->hashData($siteId);

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

        $content = $this->getParsedTagAttributes($renderedContent);

        $vars['sprig:'.$type] = Craft::$app->getSecurity()->hashData($value);

        foreach ($variables as $name => $val) {
            $vars['sprig:variables['.$name.']'] = $this->_hashVariable($name, $val);
        }

        // Allow ID to be overridden, otherwise ensure random ID does not start with a digit (to avoid a JS error)
        $id = $attributes['id'] ?? ('component-'.StringHelper::randomString(6));

        // Allow the HTML element to be overridden, otherwise default to a regular div.
        $element = $attributes['_element'] ?? 'div';

        // Merge base attributes with provided attributes, then merge attributes with parsed attributes.
        // This is done in two steps so that `hx-vars` is included in the attributes when they are parsed.
        $attributes = array_merge(
            [
                'id' => $id,
                'class' => 'sprig-component',
                'hx-target' => 'this',
                'hx-include' => '#'.$id.' *',
                'hx-trigger' => 'refresh',
                'hx-get' => UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION),
                'hx-vars' => $this->getParsedVars($vars),
            ],
            $attributes
        );
        $attributes = array_merge(
            $attributes,
            $this->getParsedAttributes($attributes)
        );

        return Template::raw(
            Html::tag($element, $content, $attributes)
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
     * Sets the response headers.
     * @see https://htmx.org/reference/#response_headers
     *
     * @param mixed $params
     */
    public function setResponseHeaders($params)
    {
        if (!empty($params['_events'])) {
            Craft::$app->getResponse()->getHeaders()->set('HX-Trigger', $params['_events']);
        }

        if (!empty($params['_url'])) {
            Craft::$app->getResponse()->getHeaders()->set('HX-Push', $params['_url']);
        }
    }

    /**
     * Returns parsed tag attributes in the provided HTML
     *
     * @param string $html
     * @return string
     */
    public function getParsedTagAttributes(string $html): string
    {
        if (empty(trim($html))) {
            return $html;
        }

        // Use HTML5DOMDocument which supports HTML5 and takes care of UTF-8 encoding
        $dom = new HTML5DOMDocument();

        // Surround html with body tag to ensure script tags are not tampered with
        // https://github.com/putyourlightson/craft-sprig/issues/34
        $dom->loadHTML('<html><body>'.$html.'</body></html>');

        /** @var DOMElement $element */
        foreach ($dom->getElementsByTagName('*') as $element) {
            if ($element->hasAttribute('sprig')) {
                $verb = 'get';
                $vars = [];

                $method = $this->getParsedAttributeValue($element, 'method');

                // Make the check case-insensitive
                if (strtolower($method) == 'post') {
                    $verb = 'post';

                    $request = Craft::$app->getRequest();
                    $vars[$request->csrfParam] = $request->getCsrfToken();
                }

                $action = $this->getParsedAttributeValue($element, 'action');

                if ($action) {
                    $vars['sprig:action'] = Craft::$app->getSecurity()->hashData($action);
                }

                $element->setAttribute('hx-'.$verb,
                    UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION)
                );

                if (!empty($vars)) {
                    $element->setAttribute('hx-vars', $this->getParsedVars($vars));
                }
            }

            $parsedAttributes = $this->getParsedAttributes($element);

            foreach ($parsedAttributes as $attribute => $value) {
                $element->setAttribute($attribute, $value);
            }
        }

        return $dom->getElementsByTagName('body')[0]->innerHTML;
    }

    /**
     * Returns parsed variables for the `hx-vars` attribute.
     *
     * @param array $values
     * @return string
     */
    public function getParsedVars(array $values): string
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
     * Returns parsed htmx attributes.
     *
     * @param DOMElement|array $attributes
     * @return array
     */
    public function getParsedAttributes($attributes): array
    {
        $parsedAttributes = [];

        foreach (self::HTMX_ATTRIBUTES as $attribute) {
            $value = $this->getParsedAttributeValue($attributes, $attribute);

            if ($value) {
                // Append value to current value if `vars`
                if ($attribute == 'vars') {
                    if ($attributes instanceof DOMElement) {
                        $currentValue = $attributes->getAttribute('hx-'.$attribute);
                    }
                    else {
                        $currentValue = $attributes['hx-'.$attribute] ?? null;
                    }

                    $value = $currentValue ? $currentValue.','.$value : $value;
                }

                $parsedAttributes['hx-'.$attribute] = $value;
            }
        }

        return $parsedAttributes;
    }

    /**
     * Returns a parsed Sprig attribute value.
     *
     * @param DOMElement|array $attributes
     * @param string $attribute
     * @return string
     */
    public function getParsedAttributeValue($attributes, string $attribute): string
    {
        $prefixes = ['s', 'sprig'];

        foreach ($prefixes as $prefix) {
            if ($attributes instanceof DOMElement) {
                $value = $attributes->getAttribute($prefix.'-'.$attribute);
            }
            else {
                $value = $attributes[$prefix.'-'.$attribute] ?? '';
            }

            if ($value) {
                return $value;
            }
        }

        return '';
    }

    /**
     * Hashes a variable, possibly throwing an exception.
     *
     * @param string $name
     * @param mixed $value
     * @return string
     * @throws InvalidVariableException
     */
    private function _hashVariable(string $name, $value): string
    {
        $variables = [
            'name' => $name,
            'value' => $value,
        ];

        if ($value instanceof ElementInterface) {
            throw new InvalidVariableException($this->_getError('variable-element', $variables));
        }

        if ($value instanceof Model) {
            throw new InvalidVariableException($this->_getError('variable-model', $variables));
        }

        if (is_array($value)) {
            throw new InvalidVariableException($this->_getError('variable-array', $variables));
        }

        if (is_object($value)) {
            throw new InvalidVariableException($this->_getError('variable-object', $variables));
        }

        return Craft::$app->getSecurity()->hashData($value);
    }

    /**
     * Returns an error from a rendered template.
     *
     * @param string $templateName
     * @param array $variables
     * @return string
     */
    private function _getError(string $templateName, array $variables = []): string
    {
        $template = 'sprig/_errors/'.$templateName;

        return Craft::$app->getView()->renderTemplate($template, $variables, View::TEMPLATE_MODE_CP);
    }
}
