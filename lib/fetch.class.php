<?php

class csFetch
{
  private static $artists;
  
  private static function getArgString()
  {
    $args = csSettings::get(CS_API_ARGS);
    
    $argString = '';
    
    if (is_array($args) && count($args))
    {
      $argString = '?';
      
      $params = array();
      foreach ($args as $key => $value)
      {
        $params[] = urlencode($key) . '=' . urlencode($value);
      }
      
      $argString .= join('&', $params);
    }
    
    return $argString;
  }
  
  public static function getArtists()
  {
    if (!is_null(self::$artists))
      return self::$artists;
    
    $baseurl = csSettings::get(CS_API_URL);
    $argString = self::getArgString();
    
    $xmldata = `curl -ks '$baseurl/getIndexes.view$argString&musicFolderId=0'`;
    
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
    $baseurl = csSettings::get(CS_API_URL);
    $argString = self::getArgString();
    
    $xmldata = `curl -ks '$baseurl/getMusicDirectory.view$argString&id=$dirId'`;
    
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
  
  public static function getSong($entry)
  {
    $baseurl = csSettings::get(CS_API_URL);
    $argString = self::getArgString();
    
    $mp3file = $entry->getFileName();
    exec("curl -ksN '$baseurl/stream.view$argString&id=$id' -o $mp3file > /dev/null 2>&1 &");
    
    return $mp3file;
  }
}