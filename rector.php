<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector;
use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\Doctrine\Rector\Property\DoctrineTargetEntityStringToClassConstantRector;
use Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromDoctrineCollectionRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php81\Rector\Class_\ConstantListClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Strict\Rector\ClassMethod\AddConstructorParentCallRector;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\CommandPropertyToAttributeRector;
use Rector\Symfony\Rector\ClassMethod\CommandConstantReturnCodeRector;
use Rector\Symfony\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Rector\MethodCall\GetHelperControllerToServiceRector;
use Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
use Rector\Symfony\Rector\MethodCall\SimplifyFormRenderingRector;
use Rector\Symfony\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    if(str_contains(strtolower(php_uname('s')), 'window')) {
        $rectorConfig->disableParallel();
    }

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_81
    //    ]);

    $rectorConfig->sets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        //NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SensiolabsSetList::FRAMEWORK_EXTRA_61,
        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_61,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION
    ]);

    $rectorConfig->rules([
        NewlineAfterStatementRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#newlineafterstatementrector
        NewlineBeforeNewAssignSetRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#newlinebeforenewassignsetrector
        SymplifyQuoteEscapeRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#symplifyquoteescaperector
        VarConstantCommentRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#varconstantcommentrector

        // Code Quality
        ArrayMergeOfNonArraysToSimpleArrayRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#arraymergeofnonarraystosimplearrayrector

        // Naming
        RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#renameforeachvaluevariabletomatchmethodcallreturntyperector

        // PHP 8.1
        AddConstructorParentCallRector::class,
        ConstantListClassToEnumRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#constantlistclasstoenumrector
        ReadOnlyPropertyRector::class,
        SpatieEnumClassToEnumRector::class, // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md#spatieenumclasstoenumrector

        // Doctrine
        CorrectDefaultTypesOnEntityPropertyRector::class,
        DoctrineTargetEntityStringToClassConstantRector::class,
        EntityAliasToClassConstantReferenceRector::class,
        ImproveDoctrineCollectionDocTypeInEntityRector::class,
        InitializeDefaultEntityCollectionRector::class,
        MakeEntityDateTimePropertyDateTimeInterfaceRector::class,
        MakeEntitySetterNullabilityInSyncWithPropertyRector::class,
        ManagerRegistryGetManagerToEntityManagerRector::class,
        TypedPropertyFromDoctrineCollectionRector::class,
        TypedPropertyFromToManyRelationTypeRector::class,
        TypedPropertyFromToOneRelationTypeRector::class,

        // Symfony
        ContainerGetToConstructorInjectionRector::class,
        CommandConstantReturnCodeRector::class,
        CommandPropertyToAttributeRector::class,
        ChangeStringCollectionOptionToConstantRector::class,
        GetHelperControllerToServiceRector::class,
        GetRequestRector::class,
        KernelTestCaseContainerPropertyDeprecationRector::class,
        LiteralGetToRequestClassConstantRector::class,
        ParamConverterAttributeToMapEntityAttributeRector::class,
        RedirectToRouteRector::class,
        ReplaceSensioRouteAnnotationWithSymfonyRector::class,
        ResponseReturnTypeControllerActionRector::class,
        ResponseStatusCodeRector::class,
        SimplifyFormRenderingRector::class,
    ]);
};
