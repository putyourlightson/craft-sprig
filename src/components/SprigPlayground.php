<?php
namespace putyourlightson\sprig\components;

use Craft;
use Exception;
use putyourlightson\sprig\base\Component;
use putyourlightson\sprig\Sprig;
use yii\web\ForbiddenHttpException;

class SprigPlayground extends Component
{
    public $variables = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        // Validate that user has permission to access the plugin in the CP
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-'.Sprig::$plugin->id)) {
            throw new ForbiddenHttpException('Access denied.');
        }
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        try {
            return Craft::$app->getView()->renderString($this->_getComponent(), $this->_getVariables());
        }
        catch (Exception $exception) {
            return $this->_getErrorMessage($exception->getMessage());
        }
    }

    private function _getComponent(): string
    {
        $component = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component', '');
        $uriEncoded = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component-URI-AutoEncoded', '');

        if ($uriEncoded == 'true') {
            $component = urldecode($component);
        }

        return $component;
    }

    private function _getVariables(): array
    {
        $variables = [];

        $headerVariables = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Variables', '');
        $headerVariables = str_replace(' ', '', $headerVariables);

        foreach (explode(',', $headerVariables) as $variablePair) {
            $keyValue = explode('=', $variablePair);

            if (count($keyValue) == 2) {
                $variables[$keyValue[0]] = $keyValue[1];
            }
        }

        return array_merge(
            $variables,
            $this->variables
        );
    }

    private function _getErrorMessage(string $error)
    {
        $error = preg_replace('/in "__string_template__(.*?)"/', '', $error);

        return '<h2 class="error">'.$error.'</h2>';
    }
}
