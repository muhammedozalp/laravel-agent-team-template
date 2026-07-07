<?php

namespace App\Checklists;

/**
 * Outcome of one auto-check probe (guides/checklists.md).
 */
final readonly class ProbeResult
{
    public function __construct(
        public bool $passed,
        public string $detail = '',
    ) {}

    public static function pass(string $detail = ''): self
    {
        return new self(true, $detail);
    }

    public static function fail(string $detail): self
    {
        return new self(false, $detail);
    }
}
