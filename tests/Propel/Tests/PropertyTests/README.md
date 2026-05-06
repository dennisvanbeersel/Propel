# Property-Based Tests

Tests in this directory use [innmind/black-box](https://github.com/Innmind/BlackBox) Set generators inside PHPUnit test methods for property-based assertions. (The eris library was originally specced but is PHP-7-only and unmaintained; black-box is the actively-maintained PHP 8 alternative.)

Per umbrella spec §4.11 / §4.14, this is the home for:

- **Phase B/B':** generated-code round-trip properties (deterministic ordering across regenerations).
- **Phase C:** SchemaParser XML round-trip; Migration apply/inverse identity; INFORMATION_SCHEMA reverse fidelity.
- **Phase F:** `replaceNames` token-equivalence property; Criterion compose/decompose; `Comparison::X->value === Criteria::X` contract.

Pattern (PHPUnit-integrated):
```php
use Innmind\BlackBox\Set;

public function testProperty(): void {
    foreach (Set::integers()->values(new \Innmind\BlackBox\Random()) as $value) {
        $this->assertTrue(myFunction($value->unwrap()) >= 0);
    }
}
```

Run with: `vendor/bin/phpunit -c tests/agnostic.phpunit.xml --testsuite property`
