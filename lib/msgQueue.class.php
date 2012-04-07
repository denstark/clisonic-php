<?php

/**
 * Class for managing shared memory
 */
class csMsgQueue extends \msgQueue\csBase
{
  const DEFAULT_CLASS = '\msgQueue\csMsgQueue';
  
  private static $className;
  
  // Singleton constructor
  private function __construct() { }
  
  public static function queueMsg($msg)
  {
    $class = self::getClass();
    return $class::queueMsg($msg);
  }
  
  public static function getNext()
  {
    $class = self::getClass();
    return $class::getNext();
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
    
    $memoryClass = csSettings::get(CS_MEMORY_CLASS);
    
    if (!is_null($memoryClass) && class_exists($memoryClass))
    {
      return self::$className = $memoryClass;
    }
    
    return self::DEFAULT_CLASS;
  }
}