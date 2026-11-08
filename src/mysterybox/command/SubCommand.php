<?php

declare(strict_types=1);

namespace mysterybox\command;

use mysterybox\MysteryBox;
use pocketmine\command\CommandSender;

abstract class SubCommand
{
    public function __construct(protected readonly MysteryBox $plugin) {}

    abstract public function getName(): string;

    abstract public function getUsage(): string;

    abstract public function getDescription(): string;

    /** @param list<string> $args */
    abstract public function execute(CommandSender $sender, array $args, string $label): void;

    /** @return list<string> */
    public function getAliases(): array
    {
        return [];
    }

    protected function sendUsage(CommandSender $sender, string $label): void
    {
        $this->plugin->getMessages()->send($sender, 'usage', [
            'label' => $label,
            'usage' => $this->getName() . ' ' . $this->getUsage(),
        ]);
    }

    protected function parseAmount(?string $raw): ?int
    {
        if ($raw === null) {
            return 1;
        }
        if (!ctype_digit($raw)) {
            return null;
        }
        $amount = (int) $raw;
        return ($amount >= 1 && $amount <= $this->plugin->getSettings()->maxGiveAmount) ? $amount : null;
    }
}