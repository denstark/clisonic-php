<?php

class csQueue
{
  private $pos = 0;
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
    if (!file_exists('/tmp/clisonic.queue'))
      return new csQueue;
    
    return unserialize(file_get_contents('/tmp/clisonic.queue'));
  }
  
  public function getEntries()
  {
    return $this->entries;
  }
  
  public function processEntry($key)
  {
    if (!isset($this->entries[$key]))
      throw new Exception('Entry not found to process');
    
    $this->entries[$key]->setProcessed(true);
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
  
  public function save()
  {
    file_put_contents('/tmp/clisonic.queue', serialize($this));
  }
}