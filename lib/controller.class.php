<?php

class csController
{
  public static function go($action, $arguments = array())
  {
    self::convertAlias($action);
    switch ($action)
    {
      case null:
        echo "clisonic-php\n";
        echo 'Type "help" for help.' . "\n\n";
        break;
      case 'browse':
        self::browse($arguments);
        break;
      case 'view':
        self::viewQueue();
        break;
      case 'clear':
        csQueue::clear();
        break;
      case 'exit':
        echo "bye\n";
        csMsgQueue::queueMsg('exit');
        exit;
        break;
      case 'stop':
      case 'restart':
      case 'previous':
      case 'next':
      default:
        csMsgQueue::queueMsg($action);
        break;
    }
  }
  
  public static function convertAlias(&$action)
  {
    $aliases = array(
      '-b' => 'browse',
      '-v' => 'view',
      '-c' => 'clear',
      '-r' => 'restart',
      '-p' => 'previous',
      '-u' => 'pause',
      'prev' => 'previous',
      '-n' => 'next',
    );
    
    $customAliases = csSettings::get(CS_CUSTOM_ALIASES);
    
    if (is_array($customAliases) && count($customAliases))
    {
      $aliases = array_merge($aliases, $customAliases);
    }
    
    $action = array_key_exists($action, $aliases)
      ? $aliases[$action]
      : $action;
  }
  
  public static function listEntries($entries)
  {
    $output = '';
    
    foreach ($entries as $key => $entry)
    {
      $output .= "$key: {$entry->getTitle()}\n";
    }
    
    return $output;
  }
  
  public static function listArtists($artists)
  {
    $output = '';
    
    foreach ($artists as $key => $artist)
    {
      $name = $artist['name'];
      $id = $artist['id'];
      
      $output .= "$key: $name\n";
    }
    
    return $output;
  }
  
  public static function browse($arguments = array())
  {
    $stdio = csSettings::get(CS_STDIO);
    
    $pattern = array_shift($arguments);
    
    $artists = csFetch::getArtists($pattern);
    echo self::listArtists($artists);
    
    $isValid = false;
    
    while (!$isValid)
    {
      echo "Please input an artist number: ";
      $artistIndex = trim(fgets($stdio));
      
      if (strtolower($artistIndex) == 'q')
      {
        return;
      }
      
      $isValid = isset($artists[$artistIndex]);
    }
    
    $dirId = $artists[$artistIndex]['id'];

    $isDir = true;
    
    while ($isDir)
    {
      $entries = csFetch::getMusicDir($dirId);
      echo self::listEntries($entries);
      echo "Please input an album/track number (A to queue the album): ";
      $albumIndex = trim(fgets($stdio));
      
      if (strtolower($albumIndex) == 'q')
      {
        return;
      }
            
      if (strtolower($albumIndex) == 'a')
      {
        csQueue::get()->add($entries);
        return;
      }
      
      if (!isset($entries[$albumIndex]))
      {
        echo "That entry is not valid.\n";
        continue;
      }
      
      $isDir = $entries[$albumIndex]->isDir();
      $dirId = $entries[$albumIndex]->getId();
    }
    
    // We now are broken out of the selection loop and need to add the song to the 
    // queue
    csQueue::get()->add($entries[$albumIndex]);
  }
  
  public static function viewQueue() 
  {
    $queue = csQueue::get();
    $currentPos = $queue->getPos();
    $entries = $queue->getEntries();
    
    if (!count($entries))
    {
      echo "Nothing in the queue!\n";
    }
    else
    {
      foreach ($entries as $key => $entry)
      {
        if ($key == $currentPos)
          echo "*";

        echo $key . ": ";
        echo $entry->getTitle() . " - " . $entry->getAlbum('Unknown Album') . "\n";
      }
    }
  }
}