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
        $component = urldecode(Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component', ''));

        $variables = [];
        $headerVariables = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Variables', '');
        $headerVariables = str_replace(' ', '', $headerVariables);

        foreach (explode(',', $headerVariables) as $variablePair) {
            $keyValue = explode('=', $variablePair);

            if (count($keyValue) == 2) {
                $variables[$keyValue[0]] = $keyValue[1];
            }
        }

        $variables = array_merge(
            $variables,
            $this->variables
        );

        try {
            return Craft::$app->getView()->renderString($component, $variables);
        }
        catch (Exception $exception) {
            return '<div class="error">'.$exception->getMessage().'</div>';
        }
    }
}
