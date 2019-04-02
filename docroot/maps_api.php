<?php
header('Content-Type: application/json');
$url = $_GET["name"];
$url = preg_replace("/[^A-Za-z0-9]| |&|-|_/",'', $url);
if (preg_match('/laurelheights/i',$url)) {
  echo file_get_contents('./maps/laurel_heights.json');
} elseif (preg_match('/missionbay/i',$url)) {
  echo file_get_contents('./maps/m_bay.json');
} elseif (preg_match('/parnassus/i',$url)) {
  echo file_get_contents('./maps/parnassus.json');
} elseif (preg_match('/sanfranciscogeneralhospital/i',$url)) {
  echo file_get_contents('./maps/sfgh.json');
} elseif (preg_match('/veteranaffairs/i',$url)) {
  echo file_get_contents('./maps/va.json');
} elseif (preg_match('/mountzion/i',$url)) {
  echo file_get_contents('./maps/mount_zion.json');
} else  {
  echo file_get_contents('./maps/mainlocations.json');
}
