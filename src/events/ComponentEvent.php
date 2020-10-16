<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\events;

use yii\base\Event;

class ComponentEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public $value;

    /**
     * @var array
     */
    public $variables;

    /**
     * @var array
     */
    public $attributes;

    /**
     * @var string|null
     */
    public $output;
}
