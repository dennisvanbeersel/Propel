<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites array literal config keys `'slaves' => [...]` to `'replicas' => [...]`.
 *
 * Caveat: this rule only handles PHP array literals. YAML configs need to
 * be migrated by hand (or via a yaml-aware sed/find-replace) — Rector
 * operates on PHP AST.
 */
final class SlavesToReplicasConfigRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            "Rewrite Propel config array key 'slaves' to 'replicas' (Propel 4.0).",
            [
                new CodeSample(
                    "['adapter' => 'mysql', 'slaves' => [['dsn' => 'mysql:host=replica']]]",
                    "['adapter' => 'mysql', 'replicas' => [['dsn' => 'mysql:host=replica']]]",
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ArrayItem::class];
    }

    /**
     * @param ArrayItem $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->key instanceof String_) {
            return null;
        }
        if ($node->key->value !== 'slaves') {
            return null;
        }
        if (!self::isPropelConnectionConfigArrayItem($node)) {
            return null;
        }
        $node->key = new String_('replicas');

        return $node;
    }

    /**
     * Heuristic: only rewrite when the surrounding Array_ also contains a
     * Propel-shaped sibling key (`adapter`, `dsn`, `master`, `primary`).
     * That excludes unrelated business arrays whose owner happens to use
     * `slaves` as a domain key.
     */
    private static function isPropelConnectionConfigArrayItem(ArrayItem $node): bool
    {
        /** @var Node|null $parent */
        $parent = $node->getAttribute('parent');
        if (!$parent instanceof Array_) {
            return false;
        }

        foreach ($parent->items as $sibling) {
            if (!$sibling instanceof ArrayItem || !$sibling->key instanceof String_) {
                continue;
            }
            if (in_array($sibling->key->value, ['adapter', 'dsn', 'master', 'primary'], true)) {
                return true;
            }
        }

        return false;
    }
}
