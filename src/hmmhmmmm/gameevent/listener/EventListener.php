<?php

namespace hmmhmmmm\gameevent\listener;

use hmmhmmmm\gameevent\GameEvent;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as TF;

class EventListener implements Listener{
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
   public function onPlayerChat(PlayerChatEvent $event): void{
      $player = $event->getPlayer();
      $message = $event->getMessage();
      if(isset($this->plugin->array["createObject"][$player->getName()])){
         switch($this->plugin->array["createObject"][$player->getName()]->getChatPage()){
            case "command-start":
               $event->setCancelled(true);
               $this->plugin->array["createObject"][$player->getName()]->setCommandStart($message);
               $this->plugin->array["createObject"][$player->getName()]->setChatPage("command-award");
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.commandstart1", [$message]));
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.commandstart2"));
               break;
            case "command-award":
               $event->setCancelled(true);
               $name = $this->plugin->array["createObject"][$player->getName()]->getName();
               $time = $this->plugin->array["createObject"][$player->getName()]->getTime();
               $info = $this->plugin->array["createObject"][$player->getName()]->getInfo();
               $infoAward = $this->plugin->array["createObject"][$player->getName()]->getInfoAward();
               $cmdStart = $this->plugin->array["createObject"][$player->getName()]->getCommandStart();
               $cmdAward = $message;
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.commandaward1", [$message]));
               $this->plugin->createEvent($name, $time, $info, $infoAward, $cmdStart, $cmdAward);
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.complete", [$name]));
               unset($this->plugin->array["createObject"][$player->getName()]);
               break;
            default:
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.error1", [$this->plugin->array["createObject"][$player->getName()]->getName(), $this->plugin->array["createObject"][$player->getName()]->getChatPage()]));
               unset($this->plugin->array["createObject"][$player->getName()]);
               break;
         }
      }
   }
   public function onPlayerInteract(PlayerInteractEvent $event): void{
      $player = $event->getPlayer();
      $block = $event->getBlock();
      $tile = $player->getLevel()->getTile($block);
      $eventData = $this->plugin->getData();
      $data = $eventData->getAll();
      
      if(isset($this->plugin->array["sign"][$player->getName()]["start"])){
         if($tile instanceof Sign){
            $name = $this->plugin->array["sign"][$player->getName()]["start"];
            $tile->setText($this->getPrefix(), $this->getPlugin()->getLanguage()->getTranslate("event.title")." ".$name, "", "");
            $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerinteract.create.signstart.complete"));
            unset($this->plugin->array["sign"][$player->getName()]);
         }else{
            $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerinteract.create.signstart.error1"));
         }
      }
      if(isset($this->plugin->array["sign"][$player->getName()]["award"])){
         if($tile instanceof Sign){
            $name = $this->plugin->array["sign"][$player->getName()]["award"];
            $tile->setText($this->getPlugin()->getLanguage()->getTranslate("sign.award.noprefix"), $this->getPlugin()->getLanguage()->getTranslate("event.title")." ".$name, "", "");
            $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerinteract.create.signaward.complete"));
            unset($this->plugin->array["sign"][$player->getName()]);
         }else{
            $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerinteract.create.signaward.error1"));
         }
      }
      if($tile instanceof Sign){
         $text = $tile->getText();
         if($text[0] == $this->getPrefix()){
            if(isset($data["event"]) && isset($data["event-present"])){
               if(count($data["event"]) == 0){
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.without"));
                  return;
               }
               $name = TF::clean($text[1]);
               $name = explode(" ", $name); 
               $name = $name[1];
               if(!isset($data["event"][$name])){
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.notfound", [$name]));
                  return;
               }
               if($data["event"][$name]["enabled"]){
                  $this->plugin->onPlayerStartEvent($player, $name);
               }else{
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.off"));
               }
            }else{
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.without"));
            }
         }
         if(TF::clean($text[0]) == $this->getPlugin()->getLanguage()->getTranslate("sign.award.noprefix")){
            if(isset($data["event"]) && isset($data["event-present"])){
               if(count($data["event"]) == 0){
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.without"));
                  return;
               }
               $name = TF::clean($text[1]);
               $name = explode(" ", $name); 
               $name = $name[1];
               if(!isset($data["event"][$name])){
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.notfound", [$name]));
                  return;
               }
               if($data["event"][$name]["enabled"]){
                  $this->plugin->onPlayerAwardEvent($player, $name);
               }else{
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.off"));
               }
            }else{
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("event.without"));
            }
         }
      }
   }
   
}