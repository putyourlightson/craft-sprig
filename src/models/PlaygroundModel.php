<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\models;

use craft\base\Model;

class PlaygroundModel extends Model
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $component;

    /**
     * @var string
     */
    public $variables;
}
