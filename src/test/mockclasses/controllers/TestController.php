<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\test\mockclasses\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\sprig\test\mockclasses\models\TestModel;
use yii\base\Model;
use yii\web\Response;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class TestController extends Controller
{
    /**
     * @var array
     */
    protected $allowAnonymous = true;

    /**
     * @return null
     */
    public function actionGetNull()
    {
        return null;
    }

    /**
     * @return Response
     */
    public function actionGetArray(): Response
    {
        return $this->asJson(['success' => true]);
    }

    /**
     * @return Response
     */
    public function actionGetModel(): Response
    {
        return $this->asJson(new TestModel(['success' => true]));
    }

    /**
     * @return Response
     */
    public function actionSaveSuccess(): Response
    {
        Craft::$app->getSession()->setNotice('Success');

        return $this->redirectToPostedUrl(['id' => 1]);
    }

    /**
     * @return null
     */
    public function actionSaveError()
    {
        Craft::$app->getSession()->setError('Error');

        Craft::$app->getUrlManager()->setRouteParams(['model' => new Model()]);

        return null;
    }
}
