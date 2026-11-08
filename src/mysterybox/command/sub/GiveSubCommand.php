<?php

declare(strict_types=1);

namespace mysterybox\command\sub;

use mysterybox\command\SubCommand;
use mysterybox\util\ItemUtil;
use pocketmine\command\CommandSender;

final class GiveSubCommand extends SubCommand
{
    public function getName(): string { return 'give'; }

    public function getUsage(): string { return '<player> [amount]'; }

    public function getDescription(): string { return 'Give Mystery Boxes to a player'; }

    public function execute(CommandSender $sender, array $args, string $label): void
    {
        $messages = $this->plugin->getMessages();

        if (!isset($args[0])) {
            $this->sendUsage($sender, $label);
            return;
        }

        $server = $this->plugin->getServer();
        $target = $server->getPlayerExact($args[0]) ?? $server->getPlayerByPrefix($args[0]);
        if ($target === null || !$target->isOnline()) {
            $messages->send($sender, 'player-offline');
            return;
        }

        $amount = $this->parseAmount($args[1] ?? null);
        if ($amount === null) {
            $messages->send($sender, 'invalid-amount', ['max' => $this->plugin->getSettings()->maxGiveAmount]);
            return;
        }

        ItemUtil::give($target, $this->plugin->getCrateFactory()->create($amount));

        $messages->send($sender, 'given-sender', ['amount' => $amount, 'player' => $target->getName()]);
        $messages->send($target, 'given-target', ['amount' => $amount, 'sender' => $sender->getName()]);
    }
}