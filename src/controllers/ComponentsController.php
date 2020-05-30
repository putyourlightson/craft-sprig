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

        $component = $this->_getValidatedParam('sprig:component');
        $action = $this->_getValidatedParam('sprig:action');
        $variables = $this->_getVariables();
        $content = '';

        if ($component) {
            $componentObject = Sprig::$plugin->componentsService->createObject($component, $variables);

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

                // Force format to HTML
                $response->format = $response::FORMAT_HTML;
            }

            $template = $this->_getValidatedParam('sprig:template');
            $content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        $response->data = Sprig::$plugin->componentsService->parseTagAttributes($content);

        return $response;
    }

    /**
     * Returns a validated request param.
     *
     * @return string|false|null
     */
    private function _getValidatedParam($name)
    {
        $value = Craft::$app->getRequest()->getParam($name);

        if ($value !== null) {
            $value = Craft::$app->getSecurity()->validateData($value);
        }

        return $value;
    }

    /**
     * Returns variables to be passed to the template.
     *
     * @return array
     */
    private function _getVariables(): array
    {
        $variables = [];
        $request = Craft::$app->getRequest();

        $variableParams = $request->getParam('sprig:variables', []);

        // The order of the sources is important as later sources will take precedence
        $variableSources = array_merge(
            $variableParams,
            $request->getQueryParams(),
            $request->getBodyParams()
        );

        foreach ($variableSources as $name => $value) {
            if (strpos($name, 'sprig:') === false) {
                $variables[$name] = $value;
            }
        }

        return $variables;
    }
}
