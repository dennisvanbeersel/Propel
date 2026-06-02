<?php

// Schemas declaring `<behavior name="nested_set"/>` cannot be Rector-rewritten
// reliably (XML, plus tree-traversal queries are domain-specific). The
// NestedSetBehaviorWarningRector rule emits an inline comment on PHP files
// that import the (now deleted) classes so consumers know to migrate.

use Propel\Generator\Behavior\NestedSet\NestedSetBehavior;

class TaxonomyEntity
{
    public function importNested(): void
    {
        $behavior = new NestedSetBehavior();
        $behavior->getName();
    }
}
