<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use craft\web\Controller;
use craft\web\UrlRule;
use putyourlightson\sprig\Sprig;
use yii\base\Event;
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
        if ($action == 'users/save-user') {
            $this->_registerSaveCurrentUserEvent();
        }

        // Add a redirect to the body params so we can extract the ID on success
        $redirectPrefix = 'https://';
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

        // Override the `currentUser` global variable with a fresh version, in case it was just updated
        // https://github.com/putyourlightson/craft-sprig/issues/81#issuecomment-758619306
        $variables['currentUser'] = Craft::$app->getUser()->getIdentity();

        $success = $actionResponse !== null;
        $variables['success'] = $success;

        if ($success) {
            $response = Craft::$app->getResponse();

            $variables['id'] = str_replace($redirectPrefix, '', $response->getHeaders()->get('location'));

            // Remove the redirect header
            $response->getHeaders()->remove('location');
        }

        // Set flash messages variable and delete them
        $variables['flashes'] = Craft::$app->getSession()->getAllFlashes(true);

        return $variables;
    }

    /**
     * Registers an event when saving the current user
     */
    private function _registerSaveCurrentUserEvent()
    {
        $currentUserId = Craft::$app->getUser()->getId();
        $userId = Craft::$app->getRequest()->getBodyParam('userId');

        if (!$currentUserId || $currentUserId != $userId) {
            return;
        }

        Event::on(User::class, User::EVENT_AFTER_SAVE, function (ModelEvent $event) {
            /** @var User $user */
            $user = $event->sender;

            // Update the user identity and regenerate the CSRF token in case the password was changed
            // https://github.com/putyourlightson/craft-sprig/issues/136
            Craft::$app->getUser()->setIdentity($user);
            Craft::$app->getRequest()->regenCsrfToken();
        });
    }
}
