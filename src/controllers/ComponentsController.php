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
        $response = Craft::$app->getResponse();

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

            Sprig::$plugin->components->setResponseHeaders($variables);

            $template = Sprig::$plugin->request->getValidatedParam('sprig:template');
            $content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        // Force 200 status code and set format to HTML
        $response->statusCode = 200;
        $response->format = $response::FORMAT_HTML;

        $response->data = Sprig::$plugin->components->getParsedTagAttributes($content);

        return $response;
    }

    /**
     * Runs an action and returns the variables from the response
     *
     * @param string $action
     * @return array
     */
    private function _runActionInternal(string $action): array
    {
        // Force the request to be an AJAX request that accepts JSON only
        Craft::$app->getRequest()->getHeaders()->set('X-Requested-With', 'XMLHttpRequest');
        Craft::$app->getRequest()->setAcceptableContentTypes(['application/json' => []]);

        $response = Craft::$app->runAction($action);

        if (!empty($response->data)) {
            if (is_array($response->data)) {
                return $response->data;
            }

            if ($response->data instanceof Model) {
                return $response->data->getAttributes();
            }
        }

        return [];
    }
}
