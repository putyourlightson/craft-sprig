<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\sprig\plugin\Sprig;
use yii\web\Response;

class PlaygroundController extends Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Sprig::$plugin->settings->enablePlayground) {
            return false;
        }

        return true;
    }

    /**
     * Renders the main playground template.
     */
    public function actionIndex(int $id = null, string $slug = null): Response
    {
        $playground = null;

        if ($id) {
            $playground = Sprig::$plugin->playground->get($id);
        }

        $samples = Sprig::$plugin->playground->getSamples();

        if ($id === null && $slug === null) {
            $slug = array_key_first($samples);
        }

        if ($slug) {
            $playground = $samples[$slug] ?? null;
        }

        return $this->renderTemplate('sprig/index', [
            'playground' => $playground,
            'slug' => $slug,
            'allSaved' => Sprig::$plugin->playground->getSaved(),
            'allSamples' => $samples,
        ]);
    }

    /**
     * Saves a component.
     */
    public function actionSave(): Response
    {
        $this->requirePostRequest();

        $name = $this->request->getParam('name', '');
        $component = $this->request->getParam('component', '');
        $variables = $this->request->getParam('variables', '');

        $id = Sprig::$plugin->playground->save($name, $component, $variables);

        Craft::$app->getSession()->setSuccess(Craft::t('sprig', 'Component saved.'));

        return $this->redirect('sprig/' . $id);
    }

    /**
     * Updates a component.
     */
    public function actionUpdate(): Response
    {
        $this->requirePostRequest();

        $id = $this->request->getParam('id');
        $component = $this->request->getParam('component', '');
        $variables = $this->request->getParam('variables', '');

        Sprig::$plugin->playground->update($id, $component, $variables);

        Craft::$app->getSession()->setSuccess(Craft::t('sprig', 'Component updated.'));

        return $this->redirect('sprig/' . $id);
    }

    /**
     * Deletes a component.
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();

        $id = $this->request->getParam('id');

        Sprig::$plugin->playground->delete($id);

        Craft::$app->getSession()->setSuccess(Craft::t('sprig', 'Component deleted.'));

        return $this->redirect('sprig');
    }
}
