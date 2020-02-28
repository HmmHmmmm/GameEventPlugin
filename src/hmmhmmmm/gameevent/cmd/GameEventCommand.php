<?php

namespace hmmhmmmm\gameevent\cmd;

use hmmhmmmm\gameevent\GameEvent;
use hmmhmmmm\gameevent\object\CreateEvent;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

class GameEventCommand extends Command implements PluginIdentifiableCommand{
   private $plugin;
   public function __construct(GameEvent $plugin){
      parent::__construct("gameevent");
      $this->plugin = $plugin;
      $this->setPermission("gameevent.command");
   }
   public function getPlugin(): Plugin{
      return $this->plugin;
   }
   public function sendConsoleError(CommandSender $sender): void{
      $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.consoleError"));
   }
   public function sendPermissionError(CommandSender $sender): void{
      $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.permissionError"));
   }
   public function getPrefix(): string{
      return $this->getPlugin()->getPrefix();
   }
   public function sendHelp(CommandSender $sender): void{
      $sender->sendMessage($this->getPrefix()." : §fCommand");
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.info.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.info.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.list.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.list.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperstart.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperstart.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperaward.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperaward.description"));
      $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.welcome.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.welcome.description"));
   }
   public function execute(CommandSender $sender, $commandLabel, array $args){
      if(!$this->testPermission($sender)){
         return true;
      }
      
      if(empty($args)){
         $this->getPlugin()->getForm()->Menu($sender);
         $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.sendHelp.empty"));
         return true;
      }
      $sub = array_shift($args);            
      if(isset($sub)){
         switch($sub){
            case "help":
               $this->sendHelp($sender);
               break;
            case "info":
               $sender->sendMessage($this->getPlugin()->getPluginInfo());
               break;
            case "create":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 4){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if($this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.error2", [$name]));
                  return true;
               }
               $time = (int) array_shift($args);
               if(!is_numeric($time)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.error3"));
                  return true;
               }
               $info = array_shift($args);
               $infoAward = array_shift($args);
               $object = [
                  "name" => $name,
                  "time" => $time,
                  "info" => $info,
                  "infoAward" => $infoAward,
                  "chatPage" => "command-start",
                  "command-start" => "?",
                  "command-award" => "?"
               ];
               $this->getPlugin()->array["createObject"][$sender->getName()] = new CreateEvent($object);
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.create.complete"));
               break;
            case "list":
               $eventData = $this->getPlugin()->getData()->getAll();
               if(!isset($eventData["event"]) && count($eventData["event"]) == 0){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.list.error1"));
                  return true;
               }
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.list.complete", [implode(", ", array_keys($eventData["event"]))]));
               break;
            case "remove":
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.error2", [$name]));
                  return true;
               }
               $this->getPlugin()->removeEvent($name);
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.complete", [$name]));
               break;
            case "settime":
               if(count($args) < 2){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.error2", [$name]));
                  return true;
               }
               $time = (int) array_shift($args);
               if(!is_numeric($time)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.error3"));
                  return true;
               }
               $this->getPlugin()->setTimeEvent($name, $time);
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.complete", [$name, $this->getPlugin()->sendTime($time)]));
               break;
            case "setpresent":
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.error2", [$name]));
                  return true;
               }
               $this->getPlugin()->setPresentEvent($name);
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.complete", [$name]));
               break;
            case "setnext":
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.error2", [$name]));
                  return true;
               }
               $this->getPlugin()->setNextEvent($name);
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.complete", [$name]));
               break;
            case "enabled":
               if(count($args) < 2){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.usage")]));
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.error2", [$name]));
                  return true;
               }
               
               $enabled = array_shift($args);
               switch($enabled){
                  case "on":
                     if($this->getPlugin()->getPresentEvent($name) !== $name){
                        $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.error3", [$name]));
                        return true;
                     }
                     $this->getPlugin()->setEnabledEvent($name, true);
                     break;
                  case "off":
                     $this->getPlugin()->setEnabledEvent($name, false);
                     break;
                  default:
                     $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.usage")]));
                     break;
               }
               break;
            case "start":
               if(count($args) < 2){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.usage")]));
                  return true;
               }
               $player = $this->getPlugin()->getServer()->getPlayer(array_shift($args));
               if($player === null){
			      $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.error2"));
			      return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.error3", [$event]));
                  return true;
               }
               if($this->getPlugin()->getEnabledEvent($event)){
                  $this->getPlugin()->onPlayerStartEvent($player, $event);
               }else{
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.start.error4"));
               }
               break;
            case "award":
               if(count($args) < 2){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.usage")]));
                  return true;
               }
               $player = $this->getPlugin()->getServer()->getPlayer(array_shift($args));
               if($player === null){
			      $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.error2"));
			      return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.error3", [$event]));
                  return true;
               }
               if($this->getPlugin()->getEnabledEvent($event)){
                  $this->getPlugin()->onPlayerAwardEvent($player, $event);
               }else{
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.award.error4"));
               }
               break;
            case "sign_start":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.usage")]));
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.error2", [$event]));
                  return true;
               }
               $this->getPlugin()->array["sign"][$sender->getName()]["start"] = $event;
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.complete"));
               break;
            case "sign_award":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.usage")]));
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.error2", [$event]));
                  return true;
               }
               $this->getPlugin()->array["sign"][$sender->getName()]["award"] = $event;
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.complete"));
               break;
            case "slapper_start":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperstart.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperstart.usage")]));
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperstart.error2", [$event]));
                  return true;
               }
               $this->getPlugin()->array["slapper"][$sender->getName()]["start"] = $event;
               $this->getPlugin()->makeSlapper($sender);
               break;
            case "slapper_award":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperaward.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperaward.usage")]));
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.slapperaward.error2", [$event]));
                  return true;
               }
               $this->getPlugin()->array["slapper"][$sender->getName()]["award"] = $event;
               $this->getPlugin()->makeSlapper($sender);
               break;
            case "welcome":
               if(count($args) < 1){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("gameevent.command.welcome.error1", [$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.welcome.usage")]));
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.welcome.error2", [$event]));
                  return true;
               }
               $this->getPlugin()->setMessageWelcomeEvent($event, !$this->getPlugin()->getMessageWelcomeEvent($event));
               if($this->getPlugin()->getMessageWelcomeEvent($event)){
                  $sender->sendMessage($this->getPrefix()." ".$this->plugin->getLanguage()->getTranslate("gameevent.command.welcome.on", [$event]));
               }else{
                  $player->sendMessage($this->getPrefix()." ".$this->plugin->getLanguage()->getTranslate("gameevent.command.welcome.off", [$event]));
               }
               break;
            default:
               $this->sendHelp($sender);
               break;
         }
      }
      return true;
   }
}