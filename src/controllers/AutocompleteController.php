<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use putyourlightson\sprig\helpers\Autocomplete as AutocompleteHelper;

use craft\web\Controller;
use yii\web\Response;

class AutocompleteController extends Controller
{
    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        $result = AutocompleteHelper::generate();

        return $this->asJson($result);
    }
}
