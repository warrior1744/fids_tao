<?php
//Edit by : Jim Chang
//==================================================================================
 /*KEYv6, v4對照表( 
                    [0]  => '航廈',
                    [1]  => '種類', 
                    [2]  => '航空公司代碼',
                    [3]  => '航空公司中文',
                    [4]  => '班次',
                    [5]  => '機門',
                    [6]  => '表訂日期',
                    [7]  => '表訂時間',
                    [8]  => '預計/實際日期',
                    [9]  => '預計/實際時間',
                    [10] => '往來地點',
                    [11] => '往來地點英文',
                    [12] => '往來地點中文',
                    [13] => 'Flight Status',
                    [14] => '機型',
                    [15] => '其它航點',
                    [16] => '其它航點英文',
                    [17] => '其它航點中文',
                    [18] => '行李轉盤',
                    [19] => '報到櫃台',
                    [20] => '航班動態中文',    //v6 only
                    [21] => '航班動態英文');*/ //v6 only
//一、二航　FIDS要排除的內容　
//index[1] category       種類　　註：全部都是出境 'D'            checked
//index[6] official_date  表訂日期 　當天，前5小時、後12小時       checked
//==================================================================================
//一、二航　FIDS要顯示的內容
//Column 1     index[7]  official_time   表訂時間
//Column 2     index[12] destination_cht 往來地點中文    index[17] othersites_cht 其它航點中文
//Column 3     index[11] destination_eng 往來地點英文    index[16] othersites_eng 其它航點英文
//Column 4     index[3]  airline_cht     航空公司中文
//Column 5     index[2]  IATA            航公司公代碼   ex: CI, BR, SQ...
//Column 6     index[4]  flight.No       班次　　ex: 2782, 108, 6, 385, 8525...
//Column 7     index[5]  gate            機門　　ex: D1, A3, C5, B9, 612...
//Column 8     index[13] flight_status   Flight Status    ex: '已到Arrived', '取消Cancelled', '出發Deptarted', '時間更改SCHEDULE CHANGE', or'客機載貨Cargo Only', 準時On Time
//Column 9     index[9]  actual_time     預計/實際時間 ex: 11:20, 16:30...  It only appears when the flight status is changed, please check Note 1 and 2.
//==================================================================================
//Note 1：
//1. If Gate doesn't match like D1, A4, B2A..., it only has numbers such as 617
//2. If the column shows '出發Departed', its 客機
//3. If the column shows '客機載貨Cargo Only', its 貨機，but you still can find '出發Departed' string in the data with the same flight
//3. When flight takes off, the ['gate'] information will be empty
//==================================================================================
//Note 2: What data should appear in the last column?

//1. The Flight when is '出發Departed'. The item which has both '客機載貨Cargo Only' and '出發Departed' found in the data,
//   show the actual time when the plane took off
//2. The Flight when is '時間更改SCHEDULE CHANGE', show the actual time instead
//==================================================================================
//source domain https://www.taoyuan-airport.com/main_ch/docdetail.aspx?uid=1706&pid=1706&docid=28402 官網
//txt檔格式 BIG-5 編碼 encoding returns ASCII 編碼 checked

define("URLv6", "https://www.taoyuan-airport.com/uploads/flightx/a_flight_v6.txt");//length is 22
define("URLv4", "https://www.taoyuan-airport.com/uploads/flightx/a_flight_v4.txt");//length is 20
define("KEYv6",  array('terminal', 'category', 'IATA','airline_cht', 'flight.No',
'gate', 'official_date', 'official_time', 'actual_date', 'actual_time',
'destination_IATA', 'destination_eng', 'destination_cht', 'flight_status', 'airplane_model',
'othersites', 'othersites_eng', 'othersites_cht', 'conveyor', 'counter',
'status_cht', 'status_eng'));
define("KEYv4",  array('terminal', 'category', 'IATA','airline_cht', 'flight.No',
'gate', 'official_date', 'official_time', 'actual_date', 'actual_time',
'destination_IATA', 'destination_eng', 'destination_cht', 'flight_status', 'airplane_model',
'othersites', 'othersites_eng', 'othersites_cht', 'conveyor', 'counter'));
$reg_cht_eng = '~(?<!\p{Latin})(?=\p{Latin})|(?<!\p{Han})(?=\p{Han})|(?<![0-9])(?=[0-9])~u';
error_reporting(0);
try{
  $key_length = 0;
  $array_combine;
  $content = @file_get_contents(URLv6, true, null );
    if($content === FALSE){
      $content = @file_get_contents(URLv4, true, null );
      $key_length = 20;
      $array_combine = KEYv4;
    }else{
      $key_length = 22;
      $array_combine = KEYv6;
    }
   }finally{
      $encoding  = mb_detect_encoding($content, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG-5','ASCII'));
      $result = mb_convert_encoding($content, 'UTF-8' ,'BIG-5');
   }
$new_data_array = array();
$data = preg_split("/((\r?\n)|(\r\n?))/", $result);
$today = new DateTime("now", new DateTimezone('Asia/Taipei'));
$today_formatted = $today -> format('Y/m/d H:i:s');//output  2021/12/15 16:01:09
$today_object = datetime::createfromformat('Y/m/d H:i:s', $today_formatted);
foreach($data as $line)
{
  $item_array = explode(',', $line); 
  if( (count($item_array) == $key_length) && ($item_array[1] == 'D'))//the array length should be exactly 22 and [1] should be 'D' for departure
  { 
    $item_key_value = array_combine($array_combine,  $item_array);
    $item_official_dateTime_object = datetime::createfromformat('Y/m/d H:i:s',  $item_key_value['official_date'].' '.$item_key_value['official_time']);//output an object 2021/12/15 16:01:09
    $item_official_diff = $today_object->diff($item_official_dateTime_object);
    $item_official_diff_min = intval($item_official_diff->format('%r%i'));//output between -59 ~ 59
    $item_official_diff_hour = intval($item_official_diff->format('%r%h'));//output between -24 ~ 24
    $item_official_diff_day = intval($item_official_diff->format('%r%a'));//output number of days
    $item_official_diff_hours = 0;

    $item_actual_dateTime_object = datetime::createfromformat('Y/m/d H:i:s',  $item_key_value['actual_date'].' '.$item_key_value['actual_time']);//output an object 2021/12/15 16:01:09
    $item_actual_diff = $today_object->diff($item_actual_dateTime_object);
    $item_actual_diff_min = intval($item_actual_diff->format('%r%i'));//output between -59 ~ 59
    $item_actual_diff_hour = intval($item_actual_diff->format('%r%h'));//output between -24 ~ 24
    $item_actual_diff_day = intval($item_actual_diff->format('%r%a'));//output number of days
    $item_actual_diff_hours = 0;

    if($item_official_diff_day >= 0)
    {
      $item_official_diff_hours = $item_official_diff->days * 24 + $item_official_diff->format('%r%h');
      $item_actual_diff_hours = $item_actual_diff->days * 24 + $item_actual_diff->format('%r%h');
    }else{
      $item_official_diff_hours = $item_official_diff->days * -24 + ($item_official_diff->format('%r%h'));
      $item_actual_diff_hours = $item_actual_diff->days * -24 + ($item_actual_diff->format('%r%h'));
    }
    
    $item_official_diff_min_sum = $item_official_diff_hours * 60 + $item_official_diff_min;//put this item into the array and give it a key, later then sort out with these values
    $item_actual_diff_min_sum = $item_actual_diff_hours * 60 + $item_actual_diff_min;

    if($item_diff_min_sum < 720 && $item_official_diff_min_sum >= -10 && $item_actual_diff_min_sum >= -60)
    { 
      $item_airline_substr = mb_substr($item_key_value['airline_cht'] ,0 ,4 ,"UTF-8" );//substr airline chinese words
      $item_official_time_substr = substr($item_key_value['official_time'] ,0 ,5);//substr official time ex: 10:35:00 to 10:35
      $item_actual_time_substr = substr($item_key_value['actual_time'] ,0 ,5);//substr actual time ex: 10:35:00 to 10:35
      $item_flight_status_replace_space = preg_replace('/[ \t]+$/', '', $item_key_value['flight_status']);//remove trailing whitespaces  ex: 出發Departed
      // $item_flight_status_insert_space = preg_replace('/(?<=[\p{Han}])(?=[A-Za-z\s])/u', ' ', $item_flight_status_replace_space);
      $item_destination_eng_replace = preg_replace('/\/.*$/' ,'', $item_key_value['destination_eng'] );//remove all characters after /   ex: Shanghai/Pudong  to Shanghai
        // echo '$item_diff_day: '.$item_diff_day.', $item_diff_hour: '.$item_diff_hour.', $item_diff_hours: '.$item_diff_hours.', $item_diff_min: '.$item_diff_min.', $item_diff_min_sum: '.$item_diff_min_sum;
        // echo '<br/>';
      $replacements = array(  'airline_cht' => $item_airline_substr,
                              'official_time' => $item_official_time_substr,
                              'actual_time' => $item_actual_time_substr,
                              'flight_status' => $item_flight_status_replace_space,
                              'destination_eng' => $item_destination_eng_replace,
                              'min_diff' => $item_diff_min_sum);
      $replaced = array_replace($item_key_value, $replacements);
      $flight_status = trim($item_key_value['flight_status']);
      if( !(($flight_status != '取消Cancelled') && ctype_space($item_key_value['gate']))){
        array_push($new_data_array, $replaced);//array_push as a new array
      }
    }
  }//end if
}//end foreach
//finally, sort the array by the time differences
usort($new_data_array, function ($a, $b) {
  return $a['min_diff'] <=> $b['min_diff'];
});
//divide the array into 32 length of arrays
$data = array_chunk($new_data_array, 32, true);

if(count($data) < 2){
  echo json_encode(array('page1' => $data[0]),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
}else{
  echo json_encode(array('page1' => $data[0], 'page2' => $data[1]),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
}
 
?>
