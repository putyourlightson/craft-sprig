<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\errors;

use Craft;
use yii\base\Exception;

class InvalidVariableException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Craft::t('sprig', 'Invalid variable');
    }
}
