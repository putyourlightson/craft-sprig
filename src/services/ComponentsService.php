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
use IvoPetkov\HTML5DOMDocument;
use IvoPetkov\HTML5DOMElement;
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
     * @const string[]
     */
    const SPRIG_PREFIXES = ['s', 'sprig'];

    /**
     * @const string[]
     */
    const HTMX_ATTRIBUTES = ['boost', 'confirm', 'delete', 'encoding', 'ext', 'get', 'history-elt', 'include', 'indicator', 'params', 'patch', 'post', 'prompt', 'push-url', 'put', 'select', 'sse', 'swap-oob', 'swap', 'target', 'trigger', 'vals', 'vars', 'ws'];

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

        $content = $this->parseHtml($renderedContent);

        $values['sprig:'.$type] = Craft::$app->getSecurity()->hashData($value);

        foreach ($variables as $name => $val) {
            $values['sprig:variables['.$name.']'] = $this->_hashVariable($name, $val);
        }

        // Allow ID to be overridden, otherwise ensure random ID does not start with a digit (to avoid a JS error)
        $id = $attributes['id'] ?? ('component-'.StringHelper::randomString(6));

        // Merge base attributes with provided attributes first, to ensure that `hx-vals` is included in the attributes when they are parsed.
        $attributes = array_merge(
            [
                'id' => $id,
                'class' => 'sprig-component',
                'hx-target' => 'this',
                'hx-include' => '#'.$id.' *',
                'hx-trigger' => 'refresh',
                'hx-get' => UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION),
                'hx-vals' => Json::htmlEncode($values),
            ],
            $attributes
        );

        $this->_parseAttributes($attributes);

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
     * Parses and returns HTML.
     *
     * @param string $html
     * @return string
     */
    public function parseHtml(string $html): string
    {
        if (empty(trim($html))) {
            return $html;
        }

        // Use HTML5DOMDocument which supports HTML5 and takes care of UTF-8 encoding
        $dom = new HTML5DOMDocument();

        // Surround html with body tag to ensure script tags are not tampered with
        // https://github.com/putyourlightson/craft-sprig/issues/34
        $html = '<!doctype html><html lang=""><body>'.$html.'</body></html>';

        // Allow duplicate IDs to avoid an error being thrown
        // https://github.com/ivopetkov/html5-dom-document-php/issues/21
        $dom->loadHTML($html, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        /** @var HTML5DOMElement $element */
        foreach ($dom->getElementsByTagName('*') as $element) {
            $attributes = $element->getAttributes();

            $this->_parseAttributes($attributes);

            foreach ($attributes as $attribute => $value) {
                $element->setAttribute($attribute, $value);
            }
        }

        return $dom->getElementsByTagName('body')[0]->innerHTML;
    }

    /**
     * Parses an array of attributes.
     *
     * @param array $attributes
     */
    public function _parseAttributes(array &$attributes)
    {
        $this->_parseSprigAttribute($attributes);

        foreach ($attributes as $key => $value) {
            $this->_parseAttribute($attributes, $key, $value);
        }
    }

    /**
     * Parses the Sprig attribute on an array of attributes.
     *
     * @param array $attributes
     */
    private function _parseSprigAttribute(array &$attributes)
    {
        // Use `!isset` over `!empty` because the attributes value will be an empty string
        if (!isset($attributes['sprig'])) {
            return;
        }

        $verb = 'get';
        $params = [];

        $method = $this->_getSprigAttributeValue($attributes, 'method');

        // Make the check case-insensitive
        if (strtolower($method) == 'post') {
            $verb = 'post';

            $request = Craft::$app->getRequest();

            $this->_appendValAttributes($attributes, [
                $request->csrfParam => $request->getCsrfToken(),
            ]);
        }

        $action = $this->_getSprigAttributeValue($attributes, 'action');

        if ($action) {
            $params['sprig:action'] = Craft::$app->getSecurity()->hashData($action);
        }

        $attributes['hx-'.$verb] = UrlHelper::actionUrl(self::RENDER_CONTROLLER_ACTION, $params);
    }

    /**
     * Parses an attribute in an array of attributes.
     *
     * @param array $attributes
     * @param string $key
     * @param string $value
     */
    public function _parseAttribute(array &$attributes, string $key, string $value)
    {
        $name = $this->_getSprigAttributeName($key);

        if (!$name) {
            return;
        }

        if (strpos($name, 'val:') === 0) {
            $name = StringHelper::toCamelCase(substr($name, 4));

            $this->_appendValAttributes($attributes, [$name => $value]);
        }
        elseif ($name == 'replace') {
            $attributes['hx-select'] = $value;
            $attributes['hx-target'] = $value;
            $attributes['hx-swap'] = 'outerHTML';
        }
        elseif (in_array($name, self::HTMX_ATTRIBUTES)) {
            // Append `s-vals` to `hx-vals`
            if ($name == 'vals') {
                $this->_appendValAttributes($attributes, Json::decode($value));
            }
            else {
                $attributes['hx-'.$name] = $value;
            }

            // Deprecate `s-vars`
            if ($name == 'vars') {
                Craft::$app->getDeprecator()->log(__METHOD__.':vars', 'The “s-vars” attribute in Sprig components has been deprecated for security reasons. Use the new “s-vals” or “s-val:*” attribute instead.');
            }
        }
    }

    /**
     * Appends `s-val:*` attributes to `hx-vals` attribute..
     *
     * @param array $attributes
     * @param array $valAttributes
     */
    private function _appendValAttributes(array &$attributes, array $valAttributes)
    {
        if (empty($valAttributes)) {
            return;
        }

        if (!empty($attributes['hx-vals'])) {
            $valAttributes = array_merge(
                Json::decode($attributes['hx-vals']),
                $valAttributes
            );
        }

        $attributes['hx-vals'] = Json::htmlEncode($valAttributes);
    }

    /**
     * Returns a Sprig attribute name if it exists.
     *
     * @param string $key
     * @return string
     */
    private function _getSprigAttributeName(string $key): string
    {
        foreach (self::SPRIG_PREFIXES as $prefix) {
            if (strpos($key, $prefix.'-') === 0) {
                return substr($key, strlen($prefix) + 1);
            }
        }

        return '';
    }

    /**
     * Returns a Sprig attribute value if it exists.
     *
     * @param array $attributes
     * @param string $name
     * @return string
     */
    private function _getSprigAttributeValue(array $attributes, string $name): string
    {
        foreach (self::SPRIG_PREFIXES as $prefix) {
            if (!empty($attributes[$prefix.'-'.$name])) {
                return $attributes[$prefix.'-'.$name];
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
