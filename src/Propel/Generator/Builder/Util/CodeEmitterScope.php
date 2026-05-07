<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Util;

/**
 * RAII-style scope handle returned by `CodeEmitter::block()`.
 *
 * On destruction (when the holding variable goes out of scope or is unset),
 * automatically calls `dedent()` on the originating emitter. This makes
 * indented-block emission idiomatic without manual `dedent()` calls:
 *
 *     $body = $emitter->block();
 *     // ... emit lines inside block ...
 *     unset($body); // closes block
 *
 * @internal Tier 3 internal helper. Public method shapes committed via
 * `tests/snapshots/tracked-classes.txt` per umbrella §3.3.
 */
final class CodeEmitterScope
{
    private ?CodeEmitter $emitter;

    private bool $closed = false;

    /**
     * @param \Propel\Generator\Builder\Util\CodeEmitter $emitter
     */
    public function __construct(CodeEmitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Explicitly close the scope (dedent now). Idempotent — repeat calls
     * are safe and will not double-dedent.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }
        $this->closed = true;
        $this->emitter?->dedent();
        $this->emitter = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
