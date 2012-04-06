<?php

namespace msgQueue;

/**
 * Base class for managing a message queue
 */
abstract class csBase
{
  private function __construct() { }
  public static function cleanUp() {}
  
  abstract public static function queueMsg($msg);
  abstract public static function getNext();
}