<?php

namespace ecstsy\essentialsx\Listeners;

use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Player\PlayerManager;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

class EventListener implements Listener {


    public function onLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        $list = Server::getInstance()->getNameBans();
        $ipList = Server::getInstance()->getIPBans();

        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }

        if ($list->isBanned($player->getName())) {
            $entry = $list->getEntry($player->getName());
            $reason = $entry->getReason();

            $player->kick(C::RED . "You are banned from this server.\n" . $reason);
        } elseif ($ipList->isBanned($player->getServer()->getIp())) {
            $entry = $ipList->getEntry($player->getServer()->getIp());
            $reason = $entry->getReason();

            $player->kick(C::RED . "You are banned from this server.\n" . $reason);
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $config = Utils::getConfiguration(Loader::getInstance(), "messages-eng.yml");
        $messages = $config->getNested("join.messages");

        $event->setJoinMessage(C::colorize(str_replace(["{nametag}", "{display_name}", "{name}"], [$player->getNameTag(), $player->getDisplayName(), $player->getName()], $config->getNested("join.connect", "&r&a{nametag} &ahas joined the server!"))));
        
        if ($messages !== null) {
            foreach ($messages as $message) {
                $player->sendMessage(C::colorize(str_replace(["{nametag}", "{display_name}", "{name}"], [$player->getNameTag(), $player->getDisplayName(), $player->getName()], $message)));
            }
        }

        PlayerManager::getInstance()->getSession($player)->setConnected(true);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $banList = Server::getInstance()->getNameBans();
        $bannedIpList = Server::getInstance()->getIPBans();
        $config = Utils::getConfiguration(Loader::getInstance(), "messages-eng.yml");

        if ($banList->isBanned($player->getName())) {
            $entry = $banList->getEntry($player->getName());
            $reason = $entry->getReason();

            $player->kick(C::colorize("&cYou are banned from this server.\n" . $reason));
        } elseif ($bannedIpList->isBanned($player->getServer()->getIp())) {
            $entry = $bannedIpList->getEntry($player->getServer()->getIp());
            $reason = $entry->getReason();

            $player->kick(C::colorize("&cYou are banned from this server.\n" . $reason));
        }

        PlayerManager::getInstance()->getSession($player)->setConnected(false);

        $event->setQuitMessage(C::colorize(str_replace(["{nametag}", "{display_name}", "{name}"], [$player->getNameTag(), $player->getDisplayName(), $player->getName()], $config->getNested("quit.disconnect", "&r&v{nametag} &chas left the server!"))));
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();
        $target = $event->getEntity();

        if ($damager instanceof Player) {
            if ($damager->isFlying()) {
                Utils::toggleFlight($damager, true);
            }
        }
        if ($target instanceof Player) {
            if ($target->isFlying()) {
                Utils::toggleFlight($target, true);
            }
        }
    }
}