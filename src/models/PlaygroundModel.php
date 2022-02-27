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
    public ?int $id = null;

    /**
     * @var string
     */
    public string $slug = '';

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $component = '';

    /**
     * @var string
     */
    public string $variables = '';

    /**
     * @inheritdoc
     */
    public function rules(): array
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
     * @inerhitdoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ]);
    }
}
