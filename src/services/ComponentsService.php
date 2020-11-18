<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\web\View;
use DOMElement;
use IvoPetkov\HTML5DOMDocument;
use putyourlightson\sprig\base\ComponentInterface;
use putyourlightson\sprig\components\SprigPlayground;
use putyourlightson\sprig\errors\InvalidVariableException;
use putyourlightson\sprig\events\ComponentEvent;
use putyourlightson\sprig\Sprig;
use Twig\Markup;
use yii\base\Model;
use yii\web\BadRequestHttpException;

/**
 * @property-write mixed $responseHeaders
 */
class ComponentsService extends Component
{
    /**
     * @event ComponentEvent
     */
    const EVENT_BEFORE_CREATE_COMPONENT = 'beforeCreateComponent';

    /**
     * @event ComponentEvent
     */
    const EVENT_AFTER_CREATE_COMPONENT = 'afterCreateComponent';

    /**
     * @const string
     */
    const COMPONENT_NAMESPACE = 'sprig\\components\\';

    /**
     * @const string
     */
    const RENDER_CONTROLLER_ACTION = 'sprig/components/render';

    /**
     * TODO: remove vars
     *
     * @const string[]
     */
    const HTMX_ATTRIBUTES = ['boost', 'confirm', 'delete', 'ext', 'get', 'history-elt', 'include', 'indicator', 'params', 'patch', 'post', 'prompt', 'push-url', 'put', 'select', 'sse', 'swap-oob', 'swap', 'target', 'trigger', 'vals', 'vars', 'ws'];

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
        $values = [];

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        $values['sprig:siteId'] = Craft::$app->getSecurity()->hashData($siteId);

        $mergedVariables = array_merge(
            $variables,
            Sprig::$plugin->request->getVariables()
        );

        $event = new ComponentEvent([
            'value' => $value,
            'variables' => $mergedVariables,
            'attributes' => $attributes,
        ]);
        $this->trigger(self::EVENT_BEFORE_CREATE_COMPONENT, $event);

        // Repopulate values from event
        $value = $event->value;
        $mergedVariables = $event->variables;
        $attributes = $event->attributes;

        $componentObject = $this->createObject($value, $mergedVariables);

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

            $renderedContent = Craft::$app->getView()->renderTemplate($value, $mergedVariables);
        }

        $content = $this->getParsedTagAttributes($renderedContent);

        $values['sprig:'.$type] = Craft::$app->getSecurity()->hashData($value);

        foreach ($variables as $name => $val) {
            $values['sprig:variables['.$name.']'] = $this->_hashVariable($name, $val);
        }

        // Allow ID to be overridden, otherwise ensure random ID does not start with a digit (to avoid a JS error)
        $id = $attributes['id'] ?? ('component-'.StringHelper::randomString(6));

        // Merge base attributes with provided attributes, then merge attributes with parsed attributes.
        // This is done in two steps so that `hx-vals` is included in the attributes when they are parsed.
        $attributes = array_merge(
            [
                'id' => $id,
                'class' => 'sprig-component',
                'hx-target' => 'this',
                'hx-include' => '#'.$id.' *',
                'hx-trigger' => 'refresh',
                'hx-get' => UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION),
                'hx-vals' => Json::encode($values),
            ],
            $attributes
        );
        $attributes = array_merge(
            $attributes,
            $this->getParsedAttributes($attributes)
        );

        $event->output = Html::tag('div', $content, $attributes);

        if ($this->hasEventHandlers(self::EVENT_AFTER_CREATE_COMPONENT)) {
            $this->trigger(self::EVENT_AFTER_CREATE_COMPONENT, $event);
        }

        return Template::raw($event->output);
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
        if ($component == 'SprigPlayground') {
            return new SprigPlayground(['variables' => $variables]);
        }

        $componentClass = self::COMPONENT_NAMESPACE.$component;

        if (!class_exists($componentClass)) {
            return null;
        }

        $componentObject = new $componentClass();

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
//        Craft::dd($html);
//        return $html;

        // Use HTML5DOMDocument which supports HTML5 and takes care of UTF-8 encoding
        $dom = new HTML5DOMDocument();

        // Surround html with body tag to ensure script tags are not tampered with
        // https://github.com/putyourlightson/craft-sprig/issues/34
        $html = '<!doctype html><html><body>'.$html.'</body></html>';

        // Allow duplicate IDs to avoid an error being thrown
        // https://github.com/ivopetkov/html5-dom-document-php/issues/21
        $dom->loadHTML($html, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        /** @var DOMElement $element */
        foreach ($dom->getElementsByTagName('*') as $element) {
            if ($element->hasAttribute('sprig')) {
                $verb = 'get';
                $values = [];

                $method = $this->getParsedAttributeValue($element, 'method');

                // Make the check case-insensitive
                if (strtolower($method) == 'post') {
                    $verb = 'post';

                    $request = Craft::$app->getRequest();
                    $values[$request->csrfParam] = $request->getCsrfToken();
                }

                $action = $this->getParsedAttributeValue($element, 'action');

                if ($action) {
                    $values['sprig:action'] = Craft::$app->getSecurity()->hashData($action);
                }

                $element->setAttribute('hx-'.$verb,
                    UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION)
                );

                if (!empty($values)) {
                    $element->setAttribute('hx-vals', Json::htmlEncode($values));
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
                // Append value to current value if `vals`
                if ($attribute == 'vals') {
                    if ($attributes instanceof DOMElement) {
                        $currentValue = $attributes->getAttribute('hx-'.$attribute);
                    }
                    else {
                        $currentValue = $attributes['hx-'.$attribute] ?? null;
                    }

                    if ($currentValue) {
                        $value = Json::encode(array_merge(
                            Json::decode($currentValue),
                            Json::decode($value)
                        ));
                    }
                }

                if ($attribute == 'vars') {
                    Craft::$app->getDeprecator()->log(__METHOD__.':vars', 'The “s-vars” attribute in Sprig components has been deprecated for security reasons. Use the “sprig.vals” template variable instead.');
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
