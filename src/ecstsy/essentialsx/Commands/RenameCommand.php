<?php

namespace ecstsy\essentialsx\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use ecstsy\essentialsx\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;

class RenameCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true);
       
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        else {
if (!$args == 0) {
  $sender->sendMessage(C::RED . "Item Cannot Be Named as Blank");
            return;
  
        }
else {
$item = $sender->GetItem()->GetItemInHand();

  if ($item === VanillaItems::AIR){

$sender->sendMessage(C::RED . "Item Cannot Be Found!");
            return;
  }
  else {
$item->setName($args[0]);
    $sender->sendMessage(C::GREEN . "Item Has Been Renamed to: " . $args[0]);
            return;
  }
}



        
        
    }

    public function getPermission(): string {
        return "essentialsx.default";
    }
}
