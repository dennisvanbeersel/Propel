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
 * If a `$onClose` callback is supplied (used by `methodBody()` to emit the
 * trailing `}` line at parent indent), it fires AFTER the dedent.
 *
 * @internal Tier 3 internal helper. Public method shapes committed via
 * `tests/snapshots/tracked-classes.txt` per umbrella §3.3.
 */
final class CodeEmitterScope
{
    /**
     * @var \Propel\Generator\Builder\Util\CodeEmitter|null
     */
    private ?CodeEmitter $emitter;

    /**
     * @var (callable(): void)|null
     */
    private $onClose;

    /**
     * @var bool
     */
    private bool $closed = false;

    /**
     * @param \Propel\Generator\Builder\Util\CodeEmitter $emitter
     * @param (callable(): void)|null $onClose Optional callback fired after dedent on close.
     */
    public function __construct(CodeEmitter $emitter, ?callable $onClose = null)
    {
        $this->emitter = $emitter;
        $this->onClose = $onClose;
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
        if ($this->onClose !== null) {
            ($this->onClose)();
        }
        $this->emitter = null;
        $this->onClose = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
