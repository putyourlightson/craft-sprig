<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\sprig\Sprig;
use yii\web\Response;

class PlaygroundController extends Controller
{
    /**
     * @param int|null $id
     * @return Response
     */
    public function actionIndex(int $id = null): Response
    {
        $playground = null;

        if ($id) {
            $playground = Sprig::$plugin->playground->get($id);
        }

        return $this->renderTemplate('sprig/index', [
            'playground' => $playground,
            'allPlaygrounds' => Sprig::$plugin->playground->getAll(),
            'fullPageForm' => true,
        ]);
    }

    /**
     * Saves a playground.
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

        Craft::$app->getSession()->setNotice(Craft::t('sprig', 'Playground saved.'));

        return $this->redirect('sprig/'.$id);
    }

    /**
     * Deletes a playground.
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');

        Sprig::$plugin->playground->delete($id);

        Craft::$app->getSession()->setNotice(Craft::t('sprig', 'Playground deleted.'));

        return $this->redirect('sprig');
    }
}
