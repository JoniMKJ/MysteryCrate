<?php

declare(strict_types=1);

namespace mysterybox\util;

use pocketmine\item\Item;
use pocketmine\player\Player;

final class ItemUtil
{
    private function __construct() {}

    /** @return bool true*/
    public static function give(Player $player, Item $item): bool
    {
        $leftover = $player->getInventory()->addItem(clone $item);
        foreach ($leftover as $rest) {
            $player->getWorld()->dropItem($player->getPosition(), $rest);
        }
        return $leftover !== [];
    }
}