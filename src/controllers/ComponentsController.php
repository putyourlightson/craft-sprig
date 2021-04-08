<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\web\Controller;
use craft\web\UrlRule;
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

        $variables = ArrayHelper::merge(
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
                $variables = ArrayHelper::merge($variables, $actionVariables);
            }

            $template = Sprig::$plugin->request->getValidatedParam('sprig:template');
            $content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        $response = Craft::$app->getResponse();
        $response->statusCode = 200;
        $response->data = Sprig::$plugin->components->parseHtml($content);

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
        // Add a redirect to the body params so we can extract the ID on success
        $redirectPrefix = 'http://';
        Craft::$app->getRequest()->setBodyParams(ArrayHelper::merge(
            Craft::$app->getRequest()->getBodyParams(),
            ['redirect' => Craft::$app->getSecurity()->hashData($redirectPrefix.'{id}')]
        ));

        $actionResponse = Craft::$app->runAction($action);

        // Extract the variables from the route params which are generally set when there are errors
        $variables = Craft::$app->getUrlManager()->getRouteParams() ?: [];

        /**
         * Merge and unset any variable called `variables`
         * https://github.com/putyourlightson/craft-sprig/issues/94#issuecomment-771489394
         * @see UrlRule::parseRequest()
         */
        if (isset($variables['variables'])) {
            $variables = ArrayHelper::merge($variables, $variables['variables']);
            unset($variables['variables']);
        }

        // TODO: remove in 2.0.0
        // Extract errors from the route param variables to maintain backwards compatibility.
        foreach ($variables as $routeParamVariable) {
            if ($routeParamVariable instanceof Model) {
                $variables['errors'] = $routeParamVariable->getErrors();

                break;
            }
        }

        $success = $actionResponse !== null;
        $variables['success'] = $success;

        if ($success) {
            $response = Craft::$app->getResponse();

            $variables['id'] = str_replace($redirectPrefix, '', $response->getHeaders()->get('location'));

            // Remove the redirect header
            $response->getHeaders()->remove('location');

            // Override the `currentUser` global variable with a fresh version if the current user was just updated
            // https://github.com/putyourlightson/craft-sprig/issues/81#issuecomment-758619306
            if ($action == 'users/save-user') {
                $userId = Craft::$app->getRequest()->getBodyParam('userId');

                if ($userId == Craft::$app->getUser()->getId()) {
                    $variables['currentUser'] = Craft::$app->getUsers()->getUserById($userId);
                }
            }
        }

        // Set flash messages variable and delete them
        $variables['flashes'] = Craft::$app->getSession()->getAllFlashes(true);

        return $variables;
    }
}
