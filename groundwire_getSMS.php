<?php
//require_once (__DIR__.'/crest/crest.php');

$recipient = isset($_GET['to']) ? trim(str_replace("+","",$_GET["to"])) : '';
$message = isset($_GET['text']) ? $_GET["text"] : '';
$sender = isset($_GET['from']) ? $_GET["from"] : '';
$type = isset($_GET['messageType']) ? $_GET["messageType"] : 'SMS';
$result = ['result' => 'false'];
if($type === "MMS"){
	$files = isset($_GET['fileUrls']) ? $_GET["fileUrls"] : "['']";
	$files = ltrim($files, "['");
	$files = rtrim($files, "']");
	$filesArr = explode("', '", $files);
	if(count($filesArr) > 1) $files = str_replace("', '", ", ", $files);
}

//Only for TESTs!
$myfile = fopen("log.txt", "a") or die("Unable to open file!");
//echo '**************************\n';
//echo date("Y.m.d G:i:s")."\n";
var_dump($_GET);
//var_dump($_POST); //from

$domain = "pbx.domain.com";
$pbxhandler = "https://".$domain."/app/sms/hook/sms_hook_vi_new.php";

if($recipient != "" && ($message != "" || $files != "") && $sender != ""){
		
	//For SMS
	$out = file_get_contents($pbxhandler."?from=".$sender."&to=".$recipient."&text=".$message . "&messageType=SMS");
	echo $out;
		
	if($type === "MMS") { //For MMS
	//file_put_contents("temp/temp.jpg",file_get_contents($filesArr[0]));
	//var_dump($_SERVER['DOCUMENT_ROOT']);
		$i = 0;
		$uploadfiles = "";
		$finalurl = "https://domain.com/temp_get_attch/";
		foreach($filesArr as $file){
			
			//$fileext = substr($file, -3);
			
			//$attchname = $i."_MMS_".time()."_.".$fileext;
			//$myattch = fopen("temp_get_attch/".$attchname, "a");
			//fwrite($myattch, file_put_contents("temp_get_attch/".$attchname, file_get_contents($file)));
			
	
			//$uploadfiles = $uploadfiles . $finalurl . $attchname . ";";
			$uploadfiles = $uploadfiles . $file . ";";
			$i = $i + 1;	
		}
		
		$message = $message . ". Attachments->" . $uploadfiles;
		$out = file_get_contents($pbxhandler."?from=".$sender."&to=".$recipient."&text=".$message . "&messageType=SMS");
		echo $out;
	
	/*$comment = "A MMS was receibed from sender: " . $sender . ", contact: " . $contact['result'][0]['NAME'] . " " . $contact['result'][0]['LAST_NAME'] . ", with the message: " . $message . " and files! The new files were uploaded as " . $uploadfiles." in ubication ".$contact['result'][0]['UF_CRM_1594736087']."!";*/	
	}
			
} else {
	echo "There it is no Information to work!</br>";
}

echo $result;

//Only for TESTs!
fwrite($myfile, file_put_contents("log.txt", ob_get_flush()));
fclose($myfile);

?>