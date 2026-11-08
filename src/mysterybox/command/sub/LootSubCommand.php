<?php

declare(strict_types=1);

namespace mysterybox\command\sub;

use mysterybox\command\SubCommand;
use mysterybox\loot\LootEditor;
use mysterybox\loot\LootTier;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class LootSubCommand extends SubCommand
{
    public function getName(): string { return 'loot'; }

    public function getUsage(): string { return '<common|exclusive>'; }

    public function getDescription(): string { return 'Editar recompensas por rareza'; }

    public function execute(CommandSender $sender, array $args, string $label): void
    {
        $messages = $this->plugin->getMessages();

        if (!$sender instanceof Player) {
            $messages->send($sender, 'player-only');
            return;
        }

        $tier = LootTier::tryFrom(strtolower($args[0] ?? 'common'));
        if ($tier === null) {
            $this->sendUsage($sender, $label);
            return;
        }

        if (!LootEditor::isAvailable()) {
            $messages->send($sender, 'editor-unavailable');
            return;
        }

        $this->plugin->getEditor()->open($sender, $tier);
    }
}