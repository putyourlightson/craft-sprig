<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\errors;

use yii\base\Exception;

class InvalidVariableException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Invalid variable';
    }
}
