<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Propel\Rector\Rule\ConnectionManagerMasterSlaveToPrimaryReplicaRector;
use Propel\Rector\Rule\CriteriaCustomToCustomConditionRector;
use Propel\Rector\Rule\CriteriaJavaHashtableMethodsRector;
use Propel\Rector\Rule\DebugPdoToConnectionWrapperRector;
use Propel\Rector\Rule\MasterToPrimaryConfigRector;
use Propel\Rector\Rule\NestedSetBehaviorWarningRector;
use Propel\Rector\Rule\PropelInitConfigurationRector;
use Propel\Rector\Rule\PropelPdoToConnectionWrapperRector;
use Propel\Rector\Rule\PropelTypesLegacyToModernRector;
use Propel\Rector\Rule\PropertyWriteToSetterRector;
use Propel\Rector\Rule\SlavesToReplicasConfigRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        // Connection migration
        DebugPdoToConnectionWrapperRector::class,
        PropelPdoToConnectionWrapperRector::class,
        ConnectionManagerMasterSlaveToPrimaryReplicaRector::class,

        // Config migration
        SlavesToReplicasConfigRector::class,
        MasterToPrimaryConfigRector::class,

        // Criteria migration
        CriteriaJavaHashtableMethodsRector::class,
        CriteriaCustomToCustomConditionRector::class,

        // Schema/types/bootstrap
        PropelTypesLegacyToModernRector::class,
        PropelInitConfigurationRector::class,
        NestedSetBehaviorWarningRector::class,

        // Asymmetric-visibility BC bridge
        PropertyWriteToSetterRector::class,
    ]);
