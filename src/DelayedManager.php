<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\DelayedCollector;
use MrLinter\Contracts\Metrics\Manager;

final class DelayedManager implements Manager, DelayedCollector
{
    /** @var array<\Closure(Manager): void> */
    private array $delayed = [];

    public function __construct(
        private readonly Manager $manager,
    ) {
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        $this->manager->inc($key, $value, $labels);
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        $this->manager->observe($key, $value, $labels);
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        $this->manager->observe($key, $value, $labels);
    }

    public function observeFn(string $key, \Closure $value, array $labels): void
    {
        $this->delayed[] = static function (Manager $manager) use ($key, $value, $labels) {
            $manager->observe($key, $value(), $labels);
        };
    }

    public function declare(array $subjects): void
    {
        $this->manager->declare($subjects);
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        $this->callDelayed();

        $this->manager->flush($transactionId, $labels);
    }

    public function read(): array
    {
        return $this->manager->read();
    }

    private function callDelayed(): void
    {
        foreach ($this->delayed as $delay) {
            $delay($this->manager);
        }

        $this->delayed = [];
    }
}
