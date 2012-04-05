<?php

namespace memory;

/**
 * Class for handling shared memory via memcache
 */
class csMemcache extends csBaseMemory
{
  const DEFAULT_PORT = 11211;
  
  private static $memcache;
  
  public static function get($key, $default = null)
  {
    $key = self::getPrefix() . $key;
    $val = self::getInstance()->get($key);
    return !is_null($val) ? $val : $default;
  }
  
  public static function set($key, $value)
  {
    $key = self::getPrefix() . $key;
    self::getInstance()->set($key, $value);
  }
  
  private static function getInstance()
  {
    if (is_null(self::$memcache))
    {
      self::$memcache = new \Memcache;
      
      $port = defined('MEMCACHE_PORT')
        ? MEMCACHE_PORT
        : self::DEFAULT_PORT;
      
      self::$memcache->connect('localhost', $port);
    }
    
    return self::$memcache;
  }
  
  private static function getPrefix()
  {
    // protection against other processes using the same memcache instance
    return substr(__DIR__, 0, 5);
  }
}