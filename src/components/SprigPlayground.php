<?php

namespace putyourlightson\sprig\plugin\components;

use Craft;
use craft\web\View;
use Exception;
use putyourlightson\sprig\base\Component;
use putyourlightson\sprig\plugin\Sprig;
use yii\web\ForbiddenHttpException;

class SprigPlayground extends Component
{
    /**
     * @var array
     */
    public array $variables = [];

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Validate that user has permission to access the plugin in the CP
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-' . Sprig::$plugin->id)) {
            throw new ForbiddenHttpException('Access denied.');
        }
    }

    /**
     * @inheritdoc
     */
    public function render(): string
    {
        $variables = $this->getVariables();

        $headerVariables = urldecode(http_build_query($variables));

        Craft::$app->getResponse()->getHeaders()->set('Sprig-Playground-Variables', $headerVariables);

        try {
            return Craft::$app->getView()->renderString($this->getComponent(), $variables, View::TEMPLATE_MODE_SITE, true);
        } catch (Exception $exception) {
            return $this->getErrorMessage($exception->getMessage());
        }
    }

    private function getComponent(): string
    {
        $component = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component', '');
        $uriEncoded = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Component-URI-AutoEncoded', '');

        if ($uriEncoded == 'true') {
            $component = urldecode($component);
        }

        return $component;
    }

    private function getVariables(): array
    {
        $headerVariables = Craft::$app->getRequest()->getHeaders()->get('Sprig-Playground-Variables', '');
        $headerVariables = str_replace(' ', '', $headerVariables);

        parse_str($headerVariables, $variables);

        return array_merge(
            $variables,
            $this->variables
        );
    }

    private function getErrorMessage(string $error): string
    {
        $error = preg_replace('/in "__string_template__(.*?)"/', '', $error);

        return '<h2 class="error">' . $error . '</h2>';
    }
}
