<?php

function getArtists()
{
  global $baseurl, $dopts;
  $xmldata = `curl -ks '$baseurl/getIndexes.view$dopts&musicFolderId=0'`;
  $artists = new SimpleXMLElement($xmldata);
  $artists_arr = array();
  $count = 0;
  foreach ($artists->indexes->index as $index)
  {
    foreach ($index->artist as $artist)
    {
     $name = (string)$artist->attributes()->name;
     $id = (string)$artist->attributes()->id;
     $artists_arr[$count] = array('name' => $name, 'id' => $id);
     $count++;
    }
  }
  return $artists_arr;
} // getArtists function

function printArtists($artists_arr)
{
  for ($i = 0; $i < count($artists_arr); $i++)
  {
    $name = $artists_arr[$i]['name'];
    $id = $artists_arr[$i]['id'];
    echo "$i: $name\n";
  }
} // printArtists function

function getMusicDir($mdirid)
{
  global $baseurl, $dopts;
  $xmldata = `curl -ks '$baseurl/getMusicDirectory.view$dopts&id=$mdirid'`;
  $entries = new SimpleXMLElement($xmldata);
  $entries_arr = array();
  $count = 0;
  foreach ($entries->directory->child as $entry)
  {
    $title = (string)$entry->attributes()->title;
    $id = (string)$entry->attributes()->id;
    $isDir = (string)$entry->attributes()->isDir;
    $duration = (int)$entry->attributes()->duration;
    $album = (string)$entry->attributes()->album;
    $isDir = ($isDir == 'true');
    if ($isDir)
    {
      $entries_arr[$count] = array('title' => $title, 'id' => $id, 'isDir' => $isDir);
    } else // It's file, do file things
    {
      $entries_arr[$count] = array(
        'title'     => $title,
        'id'        => $id,
        'isDir'     => $isDir,
        'duration'  => $duration,
        'album'     => $album,
        'p'         => 0,
      );
    }
    echo "$count: $title\n";
    $count++;
  }
  return $entries_arr;

} // getMusicDir function

function loadSong($id)
{
  global $baseurl, $dopts, $mp3dir, $mplayerfifo, $songcount;
  $mp3file = "$mp3dir/$id.mp3";
  exec("curl -ksN '$baseurl/stream.view$dopts&id=$id' -o $mp3file > /dev/null 2>&1 &");
  if ($songcount == 0)
    sleep(1);
  system('pgrep -f ".*mplayer.*clisonic.*"', $isrunning);
  if ($isrunning == 0)
  {
    // Mplayer is running
    // system("echo 'loadfile $mp3file $songcount' >> $mplayerfifo"); // 
    // Needs to be reimplemented when I figure out a solution to queuing from 
    // $x seconds behind the current song
    system("echo 'loadfile $mp3file' >> $mplayerfifo");
    // $songcount++;
  } else 
  {
    exit("Mplayer wasn't running for some reason");
  }

} // loadSong function

function addToQueue()
{
  global $artists_arr, $stdio, $queue;
  $isDir = true;
  printArtists($artists_arr);
  echo "Please input an artist number: ";
  $artist_index = trim(fgets($stdio));
  $mdirid = $artists_arr[$artist_index]['id'];

  while ($isDir)
  {
    $entries_arr = getMusicDir($mdirid);
    echo "Please input an album/track number (A to queue the album): ";
    $album_index = trim(fgets($stdio));
    if ( strtolower($album_index) == 'a')
    {
      $queue = csMemory::get(SHM_QUEUE_KEY);
      foreach ($entries_arr as $entry)
        $queue[] = $entry;
      
      csMemory::set(SHM_QUEUE_KEY, $queue);
      break 1;
    } 
    $isDir = $entries_arr[$album_index]['isDir'];
    $mdirid = $entries_arr[$album_index]['id'];
  } 
  // We now are broken out of the selection loop and need to add the song to the 
  // queue
  if (strtolower($album_index) != 'a')
  {
    $queue = csMemory::get(SHM_QUEUE_KEY);
    $queue[] = $entries_arr[$album_index];
    csMemory::set(SHM_QUEUE_KEY, $queue);
  }
  // loadSong($mdirid);

} // addToQueue function

function clearQueue()
{
  global $queue, $mplayerfifo;
  
  csMemory::set(SHM_QUEUE_KEY, array());
  csMemory::set(SHM_TIMELEFT_KEY, 0);
  
  exec("echo 'stop' >> $mplayerfifo");
} // clearQueue function

function viewQueue() 
{
  global $stdio;
  system('clear');
  
  $queue = csMemory::get(SHM_QUEUE_KEY);
  $currentPos = csMemory::get(SHM_CQ_POS_KEY);
  if (!is_array($queue))
  {
    echo "Nothing in the queue!\n";
    return;
  }
  $count = 0;
  foreach ($queue as $entry)
  {
    if ($count == $currentPos)
      echo "*";
    echo $count . ": ";
    echo $entry['title'] . " - " . $entry['album'] . "\n";
    $count++;
  }
  echo "\nPress Enter to return to the menu.\n";
  $blah = trim(fgets($stdio));
} // viewQueue function
