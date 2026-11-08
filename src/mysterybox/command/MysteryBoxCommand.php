<?php

declare(strict_types=1);

namespace mysterybox\command;

use mysterybox\command\sub\GiveAllSubCommand;
use mysterybox\command\sub\GiveSubCommand;
use mysterybox\command\sub\LootSubCommand;
use mysterybox\command\sub\ReloadSubCommand;
use mysterybox\MysteryBox;
use mysterybox\Permissions;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

final class MysteryBoxCommand extends Command implements PluginOwned
{
    use PluginOwnedTrait;

    /** @var array<string, SubCommand> nombre/alias => subcomando */
    private array $lookup = [];
    /** @var list<SubCommand> */
    private array $subCommands = [];

    public function __construct(private readonly MysteryBox $plugin)
    {
        parent::__construct(
            'mysterycrate',
            'Comandos del manejo del MysteryCrate',
            '/mysterycrate help',
            ['mcrate', 'crate', 'mysterybox', 'mbox']
        );
        $this->setPermission(Permissions::COMMAND);
        $this->owningPlugin = $plugin;

        foreach ([
            new GiveSubCommand($plugin),
            new GiveAllSubCommand($plugin),
            new LootSubCommand($plugin),
            new ReloadSubCommand($plugin),
        ] as $sub) {
            $this->subCommands[] = $sub;
            $this->lookup[$sub->getName()] = $sub;
            foreach ($sub->getAliases() as $alias) {
                $this->lookup[$alias] = $sub;
            }
        }
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($sender)) {
            return;
        }

        $name = strtolower((string) array_shift($args));
        $sub = $this->lookup[$name] ?? null;
        if ($sub === null) {
            $this->sendHelp($sender, $commandLabel);
            return;
        }

        $sub->execute($sender, $args, $commandLabel);
    }

    private function sendHelp(CommandSender $sender, string $label): void
    {
        $messages = $this->plugin->getMessages();
        $messages->send($sender, 'help-header');
        foreach ($this->subCommands as $sub) {
            $messages->send($sender, 'help-entry', [
                'label' => $label,
                'usage' => trim($sub->getName() . ' ' . $sub->getUsage()),
                'description' => $sub->getDescription(),
            ]);
        }
    }
}