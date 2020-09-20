<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PlaygroundAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@putyourlightson/sprig/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'lib/codemirror/codemirror.css',
            'css/playground.css',
        ];

        $this->js = [
            'lib/codemirror/codemirror.js',
            'lib/codemirror/twig.js',
            'lib/js-beautify/beautify-html.js',
            'js/playground.js',
        ];

        parent::init();
    }
}
