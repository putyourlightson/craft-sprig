<?php
namespace putyourlightson\sprig\components;

use Craft;
use putyourlightson\sprig\base\Component;

class SprigPlayground extends Component
{
    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $request = Craft::$app->getRequest();
        $component = $request->getParam('component', '');
        $variables = $request->getParam('variables', []);

        return Craft::$app->getView()->renderString($component, $variables);
    }
}
