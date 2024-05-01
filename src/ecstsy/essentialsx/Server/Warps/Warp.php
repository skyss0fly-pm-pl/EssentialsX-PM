<?php

declare(strict_types=1);

namespace ecstsy\essentialsx\Server\Warps;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use Ramsey\Uuid\UuidInterface;

final class Warp
{


    public function __construct(
        public string        $warp_name,
        public string        $world_name,
        public int           $x,
        public int           $y,
        public int           $z,
    )
    {
    }

    /**
     * Get home's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->warp_name;
    }

    /**
     * Get the world of the home
     *
     * @return World|null
     */
    public function getWorld(): ?World
    {
        return Server::getInstance()->getWorldManager()->getWorldByName($this->world_name);
    }

    /**
     * Get the position of the home
     *
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return ($world = $this->getWorld()) === null ? null : (new Position($this->x, $this->y, $this->z, $world));
    }

    /**
     * Utility function to teleport player directly from the home call
     *
     * @param Player $player
     * @return void
     * @throws \RuntimeException
     */
    public function teleport(Player $player): void
    {
        if (($pos = $this->getPosition()) === null) {
            throw new \RuntimeException("The target world is not available for teleport. Perhaps the world isn't loaded?");
        }
        $player->teleport($pos);
    }
}