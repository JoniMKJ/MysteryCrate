<?php

declare(strict_types=1);

namespace mysterybox\crate;

use pocketmine\player\Player;

final class CooldownManager
{
    /** @var array<string, float> uuid => timestamp en que termina */
    private array $until = [];

    public function apply(Player $player, float $seconds): void
    {
        if ($seconds > 0) {
            $this->until[$player->getUniqueId()->toString()] = microtime(true) + $seconds;
        }
    }

    public function remaining(Player $player): float
    {
        $key = $player->getUniqueId()->toString();
        $left = ($this->until[$key] ?? 0.0) - microtime(true);
        if ($left <= 0) {
            unset($this->until[$key]);
            return 0.0;
        }
        return $left;
    }

    public function clear(Player $player): void
    {
        unset($this->until[$player->getUniqueId()->toString()]);
    }
}