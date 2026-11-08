<?php

declare(strict_types=1);

namespace mysterybox\loot;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

final class LootSerializer
{
    private const LEGACY_TAG = 'items';

    private function __construct() {}

    /** @param list<Item> $items */
    public static function toList(array $items): ListTag
    {
        $tags = [];
        foreach ($items as $item) {
            $tags[] = $item->nbtSerialize();
        }
        return new ListTag($tags, NBT::TAG_Compound);
    }

    /** @return list<Item> */
    public static function fromList(ListTag $list, int &$skipped = 0): array
    {
        $items = [];
        foreach ($list->getValue() as $tag) {
            try {
                if (!$tag instanceof CompoundTag) {
                    throw new \InvalidArgumentException('Expected a compound tag');
                }
                $items[] = Item::nbtDeserialize($tag);
            } catch (\Throwable) {
                $skipped++;
            }
        }
        return $items;
    }

    /**
     * @return list<Item>
     */
    public static function fromLegacyBlob(string $blob, int &$skipped = 0): array
    {
        if ($blob === '') {
            return [];
        }
        $list = (new BigEndianNbtSerializer())->read($blob)->mustGetCompoundTag()->getListTag(self::LEGACY_TAG);
        return $list === null ? [] : self::fromList($list, $skipped);
    }
}