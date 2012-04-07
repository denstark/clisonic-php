<?php

// setting key constants (not in class for easier use in settings.inc files)
define('CS_USERNAME', 'subsonic.username');
define('CS_PASSWORD', 'subsonic.password');
define('CS_MP3_DIR', 'mp3.dir');
define('CS_FIFO', 'mplayer.fifo');
define('CS_CACHE_SIZE', 'mplayer.cache_size');
define('CS_BUFFER_LENGTH', 'buffer.length');
define('CS_QUEUE_KEY', 'queue.key');
define('CS_MESSAGE_KEY', 'message.key');
define('CS_MEMORY_CLASS', 'memory.class');
define('CS_MEMCACHE_HOST', 'memcache.host');
define('CS_MEMCACHE_PORT', 'memcache.port');
define('CS_APP', 'app');
define('CS_API_VER', 'api.ver');
define('CS_API_URL', 'api.url');
define('CS_API_ARGS', 'api.args');
define('CS_STDIO', 'stdio');
define('CS_QUEUE_PATH', 'queue.path');
define('CS_PLAYER_OUT', 'player.out');
define('CS_CUSTOM_ALIASES', 'custom_aliases');
define('CS_DAEMON_OUT', 'daemon.out');

class csSettings
{
  private static $settings = array();
  
  public static function set($key, $value)
  {
    self::$settings[$key] = $value;
  }
  
  public static function get($key, $default = null)
  {
    return !empty(self::$settings[$key])
      ? self::$settings[$key]
      : $default;
  }
  
  public static function has($key)
  {
    return isset(self::$settings[$key]);
  }
}

// helpers to make registering settings easier in settings.inc
function register_setting($key, $value)
{
  csSettings::set($key, $value);
}

function register_not_exists($key, $value)
{
  if (!csSettings::has($key))
  {
    register_setting($key, $value);
  }
}