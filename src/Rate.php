<?php

declare(strict_types=1);

namespace ZdenekGebauer\CnbClient;

class Rate
{
    public function __construct(
        public string $currency,
        public int $quantity,
        public float $rate,
        public \DateTimeImmutable $date
    ) {
    }
}
