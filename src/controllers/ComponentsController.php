<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\sprig\Sprig;
use yii\base\Model;
use yii\web\Response;

class ComponentsController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $allowAnonymous = true;

    /**
     * Renders a component.
     *
     * @return Response
     */
    public function actionRender(): Response
    {
        $siteId = Sprig::$plugin->request->getValidatedParam('sprig:siteId');
        Craft::$app->getSites()->setCurrentSite($siteId);

        $component = Sprig::$plugin->request->getValidatedParam('sprig:component');
        $action = Sprig::$plugin->request->getValidatedParam('sprig:action');

        $variables = array_merge(
            Sprig::$plugin->request->getValidatedParamValues('sprig:variables'),
            Sprig::$plugin->request->getVariables()
        );

        $content = '';

        if ($component) {
            $componentObject = Sprig::$plugin->components->createObject($component, $variables);

            if ($componentObject) {
                if ($action && method_exists($componentObject, $action)) {
                    call_user_func([$componentObject, $action]);
                }

                $content = $componentObject->render();
            }
        }
        else {
            if ($action) {
                $actionVariables = $this->_runActionInternal($action);
                $variables = array_merge($variables, $actionVariables);
            }

            $template = Sprig::$plugin->request->getValidatedParam('sprig:template');
            $content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        $this->response->statusCode = 200;
        $this->response->data = Sprig::$plugin->components->parseHtml($content);

        return $this->response;
    }

    /**
     * Runs an action and returns the variables from the response
     *
     * @param string $action
     * @return array
     */
    private function _runActionInternal(string $action): array
    {
        $actionResponse = Craft::$app->runAction($action);

        $variables = Craft::$app->getUrlManager()->getRouteParams() ?: [];
        $variables['success'] = $actionResponse !== null;

        return $variables;
    }
}
