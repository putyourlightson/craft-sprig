<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\services;

use Craft;
use craft\base\Component;
use yii\base\Exception;

class RequestService extends Component
{
    /**
     * @const string[]
     */
    const DISALLOWED_PREFIXES = ['_', 'sprig:'];

    /**
     * Returns allowed request variables.
     *
     * @return array
     */
    public function getVariables(): array
    {
        $variables = [];

        $request = Craft::$app->getRequest();

        $requestParams = array_merge(
            $request->getQueryParams(),
            $request->getBodyParams()
        );

        foreach ($requestParams as $name => $value) {
            $disallowed = false;

            foreach (self::DISALLOWED_PREFIXES as $prefix) {
                if (strpos($name, $prefix) !== 0) {
                    $disallowed = true;
                    break;
                }
            }

            if (!$disallowed) {
                $variables[$name] = $value;
            }
        }

        return $variables;
    }

    /**
     * Returns a validated request parameter.
     *
     * @param $name
     * @return string|false|null
     */
    public function getValidatedParam($name)
    {
        $value = Craft::$app->getRequest()->getParam($name);

        if ($value !== null) {
            $value = self::validateData($value);
        }

        return $value;
    }

    /**
     * Returns an array of validated request parameter values.
     *
     * @param $name
     * @return string[]
     */
    public function getValidatedParamValues($name)
    {
        $values = [];

        $param = Craft::$app->getRequest()->getParam($name, []);

        foreach ($param as $name => $value) {
            $values[$name] = self::validateData($value);
        }

        return $values;
    }

    /**
     * Validates if the given data is tampered and throws an exception.
     *
     * @param $value
     * @return string
     * @throws Exception
     */
    public function validateData($value)
    {
        $value = Craft::$app->getSecurity()->validateData($value);

        if ($value === false) {
            throw new Exception('Submitted data was tampered.');
        }

        return $value;
    }
}
