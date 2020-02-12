<?php

namespace hmmhmmmm\gameevent;

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
      $this->setPermission("gameevent.command.gameevent");
   }
   public function getPlugin(): Plugin{
      return $this->plugin;
   }
   public function sendConsoleError(CommandSender $sender): void{
      $sender->sendMessage("§cขออภัย: คำสั่งสามารถพิมพ์ได้เฉพาะในเกมส์");
   }
   public function sendPermissionError(CommandSender $sender): void{
      $sender->sendMessage("§cขออภัย: คุณไม่สามารถพิมพ์คำสั่งนี้ได้");
   }
   public function getPrefix(): string{
      return $this->getPlugin()->getPrefix();
   }
   public function sendHelp(CommandSender $sender): void{
      $sender->sendMessage($this->getPrefix()." : §fCommand");
      $sender->sendMessage("§a/gameevent info : §fเครดิตผู้สร้างปลั๊กอิน");
      $sender->sendMessage("§a/gameevent create <ชื่อกิจกรรม> <เวลา> <ข้อความอธิบาย> <ข้อความรางวัล> : §fสร้างกิจกรรม");
      $sender->sendMessage("§a/gameevent list : §fดูรายชื่อกิจกรรม");
      $sender->sendMessage("§a/gameevent remove <ชื่อกิจกรรม> : §fลบกิจกรรม");
      $sender->sendMessage("§a/gameevent settime <ชื่อกิจกรรม> <เวลา> : §fเช็ตเวลากิจกรรม");
      $sender->sendMessage("§a/gameevent setpresent <ชื่อกิจกรรม> : §fเช็ตเป็นกิจกรรมปัจจุบัน");
      $sender->sendMessage("§a/gameevent setnext <ชื่อกิจกรรม> : §fเช็ตเป็นกิจกรรมถัดไป");
      $sender->sendMessage("§a/gameevent enabled <ชื่อกิจกรรม> on|off : §fเปิด/ปิดกิจกรรม");
      $sender->sendMessage("§a/gameevent start <ชื่อผู้เล่น> <ชื่อกิจกรรม> : §fให้ผู้เล่นเริ่มทำกิจกรรม");
      $sender->sendMessage("§a/gameevent award <ชื่อผู้เล่น> <ชื่อกิจกรรม> : §fเพิ่มผู้เล่นรับรางวัลกิจกรรม");
      $sender->sendMessage("§a/gameevent sign_start : §fสร้างป้ายเริ่มกิจกรรม");
      $sender->sendMessage("§a/gameevent slapper_start : §fสร้างหุ่นเริ่มกิจกรรม");
      $sender->sendMessage("§a/gameevent slapper_award <ชื่อกิจกรรม> : §fสร้างหุ่นกิจกรรมรับรางวัล");
   }
   public function execute(CommandSender $sender, $commandLabel, array $args){
      if(!$this->testPermission($sender)){
         return true;
      }
      
      if(empty($args)){
         $this->sendHelp($sender);
         return true;
      }
      $sub = array_shift($args);            
      if(isset($sub)){
         switch($sub){
            case "info":
               foreach($this->getPlugin()->pluginInfo as $key => $value){
                  $sender->sendMessage($key." ".$value);
               }
               break;
            case "create":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 4){
                  $sender->sendMessage("§cลอง: /gameevent create <ชื่อกิจกรรม> <เวลา> <ข้อความอธิบาย> <ข้อความรางวัล>");
                  return true;
               }
               $name = array_shift($args);
               if($this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cกิจกรรม ".$name." มีอยู่แล้ว");
                  return true;
               }
               $time = (int) array_shift($args);
               if(!is_numeric($time)){
                  $sender->sendMessage($this->getPrefix()." §c<เวลา> กรุณาเขียนให้เป็นตัวเลข");
                  return true;
               }
               $info = array_shift($args);
               $infoAward = array_shift($args);
               $this->getPlugin()->array["create"][$sender->getName()]["name"] = $name;
               $this->getPlugin()->array["create"][$sender->getName()]["time"] = $time;
               $this->getPlugin()->array["create"][$sender->getName()]["info"] = $info;
               $this->getPlugin()->array["create"][$sender->getName()]["info-award"] = $infoAward;
               $this->getPlugin()->array["create"][$sender->getName()]["chatpage"] = "command-start";
               $sender->sendMessage($this->getPrefix()." กรุณาพิมคำสั่งสตาร์ทในแชท");
               break;
            case "list":
               $eventData = $this->getPlugin()->getData()->getAll();
               if(!isset($eventData["event"]) && count($eventData["event"]) == 0){
                  $sender->sendMessage($this->getPrefix()." §cไม่มีรายชื่อกิจกรรม");
                  return true;
               }
               $sender->sendMessage($this->getPrefix()." รายชื่อกิจกรรมทั้งหมด §b".implode(", ", array_keys($eventData["event"])));
               break;
            case "remove":
               if(count($args) < 1){
                  $sender->sendMessage("§cลอง: /gameevent remove <ชื่อกิจกรรม>");
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$name);
                  return true;
               }
               $this->getPlugin()->removeEvent($name);
               $sender->sendMessage($this->getPrefix()." §aได้ลบกิจกรรม ".$name." สำเร็จ!");
               break;
            case "settime":
               if(count($args) < 2){
                  $sender->sendMessage("§cลอง: /gameevent settime <ชื่อกิจกรรม> <เวลา>");
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$name);
                  return true;
               }
               $time = (int) array_shift($args);
               if(!is_numeric($time)){
                  $sender->sendMessage($this->getPrefix()." §c<เวลา> กรุณาเขียนให้เป็นตัวเลข");
                  return true;
               }
               $this->getPlugin()->setTimeEvent($name, $time);
               $sender->sendMessage($this->getPrefix()." §aได้เช็ตเวลากิจกรรม ".$name." เป็น ".$this->getPlugin()->sendTime($time)." เรียบร้อย!");
               break;
            case "setpresent":
               if(count($args) < 1){
                  $sender->sendMessage("§cลอง: /gameevent setpresent <ชื่อกิจกรรม>");
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$name);
                  return true;
               }
               $this->getPlugin()->setPresentEvent($name);
               $sender->sendMessage($this->getPrefix()." §aได้เช็ต ".$name." เป็นกิจกรรมปัจจุบันเรียบร้อย!");
               break;
            case "setnext":
               if(count($args) < 1){
                  $sender->sendMessage("§cลอง: /gameevent setnext <ชื่อกิจกรรม>");
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$name);
                  return true;
               }
               $this->getPlugin()->setNextEvent($name);
               $sender->sendMessage($this->getPrefix()." §aได้เช็ต ".$name." เป็นกิจกรรมถัดไปเรียบร้อย!");
               break;
            case "enabled":
               if(count($args) < 2){
                  $sender->sendMessage("§cลอง: /gameevent enabled <ชื่อกิจกรรม> on|off");
                  return true;
               }
               $name = array_shift($args);
               if(!$this->getPlugin()->isEvent($name)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$name);
                  return true;
               }
               
               $enabled = array_shift($args);
               switch($enabled){
                  case "on":
                     if($this->getPlugin()->getPresentEvent($name) !== $name){
                        $sender->sendMessage($this->getPrefix()." §cคุณจะไม่สามารถเปิดกิจกรรมนี้ได้หากคุณไม่ได้เช็ตกิจกรรม ".$name." เป็นกิจกรรมปัจจุบัน");
                        return true;
                     }
                     $this->getPlugin()->setEnabledEvent($name, true);
                     break;
                  case "off":
                     $this->getPlugin()->setEnabledEvent($name, false);
                     break;
                  default:
                     $sender->sendMessage("§cลอง: /gameevent enabled <ชื่อกิจกรรม> on|off");
                     break;
               }
               break;
            case "start":
               if(count($args) < 2){
                  $sender->sendMessage("§cลอง: /gameevent start <ชื่อผู้เล่น> <ชื่อกิจกรรม>");
                  return true;
               }
               $player = $this->getPlugin()->getServer()->getPlayer(array_shift($args));
               if($player === null){
			      $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อผู้เล่นหรือผู้เล่นไม่ได้ออนไลน์");
			      return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$event);
                  return true;
               }
               if($this->getPlugin()->getEnabledEvent($event)){
                  $this->getPlugin()->onPlayerStartEvent($player, $event);
               }else{
                  $player->sendMessage($this->getPrefix()." §cกิจกรรมได้จบลงแล้ว");
               }
               break;
            case "award":
               if(count($args) < 2){
                  $sender->sendMessage("§cลอง: /gameevent award <ชื่อผู้เล่น> <ชื่อกิจกรรม>");
                  return true;
               }
               $player = $this->getPlugin()->getServer()->getPlayer(array_shift($args));
               if($player === null){
			      $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อผู้เล่นหรือผู้เล่นไม่ได้ออนไลน์");
			      return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$event);
                  return true;
               }
               if($this->getPlugin()->getEnabledEvent($event)){
                  $this->getPlugin()->onPlayerAwardEvent($player, $event);
               }else{
                  $player->sendMessage($this->getPrefix()." §cกิจกรรมได้จบลงแล้ว");
               }
               break;
            case "sign_start":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               $this->getPlugin()->array["sign"][$sender->getName()] = true;
               $sender->sendMessage($this->getPrefix()." กรุณาคลิกที่ป้ายเพื่อสร้าง");
               break;
            case "slapper_start":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               $event = $this->getPlugin()->getPresentEvent();
               $this->getPlugin()->array["slapper"][$sender->getName()]["start"] = $event;
               $this->getPlugin()->makeSlapper($sender);
               break;
            case "slapper_award":
               if(!$sender instanceof Player){
                  $this->sendConsoleError($sender);
                  return true;
               }
               if(count($args) < 1){
                  $sender->sendMessage("§cลอง: /gameevent slapper_award <ชื่อกิจกรรม>");
                  return true;
               }
               $event = array_shift($args);
               if(!$this->getPlugin()->isEvent($event)){
                  $sender->sendMessage($this->getPrefix()." §cไม่พบชื่อกิจกรรม ".$event);
                  return true;
               }
               $this->getPlugin()->array["slapper"][$sender->getName()]["award"] = $event;
               $this->getPlugin()->makeSlapper($sender);
               break;
            default:
               $this->sendHelp($sender);
               break;
         }
      }
      return true;
   }
}