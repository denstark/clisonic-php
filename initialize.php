<?php

$script = isset($script) ? $script : null;

require('lib/settings.class.php');

// setup pre-defaults
register_setting(CS_FIFO, '/tmp/clisonic.fifo');
register_setting(CS_BUFFER_LENGTH, 20);
register_setting(CS_CACHE_SIZE, 500);
register_setting(CS_QUEUE_KEY, 20000);
register_setting(CS_MESSAGE_KEY, 20000);
register_setting(CS_MP3_DIR, '/tmp');
register_setting(CS_STDIO, fopen('php://stdin', 'r'));
register_setting(CS_MEMORY_CLASS, '\msgQueue\csMsgQueue');
register_setting(CS_APP, 'clisonic-php');
register_setting(CS_API_VER, '1.7.0');
register_setting(CS_QUEUE_PATH, '/tmp/clisonic.queue');
register_setting(CS_PLAYER_OUT, '/tmp/clisonic.player.out');
register_setting(CS_DAEMON_OUT, '/tmp/clisonic.out');

// Config file is required
require('settings.inc.php');

// setup post-defaults
register_not_exists(CS_API_ARGS,
  array(
    'u' => csSettings::get(CS_USERNAME),
    'p' => csSettings::get(CS_PASSWORD),
    'v' => csSettings::get(CS_API_VER),
    'c' => csSettings::get(CS_APP),
  ));

// Song queue classes
require_once('lib/queue.class.php');
require_once('lib/queueEntry.class.php');

// Subsonic facade
require_once('lib/fetch.class.php');

// Message queue classes
require_once('lib/msgQueue/base.class.php');
require_once('lib/msgQueue.class.php');
require_once('lib/msgQueue/msgQueue.class.php');

// Just the controller
if ($script == 'controller')
{
  require_once('lib/controller.class.php');
}

// Just the daemon
if ($script == 'daemon')
{
  require_once('lib/player.class.php');
}