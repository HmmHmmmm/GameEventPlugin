<?php

namespace hmmhmmmm\gameevent\scheduler;

use hmmhmmmm\gameevent\GameEvent;
use slapper\entities\SlapperEntity;
use slapper\entities\SlapperHuman;

use pocketmine\scheduler\Task;

class SlapperUpdateTask extends Task{
   private $plugin;
   public function __construct(GameEvent $plugin){
      $this->plugin = $plugin;
   }
   public function getPlugin(): GameEvent{
      return $this->plugin;
   }
   public function onRun(int $currentTick): void{
      $this->onSlapperUpdate();
   }
   public function onSlapperUpdate(): void{
      $data = $this->plugin->getData()->getAll();
      foreach($this->plugin->getServer()->getLevels() as $level){
         foreach($level->getEntities() as $entity){
            if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
               if($this->plugin->getCountEvent() == 0){
                  return;
               }
               foreach($this->plugin->getEvent() as $eventName){
	              if(!empty($entity->namedtag->getString("slapper_GameEventStart".$eventName, ""))){
                     $entity->setNameTag($this->plugin->getInfoStartEvent($eventName));
                  }
                  if(!empty($entity->namedtag->getString("slapper_GameEventAward".$eventName, ""))){
                     $entity->setNameTag($this->plugin->getInfoAwardEvent($eventName));
                  }
               }
            }
         }
      }
   }
   
}