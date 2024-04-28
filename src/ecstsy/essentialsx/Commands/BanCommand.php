<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BanCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("essentialsx.ban");

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("reason", true));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $user = Utils::getPlayerByPrefix($args["name"]);
        $config = Utils::getConfiguration(Loader::getInstance(), "messages-eng.yml");
    
        if ($user !== null) {
            if (!$user->hasPermission("essentialsx.ban.exempt")) {
                if ($user->isOnline() && $sender->hasPermission("essentialsx.ban")) { // Check if the sender can ban online players
                    $reason = implode(" ", array_slice($args, 1)); // Join all arguments after the player name into a single string
                    $format = $config->get("ban.format");
                    $message = $config->get("ban.message");
    
                    Server::getInstance()->getNameBans()->addBan($user->getName(), $reason, null, $sender->getName());
                    $user->kick(TextFormat::colorize(str_replace(["{user}", "{player}", "{reason}"], [$user->getName(), $sender->getName(), $reason], $format)));
    
                    Server::getInstance()->getLogger()->info($sender->getName() . " has banned " . $user->getName() . " for " . $reason);
                    Server::getInstance()->broadcastMessage(TextFormat::colorize(str_replace(["{user}", "{player}", "{reason}"], [$user->getName(), $sender->getName(), $reason], $message)));
                } else {
                    $sender->sendMessage(TextFormat::RED . "You cannot ban offline players.");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "Player '" . $args["name"] . "' is exempt from bans.");
            }
        } else {
            $sender->sendMessage(TextFormat::RED . "Player '" . $args["name"] . "' does not exist or is offline.");
        }
    } 
    
}