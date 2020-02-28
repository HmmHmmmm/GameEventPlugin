<?php

namespace hmmhmmmm\gameevent;

use hmmhmmmm\gameevent\cmd\GameEventCommand;
use hmmhmmmm\gameevent\data\Language;
use hmmhmmmm\gameevent\listener\EventListener;
use hmmhmmmm\gameevent\scheduler\GameEventTask;
use hmmhmmmm\gameevent\scheduler\SlapperUpdateTask;
use hmmhmmmm\gameevent\ui\Form;
use xenialdan\customui\API as FromAPI;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Config;

class GameEvent extends PluginBase implements GameEventAPI{
   /* @ GameEvent::class */
   private static $instance = null;
 
   private $prefix = "?";
   private $facebook = "§cwithout";
   private $youtube = "§cwithout";
   private $discord = "§cwithout";
  
   /* @ Language::class */
   private $language = null;
   /* @ Config */
   private $data = null;
   /* @ array[] */
   public $array = [];
   /* @ Form::class */
   private $form = null;
   /* @ Plugin */
   private $slapper = null;

   private $langClass = [
      "thai",
      "english"
   ];

   public static function getInstance(): GameEvent{
      return self::$instance;
   }
   public function onLoad(): void{
      self::$instance = $this;
   } 
   public function onEnable(): void{
      @mkdir($this->getDataFolder());
      @mkdir($this->getDataFolder()."language/");
      $this->saveDefaultConfig();
      $this->data = new Config($this->getDataFolder()."gameevent.yml", Config::YAML, array());
      $c = $this->data->getAll();
      if(!isset($c["event"])){
         $c["event"] = [];
         $this->data->setAll($c);
         $this->data->save();
      } 
      $this->prefix = "GameEvent";
      $this->facebook = "https://bit.ly/39ULjqk";
      $this->youtube = "https://bit.ly/2HL1j28";
      $this->discord = "https://discord.gg/n6CmNr";
      $this->form = new Form($this);
      $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
      $this->getScheduler()->scheduleRepeatingTask(new GameEventTask($this), 20);
      $this->getScheduler()->scheduleRepeatingTask(new SlapperUpdateTask($this), 20*3);
      $this->getServer()->getCommandMap()->register("GameEventPlugin", new GameEventCommand($this));
      $langConfig = $this->getConfig()->getNested("language");
      if(!in_array($langConfig, $this->langClass)){
         $this->getLogger()->error("§cNot found language ".$langConfig.", Please try ".implode(", ", $this->langClass));
         $this->getServer()->getPluginManager()->disablePlugin($this);
         return;
      }else{
         $this->language = new Language($this, $langConfig);
      }
      if($this->getServer()->getPluginManager()->getPlugin("Slapper") === null){
         $this->getLogger()->error($this->language->getTranslate("notfound.plugin", ["Slapper"]));
         $this->getServer()->getPluginManager()->disablePlugin($this);
         return;
      }else{
         $this->slapper = $this->getServer()->getPluginManager()->getPlugin("Slapper");
      }
      if(!class_exists(FromAPI::class)){
         $this->getLogger()->error($this->language->getTranslate("notfound.libraries", ["customui"]));
         $this->getServer()->getPluginManager()->disablePlugin($this);
         return;
      }
   }
   public function getPrefix(): string{
      return "§e[§b".$this->prefix."§e]§f";
   }
   public function getFacebook(): string{
      return $this->facebook;
   }
   public function getYoutube(): string{
      return $this->youtube;
   }
   public function getDiscord(): string{
      return $this->discord;
   }
   public function getLanguage(): Language{
      return $this->language;
   }
   public function getData(): Config{
      return $this->data;
   }
   public function getForm(): Form{
      return $this->form;
   }
   public function getPluginInfo(): string{
      $author = implode(", ", $this->getDescription()->getAuthors());
      $arrayText = [
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.name", [$this->getDescription()->getName()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.version", [$this->getDescription()->getVersion()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.author", [$author]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.description"),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.facebook", [$this->getFacebook()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.youtube", [$this->getYoutube()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.website", [$this->getDescription()->getWebsite()]),
         $this->getPrefix()." ".$this->getLanguage()->getTranslate("plugininfo.discord", [$this->getDiscord()]),
      ];
      return implode("\n", $arrayText);
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
      $msgDay = $this->getLanguage()->getTranslate("sendtime.msgday");
      $msgHours = $this->getLanguage()->getTranslate("sendtime.msghours");
      $msgMinutes = $this->getLanguage()->getTranslate("sendtime.msgminutes");
      $msgSeconds = $this->getLanguage()->getTranslate("sendtime.msgseconds");
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
   private function makeSlapperNBT(string $type, Player $player, string $name, string $cmd): CompoundTag{
      $nbt = Entity::createBaseNBT($player, null, $player->getYaw(), $player->getPitch());
      $nbt->setShort("Health", 1);
      $cmds = [new StringTag($cmd, $cmd)];
      $nbt->setTag(new CompoundTag("Commands", $cmds));
      $nbt->setString("MenuName", "");
      $nbt->setString("CustomName", $name);
      $nbt->setString("SlapperVersion", $this->getDescription()->getVersion());
      if($type === "Human") {
         $player->saveNBT();
         $inventoryTag = $player->namedtag->getListTag("Inventory");
         assert($inventoryTag !== null);
         $nbt->setTag(clone $inventoryTag);
         $skinTag = $player->namedtag->getCompoundTag("Skin");
         assert($skinTag !== null);
         $nbt->setTag(clone $skinTag);
      }
      return $nbt;
   }
   public function makeSlapper(Player $player): void{
      if(isset($this->array["slapper"][$player->getName()]["start"])){
         $name = $this->array["slapper"][$player->getName()]["start"];
         $text = $this->getInfoStartEvent($name);
         $nbt = $this->makeSlapperNBT("Human", $player, $text, "gameevent start {player} ".$name);
         $entity = Entity::createEntity("SlapperHuman", $player->getLevel(), $nbt);
         $entity->setNameTag($text);
         $entity->setNameTagVisible(true);
         $entity->setNameTagAlwaysVisible(true);
         $entity->namedtag->setString("slapper_GameEventStart".$name, $entity->getNameTag());
         $entity->spawnToAll();
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("makeslapper"));
         unset($this->array["slapper"][$player->getName()]);
      }
      if(isset($this->array["slapper"][$player->getName()]["award"])){
         $name = $this->array["slapper"][$player->getName()]["award"];
         $text = $this->getInfoAwardEvent($name);
         $nbt = $this->makeSlapperNBT("Human", $player, $text, "gameevent award {player} ".$name);
         $entity = Entity::createEntity("SlapperHuman", $player->getLevel(), $nbt);
         $entity->setNameTag($text);
         $entity->setNameTagVisible(true);
         $entity->setNameTagAlwaysVisible(true);
         $entity->namedtag->setString("slapper_GameEventAward".$name, $entity->getNameTag());
         $entity->spawnToAll();
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("makeslapper"));
         unset($this->array["slapper"][$player->getName()]);
      }
   }
   
   
   public function getEvent(): array{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return array_keys($data["event"]);
   }
   public function isEvent(string $name): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return isset($data["event"][$name]);
   }
   public function getCountEvent(): int{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return count($data["event"]);
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
      $data["event"][$name]["message-welcome"] = false;
      $data["event"][$name]["playerWin"] = [];
      $eventData->setAll($data);
      $eventData->save();
   }
   public function editEvent(string $name, int $time, string $info, string $infoAward, string $cmdStart, string $cmdAward): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["settime"] = $time;
      $data["event"][$name]["time"] = $time;
      $data["event"][$name]["info"] = $info;
      $data["event"][$name]["info-award"] = $infoAward;
      $data["event"][$name]["command-start"] = $cmdStart;
      $data["event"][$name]["command-award"] = $cmdAward;
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
   public function getSetTimeEvent(string $name): int{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event"][$name]["settime"];
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
   public function getMessageWelcomeEvent(string $name): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event"][$name]["message-welcome"];
   }
   public function setMessageWelcomeEvent(string $name, bool $enabled = false): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["message-welcome"] = $enabled;
      $eventData->setAll($data);
      $eventData->save();
   }
   public function getEnabledEvent(string $name): bool{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      return $data["event"][$name]["enabled"];
   }
   public function setEnabledEvent(string $name, bool $enabled = false): void{
      $this->getServer()->broadcastMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("event.title")." ".($enabled ? $this->getLanguage()->getTranslate("enabledevent.on", [$name]) : $this->getLanguage()->getTranslate("enabledevent.off", [$name])));
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $data["event"][$name]["enabled"] = $enabled;
      $eventData->setAll($data);
      $eventData->save();  
   }
   public function getInfoStartEvent(string $event): string{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(isset($data["event"]) && isset($data["event-present"])){
         if(count($data["event"]) == 0){
            return $this->getLanguage()->getTranslate("event.without");
         }
         $name = $event;
         if(!isset($data["event"][$name])){
            return $this->getLanguage()->getTranslate("event.notfound", [$name]);
         }
         if($data["event"][$name]["enabled"]){
            $text = $this->getPrefix().$this->getLanguage()->getTranslate("infostartevent.on", [$name, $data["event"][$name]["info"], $this->sendTime($data["event"][$name]["time"]), $data["event"][$name]["info-award"]]);
         }else{
            $text = $this->getPrefix().$this->getLanguage()->getTranslate("infostartevent.off", [$name]);
         }
         return $text;
      }else{
         return $this->getLanguage()->getTranslate("event.without");
      }
   }
   public function getInfoAwardEvent(string $event): string{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(isset($data["event"]) && isset($data["event-present"])){
         if(count($data["event"]) == 0){
            return $this->getLanguage()->getTranslate("event.without");
         }
         $name = $event;
         if(!isset($data["event"][$name])){
            return $this->getLanguage()->getTranslate("event.notfound", [$name]);
         }
         if($data["event"][$name]["enabled"]){
            $text = $this->getPrefix().$this->getLanguage()->getTranslate("infoawardevent.on", [$name, $data["event"][$name]["info-award"]]);
         }else{
            $text = $this->getPrefix().$this->getLanguage()->getTranslate("infoawardevent.off", [$name]);
         }
         return $text;
      }else{
         return $this->getLanguage()->getTranslate("event.without");
      }
   }
   public function onPlayerStartEvent(Player $player, string $event): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(in_array(strtolower($player->getName()), $data["event"][$event]["playerWin"])){
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("playerstartevent.error1"));
         return;
      }
      $command = str_replace("{player}", $player->getName(), $data["event"][$event]["command-start"]);
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
      if($this->getMessageWelcomeEvent($event)){
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("playerstartevent.welcome", [$event, $this->sendTime($data["event"][$event]["time"]), $data["event"][$event]["info-award"]]));
      }
   }
   public function onPlayerAwardEvent(Player $player, string $event): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      if(in_array(strtolower($player->getName()), $data["event"][$event]["playerWin"])){
         $player->sendMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("playerawardevent.error1"));
         return;
      }
      $data["event"][$event]["playerWin"][] = strtolower($player->getName());
      $eventData->setAll($data);
      $eventData->save();
      $command = str_replace("{player}", $player->getName(), $data["event"][$event]["command-award"]);
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
      $this->getServer()->broadcastMessage($this->getPrefix()." ".$this->getLanguage()->getTranslate("playerawardevent.complete", [$player->getName(), $event, $data["event"][$event]["info-award"]]));
   }
   
   public function onNextEvent(): void{
      $eventData = $this->getData();
      $data = $eventData->getAll();
      $event = $data["event-next"];
      $this->setPresentEvent($event);
      $this->setEnabledEvent($event, true);
      $this->removeNextEvent();
   }
   
}