<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit;

use Codeception\Test\Unit;
use Craft;
use craft\elements\Entry;
use putyourlightson\sprig\errors\InvalidVariableException;
use putyourlightson\sprig\Sprig;
use UnitTester;
use yii\base\Model;
use yii\web\BadRequestHttpException;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class ComponentsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();

        Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@templates'));
    }

    public function testCreate()
    {
        $markup = Sprig::$plugin->components->create('_component', ['number' => '15'], [
            'id' => 'abc', 's-trigger' => 'load', 's-vars' => 'limit:1'
        ]);
        $html = (string)$markup;

        $this->assertStringContainsString('id="abc"', $html);
        $this->assertStringContainsString('hx-include="#abc *"', $html);
        $this->assertStringContainsString('hx-trigger="load"', $html);
        $this->assertStringContainsString('sprig:template', $html);
        $this->assertStringContainsString('limit:1', $html);
        $this->assertStringContainsString('xyz 15', $html);
    }

    public function testCreateEmptyComponent()
    {
        $markup = Sprig::$plugin->components->create('_empty');
        $html = (string)$markup;

        $this->assertStringContainsString('hx-get', $html);
    }

    public function testCreateNoComponent()
    {
        $this->expectException(BadRequestHttpException::class);

        Sprig::$plugin->components->create('_no-component');
    }

    public function testCreateInvalidVariable()
    {
        $this->tester->mockCraftMethods('view', ['doesTemplateExist' => true]);
        Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@templates'));

        $this->expectException(InvalidVariableException::class);

        Sprig::$plugin->components->create('_component', ['number' => '', 'entry' => new Entry()]);
        Sprig::$plugin->components->create('_component', ['number' => '', 'model' => new Model()]);
        Sprig::$plugin->components->create('_component', ['number' => '', 'model' => (object)[]]);
        Sprig::$plugin->components->create('_component', ['number' => '', 'array' => []]);
    }

    public function testCreateObjectNoComponent()
    {
        $object = Sprig::$plugin->components->createObject('_no-component');

        $this->assertNull($object);
    }

    public function testGetParsedTagAttributes()
    {
        $html = '<div sprig s-method="post" s-action="a/b/c" s-vars="limit:1"></div>';

        $html = Sprig::$plugin->components->getParsedTagAttributes($html);

        $this->assertStringContainsString('hx-post', $html);
        $this->assertStringContainsString('CRAFT_CSRF_TOKEN', $html);
        $this->assertStringContainsString('sprig:action', $html);
        $this->assertStringContainsString('limit:1', $html);
    }
}
