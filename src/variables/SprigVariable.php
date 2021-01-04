<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use Craft;
use craft\db\Paginator;
use craft\db\Query;
use craft\helpers\Html;
use craft\helpers\Template;
use putyourlightson\sprig\base\Component;
use putyourlightson\sprig\Sprig;
use Twig\Markup;
use yii\web\BadRequestHttpException;

class SprigVariable
{
    /**
     * @var string
     */
    public $htmxVersion = '1.1.0';

    /**
     * Generate the SRI hash at https://www.srihash.org/
     *
     * @var string
     */
    public $htmxSRIHash = 'sha384-';

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
     * @deprecated
     */
    public function getHyperscript(array $attributes = []): Markup
    {
        Craft::$app->getDeprecator()->log(__METHOD__, 'The “sprig.hyperscript” function has been deprecated. Import hyperscript in your templates manually instead.');

        return $this->_getScript('hyperscript', $this->hyperscriptVersion, $attributes);
    }

    /**
     * Returns whether this is a Sprig request.
     *
     * @return bool
     */
    public function getIsRequest(): bool
    {
        return Component::getIsRequest();
    }

    /**
     * Returns whether this is a Sprig include.
     *
     * @return bool
     */
    public function getIsInclude(): bool
    {
        return Component::getIsInclude();
    }

    /**
     * Returns the ID of the active element.
     *
     * @return string
     * @deprecated
     */
    public function getElement(): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, 'The “sprig.element” tag has been deprecated and should be removed from templates.');

        return '';
    }

    /**
     * Returns the name of the active element.
     *
     * @return string
     * @deprecated
     */
    public function getElementName(): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, 'The “sprig.elementName” tag has been deprecated and should be removed from templates.');

        return '';
    }

    /**
     * Returns the value of the active element.
     *
     * @return string
     * @deprecated
     */
    public function getElementValue(): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, 'The “sprig.elementValue” tag has been deprecated and should be removed from templates.');

        return '';
    }

    /**
     * Returns the ID of the original target of the event that triggered the request.
     *
     * @return string
     * @deprecated
     */
    public function getEventTarget(): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, 'The “sprig.eventTarget” tag has been deprecated and should be removed from templates.');

        return '';
    }

    /**
     * Returns the value entered by the user when prompted via `s-prompt`.
     *
     * @return string
     */
    public function getPrompt(): string
    {
        return Component::getPrompt();
    }

    /**
     * Returns the ID of the target element.
     *
     * @return string
     */
    public function getTarget(): string
    {
        return Component::getTarget();
    }

    /**
     * Returns the ID of the element that triggered the request.
     *
     * @return string
     */
    public function getTrigger(): string
    {
        return Component::getTrigger();
    }

    /**
     * Returns the name of the element that triggered the request.
     *
     * @return string
     */
    public function getTriggerName(): string
    {
        return Component::getTriggerName();
    }

    /**
     * Returns the URL that the Sprig component was loaded from.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return Component::getUrl();
    }

    /**
     * Triggers client-side events.
     *
     * @param string|array $events
     * @param string $on
     */
    public function triggerEvents($events, string $on = 'load')
    {
        Component::triggerEvents($events, $on);
    }

    /**
     * Pushes the URL into the history stack.
     *
     * @param string $url
     */
    public function pushUrl(string $url)
    {
        Component::pushUrl($url);
    }

    /**
     * Paginates an element query.
     *
     * @param Query $query
     * @param int $currentPage
     * @param array $config
     * @return PaginateVariable
     */
    public function paginate(Query $query, int $currentPage = 1, array $config = []): PaginateVariable
    {
        /** @see Template::paginateCriteria() */
        $paginatorQuery = clone $query;
        $paginatorQuery->limit(null);

        $defaultConfig = [
            'currentPage' => $currentPage,
            'pageSize' => $query->limit ?: 100,
        ];
        $config = array_merge($defaultConfig, $config);
        $paginator = new Paginator($paginatorQuery, $config);

        return PaginateVariable::create($paginator);
    }

    /**
     * Returns a new component.
     *
     * @param string $value
     * @param array $variables
     * @param array $attributes
     * @return Markup
     * @throws BadRequestHttpException
     */
    public function getComponent(string $value, array $variables = [], array $attributes = []): Markup
    {
        return Sprig::$plugin->components->create($value, $variables, $attributes);
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
        else {
            // TODO: remove this conditional in 2.0.0
            if ($name == 'htmx') {
                // Add subresource integrity
                // https://github.com/bigskysoftware/htmx/issues/261
                $attributes['integrity'] = $this->htmxSRIHash;
                $attributes['crossorigin'] = 'anonymous';
            }
        }

        $script = Html::jsFile($url, $attributes);

        return Template::raw($script);
    }
}
