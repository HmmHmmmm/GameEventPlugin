<?php

namespace hmmhmmmm\gameevent\object;

class CreateEvent{
   private $object;
   
   public function __construct(array $object = []){
      $this->object = $object;
   }
   public function getName(): string{
      return $this->object["name"];
   }
   public function getTime(): int{
      return $this->object["time"];
   }
   public function getInfo(): string{
      return $this->object["info"];
   }
   public function getInfoAward(): string{
      return $this->object["infoAward"];
   }
   public function getChatPage(): string{
      return $this->object["chatPage"];
   }
   public function setChatPage(string $page): void{
      $this->object["chatPage"] = $page;
   }
   public function getCommandStart(): string{
      return $this->object["command-start"];
   }
   public function setCommandStart(string $cmd): void{
      $this->object["command-start"] = $cmd;
   }
   public function getCommandAward(): string{
      return $this->object["command-award"];
   }
   public function setCommandAward(string $cmd): void{
      $this->object["command-award"] = $cmd;
   }
}