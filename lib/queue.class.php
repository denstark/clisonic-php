<?php

class csQueue
{
  private $pos;
  private $entries = array();
  private $ordered = array();
  private $shuffled = array();
  
  private $repeatMode = 0;
  private $shuffle = false;
  
  const REPEAT_NONE = 0;
  const REPEAT_ALL = 1;
  const REPEAT_ONE = 2;
  
  private static $repeatModes = array(
    self::REPEAT_NONE => 'No repeat',
    self::REPEAT_ALL => 'Repeat all',
    self::REPEAT_ONE => 'Repeat one',
  );
  
  public static function clear()
  {
    $queue = self::get();
    $queue->setPos(null);
    $queue->entries = array();
    $queue->ordered = array();
    $queue->shuffled = array();
    $queue->save();
  }

  public static function get() 
  {
    $queueLoc = csSettings::get(CS_QUEUE_PATH);
    
    if (!file_exists($queueLoc))
      return new csQueue;
    
    return unserialize(file_get_contents($queueLoc));
  }
  
  public function isShuffled()
  {
    return $this->shuffle;
  }
  
  public function getSortingArray()
  {
    return ($this->shuffle)
      ? $this->shuffled
      : $this->ordered;
  }
  
  public function toggleRepeat()
  {
    $this->repeatMode++;
    
    if (!isset(self::$repeatModes[$this->repeatMode]))
      $this->repeatMode = 0;
    
    $this->save();
  }
  
  public function setRepeatMode($mode)
  {
    if (!isset(self::$repeatModes[$mode]))
      throw new Exception('Invalid repeat mode');
    
    $this->repeatMode = $mode;
    
    $this->save();
  }
  
  public function toggleShuffle()
  {
    $this->setShuffle(!$this->shuffle);
  }
  
  public function setShuffle($bool)
  {
    $val = ($bool) ? 'true' : 'false';
    logOut('Setting shuffle to ' . $val);
    
    $entry = $this->getEntry($this->pos);
    
    $uuid = $this->getCurrentEntryUUID();
    
    $this->shuffle = $bool;
    
    if ($bool)
    {
      $this->shuffle($uuid);
    }
    
    $helper = array_flip($this->getSortingArray());
    
    if (!is_null($uuid))
      $this->pos = $helper[$uuid];
    
    $this->save();
  }
  
  public function shuffle($uuid = null)
  {
    $shuffled = $this->ordered;
        
    if (!is_null($uuid))
    {
      $helper = array_flip($shuffled);

      unset($shuffled[$helper[$uuid]]);
      shuffle($shuffled);
      
      $final = array();
      $final[] = $uuid;
      $final += array_values($shuffled);
      
      $shuffled = $final;
    }
    else
    {
      shuffle($shuffled);
    }
    
    $this->shuffled = $shuffled;
  }
  
  public function getEntries()
  {
    return $this->entries;
  }
  
  public function getEntry($pos)
  {
    $sortArray = $this->getSortingArray();
    
    if (!isset($sortArray[$pos]))
      return null;
    
    return isset($this->entries[$sortArray[$pos]])
      ? $this->entries[$sortArray[$pos]]
      : null;
  }
  
  public function getCurrentEntryUUID()
  {
    $entry = $this->getEntry($this->pos);
    return ($entry) ? $entry->getUUID() : null;
  }
  
  public function getNextEntry()
  {
    return $this->getEntry($this->getNextPos());
  }
  
  public function add($entries)
  {
    if (!is_array($entries))
      $entries = array($entries);
    
    foreach ($entries as $entry)
    {
      if (!($entry instanceof csQueueEntry))
        throw new Exception('Invalid queue entry');
      
      if ($entry->isDir())
        throw new Exception('Cannot directly add a directory to the queue');
      
      echo "Adding {$entry->getTitle()} to the queue\n";
      $this->entries[$entry->getUUID()] = $entry;
      $this->ordered[] = $entry->getUUID();
    }
    
    $this->save();
  }
  
  public function setPos($pos)
  {
    $this->pos = $pos;
    $this->save();
  }
  
  public function getPos()
  {
    return $this->pos;
  }
  
  public function decrementPos($dist = 1)
  {
    $this->pos = (!is_null($this->pos) && $this->pos >= $dist)
      ? $this->pos - $dist
      : null;
    
    $this->save();
  }
  
  public function incrementPos($dist = 1)
  {
    $this->pos = !is_null($this->pos)
      ? $this->pos + $dist
      : $dist - 1;
    
    $this->save();
  }
  
  public function getNextPos()
  {
    return is_null($this->pos) ? 0 : $this->pos + 1;
  }

  public function save()
  {
    $queueLoc = csSettings::get(CS_QUEUE_PATH);
    file_put_contents($queueLoc, serialize($this));
  }
}