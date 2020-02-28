<?php

namespace hmmhmmmm\gameevent;

use pocketmine\Player;

interface GameEventAPI{

   public static function getInstance(): GameEvent;

   public function onPlayerAwardEvent(Player $player, string $event): void;
}