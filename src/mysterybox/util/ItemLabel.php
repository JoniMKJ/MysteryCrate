<?php

declare(strict_types=1);

namespace mysterybox\util;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

final class ItemLabel
{
    private function __construct() {}

    public static function name(Item $item): string
    {
        return TextFormat::clean($item->hasCustomName() ? $item->getCustomName() : $item->getName());
    }

    public static function withCount(Item $item): string
    {
        $count = $item->getCount() > 1 ? $item->getCount() . ' ' : '';
        return $count . self::name($item);
    }
}