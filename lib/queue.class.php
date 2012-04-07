<?php

class csQueue
{
  private $pos;
  private $entries = array();
  
  public static function clear()
  {
    $queue = self::get();
    $queue->setPos(0);
    $queue->entries = array();
    $queue->save();
    /*
    $fifo = csPlayer::getFifo();

    csMemory::set(SHM_QUEUE_KEY, array());
    csPlayer::setTimeLeft(0);

    exec("echo 'stop' >> $fifo");
    */
  }

  public static function get() 
  {
    $queueLoc = csSettings::get(CS_QUEUE_PATH);
    
    if (!file_exists($queueLoc))
      return new csQueue;
    
    return unserialize(file_get_contents($queueLoc));
  }
  
  public function getEntries()
  {
    return $this->entries;
  }
  
  public function getEntry($pos)
  {
    return isset($this->entries[$pos])
      ? $this->entries[$pos]
      : null;
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
      $this->entries[] = $entry;
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