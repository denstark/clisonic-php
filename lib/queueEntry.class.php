<?php

class csQueueEntry
{
  private $id;
  private $title;
  private $isDir = false;
  private $duration;
  private $album;
  private $unique;
  
  public function __construct()
  {
    // generate a unique ID
    $this->unique = uniqid(rand(10, 99) . '.');
  }
  
  public function getUUID() { return $this->unique; }
  
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
  
  public function wasProcessed()
  {
    return file_exists($this->getFileName());
  }
  
  public function getFileName()
  {
    $mp3dir = csSettings::get(CS_MP3_DIR);
    return "$mp3dir/" . md5($this->id) . '.mp3';
  }
}