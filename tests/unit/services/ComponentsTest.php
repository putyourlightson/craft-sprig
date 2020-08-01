<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit;

use Codeception\Test\Unit;
use Craft;
use putyourlightson\sprig\Sprig;
use UnitTester;
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

    public function testCreate()
    {
        $this->tester->mockCraftMethods('view', [
            'doesTemplateExist' => true
        ]);

        Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@templates'));
        $markup = Sprig::$plugin->components->create('_component', ['number' => '15'], ['id' => 'abc']);
        $html = (string)$markup;

        $this->assertStringContainsString('<div id="abc"', $html);
        $this->assertStringContainsString('xyz 15', $html);
    }

    public function testCreateFail()
    {
        $this->expectException(BadRequestHttpException::class);

        Sprig::$plugin->components->create('_no-component');
    }

    public function testCreateObjectFail()
    {
        $object = Sprig::$plugin->components->createObject('_no-component');

        $this->assertNull($object);
    }

    public function testParseTagAttributes()
    {
        $html = '<div sprig s-method="post" s-action="a/b/c" s-vars="limit:1"></div>';

        $html = Sprig::$plugin->components->parseTagAttributes($html);

        $this->assertStringContainsString('hx-post', $html);
        $this->assertStringContainsString('CRAFT_CSRF_TOKEN', $html);
        $this->assertStringContainsString('sprig:action', $html);
        $this->assertStringContainsString('limit:1', $html);
    }
}
