<?php

declare(strict_types=1);

namespace mysterybox\loot;

use pocketmine\item\Item;

final class LootTable
{
    /** @var list<Item> */
    private array $items = [];

    public function __construct(public readonly LootTier $tier) {}

    /** @return list<Item> copias */
    public function getItems(): array
    {
        return array_map(static fn(Item $item): Item => clone $item, $this->items);
    }

    /** @param iterable<Item> $items */
    public function setItems(iterable $items): void
    {
        $clean = [];
        foreach ($items as $item) {
            if (!$item->isNull()) {
                $clean[] = clone $item;
            }
        }
        $this->items = $clean;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function pick(): ?Item
    {
        if ($this->items === []) {
            return null;
        }
        return clone $this->items[array_rand($this->items)];
    }

    /** @return list<Item> */
    public function roll(int $amount): array
    {
        $rolled = [];
        for ($i = 0; $i < $amount; $i++) {
            $item = $this->pick();
            if ($item === null) {
                break;
            }
            $rolled[] = $item;
        }
        return $rolled;
    }
}