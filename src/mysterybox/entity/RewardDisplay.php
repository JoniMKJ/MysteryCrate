<?php

declare(strict_types=1);

namespace mysterybox\entity;

use mysterybox\util\ColorPalette;
use mysterybox\util\ItemLabel;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class RewardDisplay extends ItemEntity
{
    protected bool $gravityEnabled = false;

    private Item $reward;

    public function __construct(Location $location, Item $reward)
    {
        $this->reward = clone $reward;
        parent::__construct($location, self::asDisplayItem($reward));

        $this->setCanSaveWithChunk(false);
        $this->setNameTagAlwaysVisible();
        $this->refreshNameTag();
    }

    public function getReward(): Item
    {
        return clone $this->reward;
    }

    /** Cambia el ítem mostrado (hay que reenviar el paquete de spawn). */
    public function showItem(Item $reward): void
    {
        $this->reward = clone $reward;
        $this->item = self::asDisplayItem($reward);
        $this->refreshNameTag();

        $this->despawnFromAll();
        $this->spawnToAll();
    }

    public function onCollideWithPlayer(Player $player): void
    {
        // Nunca se recoge.
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    private function refreshNameTag(): void
    {
        $this->setNameTag(ColorPalette::random() . ItemLabel::withCount($this->reward));
    }

    private static function asDisplayItem(Item $item): Item
    {
        $display = clone $item;
        $display->setCount(1);
        return $display;
    }
}