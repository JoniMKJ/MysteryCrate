<?php

declare(strict_types=1);

namespace mysterybox\util;

use pocketmine\utils\TextFormat;

final class ColorPalette
{
    private const COLORS = [
        TextFormat::DARK_GREEN, TextFormat::GREEN, TextFormat::YELLOW, TextFormat::GOLD,
        TextFormat::RED, TextFormat::DARK_RED, TextFormat::AQUA, TextFormat::DARK_AQUA,
        TextFormat::BLUE, TextFormat::DARK_BLUE, TextFormat::LIGHT_PURPLE, TextFormat::DARK_PURPLE,
    ];

    private function __construct() {}

    public static function at(int $index): string
    {
        return self::COLORS[$index % count(self::COLORS)];
    }

    public static function random(): string
    {
        return self::COLORS[array_rand(self::COLORS)];
    }
}