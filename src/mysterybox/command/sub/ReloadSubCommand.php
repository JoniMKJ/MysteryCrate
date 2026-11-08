<?php

declare(strict_types=1);

namespace mysterybox\command\sub;

use mysterybox\command\SubCommand;
use pocketmine\command\CommandSender;

final class ReloadSubCommand extends SubCommand
{
    public function getName(): string { return 'reload'; }

    public function getUsage(): string { return ''; }

    public function getDescription(): string { return 'Cargando config.yml y messages.yml'; }

    public function execute(CommandSender $sender, array $args, string $label): void
    {
        $this->plugin->reloadSettings();
        $this->plugin->getMessages()->send($sender, 'reloaded');
    }
}