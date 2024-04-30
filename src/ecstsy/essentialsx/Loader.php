<?php

namespace ecstsy\essentialsx;

use ecstsy\essentialsx\Commands\BanCommand;
use ecstsy\essentialsx\Commands\BanLookupCommand;
use ecstsy\essentialsx\Commands\ExpCommand;
use ecstsy\essentialsx\Commands\FeedCommand;
use ecstsy\essentialsx\Commands\FlyCommand;
use ecstsy\essentialsx\Commands\GamemodeCommand;
use ecstsy\essentialsx\Commands\HealCommand;
use ecstsy\essentialsx\Commands\IPBanCommand;
use ecstsy\essentialsx\Commands\ItemDBCommand;
use ecstsy\essentialsx\Commands\KitCommand;
use ecstsy\essentialsx\Commands\NearCommand;
use ecstsy\essentialsx\Commands\SpawnCommand;
use ecstsy\essentialsx\Listeners\EventListener;
use ecstsy\essentialsx\Player\PlayerManager;
use ecstsy\essentialsx\Utils\Queries;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {
    use SingletonTrait;

    public static DataConnector $connector;

    public static PlayerManager $playerManager;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $files = ["config.yml", "messages-eng.yml", "kits.yml"];

        foreach ($files as $file) {
            $this->saveResource($file);
        }

        foreach ($this->getResources() as $resource) {
            Utils::checkConfigVersion($resource);
        }
        
        $unregisteredCommands = ["ban", "ban-ip", "gamemode"];

        foreach ($unregisteredCommands as $command) {
            $this->getServer()->getCommandMap()->unregister(Server::getInstance()->getCommandMap()->getCommand($command));
        }

        $this->getServer()->getCommandMap()->registerAll("EssentialsX", [
            new BanCommand($this, "ban", "Add a player to the banlist."),
            new IPBanCommand($this, "ban-ip", "Add an IP to the banlist.", ["ipban"]),
            new BanLookupCommand($this, "banlookup", "Lookup a player in the banlist."),
            new ExpCommand($this, "exp", "View your experience", ["xp", "experience"]),
            new ExpCommand($this, "exp", "View your experience", ["xp", "experience"]),
            new HealCommand($this, "heal", "Restore your health"),
            new FeedCommand($this, "feed", "Restore your hunger"),
            new KitCommand($this, "kit", "View server kits", ["kits"]),
            new NearCommand($this, "near", "View nearby players within a specific radius"),
            new SpawnCommand($this, "spawn", "Teleports you the spawn of the world you're in"),
            new ItemDBCommand($this, "itemdb", "View the information of the item in hand"),
            new GamemodeCommand($this, "gamemode", "Change your gamemode", ["gm"]),
            new FlyCommand($this, "fly", "Allows the player to fly"),
        ]);

        $listeners = [new EventListener()];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->waitAll();

        self::$playerManager = new PlayerManager($this);
    }

    public function onDisable(): void {
        if (isset(self::$connector)) {
            self::$connector->close();
        }
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$playerManager;
    }
}
