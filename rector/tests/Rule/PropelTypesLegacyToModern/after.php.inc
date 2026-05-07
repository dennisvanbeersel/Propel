<?php

// XML schemas can't be Rector-rewritten reliably — Rector handles PHP AST, not XML.
// The PropelTypesLegacyToModernRector rule emits informational comments on PHP
// callsites that reference removed type constants by name, so consumers know to
// migrate the corresponding `<column type="...">` declarations in their schemas.

use Propel\Generator\Model\PropelTypes;

// PROPEL 4.0: PropelTypes::BU_DATE removed — migrate <column type="BU_DATE"> to <column type="TIMESTAMP">
$type = PropelTypes::BU_DATE;
// PROPEL 4.0: PropelTypes::BOOLEAN_EMU removed — migrate <column type="BOOLEAN_EMU"> to <column type="BOOLEAN">
$columnType = PropelTypes::BOOLEAN_EMU;
// PROPEL 4.0: PropelTypes::PHP_ARRAY removed — migrate <column type="ARRAY"> to <column type="JSON">
$arrayType = PropelTypes::PHP_ARRAY;
