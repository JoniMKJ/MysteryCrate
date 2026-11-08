<?php

declare(strict_types=1);

namespace mysterybox\command\sub;

use mysterybox\command\SubCommand;
use mysterybox\util\ItemUtil;
use pocketmine\command\CommandSender;

final class GiveAllSubCommand extends SubCommand
{
    public function getName(): string { return 'giveall'; }

    public function getUsage(): string { return '[amount]'; }

    public function getDescription(): string { return 'Dar una MysteryCrate a los jugadores en linea'; }

    public function execute(CommandSender $sender, array $args, string $label): void
    {
        $messages = $this->plugin->getMessages();

        $players = $this->plugin->getServer()->getOnlinePlayers();
        if ($players === []) {
            $messages->send($sender, 'no-players');
            return;
        }

        $amount = $this->parseAmount($args[0] ?? null);
        if ($amount === null) {
            $messages->send($sender, 'invalid-amount', ['max' => $this->plugin->getSettings()->maxGiveAmount]);
            return;
        }

        $item = $this->plugin->getCrateFactory()->create($amount);
        foreach ($players as $player) {
            ItemUtil::give($player, $item);
            $messages->send($player, 'giveall-target', ['amount' => $amount]);
        }

        $messages->send($sender, 'giveall-sender', ['amount' => $amount, 'count' => count($players)]);
    }
}