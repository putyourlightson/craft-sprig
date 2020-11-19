<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit\services;

use Craft;
use craft\test\TestCase;
use putyourlightson\sprig\Sprig;
use UnitTester;
use yii\web\BadRequestHttpException;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class RequestTest extends TestCase
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testGetVariables()
    {
        $this->_mockRequestMethods(['getQueryParams' => [
            'page' => 1,
            '_secret' => 'xyz',
            'sprig:template' => 't',
        ]]);

        $variables = Sprig::$plugin->request->getVariables();

        $this->assertEquals(['page' => 1], $variables);
    }

    public function testGetValidatedParam()
    {
        $this->_mockRequestMethods(['getParam' => [
            Craft::$app->getSecurity()->hashData('xyz'),
        ]]);

        $this->tester->mockCraftMethods('request', [
            'getFullPath' => [''],
            'getParam' => Craft::$app->getSecurity()->hashData('xyz'),
        ]);

        $param = Sprig::$plugin->request->getValidatedParam('page');

        $this->assertEquals('xyz', $param);
    }

    public function testGetValidatedParamValues()
    {
        $this->_mockRequestMethods(['getParam' => [
            Craft::$app->getSecurity()->hashData('abc'),
            Craft::$app->getSecurity()->hashData('xyz'),
        ]]);

        $values = Sprig::$plugin->request->getValidatedParamValues('page');

        $this->assertEquals(['abc', 'xyz'], $values);
    }

    public function testValidateData()
    {
        $this->expectException(BadRequestHttpException::class);

        $data = Craft::$app->getSecurity()->hashData('xyz').'abc';

        Sprig::$plugin->request->validateData($data);
    }

    private function _mockRequestMethods(array $methods)
    {
        $this->tester->mockCraftMethods('request',
            array_merge(['getFullPath' => ['']], $methods)
        );
    }
}
