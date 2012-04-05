<?php

/**
 * Class for handling the current status of the music player
 */
class csPlayer
{
  private function __construct() {}
  
  /**
   * Pause the current song
   */
  public static function pause()
  {
    $isPaused = !self::isPaused();
    $fifo = self::getFifo();
    exec("echo 'pause' >> {$fifo}");
    csMemory::set(SHM_ISPAUSED_KEY, $isPaused);
  }

  /**
   * Advance to the next song
   */
  public static function next()
  {
    self::setTimeLeft(0);
  }

  /**
   * Return to the previous song
   */
  public static function prev()
  {
    $queue = csMemory::get(SHM_QUEUE_KEY);
    $currentPos = csMemory::get(SHM_CQ_POS_KEY);
    
    $queue[$currentPos - 1]['p'] = 0;
    $queue[$currentPos]['p'] = 0;
    $currentPos--;
    
    csMemory::set(SHM_QUEUE_KEY, $queue);
    csMemory::set(SHM_CQ_POS_KEY, $currentPos);
    self::setTimeLeft(0);
  }
  
  public static function setTimeLeft($val)
  {
    if (!is_numeric($val))
      throw new Exception('Time left is not numeric');
    
    csMemory::set(SHM_TIMELEFT_KEY, $val);
  }
  
  public static function isPaused()
  {
    return csMemory::get(SHM_ISPAUSED_KEY, false);
  }
  
  /**
   * Get the path for mplayerfifo
   * 
   * @global String $mplayerfifo
   * @return String 
   */
  private static function getFifo()
  {
    global $mplayerfifo;
    return $mplayerfifo;
  }
}