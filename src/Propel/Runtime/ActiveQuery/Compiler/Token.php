<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Compiler;

/**
 * Internal token value object emitted by Tokenizer.
 *
 * Tier 3. The `$type` is a string union (not a separate enum) for
 * a single integer-cmp branch in the inner loop — performance rationale.
 *
 * @psalm-api
 */
final readonly class Token
{
    /**
     * @var string
     */
    public const TYPE_IDENT = 'IDENT';

    /**
     * @var string
     */
    public const TYPE_BACKTICK_IDENT = 'BACKTICK_IDENT';

    /**
     * @var string
     */
    public const TYPE_STRING = 'STRING';

    /**
     * @var string
     */
    public const TYPE_NUMBER = 'NUMBER';

    /**
     * @var string
     */
    public const TYPE_WS = 'WS';

    /**
     * @var string
     */
    public const TYPE_OP = 'OP';

    /**
     * @var string
     */
    public const TYPE_LCOMMENT = 'LCOMMENT';

    /**
     * @var string
     */
    public const TYPE_BCOMMENT = 'BCOMMENT';

    /**
     * @var string
     */
    public const TYPE_PUNCT = 'PUNCT';

    /**
     * @psalm-api
     *
     * @param string $type One of the Token::TYPE_* constants.
     * @param string $value Source-text slice for this token.
     * @param int $offset Byte offset in the original SQL.
     */
    public function __construct(
        public string $type,
        public string $value,
        public int $offset,
    ) {
    }
}
