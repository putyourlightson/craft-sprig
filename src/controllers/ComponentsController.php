<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\sprig\Sprig;
use yii\base\InvalidRouteException;
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
     * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully.
     */
    public function actionRender(): Response
    {
        $response = Craft::$app->getResponse();

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
                // Force the request to accept JSON only
                Craft::$app->getRequest()->setAcceptableContentTypes(['application/json' => []]);

                $jsonResponse = Craft::$app->runAction($action);

                if ($jsonResponse !== null) {
                    $variables = array_merge($variables, $jsonResponse->data);
                }

                // Force 200 status code and set format to HTML
                $response->statusCode = 200;
                $response->format = $response::FORMAT_HTML;
            }

            Sprig::$plugin->setResponseHeaders($variables);

            $template = Sprig::$plugin->request->getValidatedParam('sprig:template');
            $content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        $response->data = Sprig::$plugin->components->parseTagAttributes($content);

        return $response;
    }
}
