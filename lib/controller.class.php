<?php

class csController
{
  public static function go($action, $arguments = array())
  {
    switch ($action)
    {
      case 'browse':
      case '-b':
        self::browse();
        break;
      case 'view':
      case '-v':
        self::viewQueue();
        break;
      case 'clear':
      case '-c':
        csQueue::clear();
        break;
      default:
        csMsgQueue::queueMsg($action);
        break;
    }
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
  
  public static function browse()
  {
    global $stdio;
    
    $artists = csFetch::getArtists();
    echo self::listArtists($artists);
    
    $isValid = false;
    
    while (!$isValid)
    {
      echo "Please input an artist number: ";
      $artistIndex = trim(fgets($stdio));
      
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