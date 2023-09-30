<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;
use MrLinter\Contracts\Metrics\Reader;

final class CompositeManager implements Manager, Reader
{
    /**
     * @param array<Manager> $metrics
     */
    public function __construct(
        private readonly array $metrics,
    ) {
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        foreach ($this->metrics as $metrics) {
            $metrics->inc($key, $value, $labels);
        }
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        foreach ($this->metrics as $metrics) {
            $metrics->observe($key, $value, $labels);
        }
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        foreach ($this->metrics as $metrics) {
            $metrics->measure($key, $value, $labels);
        }
    }

    public function declare(array $subjects): void
    {
        foreach ($this->metrics as $metrics) {
            $metrics->declare($subjects);
        }
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        foreach ($this->metrics as $metrics) {
            $metrics->flush($transactionId, $labels);
        }
    }

    public function read(): array
    {
        $reader = null;

        foreach ($this->metrics as $metrics) {
            if ($metrics instanceof Reader) {
                $reader = $metrics;

                break;
            }
        }

        if ($reader === null) {
            throw new MetricsException(sprintf('Reader in composed [%s] not found', implode(
                ',',
                array_map('get_class', $this->metrics),
            )));
        }

        return $reader->read();
    }
}
