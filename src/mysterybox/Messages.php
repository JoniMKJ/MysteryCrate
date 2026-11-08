<?php

declare(strict_types=1);

namespace mysterybox;

use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

final class Messages
{
    public function __construct(
        private readonly Config $config,
        private readonly Settings $settings
    ) {}

    /** @param array<string, scalar> $vars */
    public function get(string $key, array $vars = []): string
    {
        $raw = $this->config->getNested($key);
        return $this->format(is_string($raw) ? $raw : $key, $vars);
    }

    /**
     * @param array<string, scalar> $vars
     * @return list<string>
     */
    public function lines(string $key, array $vars = []): array
    {
        $raw = $this->config->getNested($key, []);
        if (!is_array($raw)) {
            $raw = [$raw];
        }
        return array_map(fn($line): string => $this->format((string) $line, $vars), array_values($raw));
    }

    /** @param array<string, scalar> $vars */
    public function send(CommandSender $to, string $key, array $vars = []): void
    {
        $to->sendMessage($this->get($key, $vars));
    }

    /** @param array<string, scalar> $vars */
    private function format(string $text, array $vars): string
    {
        $vars += [
            'prefix' => $this->settings->prefix,
            'box' => $this->settings->boxName,
            'store' => $this->settings->store,
        ];

        $replace = [];
        foreach ($vars as $name => $value) {
            $replace['{' . $name . '}'] = (string) $value;
        }

        return TextFormat::colorize(strtr($text, $replace));
    }
}