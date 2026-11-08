<?php

declare(strict_types=1);

namespace mysterybox\crate;

use mysterybox\animation\OpeningAnimation;
use mysterybox\loot\LootTier;
use mysterybox\MysteryBox;
use mysterybox\Permissions;
use mysterybox\util\ItemUtil;
use pocketmine\player\Player;

final class OpeningManager
{
    /** @var array<string, OpeningAnimation> */
    private array $active = [];
    /** @var array<string, int> */
    private array $lastAttempt = [];

    public function __construct(private readonly MysteryBox $plugin) {}

    public function tryOpen(Player $player): void
    {
        $key = $player->getUniqueId()->toString();

        // Evita doble disparo (interact + item use) en el mismo tick.
        $tick = $this->plugin->getServer()->getTick();
        if (($this->lastAttempt[$key] ?? -1) === $tick) {
            return;
        }
        $this->lastAttempt[$key] = $tick;

        $messages = $this->plugin->getMessages();
        $settings = $this->plugin->getSettings();
        $loot = $this->plugin->getLoot();

        if (!$player->hasPermission(Permissions::OPEN)) {
            $messages->send($player, 'no-permission-open');
            return;
        }
        if (isset($this->active[$key])) {
            $messages->send($player, 'already-opening');
            return;
        }
        $remaining = $this->plugin->getCooldowns()->remaining($player);
        if ($remaining > 0) {
            $messages->send($player, 'cooldown', ['seconds' => (int) ceil($remaining)]);
            return;
        }
        if (!$loot->canOpen($settings->commonCount, $settings->exclusiveCount)) {
            $messages->send($player, 'empty');
            return;
        }

        // Consume una caja.
        $inventory = $player->getInventory();
        $held = $inventory->getItemInHand();
        $held->pop();
        $inventory->setItemInHand($held);

        $animation = new OpeningAnimation(
            $this->plugin,
            $player,
            $loot->table(LootTier::COMMON)->roll($settings->commonCount),
            $loot->table(LootTier::EXCLUSIVE)->roll($settings->exclusiveCount),
            function () use ($key): void {
                unset($this->active[$key]);
            }
        );
        $this->active[$key] = $animation;
        $this->plugin->getCooldowns()->apply($player, $settings->cooldownSeconds);

        $this->plugin->getServer()->broadcastMessage(
            $messages->get('opening-broadcast', ['player' => $player->getName()])
        );

        try {
            $animation->start();
        } catch (\Throwable $e) {
            unset($this->active[$key]);
            $animation->abort();
            $this->plugin->getCooldowns()->clear($player);
            ItemUtil::give($player, $this->plugin->getCrateFactory()->create(1));
            $this->plugin->getLogger()->logException($e);
            $messages->send($player, 'open-error');
        }
    }

    public function finishFor(Player $player): void
    {
        $key = $player->getUniqueId()->toString();
        unset($this->lastAttempt[$key]);
        if (isset($this->active[$key])) {
            $this->active[$key]->finish();
        }
    }

    public function finishAll(): void
    {
        foreach ($this->active as $animation) {
            $animation->finish();
        }
    }
}