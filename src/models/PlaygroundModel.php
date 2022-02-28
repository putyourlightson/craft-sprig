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
    public function defineRules(): array
    {
        return [
            ['id', 'integer'],
            [['slug', 'name', 'component', 'variables'], 'string'],
            [['id', 'slug'], 'default', 'value' => null],
            [['name', 'component'], 'required'],
        ];
    }

    /**
     * @inerhitdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['typecast'] = [
            'class' => AttributeTypecastBehavior::class,
        ];

        return $behaviors;
    }
}
