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
            'css/codemirror.css',
            'css/playground.css',
        ];

        $this->js = [
            'js/codemirror.js',
            'js/twig.js',
            'js/playground.js',
        ];

        parent::init();
    }
}
