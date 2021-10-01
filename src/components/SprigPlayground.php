<?php
namespace putyourlightson\sprig\plugin\components;

use Craft;
use Exception;
use putyourlightson\sprig\base\Component;
use putyourlightson\sprig\plugin\Sprig;
use yii\web\ForbiddenHttpException;

class SprigPlayground extends Component
{
    public $variables = [];

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function render(): string
    {
        $variables = $this->_getVariables();

        $headerVariables = urldecode(http_build_query($variables));

        Craft::$app->getResponse()->getHeaders()->set('Sprig-Playground-Variables', $headerVariables);

        try {
            return Craft::$app->getView()->renderString($this->_getComponent(), $variables);
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
        $headerVariables = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Variables', '');
        $headerVariables = str_replace(' ', '', $headerVariables);

        parse_str($headerVariables, $variables);

        return array_merge(
            $variables,
            $this->variables
        );
    }

    private function _getErrorMessage(string $error): string
    {
        $error = preg_replace('/in "__string_template__(.*?)"/', '', $error);

        return '<h2 class="error">'.$error.'</h2>';
    }
}
