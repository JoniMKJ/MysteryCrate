<?php

declare(strict_types=1);

namespace mysterybox\loot;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use mysterybox\MysteryBox;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

final class LootEditor
{
    public function __construct(private readonly MysteryBox $plugin) {}

    public static function isAvailable(): bool
    {
        return class_exists(InvMenu::class);
    }

    public function open(Player $player, LootTier $tier): void
    {
        $plugin = $this->plugin;

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($plugin->getMessages()->get('editor-title', ['tier' => ucfirst($tier->value)]));
        $menu->getInventory()->setContents($plugin->getLoot()->table($tier)->getItems());

        $menu->setInventoryCloseListener(static function (Player $viewer, Inventory $inventory) use ($plugin, $tier): void {
            $loot = $plugin->getLoot();
            $table = $loot->table($tier);
            $table->setItems($inventory->getContents());
            $loot->save();

            $plugin->getMessages()->send($viewer, 'loot-saved', [
                'tier' => $tier->value,
                'count' => $table->count(),
            ]);
        });

        $menu->send($player);
    }
}