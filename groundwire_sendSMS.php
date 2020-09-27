<?php

//Only for TESTs!
$myfile = fopen("log.txt", "a") or die("Unable to open file!");
//echo '**************************\n';
var_dump($_GET);

//Reduce picture function
function picture($ext) {
    if($ext == 'image/jpeg' || $ext == 'image/gif' || $ext == 'image/png' || $ext == 'image/jpg'){
		//$output = shell_exec("convert ".$source." -resize 1024x768\> ".$source);
    	return true;
	} else {
		//echo "No valid extension to compress!";
		return false;
	}
}

//$d = compress($source_img, $destination_img, 90);

$sender = isset($_GET['from']) ? trim(str_replace("+","",$_GET["from"])) : '';
$recipient = isset($_GET['to']) ? trim(str_replace("+","",$_GET["to"])) : '';
$message = isset($_GET['body']) ? trim(str_replace("___"," ",$_GET['body'])) : '';
$data = isset($_GET['data']) ? trim($_GET['data']) : '';
$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';
//$data = isset($_GET['data']) ? trim(str_replace(":https:",":'https:",$_GET['data'])) : '';
/*$data = str_replace(",encryption-key:","',encryption-key:'",$data);
$data = str_replace(",hash:","',hash:",$data);
$data = str_replace("content-type:","content-type:'",$data);
$data = str_replace(",content-url:","',content-url:",$data);*/
$data = str_replace("https://","",$data);
$datapieces = explode(",", $data);
$datapieces[0] = str_replace("{attachments:[{content-size:","content-size:",$datapieces[0]);
$message = /*"'".*/str_replace("body:","",str_replace("}","",str_replace("___"," ",$datapieces[count($datapieces) - 1])))/*."'"*/;
//var_dump($datapieces[count($datapieces) - 1]);
//var_dump(str_replace("body:","",str_replace("}","",$datapieces[count($datapieces) - 1])));
var_dump($message);

$data = array();
$count = 0;
for($i = 0;$i<count($datapieces) - 1;$i++){
	$datatemp = explode(":", $datapieces[$i]);
	$datatemp[0] = str_replace("{","",$datatemp[0]);
	if(isset($data[$count][$datatemp[0]])) $count = $count + 1;
	$data[$count][$datatemp[0]] = str_replace("}","",str_replace("}]","",$datatemp[1]));
}

//var_dump($data);

//$data['attachments'][0];
//$data['attachments'][0]['content-type'];
//$data['attachments'][0]['content-url'];
//$data['attachments'][0]['encryption-key'];
//$data['attachments'][0]['hash'];

$SMSuser = 'api_user';
$SMSpass = 'password';
$iv = '00000000000000000000000000000000';
$encfile = "";
$decfile ="";
$i = 0;
$files = array();

$total = 0;
$img = false;
for($j = 0; $j < count($data); $j++){
	$total = $total + intval($data[$j]['content-size']);
	if (picture($data[$i]['content-type']) == true) $img = true;
	//var_dump(intval($data[$j]['content-size']));
}
//var_dump($total);
if($total < 2048000 || ($total > 2048000 && $img == true)) {

for($i = 0; $i < count($data); $i++){
	if((intval($data[$i]['content-size']) > 2048000 && picture($data[$i]['content-type']) == true) || (intval($data[$i]['content-size']) < 2048000 && picture($data[$i]['content-type']) == false) || (intval($data[$i]['content-size']) < 2048000)){
	
		echo "For for ".$i." until ".count($data);
		$url = "https://".$data[$i]['content-url'];
		$key = $data[$i]['encryption-key'];
		$ext = explode('/', $data[$i]['content-type']);
		$encfile = "encfile".$data[$i]['hash'].".".$ext[1];
		$decfile = "decfile".$data[$i]['hash'].".".$ext[1];
	
		//var_dump($encfile);
		//var_dump($decfile);
	
		$myencfile = fopen("temp/".$encfile, "a") or die("Unable to open file!");
		fwrite($myencfile, file_put_contents("temp/".$encfile, file_get_contents($url)));
		//echo "<br/>openssl enc -aes-128-ctr -d -K ".$key." -iv ".$iv." -nopad -in temp/".$encfile." -out temp/".$decfile;
		$output = shell_exec("openssl enc -aes-128-ctr -d -K ".$key." -iv ".$iv." -nopad -in temp/".$encfile." -out temp/".$decfile);
		if(intval($data[$i]['content-size']) > 512000){
			//$compress_result = mycompress("temp/".$decfile, $ext);
			if($ext[1] == 'jpeg' || $ext[1] == 'gif' || $ext[1] == 'png' || $ext[1] == 'jpg'){
				$outputconvert = shell_exec("convert "."temp/".$decfile." -resize 1024x768 "."temp/".$decfile);
				echo $outputconvert;
    			//return $output;
			} else {
				echo "No valid extension to compress!";
				//return false;
			}
			//if (!unlink("temp/".$decfile)) echo ("$decfile cannot be deleted due to an error");
			//$decfile = "com_" . $decfile;
		}
		//if(!isset($output)) echo ;
		//sleep for 3 seconds
		//sleep(5);
		$files[$i] = array("FileName"=>$decfile,"FileContent"=>file_get_contents("temp/".$decfile));
		//array_push($files,"MMSFile"=>["FileName"=>$decfile,"FileContent"=>base64_decode(file_get_contents("temp/".$decfile))]);
		// Use unlink() function to delete a file  
		if (!unlink("temp/".$encfile)) echo ("$encfile cannot be deleted due to an error");  
		if (!unlink("temp/".$decfile)) echo ("$decfile cannot be deleted due to an error");  
		
	} else {
		echo "File it is to big to be send; action skiped; only less of 2 mb allowed!";
		$files[$i] = array("FileName"=>"Attachemnt_error.png","FileContent"=>file_get_contents("lib/pictures/error.png"));
	}
}

} else {
	echo "The attachemnts are bigger than the allowed size!; Operation Canceled!";
	$files[0] = array("FileName"=>"Attachemnt_error.png","FileContent"=>file_get_contents("lib/pictures/error.png"));
	$message = "The attachemnts are bigger than the allowed size!; Operation Canceled!; Maximun it is 2 mb! -> " . $message;
}

try{
$soapclient = new SoapClient('https://backoffice.voipinnovations.com/Services/APIService.asmx?wsdl');
$param=array('login'=>$SMSuser,'secret'=>$SMSpass,'sender'=>$sender,'recipient'=>$recipient,'message'=>$message, 'files'=>$files);
	
	$response =$soapclient->SendMMS($param);
	$result = json_encode($response);
	echo $result;
	$smsresult = $response->SendMMSResult->responseMessage;
	if($smsresult != 'Success' && $smsresult != 'Invalid sender' && $smsresult != 'Invalid TN. Sender and recipient must be valid phone numbers and include country code.') $smsresult = 'MMS Error';
	
	$comment = "The MMS Service for recipient " . $recipient . " from " . $sender . " responded: " . $smsresult . "!";
	
				
}catch(Exception $e){
	echo $e->getMessage();
}

fwrite($myfile, file_put_contents("log.txt", ob_get_flush()));
fclose($myfile);

/**/

?>