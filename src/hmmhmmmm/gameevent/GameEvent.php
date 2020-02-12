<?php

namespace hmmhmmmm\gameevent;

use slapper\events\SlapperCreationEvent;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class GameEvent extends PluginBase implements Listener{
   private static $instance = null;
   private $prefix = null;
   private $data = null;
   public $array = [];
      
   public $pluginInfo = [
      "name" => "GameEvent",
      "version" => 1.0,
      "author" => "HmmHmmmm",
      "description" => "ปลั๊กอินนี้ทำแจก โปรดอย่าเอาไปขาย *หากจะเอาไปแจกต่อโปรดให้เครดิตด้วย*",
      "facebook" => "https://m.facebook.com/phonlakrit.knaongam.1",
      "youtube" => "https://m.youtube.com/channel/UCtjvLXDxDAUt-8CXV1eWevA",
      "github" => "https://github.com/HmmHmmmm"
   ];
   
   public static function getInstance(){
      return self::$instance;
   }
   public function onLoad(){
      self::$instance = $this;
      if($this->getServer()->getPluginManager()->getPlugin("Slapper") === null){
         $this->getLogger()->critical("§cกรุณาลงปลั๊กอิน Slapper");
         $this->getServer()->getPluginManager()->disablePlugin($this);
      }
   } 
   public function onEnable(){
      foreach($this->pluginInfo as $key => $value){
         $this->getServer()->getLogger()->notice($key." ".$value);
      }
      @mkdir($this->getDataFolder());
      $this->data = new Config($this->getDataFolder()."gameevent.yml", Config::YAML); 
      $this->prefix = "GameEvent";
      $this->getServer()->getPluginManager()->registerEvents($this, $this);
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameEventTask($this), 20);
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new SlapperUpdateTask($this), 20*3);
      $cmd = [
         new GameEventCommand($this)
      ];
      foreach($cmd as $command){
         $this->getServer()->getCommandMap()->register($command->getName(), $command);
      }
   }
   public function getPrefix(): string{
      return "§e[§b".$this->prefix."§e]§f";
   }
   public function getData(){
      return $this->data;
   }
   public function sendTime(int $second): string{
      $time = $second;
      $days = floor($time / (60 * 60 * 24));
      $time -= $days * (60 * 60 * 24);
      $hours = floor($time / (60 * 60));
      $time -= $hours * (60 * 60);
      $minutes = floor($time / 60);
      $time -= $minutes * 60;
      $seconds = floor($time);
      $time -= $seconds;    
      $ret_val = "";
      $msgDay = "ว.";
      $msgHours = "ชม.";
      $msgMinutes = "น.";
      $msgSeconds = "วิ.";
      if($days > 0){
         if($ret_val == ""){
            $ret_val = $days." ".$msgDay;
         }else{
            $ret_val = $ret_val." ".$days." ".$msgDay;
         }
      }
      if($hours > 0 || $days > 0){
         if($ret_val == ""){
            $ret_val = $hours." ".$msgHours;
         }else{
            $ret_val = $ret_val." ".$hours." ".$msgHours;
         }
      }
      if($minutes > 0 || $hours > 0 || $days > 0){
         if($ret_val == ""){
            $ret_val = $minutes." ".$msgMinutes;
         }else{
            $ret_val = $ret_val." ".$minutes." ".$msgMinutes;
         }
      }
      if($seconds > 0 || $minutes > 0 || $hours > 0 || $days > 0){
         if($ret_val == ""){
            $ret_val = $seconds." ".$msgSeconds;
         }else{
            $ret_val = $ret_val." ".$seconds." ".$msgSeconds;
         }
      }
      return $ret_val;
   }
   private function makeSlapperNBT(string $type, Player $player, string $cmd): CompoundTag{
      $nbt = new CompoundTag;
      $nbt->Pos = new ListTag("Pos", [
         new DoubleTag(0, $player->getX()),
         new DoubleTag(1, $player->getY()),
         new DoubleTag(2, $player->getZ())
      ]);
      $nbt->Motion = new ListTag("Motion", [
         new DoubleTag(0, 0),
         new DoubleTag(1, 0),
         new DoubleTag(2, 0)
      ]);
      $nbt->Rotation = new ListTag("Rotation", [
         new FloatTag(0, $player->getYaw()),
         new FloatTag(1, $player->getPitch())
      ]);
      $nbt->Health = new ShortTag("Health", 1);
      $cmds = [new StringTag($cmd, $cmd)];
      $nbt->Commands = new CompoundTag("Commands", $cmds);
      $nbt->MenuName = new StringTag("MenuName", "");
      $nbt->SlapperVersion = new StringTag("SlapperVersion", "1.3.4");
      if($type === "Human"){
         $player->saveNBT();
         $nbt->Inventory = clone $player->namedtag->Inventory;
         $nbt->Skin = new CompoundTag("Skin", ["Data" => new StringTag("Data", $player->getSkinData()), "Name" => new StringTag("Name", $player->getSkinId())]);
      }
      return $nbt;
   }
   public function makeSlapper(Player $player): void{
      if(isset($this->array["slapper"][$player->getName()]["start"])){
         $nbt = $this->makeSlapperNBT("Human", $player, "gameevent start {player} ".$this->array["slapper"][$player->getName()]["start"]);
         $entity = Entity::createEntity("SlapperHuman", $player->getLevel(), $nbt);
         $entity->setNameTag("{gameevent_start}");
         $entity->setNameTagVisible(true);
         $entity->setNameTagAlwaysVisible(true);
         $entity->namedtag->gameevent = new StringTag("gameevent", $entity->getNameTag());
         $entity->spawnToAll();
         $player->sendMessage($this->getPrefix()." §aหุ่นกิจกรรมได้สร้างสำเร็จ!");
         unset($this->array["slapper"][$player->getName()]);
      }
      if(isset($this->array["slapper"][$player->getName()]["award"])){
         $nbt = $this->makeSlapperNBT("Human", $player, "gameevent award {player} ".$this->array["slapper"][$player->getName()]["award"]);
         $entity = Entity::createEntity("SlapperHuman", $player->getLevel(), $nbt);
         $entity->setNameTag("{gameevent_award}");
         $entity->setNameTagVisible(true);
         $entity->setNameTagAlwaysVisible(true);
         $entity->namedtag->gameevent = new StringTag("gameevent", $entity->getNameTag());
         $entity->spawnToAll();
         $player->sendMessage($this->getPrefix()." §aหุ่นกิจกรรมได้สร้างสำเร็จ!");
         unset($this->array["slapper"][$player->getName()]);
      }
   }
   public function onSlapperUpdate(): void{
      foreach($this->getServer()->getLevels() as $level){
         foreach($level->getEntities() as $entity){                     
            if(isset($entity->namedtag->gameevent)){
               $tag = $this->formatText($entity->namedtag->gameevent->getValue());
               $entity->setNameTag($tag);
            }
         }
      }
   }
   public function formatText($text): string{
      $text = str_replace("{gameevent_start}", $this->getInfoEvent(), $text);
      $text = str_replace("{gameevent_award}", $this->getPrefix()."\nคลิกเพื่อรับรางวัล", $text);
      return $text;
   }
   public function onSlapperCreation(SlapperCreationEvent $event) {
      $entity = $event->getEntity();
      $entity->namedtag->gameevent = new StringTag("gameevent", $entity->getNameTag());
      $this->onSlapperUpdate();
   }
   public function onPlayerChat(PlayerChatEvent $event){
      $player = $event->getPlayer();
      $message = $event->getMessage();
      if(isset($this->array["create"][$player->getName()])){
         switch($this->array["create"][$player->getName()]["chatpage"]){
            case "command-start":
               $event->setCancelled(true);
               $this->array["create"][$player->getName()]["command-start"] = $message;
               $this->array["create"][$player->getName()]["chatpage"] = "command-award";
               $player->sendMessage($this->getPrefix()." คำสั่งสตาร์ท §b".$message);
               $player->sendMessage($this->getPrefix()." กรุณาพิมคำสั่งรางวัล");
               break;
            case "command-award":
               $event->setCancelled(true);
               $name = $this->array["create"][$player->getName()]["name"];
               $time = $this->array["create"][$player->getName()]["time"];
               $info = $this->array["create"][$player->getName()]["info"];
               $infoAward = $this->array["create"][$player->getName()]["info-award"];
               $cmdStart = $this->array["create"][$player->getName()]["command-start"];
               $cmdAward = $message;
               $player->sendMessage($this->getPrefix()." คำสั่งรางวัล §d".$cmdAward);
               $this->createEvent($name, $time, $info, $infoAward, $cmdStart, $cmdAward);
               $player->sendMessage($this->getPrefix()." §aกิจกรรม ".$name." ได้สร้างสำเร็จ!");
               unset($this->array["create"][$player->getName()]);
               break;
            default:
               $player->sendMessage($this->getPrefix()." §cกิจกรรม ".$this->array["create"][$player->getName()]["name"]." จะไม่ถูกสร้างเพราะไม่พบ ".$this->array["create"][$player->getName()]["chatpage"]);
               unset($this->array["create"][$player->getName()]);
               break;
         }
      }
   }
   public function onPlayerInteract(PlayerInteractEvent $event){
      $player = $event->getPlayer();
      $block = $event->getBlock();
      $tile = $player->getLevel()->getTile($block);
      $eventData = $this->getData();
      $data = $eventData->getAll();
      
      if(isset($this->array["sign"][$player->getName()])){
         if($tile instanceof Sign){
            $tile->setText("[กิจกรรม]", "", "", "");
            $player->sendMessage($this->getPrefix()." §aป้ายกิจกรรมได้สร้างสำเร็จ!");
            unset($this->array["sign"][$player->getName()]);
         }else{
            $player->sendMessage($this->getPrefix()." §cกรุณาคลิกที่ป้าย");
         }
      }
      if($tile instanceof Sign){
         $text = $tile->getText();
         if(TF::clean($text[0]) == "[กิจกรรม]"){
            if(isset($data["event"]) && isset($data["event-present"])){
               if(count($data["event"]) == 0){
                  $player->sendMessage($this->getPrefix()." §cไม่มีกิจกรรม");
                  return;
               }
               $event = $data["event-present"];
               if(!isset($data["event"][$event])){
                  $player->sendMessage($this->getPrefix()." §cไม่มีกิจกรรม");
                  return;
               }
               if($data["event"][$event]["enabled"]){
                  $this->onPlayerStartEvent($player, $event);
               }else{
                  $player->sendMessage($this->getPrefix()." §cกิจกรรมได้จบลงแล้ว");
               }
            }else{
               $player->sendMessage($this->getPrefix()." §cไม่มีกิจกรรม");
            }
         }
      }
   }
   public function getEvent(){
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return array_keys($data["event"]);
   }
   public function isEvent(string $name): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return isset($data["event"][$name]);
   }
   public function createEvent(string $name, int $time, string $info, string $infoAward, string $cmdStart, string $cmdAward): void{
      if(!$this->isPresentEvent()){
         $this->setPresentEvent($name);
      }
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["enabled"] = false;
      $data["event"][$name]["settime"] = $time;
      $data["event"][$name]["time"] = $time;
      $data["event"][$name]["info"] = $info;
      $data["event"][$name]["info-award"] = $infoAward;
      $data["event"][$name]["command-start"] = $cmdStart;
      $data["event"][$name]["command-award"] = $cmdAward;
      $data["event"][$name]["playerWin"] = [];
      $eventData->setAll($data);
      $eventData->save();
   }
   public function removeEvent(string $name): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      unset($data["event"][$name]);
      $eventData->setAll($data);
      $eventData->save();
   }
   public function getTimeEvent(string $name): int{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event"][$name]["time"];
   }
   public function setTimeEvent(string $name, int $time): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["time"] = $time;
      $eventData->setAll($data);
      $eventData->save();  
   }
   public function isPresentEvent(): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return isset($data["event-present"]);
   }
   public function getPresentEvent(): string{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event-present"];
   }
   public function setPresentEvent(string $name): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event-present"] = $name;
      $eventData->setAll($data);
      $eventData->save();
   }
   public function isNextEvent(): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return isset($data["event-next"]);
   }
   public function setNextEvent(string $name): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event-next"] = $name;
      $eventData->setAll($data);
      $eventData->save();
   }
   public function removeNextEvent(): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      unset($data["event-next"]);
      $eventData->setAll($data);
      $eventData->save();
   }
   public function getEnabledEvent(string $name): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event"][$name]["enabled"];
   }
   public function setEnabledEvent(string $name, bool $enabled = false): void{
      $this->getServer()->broadcastMessage($this->getPrefix()." กิจกรรรม ".($enabled ? "§a".$name." ได้เริ่มแล้ว" : "§c".$name." ได้จบลงแล้ว"));
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["enabled"] = $enabled;
      $eventData->setAll($data);
      $eventData->save();  
   }
   public function getInfoEvent(): string{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(isset($data["event"]) && isset($data["event-present"])){
         if(count($data["event"]) == 0){
            return "ไม่มีกิจกรรม";
         }
         $name = $data["event-present"];
         if(!isset($data["event"][$name])){
            return "ไม่มีกิจกรรม";
         }
         if($data["event"][$name]["enabled"]){
            $text = $this->getPrefix()."\n§fกิจกรรม ".$name."\nตอนนี้ §aเปิดอยู่\n§fเหลือเวลาอีก §e".$this->sendTime($data["event"][$name]["time"])."\n§fรางวัล §d".$data["event"][$name]["info-award"]; 
         }else{
            $text = $this->getPrefix()."\n§fกิจกรรม ".$name."\nตอนนี้ §cปิดอยู่";
         }
         return $text;
      }else{
         return "ไม่มีกิจกรรม";
      }
   }
   public function onPlayerStartEvent(Player $player, string $event): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(in_array(strtolower($player->getName()), $data["event"][$event]["playerWin"])){
         $player->sendMessage($this->getPrefix()." §cคุณได้ทำกิจกรรมนี้แล้ว");
         return;
      }
      $command = str_replace("{player}", $player->getName(), $data["event"][$event]["command-start"]);
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
      $player->sendMessage($this->getPrefix()." ยินดีต้อนรับเข้าสู่กิจกรรม ".$event." จะจบในอีก §b".$this->sendTime($data["event"][$event]["time"])." §fเมื่อคุณทำกิจกรรมสำเร็จคุณจะได้รับรางวัล §d".$data["event"][$event]["info-award"]);
   }
   public function onPlayerAwardEvent(Player $player, string $event): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(in_array(strtolower($player->getName()), $data["event"][$event]["playerWin"])){
         $player->sendMessage($this->getPrefix()." §cคุณได้ทำกิจกรรมนี้แล้ว");
         return;
      }
      $data["event"][$event]["playerWin"][] = strtolower($player->getName());
      $eventData->setAll($data);
      $eventData->save();
      $command = str_replace("{player}", $player->getName(), $data["event"][$event]["command-award"]);
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
      $this->getServer()->broadcastMessage($this->getPrefix()." ผู้เล่น ".$player->getName()." ได้ทำกิจกรรม ".$event." สำเร็จและได้รับรางวัล §b".$data["event"][$event]["info-award"]);
   }
   
   public function onNextEvent(): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $event = $data["event-next"];
      $this->setPresentEvent($event);
      $this->setEnabledEvent($event, true);
      $this->removeNextEvent();
   }
   public function runSignEvent(): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      foreach($this->getServer()->getOnlinePlayers() as $player){
         $level = $player->getLevel();
         $tiles = $level->getTiles();
         foreach($tiles as $sign){
            if($sign instanceof Sign){
               $text = $sign->getText();
               if(TF::clean($text[0]) == "[กิจกรรม]"){
                  if(isset($data["event"]) && isset($data["event-present"])){
                     if(count($data["event"]) == 0){
                        $sign->setText("§f[§eกิจกรรม§f]", "§cไม่มีกิจกรรม", "", "");
                     }
                     $event = $data["event-present"];
                     if(!isset($data["event"][$event])){
                        $sign->setText("§f[§eกิจกรรม§f]", "§cไม่มีกิจกรรม", "", "");
                        return;
                     }
                     if($data["event"][$event]["enabled"]){
                        $time = "§fเหลือ §a".$this->sendTime($data["event"][$event]["time"]);
                     }else{
                        $time = "§cกิจกรรมได้จบลงแล้ว";
                     }
                     $awardInfo = "§fรางวัล §b".$data["event"][$event]["info-award"];
                     $eventStart = "§eคลิ้กเพื่อทำกิจกรรม";
                     $sign->setText("§f[§eกิจกรรม§f]", $awardInfo, $time, $eventStart);
                  }else{
                     $sign->setText("§f[§eกิจกรรม§f]", "§cไม่มีกิจกรรม", "", "");
                  }
               }
            }
         }
      }
   }
   public function runEvent(): void{
      $eventData = $this->getData();
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
            $eventNew = "§fและจะเริ่มกิจกรรมใหม่ §a".$data["event-next"];
         }else{
            $eventNew = "";
         }
         if($data["event"][$event]["enabled"]){
            $data["event"][$event]["time"]--;
            if($data["event"][$event]["time"] <= 10){
               $this->getServer()->broadcastMessage($this->getPrefix()." กิจกรรม ".$event." จะจบลงในอีก §b".$this->sendTime($data["event"][$event]["time"])." ".$eventNew);
            }
            if($data["event"][$event]["time"] <= 0){
               $data["event"][$event]["time"] = $data["event"][$event]["settime"];
               $data["event"][$event]["playerWin"] = [];
               $data["event"][$event]["enabled"] = false;
               $this->getServer()->broadcastMessage($this->getPrefix()." กิจกรรม ".$event." §cได้จบลงแล้ว");
               if(isset($data["event-next"])){
                  $this->array["onNextEvent"] = true;
               }
            }
            $eventData->setAll($data);
            $eventData->save();
         }
         if(isset($this->array["onNextEvent"])){
            $this->onNextEvent();
            unset($this->array["onNextEvent"]);
         }
      }
   }
}