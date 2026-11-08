<?php

declare(strict_types=1);

namespace mysterybox\loot;

use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;

final class LootRepository
{
    private LootTable $common;
    private LootTable $exclusive;

    public function __construct(
        private readonly string $file,
        private readonly \Logger $logger
    ) {
        $this->common = new LootTable(LootTier::COMMON);
        $this->exclusive = new LootTable(LootTier::EXCLUSIVE);
    }

    public function fileExists(): bool
    {
        return is_file($this->file);
    }

    public function table(LootTier $tier): LootTable
    {
        return match ($tier) {
            LootTier::COMMON => $this->common,
            LootTier::EXCLUSIVE => $this->exclusive,
        };
    }

    public function canOpen(int $commonCount, int $exclusiveCount): bool
    {
        if ($commonCount <= 0 && $exclusiveCount <= 0) {
            return false;
        }
        return ($commonCount <= 0 || !$this->common->isEmpty())
            && ($exclusiveCount <= 0 || !$this->exclusive->isEmpty());
    }

    public function load(): void
    {
        if (!$this->fileExists()) {
            return;
        }

        try {
            $raw = file_get_contents($this->file);
            if ($raw === false) {
                throw new \RuntimeException('Unable to read ' . $this->file);
            }

            $root = (new BigEndianNbtSerializer())->read($raw)->mustGetCompoundTag();
            $skipped = 0;
            foreach ([LootTier::COMMON, LootTier::EXCLUSIVE] as $tier) {
                $list = $root->getListTag($tier->value);
                $this->table($tier)->setItems($list === null ? [] : LootSerializer::fromList($list, $skipped));
            }
            if ($skipped > 0) {
                $this->logger->warning("Skipped $skipped invalid item(s) while loading the loot.");
            }
        } catch (\Throwable $e) {
            $backup = $this->file . '.corrupt-' . time();
            @rename($this->file, $backup);
            $this->logger->error('Could not read the loot file, moved it to ' . basename($backup) . ': ' . $e->getMessage());
        }
    }

    public function save(): void
    {
        try {
            $root = CompoundTag::create()
                ->setTag(LootTier::COMMON->value, LootSerializer::toList($this->common->getItems()))
                ->setTag(LootTier::EXCLUSIVE->value, LootSerializer::toList($this->exclusive->getItems()));

            $data = (new BigEndianNbtSerializer())->write(new TreeRoot($root));

            $tmp = $this->file . '.tmp';
            if (file_put_contents($tmp, $data) === false || !rename($tmp, $this->file)) {
                throw new \RuntimeException('Unable to write ' . $this->file);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Could not save the loot: ' . $e->getMessage());
        }
    }
}