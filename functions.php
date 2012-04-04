<?php
function createFifo($mplayerfifo) 
{
  if (file_exists($mplayerfifo))
  {
    exec("rm -f $mplayerfifo");
  }
  exec("mkfifo $mplayerfifo");
} // createFifo function

function startMplayer()
{
  global $mplayerfifo, $cachesize;
  exec("mplayer -really-quiet -cache $cachesize -slave -input file=$mplayerfifo -idle > /dev/null 2>&1 &", $output);
} // startMplayer function

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
  global $artists_arr, $stdio, $queue, $shmem;
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
      $queue = shm_get_var($shmem, SHM_QUEUE_KEY);
      foreach ($entries_arr as $entry)
        $queue[] = $entry;
      shm_put_var($shmem, SHM_QUEUE_KEY, $queue);
      break 1;
    } 
    $isDir = $entries_arr[$album_index]['isDir'];
    $mdirid = $entries_arr[$album_index]['id'];
  } 
  // We now are broken out of the selection loop and need to add the song to the 
  // queue
  if (strtolower($album_index) != 'a')
  {
    $queue = shm_get_var($shmem, SHM_QUEUE_KEY);
    $queue[] = $entries_arr[$album_index];
    shm_put_var($shmem, SHM_QUEUE_KEY, $queue);
  }
  // loadSong($mdirid);

} // addToQueue function

function clearQueue()
{
  global $shmem, $queue, $mplayerfifo;
  $queue = array();
  shm_put_var($shmem, SHM_QUEUE_KEY, $queue);
  $timeLeft = 0;
  shm_put_var($shmem, SHM_TIMELEFT_KEY, $timeLeft);
  exec("echo 'stop' >> $mplayerfifo");
} // clearQueue function


?>
