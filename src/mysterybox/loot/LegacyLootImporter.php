<?php

declare(strict_types=1);

namespace mysterybox\loot;

use pocketmine\utils\Config;

final class LegacyLootImporter
{
    private function __construct() {}

    /** @param list<string> $configPaths */
    public static function importInto(LootRepository $repository, array $configPaths, \Logger $logger): void
    {
        foreach ($configPaths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $config = new Config($path, Config::YAML);
            $skipped = 0;
            $common = self::decode((string) $config->get('common_loot', ''), $skipped);
            $exclusive = self::decode((string) $config->get('exclusive_loot', ''), $skipped);
            if ($common === [] && $exclusive === []) {
                continue;
            }

            $repository->table(LootTier::COMMON)->setItems($common);
            $repository->table(LootTier::EXCLUSIVE)->setItems($exclusive);
            $repository->save();

            $logger->notice(sprintf(
                'Imported legacy loot from %s (%d common, %d exclusive, %d skipped).',
                $path,
                count($common),
                count($exclusive),
                $skipped
            ));
            return;
        }
    }

    /** @return list<\pocketmine\item\Item> */
    private static function decode(string $base64, int &$skipped): array
    {
        $blob = base64_decode($base64, true);
        if ($blob === false || $blob === '') {
            return [];
        }
        try {
            return LootSerializer::fromLegacyBlob($blob, $skipped);
        } catch (\Throwable) {
            return [];
        }
    }
}