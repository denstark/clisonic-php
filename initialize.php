<?php

// Config file is required
require('settings.inc.php');

// Until an autoloader has been built
require_once('lib/player.class.php');
require_once('lib/queue.class.php');
require_once('lib/queueEntry.class.php');

require_once('lib/controller.class.php');
require_once('lib/fetch.class.php');

// Memory classes
require_once('lib/memory/base.memory.php');
require_once('lib/memory.class.php');
require_once('lib/memory/memcache.memory.php');