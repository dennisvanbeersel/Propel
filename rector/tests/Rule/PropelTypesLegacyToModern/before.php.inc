<?php

// XML schemas can't be Rector-rewritten reliably — Rector handles PHP AST, not XML.
// The PropelTypesLegacyToModernRector rule emits informational comments on PHP
// callsites that reference removed type constants by name, so consumers know to
// migrate the corresponding `<column type="...">` declarations in their schemas.

use Propel\Generator\Model\PropelTypes;

$type = PropelTypes::BU_DATE;
$columnType = PropelTypes::BOOLEAN_EMU;
$arrayType = PropelTypes::PHP_ARRAY;
