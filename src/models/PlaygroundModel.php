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
    protected function defineRules(): array
    {
        return [
            ['id', 'integer'],
            [['slug', 'name', 'component', 'variables'], 'string'],
            [['id', 'slug'], 'default', 'value' => null],
            [['name', 'component'], 'required'],
        ];
    }
}
