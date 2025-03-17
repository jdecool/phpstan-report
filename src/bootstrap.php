<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

if (!function_exists('registerClassIfNotExists')) {
    function registerClassIfNotExists(string $bridge, string $class): void
    {
        if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
            class_alias($bridge, $class);
        }
    }
}

registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Analyzer\Error::class, PHPStan\Analyser\Error::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode::class, PHPStan\Dependency\ExportedNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\RootExportedNode::class, PHPStan\Dependency\RootExportedNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedAttributeNode::class, PHPStan\Dependency\ExportedNode\ExportedAttributeNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::class, PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::class, PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedClassNode::class, PHPStan\Dependency\ExportedNode\ExportedClassNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::class, PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedEnumNode::class, PHPStan\Dependency\ExportedNode\ExportedEnumNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedFunctionNode::class, PHPStan\Dependency\ExportedNode\ExportedFunctionNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::class, PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedMethodNode::class, PHPStan\Dependency\ExportedNode\ExportedMethodNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedParameterNode::class, PHPStan\Dependency\ExportedNode\ExportedParameterNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::class, PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::class, PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedTraitNode::class, PHPStan\Dependency\ExportedNode\ExportedTraitNode::class);
registerClassIfNotExists(JDecool\PHPStanReport\Bridge\PHPStan\Dependency\ExportedNode\ExportedTraitUseAdaptation::class, PHPStan\Dependency\ExportedNode\ExportedTraitUseAdaptation::class);
