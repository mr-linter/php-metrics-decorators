<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;
use Psr\Log\LoggerInterface;
use MrLinter\Contracts\Metrics\MetricsException;

final class SilentManager implements Manager
{
    public function __construct(
        private readonly Manager         $metrics,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        try {
            $this->metrics->inc($key, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf(
                '[Metrics] Operation "inc" was failed: %s',
                $e->getMessage(),
            ));
        }
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        try {
            $this->metrics->observe($key, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf(
                '[Metrics] Operation "observe" was failed: %s',
                $e->getMessage(),
            ));
        }
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        try {
            $this->metrics->measure($key, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf(
                '[Metrics] Operation "measure" was failed: %s',
                $e->getMessage(),
            ));
        }
    }

    public function declare(array $subjects): void
    {
        $this->metrics->declare($subjects);
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        try {
            $this->metrics->flush($transactionId, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf(
                '[Metrics] Operation "inc" was failed: %s',
                $e->getMessage(),
            ), [
                'metrics_flush_transaction_id' => $transactionId,
            ]);
        }
    }
}
