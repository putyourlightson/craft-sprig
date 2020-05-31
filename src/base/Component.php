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
    protected $template;

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if ($this->template !== null) {
            return Craft::$app->getView()->renderTemplate($this->template, $this->getAttributes());
        }

        return '';
    }
}
