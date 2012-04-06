<?php

namespace msgQueue;

/**
 * Class for handling a message queue using the built-in message queue
 */
class csMsgQueue extends csBase
{
  private static $queue;
  
  public static function queueMsg($msg)
  {
    self::set(SHM_QUEUE_KEY, $msg);
  }
  
  public static function getNext()
  {
    return self::get(SHM_QUEUE_KEY);
  }
  
  /**
   * Retrieve a value from the message queue
   * 
   * @param String $key
   * @return Mixed
   */
  private static function get($key, $default = null)
  {
    msg_receive(self::getQueue(), $key, $realKey, 10000, $val, true, MSG_IPC_NOWAIT);
    return !is_null($val) ? $val : $default;
  }
  
  /**
   * Put a value into the message queue with the provided key
   * 
   * @param String $key
   * @param Mixed $value 
   */
  private static function set($key, $value)
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