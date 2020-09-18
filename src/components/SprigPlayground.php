<?php
namespace putyourlightson\sprig\components;

use Craft;
use Exception;
use putyourlightson\sprig\base\Component;

class SprigPlayground extends Component
{
    public $variables = [];

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
        return urldecode(Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component', ''));
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
        $error = preg_replace('/"__string_template__(.*?)"/', 'component', $error);

        return '<div id="sprig-error" class="error">'.$error.'</div>';
    }
}
