<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\twigextensions;

use putyourlightson\sprig\Sprig;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class SprigTwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('sprig', [Sprig::$plugin->componentsService, 'create']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getGlobals(): array
    {
        return [
            'sprig' => Sprig::$sprigVariable,
        ];
    }
}
