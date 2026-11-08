<?php

declare(strict_types=1);

namespace mysterybox\listener;

use mysterybox\MysteryBox;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class CrateListener implements Listener
{
    public function __construct(private readonly MysteryBox $plugin) {}

    public function onItemUse(PlayerItemUseEvent $event): void
    {
        if (!$this->plugin->getCrateFactory()->isCrate($event->getItem())) {
            return;
        }
        $event->cancel();
        $this->plugin->getOpenings()->tryOpen($event->getPlayer());
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return;
        }
        if (!$this->plugin->getCrateFactory()->isCrate($event->getItem())) {
            return;
        }
        $event->cancel();
        $this->plugin->getOpenings()->tryOpen($event->getPlayer());
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        if ($this->plugin->getCrateFactory()->isCrate($event->getItem())) {
            $event->cancel();
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $this->plugin->getOpenings()->finishFor($player);
        $this->plugin->getCooldowns()->clear($player);
    }
}