<?php

declare(strict_types=1);

namespace mysterybox;

use pocketmine\utils\Config;

final class Settings
{
    /**
     * @param list<string> $loreHeader
     */
    public function __construct(
        public readonly string $boxName,
        public readonly string $prefix,
        public readonly string $store,
        public readonly string $crateItem,
        public readonly float $cooldownSeconds,
        public readonly int $maxGiveAmount,
        public readonly int $commonCount,
        public readonly int $exclusiveCount,
        public readonly int $intervalTicks,
        public readonly int $spinSteps,
        public readonly int $pauseSteps,
        public readonly int $revealGapSteps,
        public readonly int $cycleSteps,
        public readonly float $radius,
        public readonly float $innerRadius,
        public readonly float $height,
        public readonly float $textHeight,
        public readonly float $rotationSpeed,
        public readonly bool $particles,
        public readonly bool $sounds,
        public readonly bool $revealExclusiveLast,
        public readonly array $loreHeader,
        public readonly string $loreCommonTitle,
        public readonly string $loreExclusiveTitle,
        public readonly string $loreMore,
        public readonly int $loreMaxEntries,
    ) {}

    public static function fromConfig(Config $config): self
    {
        $int = static fn(string $key, int $default, int $min = 0): int => max($min, (int) $config->getNested($key, $default));
        $float = static fn(string $key, float $default, float $min = 0.0): float => max($min, (float) $config->getNested($key, $default));
        $bool = static fn(string $key, bool $default): bool => (bool) $config->getNested($key, $default);
        $str = static fn(string $key, string $default): string => (string) $config->getNested($key, $default);

        $header = $config->getNested('lore.header', ['&bUnlock at {store}', ' ']);

        return new self(
            boxName: $str('box.name', '&r&bMystery Box'),
            prefix: $str('box.prefix', '&7[&bMystery&7] '),
            store: $str('box.store', 'store.example.com'),
            crateItem: $str('box.item', 'beacon'),
            cooldownSeconds: $float('box.cooldown-seconds', 10.0),
            maxGiveAmount: $int('box.max-give-amount', 64, 1),
            commonCount: $int('rewards.common-count', 7),
            exclusiveCount: $int('rewards.exclusive-count', 1),
            intervalTicks: $int('animation.interval-ticks', 2, 1),
            spinSteps: $int('animation.spin-steps', 40),
            pauseSteps: $int('animation.pause-steps', 10),
            revealGapSteps: $int('animation.reveal-gap-steps', 5, 1),
            cycleSteps: $int('animation.cycle-steps', 5, 1),
            radius: $float('animation.radius', 3.0),
            innerRadius: $float('animation.inner-radius', 1.2),
            height: $float('animation.height', 1.0),
            textHeight: $float('animation.text-height', 2.5),
            rotationSpeed: $float('animation.rotation-speed', 0.35),
            particles: $bool('animation.particles', true),
            sounds: $bool('animation.sounds', true),
            revealExclusiveLast: $bool('animation.reveal-exclusive-last', false),
            loreHeader: is_array($header) ? array_map('strval', array_values($header)) : [],
            loreCommonTitle: $str('lore.common-title', '&a&lCOMMON'),
            loreExclusiveTitle: $str('lore.exclusive-title', '&c&lEXCLUSIVE'),
            loreMore: $str('lore.more', '&7... and {count} more'),
            loreMaxEntries: $int('lore.max-entries-per-tier', 20),
        );
    }
}