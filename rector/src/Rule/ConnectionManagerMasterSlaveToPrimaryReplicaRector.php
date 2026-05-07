<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites the deprecated `ConnectionManagerMasterSlave` class to its
 * 4.0 successor `ConnectionManagerPrimaryReplica`. Also rewrites the
 * companion method calls `isForceMasterConnection()` /
 * `setForceMasterConnection()` to `isForcePrimaryConnection()` /
 * `setForcePrimaryConnection()`.
 */
final class ConnectionManagerMasterSlaveToPrimaryReplicaRector extends AbstractRector
{
    private const OLD_CLASS = 'Propel\\Runtime\\Connection\\ConnectionManagerMasterSlave';
    private const NEW_CLASS = 'Propel\\Runtime\\Connection\\ConnectionManagerPrimaryReplica';

    private const METHOD_RENAMES = [
        'isForceMasterConnection' => 'isForcePrimaryConnection',
        'setForceMasterConnection' => 'setForcePrimaryConnection',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated ConnectionManagerMasterSlave with ConnectionManagerPrimaryReplica.',
            [
                new CodeSample(
                    '$m = new \Propel\Runtime\Connection\ConnectionManagerMasterSlave("default");
$m->setForceMasterConnection(true);
$on = $m->isForceMasterConnection();',
                    '$m = new \Propel\Runtime\Connection\ConnectionManagerPrimaryReplica("default");
$m->setForcePrimaryConnection(true);
$on = $m->isForcePrimaryConnection();',
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof New_) {
            return $this->refactorNew($node);
        }

        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        return null;
    }

    private function refactorNew(New_ $node): ?Node
    {
        $cls = $node->class;
        if (!$cls instanceof FullyQualified && !$cls instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (ltrim($cls->toString(), '\\') !== self::OLD_CLASS) {
            return null;
        }

        return new New_(new FullyQualified(self::NEW_CLASS), $node->args);
    }

    private function refactorMethodCall(MethodCall $node): ?Node
    {
        $name = $node->name;
        if (!$name instanceof Identifier) {
            return null;
        }
        if (!isset(self::METHOD_RENAMES[$name->toString()])) {
            return null;
        }

        $node->name = new Identifier(self::METHOD_RENAMES[$name->toString()]);

        return $node;
    }
}
