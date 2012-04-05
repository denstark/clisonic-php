<?php

class csQueue
{
  public static function clear()
  {
    $fifo = csPlayer::getFifo();

    csMemory::set(SHM_QUEUE_KEY, array());
    csPlayer::setTimeLeft(0);

    exec("echo 'stop' >> $fifo");
  }

  public static function get() 
  {
    return csMemory::get(SHM_QUEUE_KEY);
  }
  
  public static function set($queue)
  {
    csMemory::set(SHM_QUEUE_KEY, $queue);
  }
  
  public static function add($entries)
  {
    $queue = csQueue::get();
    
    if (!is_array($entries))
      $entries = array($entries);
    
    foreach ($entries as $entry)
    {
      if (!($entry instanceof csQueueEntry))
        throw new Exception('Invalid queue entry');
      
      if ($entry->isDir())
        throw new Exception('Cannot directly add a directory to the queue');
      
      echo "Adding {$entry->getTitle()} to the queue\n";
      $queue[] = $entry;
    }
    
    csQueue::set($queue);
  }
  
  public static function setPos($pos)
  {
    csMemory::set(SHM_CQ_POS_KEY, $pos);
  }
  
  public static function getPos()
  {
    return csMemory::get(SHM_CQ_POS_KEY);
  }
}