<?php

namespace MrLinter\Metrics\Decorators;

use MrLinter\Contracts\Metrics\Manager;

final class WaitManagerProxy implements Manager
{
    /** @var array<\Closure> */
    private array $calls = [];

    private ?Manager $manager = null;

    public function attach(Manager $manager): void
    {
        if ($this->manager !== null) {
            throw new \LogicException('Manager already attached');
        }

        $this->manager = $manager;
        $this->sync();
    }

    public function inc(string $key, float $value = 1, array $labels = []): void
    {
        if ($this->manager !== null) {
            $this->manager->inc($key, $value, $labels);
        } else {
            $this->calls[] = static fn (Manager $manager) => $manager->inc($key, $value, $labels);
        }
    }

    public function observe(string $key, float $value, array $labels = []): void
    {
        if ($this->manager !== null) {
            $this->manager->observe($key, $value, $labels);
        } else {
            $this->calls[] = static fn (Manager $manager) => $manager->observe($key, $value, $labels);
        }
    }

    public function measure(string $key, float $value, array $labels = []): void
    {
        if ($this->manager !== null) {
            $this->manager->measure($key, $value, $labels);
        } else {
            $this->calls[] = static fn (Manager $manager) => $manager->measure($key, $value, $labels);
        }
    }

    public function declare(array $subjects): void
    {
        if ($this->manager !== null) {
            $this->manager->declare($subjects);
        } else {
            $this->calls[] = static fn (Manager $manager) => $manager->declare($subjects);
        }
    }

    public function flush(string $transactionId, array $labels = []): void
    {
        if ($this->manager !== null) {
            $this->manager->flush($transactionId, $labels);
        } else {
            $this->calls[] = static fn (Manager $manager) => $manager->flush($transactionId, $labels);
        }
    }

    public function read(): array
    {
        return $this->manager->read();
    }

    private function sync(): void
    {
        foreach ($this->calls as $call) {
            $call($this->manager);
        }
    }
}
