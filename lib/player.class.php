<?php

/**
 * Class for handling the current status of the music player
 */
class csPlayer
{
  /**
   * Pause the current song
   */
  function pause()
  {
    $isPaused = !csMemory::get(SHM_ISPAUSED_KEY, false);
    exec("echo 'pause' >> {$this->getFifo()}");
    csMemory::set(SHM_ISPAUSED_KEY, $isPaused);
  }

  /**
   * Advance to the next song
   */
  function next()
  {
    csMemory::set(SHM_TIMELEFT_KEY, 0);
  }

  /**
   * Return to the previous song
   */
  function prev()
  {
    $queue = csMemory::get(SHM_QUEUE_KEY);
    $currentPos = csMemory::get(SHM_CQ_POS_KEY);
    
    $queue[$currentPos - 1]['p'] = 0;
    $queue[$currentPos]['p'] = 0;
    $currentPos--;
    
    csMemory::set(SHM_QUEUE_KEY, $queue);
    csMemory::set(SHM_CQ_POS_KEY, $currentPos);
    csMemory::set(SHM_TIMELEFT_KEY, 0);
  }
  
  /**
   * Get the path for mplayerfifo
   * 
   * @global String $mplayerfifo
   * @return String 
   */
  private function getFifo()
  {
    global $mplayerfifo;
    return $mplayerfifo;
  }
}