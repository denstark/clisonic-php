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
    $queue = csQueue::get();
    $currentPos = csQueue::getPos();
    
    if (isset($queue[$currentPos - 1]))
      $queue[$currentPos - 1]->setProcessed(false);
    
    if (isset($queue[$currentPos]))
      $queue[$currentPos]->setProcessed(false);
    
    $currentPos--;
    
    csQueue::set($queue);
    csQueue::setPos($currentPos);
    self::setTimeLeft(0);
  }
  
  public static function setTimeLeft($val)
  {
    if (!is_numeric($val))
      throw new Exception('Time left is not numeric');
    
    csMemory::set(SHM_TIMELEFT_KEY, $val);
  }
  
  public static function getTimeLeft()
  {
    return csMemory::get(SHM_TIMELEFT_KEY);
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
  
  public static function loadSong($id)
  {
    $mp3file = csFetch::getSong($id);
    $fifo = self::$fifo;
    
    sleep(1);
    system('pgrep -f ".*mplayer.*clisonic.*"', $isrunning);
    
    if ($isrunning == 0)
    {
      // Mplayer is running
      // system("echo 'loadfile $mp3file $songcount' >> $mplayerfifo"); // 
      // Needs to be reimplemented when I figure out a solution to queuing from 
      // $x seconds behind the current song
      system("echo 'loadfile $mp3file' >> $fifo");
      // $songcount++;
    }
    else 
    {
      throw new Exception('Mplayer is not runnning');
    }
  }
}