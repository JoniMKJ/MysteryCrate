<?php

declare(strict_types=1);

namespace mysterybox;

use muqsit\invmenu\InvMenuHandler;
use mysterybox\command\MysteryBoxCommand;
use mysterybox\crate\CooldownManager;
use mysterybox\crate\CrateItemFactory;
use mysterybox\crate\OpeningManager;
use mysterybox\entity\RewardDisplay;
use mysterybox\listener\CrateListener;
use mysterybox\loot\LegacyLootImporter;
use mysterybox\loot\LootEditor;
use mysterybox\loot\LootRepository;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;

final class MysteryBox extends PluginBase
{
    private Settings $settings;
    private Messages $messages;
    private LootRepository $loot;
    private CooldownManager $cooldowns;
    private CrateItemFactory $crateFactory;
    private OpeningManager $openings;
    private LootEditor $editor;

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource('messages.yml');
        $this->reloadSettings();

        EntityFactory::getInstance()->register(
            RewardDisplay::class,
            static function (World $world, CompoundTag $nbt): Entity {
                throw new SavedDataLoadingException('RewardDisplay is not persistent');
            },
            ['mysterybox:reward_display']
        );

        $this->loot = new LootRepository($this->getDataFolder() . 'loot.nbt', $this->getLogger());
        $this->loot->load();
        if (!$this->loot->fileExists()) {
            LegacyLootImporter::importInto($this->loot, $this->legacyConfigPaths(), $this->getLogger());
        }

        $this->cooldowns = new CooldownManager();
        $this->crateFactory = new CrateItemFactory($this);
        $this->openings = new OpeningManager($this);
        $this->editor = new LootEditor($this);

        if (LootEditor::isAvailable()) {
            if (!InvMenuHandler::isRegistered()) {
                InvMenuHandler::register($this);
            }
        } else {
            $this->getLogger()->warning('InvMenu not found: the loot editor (/mysterycrate loot) is disabled.');
        }

        $this->getServer()->getCommandMap()->register('mysterybox', new MysteryBoxCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new CrateListener($this), $this);
    }

    protected function onDisable(): void
    {
        if (isset($this->openings)) {
            $this->openings->finishAll();
        }
        if (isset($this->loot)) {
            $this->loot->save();
        }
    }

    public function reloadSettings(): void
    {
        $this->reloadConfig();
        $this->settings = Settings::fromConfig($this->getConfig());
        $this->messages = new Messages(
            new Config($this->getDataFolder() . 'messages.yml', Config::YAML),
            $this->settings
        );
    }

    /** @return list<string> */
    private function legacyConfigPaths(): array
    {
        $plugins = dirname(rtrim($this->getDataFolder(), '/\\'));
        return [
            $plugins . DIRECTORY_SEPARATOR . 'MysteryCrate' . DIRECTORY_SEPARATOR . 'config.yml',
            $plugins . DIRECTORY_SEPARATOR . 'MysteryBox' . DIRECTORY_SEPARATOR . 'config.yml',
        ];
    }

    public function getSettings(): Settings { return $this->settings; }
    public function getMessages(): Messages { return $this->messages; }
    public function getLoot(): LootRepository { return $this->loot; }
    public function getCooldowns(): CooldownManager { return $this->cooldowns; }
    public function getCrateFactory(): CrateItemFactory { return $this->crateFactory; }
    public function getOpenings(): OpeningManager { return $this->openings; }
    public function getEditor(): LootEditor { return $this->editor; }
}