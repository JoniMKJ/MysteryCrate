<?php

declare(strict_types=1);

namespace mysterybox\loot;

enum LootTier: string
{
    case COMMON = 'common';
    case EXCLUSIVE = 'exclusive';
}