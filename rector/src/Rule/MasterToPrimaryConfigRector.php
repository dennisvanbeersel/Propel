<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites array literal config keys `'master' => [...]` to `'primary' => [...]`.
 *
 * Same PHP-array-literal caveat as `SlavesToReplicasConfigRector`.
 */
final class MasterToPrimaryConfigRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            "Rewrite Propel config array key 'master' to 'primary' (Propel 4.0).",
            [
                new CodeSample(
                    "['adapter' => 'mysql', 'master' => ['dsn' => 'mysql:host=primary']]",
                    "['adapter' => 'mysql', 'primary' => ['dsn' => 'mysql:host=primary']]",
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
        if ($node->key->value !== 'master') {
            return null;
        }
        $node->key = new String_('primary');

        return $node;
    }
}
