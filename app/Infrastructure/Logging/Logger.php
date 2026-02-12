<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

final class Logger
{
    public function info(string $event, array $context = []): void
    {
        $this->write('INFO', $event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        $this->write('ERROR', $event, $context);
    }

    private function write(string $level, string $event, array $context): void
    {
        $payload = [
            'ts' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'level' => $level,
            'event' => $event,
            'context' => $context,
        ];

        error_log(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}