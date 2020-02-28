<?php

namespace hmmhmmmm\gameevent\ui;

use hmmhmmmm\gameevent\GameEvent;
use xenialdan\customui\event\UICloseEvent;
use xenialdan\customui\event\UIDataReceiveEvent;
use xenialdan\customui\elements\Button;
use xenialdan\customui\elements\Dropdown;
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Label;
use xenialdan\customui\elements\Slider;
use xenialdan\customui\elements\StepSlider;
use xenialdan\customui\elements\Toggle;
use xenialdan\customui\windows\CustomForm;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class Form{
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
  
   public function Menu(Player $player, string $content = ""): void{
      $array = [];
      if($this->getPlugin()->getCountEvent() !== 0){
         foreach($this->getPlugin()->getEvent() as $eventName){
            $array[] = $eventName;
         }
      }
      $form = new SimpleForm(
         $this->getPrefix()." Menu",
         $content
      );
      
      $form->addButton(new Button($this->getPlugin()->getLanguage()->getTranslate("form.menu.button1")));
      for($i = 0; $i < count($array); $i++){
         $form->addButton(new Button($array[$i]));
      }
      $form->setCallable(function (Player $player, $data) use ($array){
         if(!($data === null)){
            switch($data){
               case $this->getPlugin()->getLanguage()->getTranslate("form.menu.button1"):
                  $this->Create($player);
                  break;
               default:
                  $this->Edit($player, $data);
                  break;
            }
         }
      });
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);
   }
   
   public function Create(Player $player, string $content = ""): void{
      $form = new CustomForm(
         $this->getPrefix()." Create Event"
      );
      $form->addElement(new Label($content));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input1"), "Test"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input2"), "600"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input3"), "??"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input4"), "Diamond64"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input5"), "??"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input6"), "give {player} 264 64"));
      
      $form->setCallable(function (Player $player, $data){
         if($data == null){
            return;
         }
         $name = explode(" ", $data[1]); 
         if($name[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error1", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input1")]);
            $this->Create($player, $text);
            return;
         }
         $name = $name[0];
         if($this->getPlugin()->isEvent($name)){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error2", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input1"), $name]);
            $this->Create($player, $text);
            return;
         }
         $time = explode(" ", $data[2]); 
         if($time[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->Create($player, $text);
            return;
         }
         if(!is_numeric($time[0])){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->Create($player, $text);
            return;
         }
         $time = (int) $time[0];
         $info = explode(" ", $data[3]); 
         if($info[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input3")]);
            $this->Create($player, $text);
            return;
         }
         $info = $data[3];
         $infoAward = explode(" ", $data[4]); 
         if($infoAward[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input4")]);
            $this->Create($player, $text);
            return;
         }
         $infoAward = $data[4];
         $commandStart = explode(" ", $data[5]); 
         if($commandStart[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input5")]);
            $this->Create($player, $text);
            return;
         }
         $cmdStart = $data[5];
         $commandAward = explode(" ", $data[6]); 
         if($commandAward[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input6")]);
            $this->Create($player, $text);
            return;
         }
         $cmdAward = $data[6];
         $this->getPlugin()->createEvent($name, $time, $info, $infoAward, $cmdStart, $cmdAward);
         $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("listener.playerchat.create.event.complete", [$name]));
      });
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);
   }
   
   public function Edit(Player $player, string $eventName, string $content = ""): void{
      if($this->getPlugin()->getEnabledEvent($eventName)){
         $enabled = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.on");
      }else{
         $enabled = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.off");
      }
      if($this->getPlugin()->getPresentEvent() == $eventName){
         $present = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.yes");
      }else{
         $present = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.no");
      }
      if($this->getPlugin()->isNextEvent()){
         $next = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.yes");
      }else{
         $next = $this->getPlugin()->getLanguage()->getTranslate("form.edit.info.no");
      }
      
      $text = $this->getPlugin()->getLanguage()->getTranslate("form.edit.content", [$eventName, $enabled, $present, $next, $this->getPlugin()->sendTime($this->getPlugin()->getSetTimeEvent($eventName))]);
      $form = new SimpleForm(
         $this->getPrefix()." Edit ".$eventName,
         $content."\n".$text
      );
      $array = [
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button1") => 0,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button2") => 1,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button3") => 2,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button4") => 3,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button5") => 4,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button6") => 5,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button7") => 6,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button8") => 7,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button9") => 8,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button10") => 9,
         $this->getPlugin()->getLanguage()->getTranslate("form.edit.button11") => 10
      ];
      foreach($array as $button => $value){
         $form->addButton(new Button($button));
      }
      $form->setCallable(function ($player, $data) use ($eventName, $array){
         if(!($data === null)){
            switch($array[$data]){
               case 0:
                  $this->Edit2($player, $eventName);
                  break;
               case 1:
                  if($this->getPlugin()->getPresentEvent($eventName) !== $eventName){
                     $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.enabled.error3", [$eventName]));
                     return;
                  }
                  $this->getPlugin()->setEnabledEvent($eventName, !$this->getPlugin()->getEnabledEvent($eventName));
                  break;
               case 2:
                  $this->getPlugin()->setPresentEvent($eventName);
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setpresent.complete", [$eventName]));
                  break;
               case 3:
                  $this->getPlugin()->setNextEvent($eventName);
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.setnext.complete", [$eventName]));
                  break;
               case 4:
                  $this->SetTime($player, $eventName);
                  break;
               case 5:
                  $this->getPlugin()->array["sign"][$player->getName()]["start"] = $eventName;
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signstart.complete"));
                  break;
               case 6:
                  $this->getPlugin()->array["sign"][$player->getName()]["award"] = $eventName;
                  $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.signaward.complete"));
                  break;
               case 7:
                  $this->getPlugin()->array["slapper"][$player->getName()]["start"] = $eventName;
                  $this->getPlugin()->makeSlapper($player);
                  break;
               case 8:
                  $this->getPlugin()->array["slapper"][$player->getName()]["award"] = $eventName;
                  $this->getPlugin()->makeSlapper($player);
                  break;
               case 9:
                  $this->getPlugin()->setMessageWelcomeEvent($eventName, !$this->getPlugin()->getMessageWelcomeEvent($eventName));
                  if($this->getPlugin()->getMessageWelcomeEvent($eventName)){
                     $player->sendMessage($this->getPrefix()." ".$this->plugin->getLanguage()->getTranslate("gameevent.command.welcome.on", [$eventName]));
                  }else{
                     $player->sendMessage($this->getPrefix()." ".$this->plugin->getLanguage()->getTranslate("gameevent.command.welcome.off", [$eventName]));
                  }
                  break;
               case 10:
                  $this->Remove($player, $eventName);
                  break;
            }
            
         }
      });
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);
   }
   public function Edit2(Player $player, string $eventName, string $content = ""): void{
      $form = new CustomForm(
         $this->getPrefix()." Edit ".$eventName
      );
      $form->addElement(new Label($content));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input2"), "600"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input3"), "??"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input4"), "Diamond64"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input5"), "??"));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input6"), "give {player} 264 64"));
      
      $form->setCallable(function ($player, $data) use ($eventName){
         if($data == null){
            return;
         }
         $name = $eventName;
         $time = explode(" ", $data[1]); 
         if($time[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         if(!is_numeric($time[0])){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         $time = (int) $time[0];
         $info = explode(" ", $data[2]); 
         if($info[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input3")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         $info = $data[2];
         $infoAward = explode(" ", $data[3]); 
         if($infoAward[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input4")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         $infoAward = $data[3];
         $commandStart = explode(" ", $data[4]); 
         if($commandStart[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input5")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         $cmdStart = $data[4];
         $commandAward = explode(" ", $data[5]); 
         if($commandAward[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error4", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input6")]);
            $this->Edit2($player, $name, $text);
            return;
         }
         $cmdAward = $data[5];
         $this->getPlugin()->editEvent($name, $time, $info, $infoAward, $cmdStart, $cmdAward);
         $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("form.edit2.complete", [$name]));
      });
      
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);
   }
   public function SetTime(Player $player, string $eventName, string $content = ""): void{
      $form = new CustomForm(
         $this->getPrefix()." SetTime ".$eventName
      );
      $form->addElement(new Label($content));
      $form->addElement(new Input($this->getPlugin()->getLanguage()->getTranslate("form.create.input2"), "600"));
      
      $form->setCallable(function ($player, $data) use ($eventName){
         if($data == null){
            return;
         }
         $name = $eventName;
         $time = explode(" ", $data[1]); 
         if($time[0] == null){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->SetTime($player, $name, $text);
            return;
         }
         if(!is_numeric($time[0])){
            $text = $this->getPlugin()->getLanguage()->getTranslate("form.create.error3", [$this->getPlugin()->getLanguage()->getTranslate("form.create.input2")]);
            $this->SetTime($player, $name, $text);
            return;
         }
         $time = (int) $time[0];
         
         $this->getPlugin()->setTimeEvent($name, $time);
         $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.settime.complete", [$name, $this->getPlugin()->sendTime($time)]));
      });
      
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);      
   }
   public function Remove(Player $player, string $eventName): void{
      $form = new ModalForm(
         $this->getPrefix()." Remove ".$eventName,
         $this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("form.remove.content", [$eventName]),
         $this->getPlugin()->getLanguage()->getTranslate("form.remove.yes"),
         $this->getPlugin()->getLanguage()->getTranslate("form.remove.no")
      );
      $form->setCallable(function ($player, $data) use ($eventName){
         if(!($data === null)){
            if($data){
               $this->getPlugin()->removeEvent($eventName);
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("gameevent.command.remove.complete", [$eventName]));
            }
         }
      });
      $form->setCallableClose(function (Player $player){
         //??
      });
      $player->sendForm($form);
   }
}