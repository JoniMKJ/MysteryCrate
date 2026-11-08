<?php

declare(strict_types=1);

namespace mysterybox\animation;

use Closure;
use mysterybox\entity\RewardDisplay;
use mysterybox\loot\LootTier;
use mysterybox\MysteryBox;
use mysterybox\Settings;
use mysterybox\util\ColorPalette;
use mysterybox\util\ItemLabel;
use mysterybox\util\ItemUtil;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\World;

final class OpeningAnimation extends Task
{
    private readonly Settings $settings;
    private readonly Location $origin;
    private readonly World $world;

    /** @var list<RewardSlot> */
    private array $slots = [];
    private ?FloatingLabel $label = null;

    private float $angle = 0.0;
    private int $step = 0;
    private int $revealedCount = 0;
    private bool $finished = false;

    /**
     * @param list<Item> $commonRewards
     * @param list<Item> $exclusiveRewards
     * @param Closure(): void $onFinish
     */
    public function __construct(
        private readonly MysteryBox $plugin,
        private readonly Player $player,
        private readonly array $commonRewards,
        private readonly array $exclusiveRewards,
        private readonly Closure $onFinish
    ) {
        $this->settings = $plugin->getSettings();
        $this->origin = $player->getLocation();
        $this->world = $this->origin->getWorld();
    }

    public function start(): void
    {
        $s = $this->settings;
        $messages = $this->plugin->getMessages();

        $exclusiveOrbit = count($this->exclusiveRewards) === 1 ? Orbit::CENTER : Orbit::INNER;
        $commons = $this->buildSlots(LootTier::COMMON, $this->commonRewards, Orbit::OUTER);
        $exclusives = $this->buildSlots(LootTier::EXCLUSIVE, $this->exclusiveRewards, $exclusiveOrbit);

        $order = $s->revealExclusiveLast
            ? array_merge($commons, $exclusives)
            : array_merge($exclusives, $commons);
        $revealStart = $s->spinSteps + $s->pauseSteps;
        foreach ($order as $i => $slot) {
            $slot->revealStep = $revealStart + $i * $s->revealGapSteps;
        }

        $this->label = new FloatingLabel(
            new Position($this->origin->x, $this->origin->y + $s->textHeight, $this->origin->z, $this->world),
            $messages->get('floating-title'),
            $messages->get('floating-subtitle')
        );
        $this->label->show();

        $this->plugin->getScheduler()->scheduleRepeatingTask($this, $s->intervalTicks);
    }

    public function onRun(): void
    {
        if ($this->finished) {
            return;
        }

        try {
            $this->advance();
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->logException($e);
            $this->finish();
        }
    }

    public function finish(): void
    {
        if ($this->finished) {
            return;
        }
        $this->finished = true;

        $this->getHandler()?->cancel();
        $this->cleanup();
        $this->deliver();
        ($this->onFinish)();
    }

    public function abort(): void
    {
        if ($this->finished) {
            return;
        }
        $this->finished = true;

        $this->getHandler()?->cancel();
        $this->cleanup();
    }

    private function advance(): void
    {
        if (!$this->player->isConnected() || !$this->world->isLoaded()) {
            $this->finish();
            return;
        }

        if ($this->step < $this->settings->spinSteps) {
            $this->spin();
        } elseif ($this->step === $this->settings->spinSteps) {
            $this->lockRewards();
        }

        $this->revealDue();
        $this->step++;

        if ($this->revealedCount >= count($this->slots)) {
            $this->finish();
        }
    }

    private function spin(): void
    {
        $s = $this->settings;
        $this->angle += $s->rotationSpeed;
        $loot = $this->plugin->getLoot();

        foreach ($this->slots as $slot) {
            if ($slot->display->isClosed()) {
                continue;
            }

            if ($slot->orbit !== Orbit::CENTER) {
                $pos = $this->positionFor($slot->orbit, $slot->index, $slot->ringSize);
                $slot->display->teleport($pos);
                if ($s->particles) {
                    $this->sparkle($pos, 2);
                }
            }

            if ($this->step > 0 && ($this->step + $slot->index) % $s->cycleSteps === 0) {
                $next = $loot->table($slot->tier)->pick();
                if ($next !== null) {
                    $slot->display->showItem($next);
                }
            }
        }
    }

    private function lockRewards(): void
    {
        foreach ($this->slots as $slot) {
            if (!$slot->display->isClosed()) {
                $slot->display->showItem($slot->reward);
            }
        }
    }

    private function revealDue(): void
    {
        foreach ($this->slots as $slot) {
            if ($slot->revealed || $this->step < $slot->revealStep) {
                continue;
            }
            $slot->revealed = true;
            $this->revealedCount++;

            $pos = $slot->display->getPosition();
            if ($this->settings->sounds) {
                $this->world->addSound($pos, new XpCollectSound());
            }
            if ($this->settings->particles) {
                $this->sparkle($pos, 8);
                $this->world->addParticle($pos, new HappyVillagerParticle());
            }
            $slot->display->close();
        }
    }

    private function cleanup(): void
    {
        foreach ($this->slots as $slot) {
            if (!$slot->display->isClosed()) {
                $slot->display->close();
            }
        }
        $this->label?->hide();
    }

    private function deliver(): void
    {
        $dropped = false;
        foreach ($this->slots as $slot) {
            if (ItemUtil::give($this->player, $slot->reward)) {
                $dropped = true;
            }
        }

        if (!$this->player->isConnected()) {
            return;
        }

        $messages = $this->plugin->getMessages();
        $this->player->sendMessage(implode("\n", $this->buildSummary()));
        if ($dropped) {
            $messages->send($this->player, 'inventory-full');
        }
    }

    /** @return list<string> */
    private function buildSummary(): array
    {
        $messages = $this->plugin->getMessages();
        $lines = $messages->lines('result.header', ['player' => $this->player->getName()]);

        $sections = [];
        foreach ([LootTier::COMMON, LootTier::EXCLUSIVE] as $tier) {
            $entries = [];
            foreach ($this->slots as $slot) {
                if ($slot->tier === $tier) {
                    $entries[] = $messages->get('result.entry', [
                        'color' => ColorPalette::random(),
                        'item' => ItemLabel::withCount($slot->reward),
                    ]);
                }
            }
            if ($entries !== []) {
                $sections[] = array_merge([$messages->get('result.' . $tier->value . '-title')], $entries);
            }
        }

        foreach ($sections as $i => $section) {
            if ($i > 0) {
                $lines[] = ' ';
            }
            array_push($lines, ...$section);
        }

        array_push($lines, ...$messages->lines('result.footer'));
        return $lines;
    }

    /**
     * @param list<Item> $rewards
     * @return list<RewardSlot>
     */
    private function buildSlots(LootTier $tier, array $rewards, Orbit $orbit): array
    {
        $created = [];
        $size = count($rewards);

        foreach (array_values($rewards) as $index => $reward) {
            $initial = $this->plugin->getLoot()->table($tier)->pick() ?? $reward;
            $pos = $this->positionFor($orbit, $index, $size);

            $display = new RewardDisplay(
                new Location($pos->x, $pos->y, $pos->z, $this->world, 0.0, 0.0),
                $initial
            );
            $display->spawnToAll();

            $slot = new RewardSlot($tier, $reward, $display, $orbit, $index, $size);
            $this->slots[] = $slot;
            $created[] = $slot;
        }

        return $created;
    }

    private function positionFor(Orbit $orbit, int $index, int $size): Vector3
    {
        $o = $this->origin;
        $y = $o->y + $this->settings->height;

        if ($orbit === Orbit::CENTER) {
            return new Vector3($o->x, $y, $o->z);
        }

        [$base, $radius] = $orbit === Orbit::OUTER
            ? [$this->angle, $this->settings->radius]
            : [-$this->angle, $this->settings->innerRadius];

        $a = $base + (2 * M_PI * $index / max(1, $size));
        return new Vector3($o->x + $radius * cos($a), $y, $o->z + $radius * sin($a));
    }

    private function sparkle(Vector3 $center, int $amount): void
    {
        for ($i = 0; $i < $amount; $i++) {
            $this->world->addParticle(
                $center->add(self::rand() - 0.5, self::rand() + 0.2, self::rand() - 0.5),
                new CriticalParticle()
            );
        }
    }

    private static function rand(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}