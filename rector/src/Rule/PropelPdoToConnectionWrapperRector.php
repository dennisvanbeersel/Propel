<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites `new \Propel\Runtime\Connection\PropelPDO($conn)` to
 * `new \Propel\Runtime\Connection\ConnectionWrapper($conn)`.
 */
final class PropelPdoToConnectionWrapperRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace PropelPDO instantiation with ConnectionWrapper.',
            [
                new CodeSample(
                    '$wrapped = new \Propel\Runtime\Connection\PropelPDO($conn);',
                    '$wrapped = new \Propel\Runtime\Connection\ConnectionWrapper($conn);',
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
        $cls = $node->class;
        if (!$cls instanceof FullyQualified && !$cls instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (ltrim($cls->toString(), '\\') !== 'Propel\\Runtime\\Connection\\PropelPDO') {
            return null;
        }

        return new New_(
            new FullyQualified('Propel\\Runtime\\Connection\\ConnectionWrapper'),
            $node->args,
        );
    }
}
