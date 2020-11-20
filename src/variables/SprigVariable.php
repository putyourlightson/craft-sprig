<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use Craft;
use craft\db\Paginator;
use craft\db\Query;
use craft\helpers\Html;
use craft\helpers\Json;
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
    public $htmxVersion = '0.4.0';

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
     */
    public function getElement(): string
    {
        return Component::getElement();
    }

    /**
     * Returns the name of the active element.
     *
     * @return string
     */
    public function getElementName(): string
    {
        return Component::getElementName();
    }

    /**
     * Returns the value of the active element.
     *
     * @return string
     */
    public function getElementValue(): string
    {
        return Component::getElementValue();
    }

    /**
     * Returns the ID of the original target of the event that triggered the request.
     *
     * @return string
     */
    public function getEventTarget(): string
    {
        return Component::getEventTarget();
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

        $script = Html::jsFile($url, $attributes);

        return Template::raw($script);
    }
}
