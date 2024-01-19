<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;

return static function (RectorConfig $rectorConfig): void {
    // bootstrap files
    $rectorConfig->bootstrapFiles([__DIR__.'/vendor/autoload.php']);

    // paths
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/public',
    ]);

    // rules to skip
    $rectorConfig->skip([
        AddSeeTestAnnotationRector::class,
        DisallowedEmptyRuleFixerRector::class,
        PreferPHPUnitThisCallRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ]);

    // rules to apply
    $rectorConfig->sets([
        // global
        SetList::PHP_83,
        SetList::CODE_QUALITY,
        // Doctrine
        DoctrineSetList::DOCTRINE_DBAL_30,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        // Symfony
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        // twig
        TwigSetList::TWIG_240,
        TwigSetList::TWIG_UNDERSCORE_TO_NAMESPACE,
    ]);
};
