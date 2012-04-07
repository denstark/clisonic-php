<?php

namespace msgQueue;

/**
 * Class for handling a message queue using memcache
 */
class csMemcache extends csBase
{
  const DEFAULT_HOST = 'localhost';
  const DEFAULT_PORT = 11211;
  
  private static $memcache;
  
  public static function queueMsg($msg)
  {
    $queue = self::get('msgQueue', array());
    $queue[] = $msg;
    self::set('msgQueue', $queue);
  }
  
  public static function getNext()
  {
    $queue = self::get('msgQueue', array());
    $msg = array_shift($queue);
    self::set('msgQueue', $queue);
    
    return $msg;
  }
  
  private static function get($key, $default = null)
  {
    $key = self::getPrefix() . $key;
    $val = self::getInstance()->get($key);
    return !is_null($val) ? $val : $default;
  }
  
  private static function set($key, $value)
  {
    $key = self::getPrefix() . $key;
    self::getInstance()->set($key, $value);
  }
  
  private static function getInstance()
  {
    if (is_null(self::$memcache))
    {
      self::$memcache = new \Memcache;
      
      $host = \csSettings::get(CS_MEMCACHE_HOST, self::DEFAULT_HOST);
      $port = \csSettings::get(CS_MEMCACHE_PORT, self::DEFAULT_PORT);
      
      self::$memcache->connect($host, $port);
    }
    
    return self::$memcache;
  }
  
  private static function getPrefix()
  {
    // protection against other processes using the same memcache instance
    return substr(__DIR__, 0, 5);
  }
}