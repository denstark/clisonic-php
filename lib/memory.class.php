<?php

/**
 * Class for managing shared memory
 */
class csMemory extends \memory\csBaseMemory
{
  const DEFAULT_CLASS = '\memory\csMsgQueue';
  
  private static $className;
  
  // Singleton constructor
  private function __construct() { }
  
  /**
   * Retrieve a value from memory
   * 
   * @param String $key
   * @return Mixed
   */
  public static function get($key, $default = null)
  {
    $class = self::getClass();
    return $class::get($key, $default);
  }
  
  /**
   * Put a value into memory with the provided key
   * 
   * @param String $key
   * @param Mixed $value 
   */
  public static function set($key, $value)
  {
    $class = self::getClass();
    return $class::set($key, $value);
  }
  
  public static function cleanUp()
  {
    $class = self::getClass();
    return $class::cleanUp();
  }
  
  /**
   * The class to be used for handling memory
   * 
   * @return String
   */
  private static function getClass()
  {
    if (!is_null(self::$className))
      return self::$className;
    
    if (defined('MEMORY_CLASS') && class_exists(MEMORY_CLASS))
    {
      return self::$className = MEMORY_CLASS;
    }
    
    return self::DEFAULT_CLASS;
  }
}