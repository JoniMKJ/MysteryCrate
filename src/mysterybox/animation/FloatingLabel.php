<?php

declare(strict_types=1);

namespace mysterybox\animation;

use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;

final class FloatingLabel
{
    private FloatingTextParticle $particle;

    public function __construct(
        private readonly Position $position,
        string $title,
        string $text
    ) {
        $this->particle = new FloatingTextParticle($text, $title);
    }

    public function show(): void
    {
        $this->position->getWorld()->addParticle($this->position, $this->particle);
    }

    public function hide(): void
    {
        $world = $this->position->getWorld();
        if ($world->isLoaded()) {
            $this->particle->setInvisible();
            $world->addParticle($this->position, $this->particle);
        }
    }
}