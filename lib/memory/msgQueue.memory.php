<?php

namespace memory;

/**
 * Class for handling shared memory using the message queue
 */
class csMsgQueue extends csBaseMemory
{
  private static $queue;
  
  /**
   * Retrieve a value from the message queue
   * 
   * @param String $key
   * @return Mixed
   */
  public static function get($key, $default = null)
  {
    msg_receive(self::getQueue(), $key, $realKey, 10000, $val);
    return !is_null($val) ? $val : $default;
  }
  
  /**
   * Put a value into the message queue with the provided key
   * 
   * @param String $key
   * @param Mixed $value 
   */
  public static function set($key, $value)
  {
    msg_send(self::getQueue(), $key, $value);
  }
  
  public static function cleanUp()
  {
    msg_remove_queue(self::getQueue());
  }
  
  /**
   * Get (creating if needed) the shared memory instance
   * 
   * @return Resource 
   */
  private static function getQueue()
  {
    return !is_null(self::$queue)
      ? self::$queue
      : self::$queue = msg_get_queue(SHM_KEY);
  }
}