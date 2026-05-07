<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites `new \Propel\Runtime\Connection\DebugPDO($conn)` to
 * `(new \Propel\Runtime\Connection\ConnectionWrapper($conn))->useDebug(true)`.
 */
final class DebugPdoToConnectionWrapperRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace DebugPDO instantiation with ConnectionWrapper + useDebug(true).',
            [
                new CodeSample(
                    '$wrapped = new \Propel\Runtime\Connection\DebugPDO($conn);',
                    '$wrapped = (new \Propel\Runtime\Connection\ConnectionWrapper($conn))->useDebug(true);',
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isClassReference($node, 'Propel\\Runtime\\Connection\\DebugPDO')) {
            return null;
        }

        $wrapperNew = new New_(
            new FullyQualified('Propel\\Runtime\\Connection\\ConnectionWrapper'),
            $node->args,
        );

        return new MethodCall(
            $wrapperNew,
            new Identifier('useDebug'),
            [new Arg(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true')))],
        );
    }

    private function isClassReference(New_ $node, string $fqcn): bool
    {
        $cls = $node->class;
        if (!$cls instanceof FullyQualified && !$cls instanceof \PhpParser\Node\Name) {
            return false;
        }

        return ltrim($cls->toString(), '\\') === $fqcn;
    }
}
