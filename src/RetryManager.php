<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;
use Psr\Log\LoggerInterface;
use MrLinter\Contracts\Metrics\MetricsException;

final class RetryManager implements Manager
{
    public function __construct(
        private readonly Manager $manager,
        private readonly LoggerInterface $logger,
        private readonly int $retryAttempts,
        private readonly int $retrySleepMicroseconds,
    ) {
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        $this->retry(__FUNCTION__, fn () => $this->manager->inc($key, $value, $labels));
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        $this->retry(__FUNCTION__, fn () => $this->manager->observe($key, $value, $labels));
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        $this->retry(__FUNCTION__, fn () => $this->manager->measure($key, $value, $labels));
    }

    public function declare(array $subjects): void
    {
        $this->retry(__FUNCTION__, fn () => $this->manager->declare($subjects));
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        $this->retry(__FUNCTION__, fn () => $this->manager->flush($transactionId, $labels));
    }

    private function retry(string $operation, callable $request): void
    {
        $attempts = 0;

        while ($attempts++ < $this->retryAttempts + 1) {
            try {
                $request();

                return;
            } catch (MetricsException $e) {
                if ($attempts === $this->retryAttempts + 1) {
                    throw $e;
                }

                $this->logger->warning(sprintf(
                    '[Metrics] Operation "%s" was failed: %s. Try after %d ms',
                    $operation,
                    $e->getMessage(),
                    $this->retrySleepMicroseconds,
                ));

                usleep($this->retrySleepMicroseconds);
            }
        }
    }

    public function read(): array
    {
        return $this->manager->read();
    }
}
