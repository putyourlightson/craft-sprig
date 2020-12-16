<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit\controllers;

use Codeception\Test\Unit;
use Craft;
use craft\web\View;
use putyourlightson\sprig\Sprig;
use putyourlightson\sprig\test\mockclasses\controllers\TestController;
use UnitTester;
use yii\web\Response;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class ComponentsControllerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();

        // Set controller namespace to web
        Sprig::$plugin->controllerNamespace = str_replace('\\console', '', Sprig::$plugin->controllerNamespace);

        // Add test controller
        Sprig::$plugin->controllerMap = ['test' => TestController::class];

        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
        Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@templates'));
    }

    public function testRender()
    {
        Craft::$app->getRequest()->setQueryParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_empty'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertEquals('', trim($response->data));
    }

    public function testRenderNull()
    {
        Craft::$app->getRequest()->setQueryParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_action'),
            'sprig:action' => Craft::$app->getSecurity()->hashData('sprig/test/get-null'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertStringContainsString('success:false', trim($response->data));
    }

    public function testRenderArray()
    {
        Craft::$app->getRequest()->setQueryParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_action'),
            'sprig:action' => Craft::$app->getSecurity()->hashData('sprig/test/get-array'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertStringContainsString('success:true', trim($response->data));
    }

    public function testRenderModel()
    {
        Craft::$app->getRequest()->setQueryParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_action'),
            'sprig:action' => Craft::$app->getSecurity()->hashData('sprig/test/get-model'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertStringContainsString('success:true', trim($response->data));
    }

    public function testControllerActionSuccess()
    {
        Craft::$app->getRequest()->setBodyParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_action'),
            'sprig:action' => Craft::$app->getSecurity()->hashData('sprig/test/save-success'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertStringContainsString('success:true', trim($response->data));
        $this->assertStringContainsString('id:1', trim($response->data));
        $this->assertStringContainsString('flashes[notice]:Success', trim($response->data));
    }

    public function testControllerActionError()
    {
        Craft::$app->getRequest()->setBodyParams([
            'sprig:template' => Craft::$app->getSecurity()->hashData('_action'),
            'sprig:action' => Craft::$app->getSecurity()->hashData('sprig/test/save-error'),
        ]);

        /** @var Response $response */
        $response = Sprig::$plugin->runAction('components/render');

        $this->assertStringContainsString('success:false', trim($response->data));
        $this->assertStringContainsString('flashes[error]:Error', trim($response->data));
        $this->assertStringContainsString('model', trim($response->data));
        $this->assertStringContainsString('errors', trim($response->data));
    }
}
