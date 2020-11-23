<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit\services;

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
        $markup = Sprig::$plugin->components->create(
            '_component',
            ['number' => '15'],
            ['id' => 'abc', 's-trigger' => 'load', 's-vars' => 'limit:1', 's-push-url' => 'new-url']
        );
        $html = (string)$markup;

        $this->assertStringContainsString('id="abc"', $html);
        $this->assertStringContainsString('hx-include="#abc *"', $html);
        $this->assertStringContainsString('hx-trigger="load"', $html);
        $this->assertStringContainsString('sprig:template', $html);
        $this->assertStringContainsString('limit:1', $html);
        $this->assertStringContainsString('hx-push-url="new-url"', $html);
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

    public function testCreateInvalidVariableEntry()
    {
        $this->_testCreateInvalidVariable(['number' => '', 'entry' => new Entry()]);
    }

    public function testCreateInvalidVariableModel()
    {
        $this->_testCreateInvalidVariable(['number' => '', 'model' => new Model()]);
    }

    public function testCreateInvalidVariableObject()
    {
        $this->_testCreateInvalidVariable(['number' => '', 'model' => (object)[]]);
    }

    public function testCreateInvalidVariableArray()
    {
        $this->_testCreateInvalidVariable(['number' => '', 'array' => []]);
    }

    public function testCreateObjectNoComponent()
    {
        $object = Sprig::$plugin->components->createObject('_no-component');

        $this->assertNull($object);
    }

    public function testGetParsedTagAttributes()
    {
        $html = '<div sprig s-method="post" s-action="a/b/c" s-vals=\'{"limit":1}\'></div>';

        $html = Sprig::$plugin->components->parseHtml($html);

        $this->assertStringContainsString('hx-post=', $html);
        $this->assertStringContainsString('CRAFT_CSRF_TOKEN', $html);
        $this->assertStringContainsString('sprig:action', $html);
        $this->assertStringContainsString('"limit":1', $html);
    }

    public function testGetParsedTagAttributesVals()
    {
        $html = '<div sprig s-val:x-y-z="a" s-vals=\'{"limit":1}\'></div>';
        $html = Sprig::$plugin->components->parseHtml($html);
        $this->assertStringContainsString('hx-vals=\'{"xYZ":"a","limit":1}\'', $html);
    }

    public function testGetParsedTagAttributesValsEncodedAndSanitized()
    {
        $html = '<div sprig s-val:x="alert(\'xss\')" s-val:z=\'alert("xss")\' s-vals=\'{"limit":1}\'></div>';
        $html = Sprig::$plugin->components->parseHtml($html);
        $this->assertStringContainsString('hx-vals=\'{"x":"alert(\u0027xss\u0027)","z":"alert(\u0022xss\u0022)","limit":1}\'', $html);
    }

    public function testGetParsedTagAttributesEmpty()
    {
        $html = '';
        $result = Sprig::$plugin->components->parseHtml($html);
        $this->assertEquals($html, $result);
    }

    public function testGetParsedTagAttributesHtml()
    {
        $html = '<div><p><span><template><h1>Hello</h1></template></span></p></div>';
        $result = Sprig::$plugin->components->parseHtml($html);
        $this->assertEquals($html, $result);
    }

    public function testGetParsedTagAttributesDuplicateIds()
    {
        $html = '<div id="my-id"><p id="my-id"><span id="my-id"></span></p></div>';
        $result = Sprig::$plugin->components->parseHtml($html);
        $this->assertEquals($html, $result);
    }

    public function testGetParsedTagAttributesScript()
    {
        $html = '<script><h1>Hello</h1></script>';
        $result = Sprig::$plugin->components->parseHtml($html);
        $this->assertEquals($html, $result);
    }

    public function testGetParsedTagAttributesUtfEncoding()
    {
        $html = 'ÆØÅäöü';
        $result = Sprig::$plugin->components->parseHtml($html);
        $this->assertEquals($html, $result);
    }

    private function _testCreateInvalidVariable(array $variables)
    {
        $this->tester->mockCraftMethods('view', ['doesTemplateExist' => true]);
        Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@templates'));

        $this->expectException(InvalidVariableException::class);

        Sprig::$plugin->components->create('_component', $variables);
    }
}
