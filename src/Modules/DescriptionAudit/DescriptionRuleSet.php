<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\DescriptionAudit;

final readonly class DescriptionRuleSet
{
    public function __construct(
        public int $minLength = 4,
        public array $flaggedTerms = ['temp', 'test', 'unknown', 'unused'],
        public array $excludedPortIds = [],
    ) {}
}
