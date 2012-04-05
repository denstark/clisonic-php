<?php

namespace memory;

/**
 * Base class for memory classes
 */
abstract class csBaseMemory
{
  private function __construct() { }
  public static function cleanUp() {}
  
  /**
   * Retrieve a value from memory
   * 
   * @param String $key
   * @return Mixed
   */
  abstract public static function get($key, $default = null);
  
  /**
   * Put a value into memory with the provided key
   * 
   * @param String $key
   * @param Mixed $value 
   */
  abstract public static function set($key, $value);
}