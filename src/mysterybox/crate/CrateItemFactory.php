<?php

declare(strict_types=1);

namespace mysterybox\crate;

use mysterybox\loot\LootTier;
use mysterybox\MysteryBox;
use mysterybox\util\ColorPalette;
use mysterybox\util\ItemLabel;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\TextFormat;

final class CrateItemFactory
{
    public const NBT_KEY = 'mystery_crate';

    public function __construct(private readonly MysteryBox $plugin) {}

    public function isCrate(Item $item): bool
    {
        return !$item->isNull() && $item->getNamedTag()->getTag(self::NBT_KEY) !== null;
    }

    public function create(int $count = 1): Item
    {
        $settings = $this->plugin->getSettings();

        $parsed = StringToItemParser::getInstance()->parse($settings->crateItem);
        $item = $parsed !== null ? clone $parsed : VanillaBlocks::BEACON()->asItem();

        $item->setCount($count);
        $item->setCustomName(TextFormat::RESET . TextFormat::colorize($settings->boxName));
        $item->setLore($this->buildLore());

        $tag = $item->getNamedTag();
        $tag->setInt(self::NBT_KEY, 1);
        $item->setNamedTag($tag);

        return $item;
    }

    /** @return list<string> */
    private function buildLore(): array
    {
        $settings = $this->plugin->getSettings();

        $lines = [];
        foreach ($settings->loreHeader as $line) {
            $lines[] = str_replace('{store}', $settings->store, $line);
        }

        $colorIndex = 0;
        $seen = 0;
        foreach ([LootTier::COMMON, LootTier::EXCLUSIVE] as $tier) {
            if ($tier === LootTier::EXCLUSIVE) {
                $lines[] = ' ';
            }
            $lines[] = $tier === LootTier::COMMON ? $settings->loreCommonTitle : $settings->loreExclusiveTitle;

            $items = $this->plugin->getLoot()->table($tier)->getItems();
            foreach ($items as $shown => $item) {
                if ($shown >= $settings->loreMaxEntries) {
                    $lines[] = str_replace('{count}', (string) (count($items) - $shown), $settings->loreMore);
                    break;
                }
                $lines[] = '&7* ' . ColorPalette::at($colorIndex) . ItemLabel::withCount($item);
                if (++$seen % 3 === 0) {
                    $colorIndex++;
                }
            }
        }

        return array_map(
            static fn(string $line): string => TextFormat::RESET . TextFormat::colorize($line),
            $lines
        );
    }
}