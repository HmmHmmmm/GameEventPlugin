<?php

namespace hmmhmmmm\gameevent;

use pocketmine\scheduler\Task;

class SlapperUpdateTask extends Task{
   private $plugin;
   public function __construct(GameEvent $plugin){
      $this->plugin = $plugin;
   }
   public function getPlugin(): GameEvent{
      return $this->plugin;
   }
   public function onRun($currentTick){
      $this->getPlugin()->onSlapperUpdate();
   }
}