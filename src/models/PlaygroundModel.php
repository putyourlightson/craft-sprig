<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\models;

use craft\base\Model;
use yii\behaviors\AttributeTypecastBehavior;

class PlaygroundModel extends Model
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string
     */
    public $slug;

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

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['id', 'integer'],
                ['id', 'default', 'value' => null],
                ['slug', 'string'],
                ['slug', 'default', 'value' => null],
                ['name', 'required'],
                ['name', 'string'],
                ['component', 'required'],
                ['component', 'string'],
                ['variables', 'string'],
            ]
        );
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ]);
    }
}
