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

                // Force 200 status code and set format to HTML
                $response->statusCode = 200;
                $response->format = $response::FORMAT_HTML;
            }

            Sprig::$plugin->setResponseHeaders($variables);

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
        $request = Craft::$app->getRequest();

        $params = $request->getParam('sprig:variables', []);

        $requestParams = array_merge(
            $request->getQueryParams(),
            $request->getBodyParams()
        );

        foreach ($requestParams as $name => $value) {
            // Only include the variable if its name does not begin with an underscore
            if (strpos($name, '_') !== 0) {
                $params[$name] = $value;
            }
        }

        foreach ($params as $name => $value) {
            // Don't include the variable if its name begins with `sprig:`
            if (strpos($name, 'sprig:') === 0) {
                continue;
            }

            // Validate the variable value if its name begins with an underscore
            if (strpos($name, '_') === 0) {
                $value = Craft::$app->getSecurity()->validateData($value);
            }

            $variables[$name] = $value;
        }

        return $variables;
    }
}
