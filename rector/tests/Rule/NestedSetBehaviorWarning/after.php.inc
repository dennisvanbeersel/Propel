<?php

// Schemas declaring `<behavior name="nested_set"/>` cannot be Rector-rewritten
// reliably (XML, plus tree-traversal queries are domain-specific). The
// NestedSetBehaviorWarningRector rule emits an inline comment on PHP files
// that import the (now deleted) classes so consumers know to migrate.

// PROPEL 4.0: NestedSetBehavior was removed. Migrate <behavior name="nested_set"/>
// schemas to a recursive-CTE / closure-table pattern. See
// docs/MIGRATION-FROM-PRE-AI.md and docs/PHASE-D-SUMMARY.md for the recipe.
use Propel\Generator\Behavior\NestedSet\NestedSetBehavior;

class TaxonomyEntity
{
    public function importNested(): void
    {
        // PROPEL 4.0: NestedSetBehavior was removed. Manual migration required.
        $behavior = new NestedSetBehavior();
        $behavior->getName();
    }
}
