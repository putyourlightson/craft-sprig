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
    /**
     * @param int|null $id
     * @param string|null $slug
     * @return Response
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
     *
     * @return Response
     */
    public function actionSave(): Response
    {
        $request = Craft::$app->getRequest();

        $name = $request->getParam('name', '');
        $component = $request->getParam('component', '');
        $variables = $request->getParam('variables', '');

        $id = Sprig::$plugin->playground->save($name, $component, $variables);

        Craft::$app->getSession()->setNotice(Craft::t('sprig', 'Component saved.'));

        return $this->redirect('sprig/'.$id);
    }

    /**
     * Updates a component.
     *
     * @return Response
     */
    public function actionUpdate(): Response
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $component = $request->getParam('component', '');
        $variables = $request->getParam('variables', '');

        Sprig::$plugin->playground->update($id, $component, $variables);

        Craft::$app->getSession()->setNotice(Craft::t('sprig', 'Component updated.'));

        return $this->redirect('sprig/'.$id);
    }

    /**
     * Deletes a component.
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');

        Sprig::$plugin->playground->delete($id);

        Craft::$app->getSession()->setNotice(Craft::t('sprig', 'Component deleted.'));

        return $this->redirect('sprig');
    }
}
