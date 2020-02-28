<?php

namespace hmmhmmmm\gameevent\scheduler;

use hmmhmmmm\gameevent\GameEvent;

use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as TF;

class GameEventTask extends Task{
   private $plugin;
   public function __construct(GameEvent $plugin){
      $this->plugin = $plugin;
   }
   public function getPlugin(): GameEvent{
      return $this->plugin;
   }
   public function getPrefix(): string{
      return $this->getPlugin()->getPrefix();
   }
   public function onRun(int $currentTick): void{
      $this->onRunEvent();
      $this->onRunSignEvent();
   }
   public function onRunSignEvent(): void{
      $eventData = $this->plugin->getData();
      $data = $eventData->getAll();
      foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
         $level = $player->getLevel();
         $tiles = $level->getTiles();
         foreach($tiles as $sign){
            if($sign instanceof Sign){
               $text = $sign->getText();
               if($text[0] == $this->getPrefix()){
                  if(isset($data["event"]) && isset($data["event-present"])){
                     if(count($data["event"]) == 0){
                        $sign->setText($this->getPrefix(), $this->getPlugin()->getLanguage()->getTranslate("event.without"), "", "");
                     }
                     $event = TF::clean($text[1]);
                     $event = explode(" ", $event); 
                     $event = $event[1];
                     if(!isset($data["event"][$event])){
                        $sign->setText($this->getPrefix(), $this->getPlugin()->getLanguage()->getTranslate("event.notfound", [$event]), "", "");
                        return;
                     }
                     $eventName = $this->getPlugin()->getLanguage()->getTranslate("event.title")." §a".$event;
                     if($data["event"][$event]["enabled"]){
                        $time = $this->getPlugin()->getLanguage()->getTranslate("scheduler.runsignevent.sign.timeon", [$this->plugin->sendTime($data["event"][$event]["time"])]);
                     }else{
                        $time = $this->getPlugin()->getLanguage()->getTranslate("event.off");
                     }
                     $awardInfo = $this->getPlugin()->getLanguage()->getTranslate("scheduler.runsignevent.sign.award", [$data["event"][$event]["info-award"]]);
                     $sign->setText($this->getPrefix(), $eventName, $awardInfo, $time);
                  }else{
                     $sign->setText($this->getPrefix(), $this->getPlugin()->getLanguage()->getTranslate("event.without"), "", "");
                  }
               }
               if(TF::clean($text[0]) == $this->getPlugin()->getLanguage()->getTranslate("sign.award.noprefix")){
                  if(isset($data["event"]) && isset($data["event-present"])){
                     if(count($data["event"]) == 0){
                        $sign->setText($this->getPlugin()->getLanguage()->getTranslate("sign.award.prefix"), $this->getPlugin()->getLanguage()->getTranslate("event.without"), "", "");
                     }
                     $event = TF::clean($text[1]);
                     $event = explode(" ", $event); 
                     $event = $event[1];
                     if(!isset($data["event"][$event])){
                        $sign->setText($this->getPlugin()->getLanguage()->getTranslate("sign.award.prefix"), $this->getPlugin()->getLanguage()->getTranslate("event.notfound", [$event]), "", "");
                        return;
                     }
                     $eventName = $this->getPlugin()->getLanguage()->getTranslate("event.title")." §a".$event;
                     if($data["event"][$event]["enabled"]){
                        $time = $this->getPlugin()->getLanguage()->getTranslate("scheduler.runsignevent.sign.timeon", [$this->plugin->sendTime($data["event"][$event]["time"])]);
                     }else{
                        $time = $this->getPlugin()->getLanguage()->getTranslate("event.off");
                     }
                     $awardInfo = $this->getPlugin()->getLanguage()->getTranslate("scheduler.runsignevent.sign.award", [$data["event"][$event]["info-award"]]);
                     $sign->setText($this->getPlugin()->getLanguage()->getTranslate("sign.award.prefix"), $eventName, $awardInfo, $time);
                  }else{
                     $sign->setText($this->getPlugin()->getLanguage()->getTranslate("sign.award.prefix"), $this->getPlugin()->getLanguage()->getTranslate("event.without"), "", "");
                  }
               }
            }
         }
      }
   }
   public function onRunEvent(): void{
      $eventData = $this->plugin->getData();
      $data = $eventData->getAll();
      if(isset($data["event"]) && isset($data["event-present"])){
         if(count($data["event"]) == 0){
            return;
         }
         $event = $data["event-present"];
         if(!isset($data["event"][$event])){
            return;
         }
         if(isset($data["event-next"])){
            $eventNew = $this->getPlugin()->getLanguage()->getTranslate("scheduler.runevent.new", [$data["event-next"]]);
         }else{
            $eventNew = "";
         }
         if($data["event"][$event]["enabled"]){
            $data["event"][$event]["time"]--;
            if($data["event"][$event]["time"] <= 10){
               $this->plugin->getServer()->broadcastMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("scheduler.runevent.willend", [$event, $this->plugin->sendTime($data["event"][$event]["time"]), $eventNew]));
            }
            if($data["event"][$event]["time"] <= 0){
               $data["event"][$event]["time"] = $data["event"][$event]["settime"];
               $data["event"][$event]["playerWin"] = [];
               $data["event"][$event]["enabled"] = false;
               $this->plugin->getServer()->broadcastMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("scheduler.runevent.end", [$event]));
               if(isset($data["event-next"])){
                  $this->plugin->array["onNextEvent"] = true;
               }
            }
            $eventData->setAll($data);
            $eventData->save();
         }
         if(isset($this->plugin->array["onNextEvent"])){
            $this->plugin->onNextEvent();
            unset($this->plugin->array["onNextEvent"]);
         }
      }
   }
}