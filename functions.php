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
  echo "starting mpalyer\n";
  exec("mplayer -really-quiet -cache $cachesize -slave -input file=$mplayerfifo -idle > /dev/null 2>&1 &", $output);
  echo "mplayer started!\n";
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
    if ($isDir) 
    {
      $entries_arr[$count] = array('title' => $title, 'id' => $id, 'isDir' => $isDir);
    } else // It's file, do file things
    {

    }
    echo "$count: $title\n";
    $count++;

    //print_r($entry);
  }
  return $entries_arr;

} // getMusicDir function

function loadSong($id)
{
  global $baseurl, $dopts, $mp3dir, $mplayerfifo, $songcount;
  $mp3file = "$mp3dir/$id.mp3";
  exec("curl -ksN '$baseurl/stream.view$dopts&id=$id' -o $mp3file > /dev/null 2>&1 &");
  if ($songcount == 0)
    sleep(2);
  system('pgrep -f ".*mplayer.*clisonic.*"', $isrunning);
  if ($isrunning == 0)
  {
    // Mplayer is running
    system("echo 'loadfile $mp3file $songcount' >> $mplayerfifo");
    $songcount++;
  } else 
  {
    exit("Mplayer wasn't running for some reason");
  }

} // loadSong function





?>
