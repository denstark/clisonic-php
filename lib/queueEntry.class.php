<?php

class csQueueEntry
{
  private $id;
  private $title;
  private $isDir = false;
  private $duration;
  private $album;
  private $processed = false;
  
  public function getId() { return $this->id; }
  public function setId($val) { $this->id = $val; }
  
  public function isDir() { return (bool) $this->isDir; }
  public function setIsDir($val) { $this->isDir = (bool) $val; }
  
  public function getTitle() { return $this->title; }
  public function setTitle($val) { $this->title = $val; }
  
  public function getDuration() { return $this->duration; }
  public function setDuration($val) { $this->duration = $val; }
  
  public function getAlbum($default)
  {
    return !is_null($this->album) ? $this->album : $default;
  }
  public function setAlbum($val) { $this->album = $val; }
  
  public function wasProcessed() { return $this->processed; }
  public function setProcessed($val) { $this->processed = $val; }
}