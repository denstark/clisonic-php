<?php

class csFetch
{
  private static $artists;
  
  public static function getArtists()
  {
    if (!is_null(self::$artists))
      return self::$artists;
    
    global $baseurl, $dopts;
    $xmldata = `curl -ks '$baseurl/getIndexes.view$dopts&musicFolderId=0'`;
    
    $artistsXML = new SimpleXMLElement($xmldata);
    $artists = array();
    
    foreach ($artistsXML->indexes->index as $index)
    {
      foreach ($index->artist as $artist)
      {
        $name = (string) $artist->attributes()->name;
        $id = (string) $artist->attributes()->id;
        $artists[] = array('name' => $name, 'id' => $id);
      }
    }
    
    return self::$artists = $artists;
  }
  
  public static function getMusicDir($dirId)
  {
    global $baseurl, $dopts;
    $xmldata = `curl -ks '$baseurl/getMusicDirectory.view$dopts&id=$dirId'`;
    
    $entriesXML = new SimpleXMLElement($xmldata);
    $entries = array();
    
    foreach ($entriesXML->directory->child as $entryXML)
    {
      $entry = new csQueueEntry();
      $entry->setId((string) $entryXML->attributes()->id);
      $entry->setTitle((string) $entryXML->attributes()->title);
      $entry->setIsDir((bool) ($entryXML->attributes()->isDir == 'true'));
      
      if (!$entry->isDir())
      {
        $duration = (int) $entryXML->attributes()->duration;
        $album = (string) $entryXML->attributes()->album;

        $entry->setDuration($duration);
        $entry->setAlbum($album);
      }
      
      $entries[] = $entry;
    }
    
    return $entries;
  }
  
  public static function getSong($id)
  {
    global $baseurl, $dopts, $mp3dir;
    
    $mp3file = "$mp3dir/$id.mp3";
    exec("curl -ksN '$baseurl/stream.view$dopts&id=$id' -o $mp3file > /dev/null 2>&1 &");
    
    return $mp3file;
  }
}