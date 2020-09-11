<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\test\mockclasses\controllers;

use craft\web\Controller;
use putyourlightson\sprig\test\mockclasses\models\TestModel;

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
     *
     */
    public function actionGetNull()
    {
        return null;
    }

    /**
     *
     */
    public function actionGetArray()
    {
        return $this->asJson(['success' => true]);
    }

    /**
     *
     */
    public function actionGetModel()
    {
        return $this->asJson(new TestModel(['success' => true]));
    }
}
