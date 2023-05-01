<?php
function set_data_updated(){
	global $wooden_walkways;
	$wooden_walkways=read_to_wooden_walkway();
	//如果檔案不是檔案不是今天就更新
	if ($wooden_walkways==false||date("Ymd")!=date("Ymd",strtotime($wooden_walkways[0]->date)))
		read_to_open_times();
	//display_wooden_walkways();	
}
function read_to_json($url)
{
	$fp=fopen($url,"r");
	$json_data="";
	while(($data=fgets($fp))!== FALSE)
			$json_data.=$data;
	fclose($fp);
	return $json_data;
}
function counter()
{
	$filename="counter.dat";
	if (!file_exists($filename)) 
		touch($filename);	
	$counter = intval(file_get_contents($filename));
	if (!isset($_COOKIE['visitor'])) {
		$counter++;
		$fp = fopen($filename, "w");
		flock($fp, LOCK_EX);   // do an exclusive lock
		fwrite($fp, $counter);
		flock($fp, LOCK_UN);   // release the lock
		fclose($fp);
		setcookie("visitor", 1, time()+3600);
	}
	return $counter;
}
function read_to_wooden_walkway(){
	global $WoodenWalkwayFile;
	if (file_exists($WoodenWalkwayFile)){
		$objData = file_get_contents($WoodenWalkwayFile);
		$obj = unserialize($objData);           
		return $obj;
		}
	return false;	
}
function wooden_walkway_save_to_file($content){
	global $WoodenWalkwayFile;
	if (!file_exists($WoodenWalkwayFile)) 
		touch($WoodenWalkwayFile);
	if (is_writable($WoodenWalkwayFile)) {
		$fp = fopen($WoodenWalkwayFile, "w"); 
		fwrite($fp, serialize($content)); 
		fclose($fp);
		return true;
	}
	return false;
}
function read_to_open_times(){
	global $Sunset_URL,$Tidal_URL,$wooden_walkways;
	$wooden_walkways=array();//開放日期時間陣列
	$tidal=read_to_json($Tidal_URL);
	$tidal_json=json_decode($tidal);
	foreach ($tidal_json->records->location[0]->validTime as $validTime) 
		{
			$wooden_walkway=new WoodenWalkway;
			$wooden_walkway->date=$validTime->startTime;
			set_open_times($wooden_walkway,$validTime->weatherElement);
			array_push($wooden_walkways, $wooden_walkway);
			//display_wooden_walkway($wooden_walkway);
		}
	//$Sunset_URL timeFrom=2022-10-29&timeTo=2022-11-29
	$timeFrom=date("Y-m-d");
	$timeTo=date("Y-m-d",strtotime("+30 day"));
	$Sunset_URL = str_replace("2022-10-29", $timeFrom, $Sunset_URL);
	$Sunset_URL = str_replace("2022-11-29", $timeTo, $Sunset_URL);	
	$sunset=read_to_json($Sunset_URL);
	$sunset_json=json_decode($sunset);
	//echo $sunset;
	//display_wooden_walkways();
	for ($i=0;$i<30;$i++)
		set_sunset($wooden_walkways[$i],$sunset_json->records->locations->location[0]->time[$i]);
	wooden_walkway_save_to_file($wooden_walkways);//存檔
}
function display_wooden_walkway($wooden_walkway){
	for ($i=0;$i<count($wooden_walkway->open_times_start);$i++)	{
		echo $wooden_walkway->date."--";
		echo $wooden_walkway->open_times_start[$i]."--";
		echo $wooden_walkway->open_times_end[$i]."--";
		echo "<br>";	
	}
}
function display_wooden_walkways(){
	global $wooden_walkways;
	foreach ($wooden_walkways as $wooden_walkway){
		for ($i=0;$i<count($wooden_walkway->open_times_start);$i++)	{
			echo $wooden_walkway->date."--";
			echo $wooden_walkway->open_times_start[$i]."--";
			echo $wooden_walkway->open_times_end[$i]."--";
			echo "<br>";	
		}
	}
}

function set_sunset(&$wooden_walkway,$time){
	//echo $time->Date."<br>";
	$last_one=count($wooden_walkway->open_times_start)-1;
	$start_time=string_to_minutes($wooden_walkway->open_times_start[$last_one]);
	$end_time=string_to_minutes($wooden_walkway->open_times_end[$last_one]);
	//echo "SunSetTime:".$time->SunSetTime."<br>";
	$sunset=string_to_minutes($time->SunSetTime);
	//echo "sunset"+$sunset+"<br>";
	if ($sunset>=$end_time)
		return;
	if 	($sunset<$start_time)
		{
		array_splice($wooden_walkway->open_times_end,1);
		array_splice($wooden_walkway->open_times_start,1);
		}
	if 	($sunset>$start_time&&$sunset<$end_time)
		$wooden_walkway->open_times_end[$last_one]=$time->SunSetTime;
}
function set_open_times(&$wooden_walkway,$weatherElement){
	if ($weatherElement[1]->elementValue=="大")//潮差
		$time_interval=120;
	else
		$time_interval=90;
	array_push($wooden_walkway->open_times_start,"08:00");
	array_push($wooden_walkway->open_times_end,"18:30");
	foreach ($weatherElement[2]->time as $time)
		if ($time->parameter[0]->parameterValue=="滿潮")
			separate_open_times($wooden_walkway,$time_interval,$time->dataTime);
}
function separate_open_times(&$wooden_walkway,$time_interval,$dataTime){
	//"2022-10-30 01:16:00"
	$tidal_start=string_to_minutes(substr($dataTime,11,5))-$time_interval;
	$tidal_end=string_to_minutes(substr($dataTime,11,5))+$time_interval;
	//echo minutes_to_string($tidal_start)."*".minutes_to_string($tidal_end)."*";
	if ($tidal_end<=480||$tidal_start>=1110)
		return;
	for ($i=0;$i<count($wooden_walkway->open_times_start);$i++)
		{
		$start_time=string_to_minutes($wooden_walkway->open_times_start[$i]);
		$end_time=string_to_minutes($wooden_walkway->open_times_end[$i]);
		//echo $dataTime."*".$tidal_start."*".$tidal_end."*".$start_time."*".$end_time."*<br>";
		//*900*1080*480*1080
		if ($tidal_end<=$start_time || $tidal_start>=$end_time)
			continue;
		if ( $tidal_end>$start_time && $tidal_start<=$start_time)
			$wooden_walkway->open_times_start[$i]=minutes_to_string($tidal_end);
		if 	($tidal_end<$end_time && $tidal_start>$start_time){
			array_push($wooden_walkway->open_times_start,minutes_to_string($tidal_end));
			array_push($wooden_walkway->open_times_end,$wooden_walkway->open_times_end[$i]);
			$wooden_walkway->open_times_end[$i]=minutes_to_string($tidal_start);
			}
		if ($tidal_end>=$end_time && $tidal_start<$end_time)	
			{
			$wooden_walkway->open_times_end[$i]=minutes_to_string($tidal_start);
			//echo "*".$end_time."*";
			}
		}

}
function string_to_minutes($string){
//07:40
	return intval(substr($string,0,2))*60+intval(substr($string,3,2)); 
}
function minutes_to_string($minutes){
	$hours=floor($minutes/60);
	$minute=$minutes % 60;
	return two_digits($hours).":".two_digits($minute);
}
function two_digits($number){
	if ($number<=9)
		return "0".$number;
	else
		return "".$number;	
}
class WoodenWalkway{
	public $date;
	public $open_times_start=array();
	public $open_times_end=array();
} 
?>
