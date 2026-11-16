<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortFlaps;

final readonly class FlapThresholds
{
    public function __construct(public int $warning = 3, public int $high = 10, public int $critical = 25) {}
}
