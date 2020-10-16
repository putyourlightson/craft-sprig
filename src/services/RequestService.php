<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\services;

use Craft;
use craft\base\Component;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 * @property-read array $variables
 */
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
                if (strpos($name, $prefix) === 0) {
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
     * @param string $name
     * @return string|false|null
     */
    public function getValidatedParam(string $name)
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
     * @param string $name
     * @return string[]
     */
    public function getValidatedParamValues(string $name)
    {
        $values = [];

        $param = Craft::$app->getRequest()->getParam($name, []);

        foreach ($param as $name => $value) {
            $values[$name] = self::validateData($value);
        }

        return $values;
    }

    /**
     * Validates if the given data is tampered with and throws an exception if it is.
     *
     * @param mixed $value
     * @return string
     * @throws Exception
     */
    public function validateData($value)
    {
        $value = Craft::$app->getSecurity()->validateData($value);

        if ($value === false) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        return $value;
    }
}
