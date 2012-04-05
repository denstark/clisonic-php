<?php

class csController
{
  public static function go($stdio)
  {
    system('clear');
    
    $menuOpts = array(
      '1: Add Song/Album',
      '2: View Queue',
      '3: Clear Queue',
      'u: Pause/Unpause',
      'n: Next',
      'p: Prev',
      'q: Quit',
    );
    
    $menuTxt = join("\n", $menuOpts);

    $menu = <<<TXT
##############################
#     clisonic main menu     #
##############################

{$menuTxt}

Enter your choice: 
TXT;
    
    echo $menu;

    $choice = trim(fgets($stdio));

    switch ($choice)
    {
      case 1:
        csController::browse();
        break;
      case 2:
        csController::viewQueue();
        break;
      case 3:
        csQueue::clear();
        break;
      case 'u':
        csPlayer::pause();
        break;
      case 'n':
        csPlayer::next();
        break;
      case 'p':
        csPlayer::prev();
        break;
      case 'q':
        csMemory::cleanUp();
        exit(0);
        break;
      default:
        echo "I did not understand your command. Press enter to try again.";
        fgets($stdio);
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
        csQueue::add($entries);
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
    csQueue::add($entries[$albumIndex]);
  }
  
  public static function viewQueue() 
  {
    global $stdio;
    system('clear');

    $queue = csQueue::get();
    $currentPos = csQueue::getPos();

    if (!is_array($queue))
    {
      echo "Nothing in the queue!\n";
    }
    else
    {
      foreach ($queue as $key => $entry)
      {
        if ($key == $currentPos)
          echo "*";

        echo $key . ": ";
        echo $entry->getTitle() . " - " . $entry->getAlbum('Unknown Album') . "\n";
      }
    }
    
    echo "\nPress Enter to return to the menu.\n";
    $blah = trim(fgets($stdio));
  }
}