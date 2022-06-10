<?php

declare(strict_types=1);

use craft\ecs\SetList;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function(ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();
    $ecsConfig->paths([
        __DIR__ . '/src',
        __FILE__,
    ]);

    $ecsConfig->sets([SetList::CRAFT_CMS_4]);

    // Sets the control structure continuation keyword to be on the next line.
    $ecsConfig->ruleWithConfiguration(ControlStructureContinuationPositionFixer::class, [
        'position' => ControlStructureContinuationPositionFixer::NEXT_LINE,
    ]);
};
