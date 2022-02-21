<?php

class timeDiff{

public $now;
public $time_diff;


  
        function __construct(){
                $this->setNow();
        }


function setNow(){
$this->now = new DateTime("now", new DateTimezone('Asia/Taipei'));
$this->now = $this->now -> format('Y/m/d H:i:s');
$this->now = datetime::createfromformat('Y/m/d H:i:s', $this->now);
}

function setTime_diff($time){
  $this->time_diff = $this->now->diff($time);
}
function getTime_diff(){
  return $this->time_diff;
}
function getDayDiff(){
  return intval($this->time_diff->format('%r%a'));
}
function getHourDiff(){
  return intval($this->time_diff->format('%r%h'));
}
function getMinDiff(){
  return intval($this->time_diff->format('%r%i'));
}


// $item_official_diff_min = intval($item_official_diff->format('%r%i'));//output between -59 ~ 59
// $item_official_diff_hour = intval($item_official_diff->format('%r%h'));//output between -24 ~ 24
// $item_official_diff_day = intval($item_official_diff->format('%r%a'));//output number of days

}



?>