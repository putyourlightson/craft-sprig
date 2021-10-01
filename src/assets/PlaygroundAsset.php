<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PlaygroundAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@putyourlightson/sprig/plugin/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/playground.css',
        ];

        $this->js = [
            'lib/js-beautify/beautify-html.js',
            'lib/monaco-editor/min/vs/loader.js',
            'js/playground.js',
        ];

        parent::init();
    }
}
