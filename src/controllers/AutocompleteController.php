<?php
/**
 * @copyright Copyright (c) nystudio107, PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\controllers;

use craft\web\Controller;

use putyourlightson\sprig\plugin\helpers\Autocomplete as AutocompleteHelper;
use yii\web\Response;

/**
 * @author    nystudio107
 * @package   Sprig
 * @since     1.9.0
 */
class AutocompleteController extends Controller
{
    /**
     * Returns the autocomplete array.
     */
    public function actionIndex(): Response
    {
        $result = AutocompleteHelper::generate();

        return $this->asJson($result);
    }
}
