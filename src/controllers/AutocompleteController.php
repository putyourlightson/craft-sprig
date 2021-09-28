<?php
/**
 * @copyright Copyright (c) nystudio107, PutYourLightsOn
 */

namespace putyourlightson\sprig\controllers;

use putyourlightson\sprig\helpers\Autocomplete as AutocompleteHelper;

use craft\web\Controller;
use yii\web\Response;

/**
 * Class AutocompleteController
 *
 * @author    nystudio107
 * @package   Sprig
 * @since     1.9.0
 */
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
