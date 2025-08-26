<?php
require_once('vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 

defined('BASEPATH') OR exit('No direct script access allowed');

class Nusukbot extends CI_Controller {
    function __construct(){
        parent::__construct();

    }
 
    function index(){
		// Get the incoming data
		$json = file_get_contents("php://input");
		
		// Debug logging
		error_log("Received webhook data: " . $json);
		
		$up = json_decode($json, true);
		
		// Validate that we have valid data before processing
		if ($up === null || !is_array($up)) {
			// Log the error for debugging
			error_log("Invalid JSON data received: " . $json);
			// Return early to prevent further errors
			return;
		}
		
		// Extract data directly from the array
		$chatID = isset($up['message']['chat']['id']) ? $up['message']['chat']['id'] :
				(isset($up['callback_query']['message']['chat']['id']) ? $up['callback_query']['message']['chat']['id'] : null);

		$fname = isset($up['message']['chat']['first_name']) ? $up['message']['chat']['first_name'] :
				(isset($up['callback_query']['message']['chat']['first_name']) ? $up['callback_query']['message']['chat']['first_name'] : 'User');

		$lname = isset($up['message']['chat']['last_name']) ? $up['message']['chat']['last_name'] :
				(isset($up['callback_query']['message']['chat']['last_name']) ? $up['callback_query']['message']['chat']['last_name'] : '');

		$message = isset($up['message']['text']) ? $up['message']['text'] :
				(isset($up['callback_query']['data']) ? $up['callback_query']['data'] : '[Tidak ada pesan]');
		
		// Debug logging for extracted data
		error_log("Extracted data - chatID: " . $chatID . ", message: " . $message . ", fname: " . $fname . ", lname: " . $lname);
		error_log("Full update data: " . json_encode($up));
		
		// Additional validation - if we don't have a chatID, we can't proceed
		if (!$chatID) {
			error_log("No valid chat ID found in the update data");
			return;
		}

        // Ensure we have a valid message to process
        if (empty($message) || $message === '[Tidak ada pesan]') {
            error_log("No valid message to process");
            return;
        }
        
        $msgdata = explode(" ", $message);
		$msgcount = count($msgdata);
		$fullname = "$fname $lname";
		
		
		// Helper function to send messages using telegram_lib
		$sendMessage = function($text) use ($chatID) {
			if ($chatID && !empty($text)) {
				// Debug logging
				error_log("Sending message to chat ID: " . $chatID . " with text: " . $text);
				// Use direct API call as fallback
				$TOKEN = "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ";
				$apiURL = "https://api.telegram.org/bot$TOKEN";
				$result = file_get_contents($apiURL."/sendmessage?chat_id=".$chatID."&text=".urlencode($text)."&parse_mode=HTML");
				error_log("Send result: " . $result);
				return $result;
			} else {
				error_log("Cannot send message - chatID: " . $chatID . ", text: " . $text);
			}
			return false;
		};
			
		if (strpos($message, "/start") === 0) 
		{
			error_log("Processing /start command for chat ID: " . $chatID);
			$sendMessage("Hai Bosku <b>".$fname." ".$lname."</b>. Selamat datang di layanan Nusukbot.");
		}
		elseif (strpos($message, "/id") === 0) 
		{
			error_log("Processing /id command for chat ID: " . $chatID);
			$sendMessage("Hai kak ".$fname." ".$lname.". ID Telegram: <code>".$chatID."</code>");
		}
		elseif (strpos($message, "/emote") === 0) 
		{
			error_log("Processing /emote command for chat ID: " . $chatID);
			$sendMessage("TESTTTTT".json_decode('"\u2714\u2714\u2705"')."Tanggal ".date("d-M-Y H:i:s")." WITA\n\n");
		}
		elseif (strpos($message, "/test") === 0) 
		{
			error_log("Processing /test command for chat ID: " . $chatID);
			$sendMessage("Test berhasil! Bot berfungsi dengan baik. Chat ID: " . $chatID);
		}
		
		
		elseif (strpos($message, "/exportpdf") === 0) 
		{	
			if($telegram_user['id_user'] != NULL)
			{
				if($msgdata[1] == NULL || $msgdata[2] == NULL)
				{
				$sendMessage("Format Syntax /exportpdf tanggal_awal tanggal_akhir");
				$sendMessage("Contoh: /exportpdf 2021-12-01 2021-12-30");
				}
				else
				{
					$this->mapping_model->export_tg_pdf($msgdata[1],$msgdata[2],$telegram_user['id_area']);
					$filelocation = $_SERVER['DOCUMENT_ROOT']."/xdark/doc/mapping/Data Mapping Capel GMEDIA ".$msgdata[1]." sd ".$msgdata[2].".pdf";
					$finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filelocation);
					$cFile = new CURLFile($filelocation, $finfo);
					
					$content = array('chat_id' => $chatID, 'reply_to_message_id' =>$msg_id,'document' => $cFile);
					$telegram->sendDocument($content);
					unlink($filelocation);
				}
			}
			else
			{
			$sendMessage("<b>Maaf anda tidak memiliki akses.</b>");
			}
		}
		elseif (strpos($message, "/exportxls") === 0) 
		{	
			if($telegram_user['id_user'] != NULL)
			{
				if($msgdata[1] == NULL || $msgdata[2] == NULL)
				{
				$sendMessage("Format Syntax /exportxls tanggal_awal tanggal_akhir");
				$sendMessage("Contoh: /exportxls 2021-12-01 2021-12-30");
				}
				else
				{
					$this->mapping_model->export_tg_xls($msgdata[1],$msgdata[2],$telegram_user['id_area']);
					$filelocation = $_SERVER['DOCUMENT_ROOT']."/xdark/doc/mapping/Data Mapping Capel GMEDIA ".$msgdata[1]." sd ".$msgdata[2].".xlsx";
					$finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filelocation);
					$cFile = new CURLFile($filelocation, $finfo);
					
					$content = array('chat_id' => $chatID, 'reply_to_message_id' =>$msg_id,'document' => $cFile);
					$telegram->sendDocument($content);
					unlink($filelocation);
				}
			}
			else
			{
			$sendMessage("<b>Maaf anda tidak memiliki akses.</b>");
			}
		}
		//Get Data Daily Report
		elseif (strpos($message, "/dailyreport") === 0) 
		{
			if($telegram_user['id_user'] != NULL && $telegram_user['id_area'] == "NTB" && $telegram_user['status'] == "1")
			{
				
				date_default_timezone_set("Asia/Singapore");
				$this->load->helper(array('form', 'url'));
				$daily  = $this->db->get_where('blip_mondevice', array('status' => "down"))->result_array();
				$message_report = "";
				foreach($daily as $repdaily){
				$message_report .= json_decode('"\u27a1"')." Device: <b>".$repdaily['device']."</b>\n".json_decode('"\u27a1"')." Waktu Down: ".$repdaily['waktu_down']."\n".json_decode('"\u27a1"')." Status: <b>".$repdaily['status']."</b>\n\n";
				}
				
				if($message_report == NULL){
				$message = "<b>Tidak ada data down</b>";
				}else{
				$message = $message_report;
				}
				$this->telegram_lib_noc->sendmsg("".json_decode('"\u270F\u270F\u270F"')." <b>DAILY REPORT</b> ".json_decode('"\u270F\u270F\u270F"')."\n<pre>".$message."</pre>".json_decode('"\u270F\u270F\u270F"')." <b>DAILY REPORT</b> ".json_decode('"\u270F\u270F\u270F"'));	
			}
			else
			{
				$sendMessage("<b>Maaf anda tidak memiliki akses.</b>");
			}
		}
				
		
		
	}
	//END SYNTAX DENGAN AKSES TERDAFTAR

public function setwebhook() {
		$TOKEN = "1117257670:AAHlAgCIfzL3DTe4xnI8ygVdVVpr2z9JDls";
		$webhook_url = "https://arrayyan.web.id/gmediabot";
		$apiURL = "https://api.telegram.org/bot$TOKEN/setWebhook?url=" . $webhook_url;
		$result = file_get_contents($apiURL);
		echo "Webhook set result: " . $result;
	}

public function getwebhook() {
		$TOKEN = "1117257670:AAHlAgCIfzL3DTe4xnI8ygVdVVpr2z9JDls";
		$apiURL = "https://api.telegram.org/bot$TOKEN/getWebhookInfo";
		$result = file_get_contents($apiURL);
		echo "Webhook info: " . $result;
	}

public function test() {
		echo "Bot is working!";
		error_log("Test function called");
	}



}  




