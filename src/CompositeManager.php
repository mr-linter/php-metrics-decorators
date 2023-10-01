<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;

final class CompositeManager implements Manager
{
    /**
     * @param array<Manager> $managers
     */
    public function __construct(
        private readonly array $managers,
    ) {
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        foreach ($this->managers as $metrics) {
            $metrics->inc($key, $value, $labels);
        }
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        foreach ($this->managers as $metrics) {
            $metrics->observe($key, $value, $labels);
        }
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        foreach ($this->managers as $metrics) {
            $metrics->measure($key, $value, $labels);
        }
    }

    public function declare(array $subjects): void
    {
        foreach ($this->managers as $metrics) {
            $metrics->declare($subjects);
        }
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        foreach ($this->managers as $metrics) {
            $metrics->flush($transactionId, $labels);
        }
    }

    public function read(): array
    {
        foreach ($this->managers as $manager) {
            $metrics = $manager->read();

            if ($metrics !== []) {
                return $metrics;
            }
        }

        return [];
    }
}
