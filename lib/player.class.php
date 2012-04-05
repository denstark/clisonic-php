<?php

/**
 * Class for handling the current status of the music player
 */
class csPlayer
{
  private static $fifo;
  
  private function __construct() {}
  
  public static function init($mplayerfifo, $cachesize)
  {
    self::$fifo = $mplayerfifo;
    self::createFifo();
    self::start($cachesize);
  }
  
  private static function createFifo() 
  {
    $fifo = self::$fifo;
    
    if (file_exists($fifo))
    {
      exec("rm -f $fifo");
    }
    exec("mkfifo $fifo");
  }

  private static function start($cachesize)
  {
    $fifo = self::$fifo;
    exec("mplayer -really-quiet -cache $cachesize -slave -input file=$fifo -idle > /dev/null 2>&1 &", $output);
  }
  
  /**
   * Pause the current song
   */
  public static function pause()
  {
    $isPaused = !self::isPaused();
    $fifo = self::$fifo;
    exec("echo 'pause' >> {$fifo}");
    csMemory::set(SHM_ISPAUSED_KEY, $isPaused);
  }
  
  public static function resetPause()
  {
    csMemory::set(SHM_ISPAUSED_KEY, false);
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
  
  public static function getFifo()
  {
    return self::$fifo;
  }
  
  public static function setFifo($val)
  {
    self::$fifo = $val;
  }
}