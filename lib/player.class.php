<?php

/**
 * Class for handling the current status of the music player
 */
class csPlayer
{
  private $mplayerString;
  private $fifo;
  private $paused = false;
  private $currentSong;
  
  public function __construct($mplayerfifo, $cachesize)
  {
    $this->fifo = $mplayerfifo;
    $this->createFifo();
    $this->start($cachesize);
  }
  
  public function __destruct()
  {
    $this->sendMsg('quit');
  }
  
  private function createFifo() 
  {
    if (file_exists($this->fifo))
    {
      exec("rm -f {$this->fifo}");
    }
    exec("mkfifo {$this->fifo}");
  }

  private function start($cachesize)
  {
    $this->mplayerString = "mplayer -quiet -cache $cachesize -slave -input file={$this->fifo} -idle";
    exec($this->mplayerString . ' > /tmp/clisonic-mplayer 2>&1 &', $output);
  }
  
  /**
   * Pause the current song
   */
  public function pause()
  {
    $this->paused = !$this->paused;
    echo "Sending pause request to fifo ({$this->fifo})\n";
    $this->sendMsg('pause');
  }
  
  public function isPaused()
  {
    return $this->paused;
  }

  /**
   * Advance to the next song
   */
  public function next()
  {
    self::setTimeLeft(0);
  }

  /**
   * Return to the previous song
   */
  public function prev()
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
    
  public function getTimePos()
  {
    $this->sendMsg('get_property time_pos');
    
    $out = `tac /tmp/clisonic-mplayer | grep -m1 'ANS_time_pos\|Failed.*time_pos.*'`;
    
    if (preg_match('/=(.*)/', $out, $matches))
      return ceil($matches[1]);
    else
      return 0;
  }

  public function getTimeLeft()
  {
    if (is_null($this->currentSong))
      return 0;

    return $this->currentSong->getDuration() - $this->getTimePos();
  }

  public function sendMsg($msg)
  {
    if (empty($msg))
      throw new Exception('No message to send');

    echo "Sending msg '$msg' to fifo\n";
    exec("echo '$msg' >> {$this->fifo}");
  }
  
  public function getFifo()
  {
    return $this->fifo;
  }
  
  public function loadSong($entry)
  {
    csFetch::getSong($entry->getId());
  }
  
  public function playSong($entry)
  {
    $this->currentSong = $entry;
    $this->sendMsg("loadfile {$entry->getFileName()}");
  }
}
