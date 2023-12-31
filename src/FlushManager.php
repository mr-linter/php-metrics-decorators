<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;

final class FlushManager implements Manager
{
    public function __construct(
        private readonly Manager $metrics,
    ) {
    }

    public function declare(array $subjects): void
    {
        $this->metrics->declare($subjects);
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        $this->metrics->inc($key, $value, $labels);

        $this->flush('empty-transaction-id');
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        $this->metrics->observe($key, $value, $labels);

        $this->flush('empty-transaction-id');
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        $this->metrics->measure($key, $value, $labels);

        $this->flush('empty-transaction-id');
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        $this->metrics->flush($transactionId, $labels);
    }

    public function read(): array
    {
        return $this->metrics->read();
    }
}
