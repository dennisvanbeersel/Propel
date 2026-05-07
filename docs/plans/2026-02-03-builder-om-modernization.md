# Builder/Om Code Generation Modernization Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix bugs, security issues, and modernize the generated PHP output from Propel2's Active Record code generation system (Builder/Om layer).

**Architecture:** The Builder/Om system generates Active Record model classes from XML schemas via string concatenation in ~14,000 lines of builder code across ObjectBuilder.php (3,664 lines), 4 builder traits (~3,766 lines), QueryBuilder.php (2,255 lines), TableMapBuilder.php (1,601 lines), and supporting builders. Changes target both the builder code itself and the PHP code it generates.

**Tech Stack:** PHP 8.3+, PHPUnit, PHPStan level 7, Spryker coding standard

**Relation to existing plan:** This is a companion to `docs/MODERNIZATION_PLAN.md` which covers broad infrastructure. This plan focuses specifically on Builder/Om bug fixes, security hardening, and generated code quality.

---

## Phase 1: Bug Fixes & Security (No API Changes)

**Goal:** Fix correctness bugs and security vulnerabilities without changing the generated API surface.

**Verification after each task:** `composer test:agnostic`

---

### Task 1.1: Fix `strftime()` deprecation in query class header template

**Files:**
- Modify: `templates/Builder/Om/baseQueryClassHeader.php:31`

**Step 1: Fix the deprecated call**

Replace:
```php
 * <?= strftime('%c') ?>
```
With:
```php
 * <?= date('c') ?>
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add templates/Builder/Om/baseQueryClassHeader.php
git commit -m "fix: replace deprecated strftime() with date() in query header template"
```

---

### Task 1.2: Fix `!= 0` loose comparison bug in FK lazy loading

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:206-207`

**Step 1: Fix the numeric FK conditional**

In `addFKAccessorBody()`, find the section that builds the lazy-loading conditional. Change:
```php
if ($cptype === 'int' || $cptype === 'float' || $cptype === 'double') {
    $conditional .= $and . '$this->' . $clo . ' != 0';
```
To:
```php
if ($cptype === 'int' || $cptype === 'float' || $cptype === 'double') {
    $conditional .= $and . '$this->' . $clo . ' !== null';
```

This fixes the bug where FK value `0` would prevent lazy loading, and aligns numeric FKs with the same `!== null` check used for string FKs.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php
git commit -m "fix: use !== null instead of != 0 for FK lazy loading check"
```

---

### Task 1.3: Fix null dereference in 1:1 FK bidirectional binding

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:249-252`

**Step 1: Add null check before bidirectional binding**

In `addFKAccessorBody()`, find the `isLocalPrimaryKey()` section. Wrap the bidirectional binding in a null check. Change the generated code from:
```php
$this->{$varName}->set...($this);
```
To:
```php
if ($this->{$varName} !== null) {
    $this->{$varName}->set...($this);
}
```

The builder code should generate the null-safe version.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php
git commit -m "fix: prevent null dereference in 1:1 FK bidirectional binding"
```

---

### Task 1.4: Fix `stream_get_contents()` position bug in Object mutator

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:385-394`

**Step 1: Add rewind before stream comparison**

In `addObjectMutator()`, the generated code must rewind the stream before calling `stream_get_contents()`. Change:
```php
if (null === \$this->$clo || stream_get_contents(\$this->$clo) !== serialize(\$v)) {
```
To:
```php
if (null === \$this->$clo || (rewind(\$this->$clo) !== false && stream_get_contents(\$this->$clo) !== serialize(\$v))) {
```

Or alternatively, add a rewind call on a separate line before the comparison.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "fix: rewind stream before dirty check in Object column mutator"
```

---

### Task 1.5: Fix dead duplicate branch in `addFilterByArrayCol()`

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php:1206-1210`

**Step 1: Fix the else branch**

In `addFilterByArrayCol()`, the `CONTAINS_NONE` handler has identical if/else branches. The else branch should use `$this->add()` not `$this->addAnd()`. Change:
```php
if ($this->containsKey($key)) {
    $this->addAnd($key, $$variableName, $comparison);
} else {
    $this->addAnd($key, $$variableName, $comparison);
}
```
To:
```php
if ($this->containsKey($key)) {
    $this->addAnd($key, $$variableName, $comparison);
} else {
    $this->add($key, $$variableName, $comparison);
}
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "fix: use add() instead of addAnd() for first filter criterion in array column"
```

---

### Task 1.6: Fix missing UuidConverter import in generated Query classes

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php:1155-1165`

**Step 1: Add declareClasses call for UuidConverter**

In `addFilterByCol()`, before or inside the UUID binary type branch, add:
```php
$this->declareClasses(
    'Propel\\Runtime\\Util\\UuidConverter',
);
```

Model this after how `SetColumnConverter` is handled at lines 1096-1099.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "fix: add missing UuidConverter import for UUID binary filter methods"
```

---

### Task 1.7: Fix `null == $key` loose comparison in TableMapBuilder

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/TableMapBuilder.php:1175`

**Step 1: Use strict comparison**

Change:
```php
} elseif (null == $key) {
```
To:
```php
} elseif (null === $key) {
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/TableMapBuilder.php
git commit -m "fix: use strict null comparison in TableMap populateObject"
```

---

### Task 1.8: Fix `addslashes(null)` in QueryBuilder

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php:250`

**Step 1: Handle null case for entity not found exception class**

Change:
```php
$script .= "    protected ?string \$entityNotFoundExceptionClass = '" . addslashes($this->getEntityNotFoundExceptionClass()) . "';\n";
```
To:
```php
$exceptionClass = $this->getEntityNotFoundExceptionClass();
$script .= "    protected ?string \$entityNotFoundExceptionClass = " . ($exceptionClass !== null ? "'" . addslashes($exceptionClass) . "'" : 'null') . ";\n";
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "fix: handle null entity-not-found exception class without addslashes deprecation"
```

---

### Task 1.9: Fix wrong error message in QueryInheritanceBuilder

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryInheritanceBuilder.php:88-89`
- Modify: `src/Propel/Generator/Builder/Om/ExtensionQueryInheritanceBuilder.php:73`

**Step 1: Fix class name in exception messages**

In `QueryInheritanceBuilder.php`, change:
```php
throw new BuildException('The MultiExtendObjectBuilder needs to be told which child class to build...');
```
To:
```php
throw new BuildException('The QueryInheritanceBuilder needs to be told which child class to build...');
```

In `ExtensionQueryInheritanceBuilder.php`, change similarly to reference `ExtensionQueryInheritanceBuilder`.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryInheritanceBuilder.php src/Propel/Generator/Builder/Om/ExtensionQueryInheritanceBuilder.php
git commit -m "fix: correct class names in inheritance builder error messages"
```

---

### Task 1.10: Add `JSON_THROW_ON_ERROR` to generated JSON operations

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php:341`
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:417-419`

**Step 1: Fix JSON accessor**

In `addJsonAccessorBody()`, change:
```php
return json_decode($this->$clo, $asArray);
```
To:
```php
return json_decode($this->$clo, $asArray, 512, JSON_THROW_ON_ERROR);
```

**Step 2: Fix JSON mutator**

In `addJsonMutator()`, change:
```php
$v = json_decode($v);
```
To:
```php
$v = json_decode($v, false, 512, JSON_THROW_ON_ERROR);
```

And:
```php
$encodedValue = json_encode($v);
```
To:
```php
$encodedValue = json_encode($v, JSON_THROW_ON_ERROR);
```

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "fix: add JSON_THROW_ON_ERROR to all generated json operations"
```

---

### Task 1.11: Add `allowed_classes` to generated `unserialize()` calls

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php:258`

**Step 1: Restrict unserialize**

Change:
```php
$this->$cloUnserialized = unserialize($serialisedString);
```
To:
```php
$this->$cloUnserialized = unserialize($serialisedString, ['allowed_classes' => true]);
```

This is the minimum safe default. Projects requiring stricter controls can override in the stub class.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
git commit -m "security: add allowed_classes option to generated unserialize() calls"
```

---

### Task 1.12: Fix visibility bug in addArrayElement/removeArrayElement

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:466,514`

**Step 1: Use mutator visibility instead of accessor visibility**

In both `addAddArrayElement()` and `addRemoveArrayElement()`, change:
```php
$visibility = $col->getAccessorVisibility();
```
To:
```php
$visibility = $col->getMutatorVisibility();
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "fix: use mutator visibility for addElement/removeElement methods"
```

---

## Phase 2: Generated Code Modernization (PHP 8.3+)

**Goal:** Modernize the PHP code that gets generated by the builders. Conservative API changes — add types, don't remove methods.

**Verification after each task:** `composer test:agnostic`

---

### Task 2.1: Add `declare(strict_types=1)` to generated output

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php:92-94`

**Step 1: Add strict_types to generated PHP opening**

Change:
```php
$script = "<?php

" . $script;
```
To:
```php
$script = "<?php

declare(strict_types=1);

" . $script;
```

**Step 2: Regenerate test fixtures and run tests**

Run: `composer test:agnostic`
Expected: This may surface type errors in generated code. Fix any that appear.

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/AbstractOMBuilder.php
git commit -m "feat: add declare(strict_types=1) to all generated PHP files"
```

---

### Task 2.2: Fix legacy type aliases in PropelTypes

**Files:**
- Modify: `src/Propel/Generator/Model/PropelTypes.php:67-69,80-81`

**Step 1: Update type constants**

Change:
```php
public const REAL_NATIVE_TYPE = 'double';
public const FLOAT_NATIVE_TYPE = 'double';
public const DOUBLE_NATIVE_TYPE = 'double';
// ...
public const BOOLEAN_NATIVE_TYPE = 'boolean';
```
To:
```php
public const REAL_NATIVE_TYPE = 'float';
public const FLOAT_NATIVE_TYPE = 'float';
public const DOUBLE_NATIVE_TYPE = 'float';
// ...
public const BOOLEAN_NATIVE_TYPE = 'bool';
```

**Step 2: Fix the explicit `(boolean)` cast in boolean mutator**

In `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:699`, change:
```php
\$v = (boolean) \$v;
```
To:
```php
\$v = (bool) \$v;
```

**Step 3: Replace `array()` with `[]` in generated code**

In `ColumnAccessorBuilderTrait.php:382`:
```php
: array();  ->  : [];
```

In `ColumnMutatorBuilderTrait.php:697`:
```php
array('false', 'off', '-', 'no', 'n', '0', '')  ->  ['false', 'off', '-', 'no', 'n', '0', '']
```

**Step 4: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 5: Commit**

```bash
git add src/Propel/Generator/Model/PropelTypes.php src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
git commit -m "modernize: use canonical PHP type names (bool, float) and short array syntax"
```

---

### Task 2.3: Add typed properties to generated column attributes

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ObjectBuilder.php:553-558`

**Step 1: Update `addColumnAttributeDeclaration()` to emit typed properties**

Change the method to use the column's PHP type and nullability:
```php
protected function addColumnAttributeDeclaration(string &$script, Column $column): void
{
    $clo = $column->getLowercasedName();
    $phpType = $column->getPhpType();

    // Map PHP types to property type declarations
    $typeDeclaration = match ($phpType) {
        'int', 'integer' => 'int',
        'float', 'double' => 'float',
        'bool', 'boolean' => 'bool',
        'string' => 'string',
        default => null,
    };

    if ($typeDeclaration !== null) {
        $script .= "\n    protected ?" . $typeDeclaration . " \$" . $clo . " = null;\n";
    } else {
        // Complex types (DateTime, objects) - keep untyped for now
        $script .= "\n    protected \$" . $clo . ";\n";
    }
}
```

Note: All properties default to `null` since they start unhydrated. NOT NULL columns are enforced at the database level, not the PHP property level.

**Step 2: Update lazy-load flag properties**

In the same file around line 594, change:
```php
protected $column_isLoaded = false;
```
To:
```php
protected bool $column_isLoaded = false;
```

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: May need fixes where generated code assigns incompatible types.

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ObjectBuilder.php
git commit -m "feat: add native PHP type declarations to generated column properties"
```

---

### Task 2.4: Add typed properties to generated FK and referrer attributes

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:69-73`
- Modify: `src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php:130-150`

**Step 1: Type FK object properties**

In `addFKAttributes()`, change:
```php
protected $" . $varName . ";
```
To:
```php
protected ?" . $className . " $" . $varName . " = null;
```

**Step 2: Type referrer collection partial-load flags**

In `addRefFKAttributes()`, change:
```php
protected $" . $collName . "Partial;
```
To:
```php
protected bool $" . $collName . "Partial = false;
```

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php
git commit -m "feat: add type declarations to generated FK and referrer properties"
```

---

### Task 2.5: Add typed properties to generated base object attributes

**Files:**
- Modify: `templates/Builder/Om/baseObjectAttributes.php`

**Step 1: Add types to base attributes**

Change:
```php
protected $new = true;
protected $deleted = false;
protected $modifiedColumns = [];
protected $virtualColumns = [];
```
To:
```php
protected bool $new = true;
protected bool $deleted = false;
protected array $modifiedColumns = [];
protected array $virtualColumns = [];
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add templates/Builder/Om/baseObjectAttributes.php
git commit -m "feat: add type declarations to generated base object attributes"
```

---

### Task 2.6: Add return types to generated setter methods

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:65-75`

**Step 1: Update `addMutatorClose()` to include `static` return type**

The `addMutatorClose()` method generates the closing of every setter. Find where it emits the method signature opening (in `addMutatorOpenOpen()`) and add the return type. Change the generated signature from:
```php
public function setFoo($v)
```
To:
```php
public function setFoo($v): static
```

This is done by modifying `addMutatorOpenOpen()` to append `: static` to the function signature.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "feat: add static return type to all generated setter methods"
```

---

### Task 2.7: Add return types to generated FK getter/setter methods

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:108,236`

**Step 1: Add return type to FK setter**

Change generated signature from:
```php
public function setAuthor(?Author $v = null)
```
To:
```php
public function setAuthor(?Author $v = null): static
```

**Step 2: Add return type to FK getter**

Change generated signature from:
```php
public function getAuthor(?ConnectionInterface $con = null)
```
To:
```php
public function getAuthor(?ConnectionInterface $con = null): ?Author
```

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php
git commit -m "feat: add return types to generated FK getter and setter methods"
```

---

### Task 2.8: Add return types to base object methods template

**Files:**
- Modify: `templates/Builder/Om/baseObjectMethods.php`

**Step 1: Add return types to template methods**

Update all methods in the template with proper return types:
- `equals($obj)` -> `equals(mixed $obj): bool`
- `getVirtualColumn(string $name)` -> `: mixed`
- `setVirtualColumn(string $name, $value)` -> `: static`
- `exportTo($parser, ...)` -> `exportTo(AbstractParser|string $parser, ...): string`
- `log(string $msg, ...)` -> `: void`

**Step 2: Replace `__sleep()` with `__serialize()` / `__unserialize()`**

Replace the Reflection-based `__sleep()` with a generated property list, or add `__serialize()` / `__unserialize()` as the modern alternative.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add templates/Builder/Om/baseObjectMethods.php
git commit -m "feat: add return types and modernize serialization in base object methods"
```

---

### Task 2.9: Fix `strftime()` reference in temporal accessor docblock

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php:77`

**Step 1: Remove strftime reference**

Change:
```php
* @param string|null $format The date/time format string (either date()-style or strftime()-style).
```
To:
```php
* @param string|null $format The date/time format string (date()-style).
```

**Step 2: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
git commit -m "docs: remove strftime() reference from temporal accessor docblock"
```

---

### Task 2.10: Use `match` expressions in builder code

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php:112-123`
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php:323-334`

**Step 1: Convert switch to match in temporal accessor**

Change the `switch` statement in `getTemporalFormatterOption()` to a `match` expression.

**Step 2: Convert switch to match in temporal mutator**

Change the `switch` statement for date format selection. Also replace raw string `'DATE'` and `'TIME'` with `PropelTypes::DATE` and `PropelTypes::TIME` constants.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "modernize: use match expressions and PropelTypes constants in temporal builders"
```

---

## Phase 3: ENUM Backed Enums

**Goal:** Generate PHP 8.1 backed enum classes for schema ENUM columns.

**Verification:** `composer test:agnostic`

---

### Task 3.1: Create EnumBuilder class

**Files:**
- Create: `src/Propel/Generator/Builder/Om/EnumBuilder.php`

**Step 1: Design the EnumBuilder**

Create a new builder that generates a backed string enum class for each ENUM column. For a column named `status` with values `['draft', 'published', 'archived']`, it should generate:

```php
<?php

declare(strict_types=1);

namespace App\Model;

enum BookStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

The enum class name should be `{TablePhpName}{ColumnPhpName}` (e.g., `BookStatus`).

**Step 2: Write tests for enum generation**

Test that the builder generates valid PHP enum syntax for various value sets.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/EnumBuilder.php tests/...
git commit -m "feat: add EnumBuilder for generating PHP backed enum classes"
```

---

### Task 3.2: Register EnumBuilder in the build pipeline

**Files:**
- Modify: `src/Propel/Generator/Manager/ModelManager.php`
- Modify: `src/Propel/Generator/Builder/DataModelBuilder.php`

**Step 1: Add EnumBuilder to the build process**

For each table, iterate over ENUM columns and invoke EnumBuilder to generate an enum class per column. Register the builder similarly to how ObjectBuilder/QueryBuilder are registered.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Manager/ModelManager.php src/Propel/Generator/Builder/DataModelBuilder.php
git commit -m "feat: register EnumBuilder in model build pipeline"
```

---

### Task 3.3: Update ENUM accessor to return backed enum

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php` (addEnumAccessor area)

**Step 1: Update generated accessor**

Change the enum getter to return the backed enum type instead of string:
```php
// Before (generated)
return $valueSet[$this->status];

// After (generated)
return BookStatus::from($valueSet[$this->status]);
```

Or, if we store the enum value directly (not the integer index), simply:
```php
return $this->status !== null ? BookStatus::from($this->status) : null;
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
git commit -m "feat: generate enum-typed accessors for ENUM columns"
```

---

### Task 3.4: Update ENUM mutator to accept backed enum

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php` (addEnumMutator area)

**Step 1: Update generated mutator**

Change the enum setter to accept the backed enum type:
```php
// Before (generated)
public function setStatus($v)

// After (generated)
public function setStatus(?BookStatus $v): static
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
git commit -m "feat: generate enum-typed mutators for ENUM columns"
```

---

### Task 3.5: Update hydrate and persistence for enum columns

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ObjectBuilder.php` (hydrate, doInsert, doUpdate areas)
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php` (filterByEnum area)

**Step 1: Update hydrate to handle enum conversion**

When hydrating from a database result, convert the stored string value to the enum.

**Step 2: Update persistence to store enum value**

When inserting/updating, use `$enum->value` to get the string for storage.

**Step 3: Update filterByEnum in QueryBuilder**

Accept the enum type in `filterBy*()` methods for enum columns.

**Step 4: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 5: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ObjectBuilder.php src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "feat: integrate backed enums into hydration, persistence, and query filtering"
```

---

## Phase 4: Builder Infrastructure Cleanup

**Goal:** Clean up dead code, fix DRY violations, and improve builder architecture. Internal changes only.

**Verification:** `composer test:agnostic && composer stan`

---

### Task 4.1: Remove dead code

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ObjectBuilder.php` (remove deprecated methods)
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:565` (remove commented-out code)
- Modify: `src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php:306-312,563-568,595-600` (remove dead branches)

**Step 1: Remove deprecated `addGetPrimaryKeyNoPK()`**

Remove the method marked `@deprecated` with "Not needed anymore."

**Step 2: Remove `addDoInsertBodyStandard()` and `addDoInsertBodyWithIdMethod()`**

These are never called — `addDoInsert()` calls `addDoInsertBodyRaw()` directly.

**Step 3: Remove commented-out code**

Remove `$this->addCrossFKRemoves($script, $crossFKs);` commented-out line.

**Step 4: Fix dead `getChildrenColumn()` branches**

In `addRefFKAdd`, `addRefFKRemove`, and `addRefFKDoAdd`, the `if ($tblFK->getChildrenColumn())` blocks reassign `$className` to the same value. Remove these dead branches.

**Step 5: Remove dead Trac URL comments**

Replace references to `propel.phpdb.org/trac/ticket/` with inline explanations of the actual fix/reason.

**Step 6: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 7: Commit**

```bash
git add -A
git commit -m "cleanup: remove dead code, deprecated methods, and dead Trac URL references"
```

---

### Task 4.2: Remove Java-era legacy from ClassTools and path handling

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ClassTools.php`
- Modify: `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php:226-244`

**Step 1: Remove dot-path notation support from ClassTools::classname()**

Remove the `strrpos($qualifiedName, '.')` branch. Only support PHP namespace backslash notation.

**Step 2: Simplify `getPackagePath()`**

Remove the `...` to `../` conversion hack and the dot-to-slash transformation. Keep only the `/`-based path handling.

**Step 3: Remove trivial wrapper methods from ClassTools**

Remove `getBaseClass()` and `getInterface()` — they are pass-throughs to `Table`. Update any callers to use `$table->getBaseClass()` directly.

**Step 4: Update `getPhpReservedWords()` to include PHP 8.x keywords**

Add `match`, `enum`, `readonly`, `never`, `true`, `false`, `null`. Remove duplicate `extends`.

**Step 5: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 6: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ClassTools.php src/Propel/Generator/Builder/Om/AbstractOMBuilder.php
git commit -m "cleanup: remove Java-era dot-path notation and update reserved words for PHP 8.3+"
```

---

### Task 4.3: Fix `declareClasses()` to use variadic parameter

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php:481-487`

**Step 1: Modernize signature**

Change:
```php
public function declareClasses(): void
{
    $args = func_get_args();
    foreach ($args as $class) {
        $this->declareClass($class);
    }
}
```
To:
```php
public function declareClasses(string ...$classes): void
{
    foreach ($classes as $class) {
        $this->declareClass($class);
    }
}
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/AbstractOMBuilder.php
git commit -m "modernize: use variadic parameter for declareClasses()"
```

---

### Task 4.4: Extract shared phpDoc generation to base class

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/ExtensionObjectBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/ExtensionQueryBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/MultiExtendObjectBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/QueryInheritanceBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/ExtensionQueryInheritanceBuilder.php`

**Step 1: Create shared method in AbstractOMBuilder**

Add `addClassLevelComment(string &$script, string $description): void` to consolidate the duplicated phpDoc comment generation across 5+ builders.

**Step 2: Update all extension/inheritance builders to use it**

Replace the copy-pasted phpDoc blocks with calls to the shared method.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/AbstractOMBuilder.php src/Propel/Generator/Builder/Om/Extension*.php src/Propel/Generator/Builder/Om/MultiExtendObjectBuilder.php src/Propel/Generator/Builder/Om/QueryInheritanceBuilder.php
git commit -m "refactor: extract shared phpDoc generation to AbstractOMBuilder"
```

---

### Task 4.5: Move MySQL-specific date handling to Platform

**Files:**
- Modify: `src/Propel/Generator/Platform/MysqlPlatform.php`
- Modify: `src/Propel/Generator/Platform/PlatformInterface.php`
- Modify: `src/Propel/Generator/Builder/Om/ObjectBuilder.php:179,1251`
- Modify: `src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php:57-66`

**Step 1: Add method to PlatformInterface**

Add `getInvalidDateString(string $columnType): ?string` to the platform interface. Return `'0000-00-00 00:00:00'` (or the appropriate variant) for MySQL, `null` for PostgreSQL.

**Step 2: Update builders to use platform method**

Replace the 3 occurrences of `$this->getPlatform() instanceof MysqlPlatform` with calls to the platform method.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Platform/MysqlPlatform.php src/Propel/Generator/Platform/PlatformInterface.php src/Propel/Generator/Platform/DefaultPlatform.php src/Propel/Generator/Platform/PgsqlPlatform.php src/Propel/Generator/Builder/Om/ObjectBuilder.php src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
git commit -m "refactor: move MySQL-specific date handling from builders to Platform"
```

---

### Task 4.6: Fix `InterfaceBuilder` inheritance and `$tablemapBuilder` casing

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/InterfaceBuilder.php:21`
- Modify: `src/Propel/Generator/Builder/DataModelBuilder.php:82`

**Step 1: Change InterfaceBuilder to extend AbstractOMBuilder**

Change:
```php
class InterfaceBuilder extends AbstractObjectBuilder
```
To:
```php
class InterfaceBuilder extends AbstractOMBuilder
```

Add the behavior modifier methods directly (they are simple overrides).

**Step 2: Fix property casing**

Change:
```php
protected ?TableMapBuilder $tablemapBuilder = null;
```
To:
```php
private ?TableMapBuilder $tableMapBuilder = null;
```

Update all references in the same file.

**Step 3: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/InterfaceBuilder.php src/Propel/Generator/Builder/DataModelBuilder.php
git commit -m "refactor: fix InterfaceBuilder inheritance and tablemapBuilder casing"
```

---

### Task 4.7: Fix loose comparisons throughout builder code

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php:73,80-81`
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php` (multiple `==` comparisons for Criteria constants)

**Step 1: Replace all `==` with `===` in builder string comparisons**

In ReferrerBuilderTrait, change loose comparisons at lines 73 and 80-81 to strict.

In QueryBuilder, change all `$comparison == Criteria::CONTAINS_*` to `===`.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "cleanup: replace loose comparisons with strict comparisons in builder code"
```

---

### Task 4.8: Remove commented-out code from generated output

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php:254-261`

**Step 1: Remove the explanatory comment block from generated FK getter**

The large `/* The following can be used additionally to... */` comment is generated in every model's FK getter. Move this explanation to the builder code as a regular comment and remove it from the generated output.

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php
git commit -m "cleanup: remove explanatory comment block from generated FK getter output"
```

---

### Task 4.9: Fix `create()` return type in QueryBuilder

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php:458-486`

**Step 1: Change return type from `Criteria` to `static`**

Change the generated `create()` factory signature from:
```php
public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
```
To:
```php
public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
```

**Step 2: Run tests**

Run: `composer test:agnostic`
Expected: PASS

**Step 3: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php
git commit -m "fix: change generated Query::create() return type from Criteria to static"
```

---

### Task 4.10: Final verification

**Step 1: Run full test suite**

```bash
composer test:agnostic
composer stan
composer cs-check
```

**Step 2: Regenerate test fixtures and verify output**

Regenerate the bookstore test fixtures and manually inspect a generated model class to verify:
- `declare(strict_types=1)` is present
- Properties have type declarations
- Setters have `static` return type
- `(bool)` and `(float)` used instead of `(boolean)` and `(double)`
- `[]` used instead of `array()`
- JSON operations use `JSON_THROW_ON_ERROR`

**Step 3: Commit any final fixes**

```bash
git add -A
git commit -m "chore: final verification pass for builder modernization"
```

---

## Summary

| Phase | Tasks | Key Changes |
|-------|-------|-------------|
| Phase 1 | 12 tasks | Bug fixes, security (JSON_THROW_ON_ERROR, unserialize safety), FK lazy load fix |
| Phase 2 | 10 tasks | strict_types, typed properties, return types, modern casts, match expressions |
| Phase 3 | 5 tasks | Generate PHP backed enum classes for ENUM columns |
| Phase 4 | 10 tasks | Dead code removal, Java legacy cleanup, DRY fixes, loose comparisons |
| **Total** | **37 tasks** | |

## Files Touched (Primary)

```
src/Propel/Generator/Builder/Om/ObjectBuilder.php
src/Propel/Generator/Builder/Om/AbstractOMBuilder.php
src/Propel/Generator/Builder/Om/AbstractObjectBuilder.php
src/Propel/Generator/Builder/Om/ColumnAccessorBuilderTrait.php
src/Propel/Generator/Builder/Om/ColumnMutatorBuilderTrait.php
src/Propel/Generator/Builder/Om/ForeignKeyBuilderTrait.php
src/Propel/Generator/Builder/Om/ReferrerBuilderTrait.php
src/Propel/Generator/Builder/Om/QueryBuilder.php
src/Propel/Generator/Builder/Om/TableMapBuilder.php
src/Propel/Generator/Builder/Om/ClassTools.php
src/Propel/Generator/Builder/Om/InterfaceBuilder.php
src/Propel/Generator/Builder/Om/ExtensionObjectBuilder.php
src/Propel/Generator/Builder/Om/ExtensionQueryBuilder.php
src/Propel/Generator/Builder/Om/QueryInheritanceBuilder.php
src/Propel/Generator/Builder/Om/ExtensionQueryInheritanceBuilder.php
src/Propel/Generator/Builder/DataModelBuilder.php
src/Propel/Generator/Model/PropelTypes.php
src/Propel/Generator/Platform/MysqlPlatform.php
src/Propel/Generator/Platform/PlatformInterface.php
templates/Builder/Om/baseObjectAttributes.php
templates/Builder/Om/baseObjectMethods.php
templates/Builder/Om/baseQueryClassHeader.php
```
