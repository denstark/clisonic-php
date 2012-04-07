<?php

namespace msgQueue;

/**
 * Class for handling a message queue using SHM
 */
class csSHM extends csBase
{
  private static $shmem;
  
  public static function queueMsg($msg)
  {
    $key = \csSettings::get(CS_MESSAGE_KEY);
    $queue = self::get($key, array());
    $queue[] = $msg;
    self::set($key, $queue);
  }
  
  public static function getNext()
  {
    $key = \csSettings::get(CS_MESSAGE_KEY);
    $queue = self::get($key, array());
    $msg = array_shift($queue);
    self::set($key, $queue);
    
    return $msg;
  }
  
  /**
   * Retrieve a value from shared memory
   * 
   * @param String $key
   * @return Mixed
   */
  private static function get($key, $default = null)
  {
     $val = shm_get_var(self::getShmem(), $key);
     return !is_null($val) ? $val : $default;
  }
  
  /**
   * Put a value into shared memory with the provided key
   * 
   * @param String $key
   * @param Mixed $value 
   */
  private static function set($key, $value)
  {
    shm_put_var(self::getShmem(), $key, $value);
  }
  
  public static function cleanUp()
  {
    shm_remove(self::$shmem);
  }
  
  /**
   * Get (creating if needed) the shared memory instance
   * 
   * @return Resource 
   */
  private static function getShmem()
  {
    return !is_null(self::$shmem)
      ? self::$shmem
      : self::$shmem = shm_attach(\csSettings::get(CS_QUEUE_KEY));
  }
}