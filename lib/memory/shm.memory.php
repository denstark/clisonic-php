<?php

namespace memory;

/**
 * Class for handling shared memory
 */
class csSHM extends csBaseMemory
{
  private static $shmem;
  
  /**
   * Retrieve a value from shared memory
   * 
   * @param String $key
   * @return Mixed
   */
  public static function get($key, $default = null)
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
  public static function set($key, $value)
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
      : self::$shmem = shm_attach(SHM_KEY);
  }
}