<?php

$script = isset($script) ? $script : null;

// Config file is required
require('settings.inc.php');

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