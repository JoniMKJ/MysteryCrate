<?php

declare(strict_types=1);

namespace mysterybox\animation;

use mysterybox\entity\RewardDisplay;
use mysterybox\loot\LootTier;
use pocketmine\item\Item;

final class RewardSlot
{
    public int $revealStep = 0;
    public bool $revealed = false;

    public function __construct(
        public readonly LootTier $tier,
        public readonly Item $reward,
        public readonly RewardDisplay $display,
        public readonly Orbit $orbit,
        public readonly int $index,
        public readonly int $ringSize
    ) {}
}