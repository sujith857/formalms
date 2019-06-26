<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

define("CORE", true);
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

if(!function_exists("report_log")){
	function report_log($string){
		ob_end_flush();
		$curtime = date("d-m-Y G:i:s");
		echo "[$curtime] $string".PHP_EOL;
		ob_start();
	}
}

require_once(_adm_.'/lib/lib.permission.php');
require_once(_base_.'/lib/lib.pagewriter.php');

//--- here the specific code ---------------------------------------------------
//#17598 - REPORT - CRON REPORT RICHIEDE UTENTE LOGGATO (patch)
$roleid='/admin/view';
$GLOBALS['user_roles'][$roleid] = true;
$roleid='/admin/view_all';
$GLOBALS['user_roles'][$roleid] = true;

setLanguage('english');

function getReportRecipients($id_rep) {
	$output = [];
	$selected_schedules = [];
	$current_time = date('H:i');
	
	//check for daily
	$recipients = [];
	$qry = "
			SELECT * FROM %lms_report_schedule
			WHERE period LIKE '%day%'
			AND id_report_filter=$id_rep
			AND time < '$current_time'
			AND enabled = 1
			AND (last_execution is null OR last_execution < CURDATE())
		";
	$res = sql_query($qry);
	
	while($row = sql_fetch_assoc($res)){
		
		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=" . $row['id_report_schedule'];
		$res2 = sql_query($qry2);
		
		while(list($recipient) = sql_fetch_row($res2)){
			$recipients[] = $recipient; //idst of the recipients
		}
		
		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if(!empty($recipients_flat)){
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (" . implode(',', $recipients_flat) . ") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while(list($email) = sql_fetch_row($res3)) $output[] = $email;
			
			//registra la pianificazione tra quelle da eseguire
			$selected_schedules[] = [
				'id_report_schedule' => $row['id_report_schedule'],
				'period' => 'day'
			];
		}
	}
	
	
	//cerca i report da eseguire prima possibile
	
	$recipients = [];
	$qry = "
				SELECT * FROM %lms_report_schedule
				WHERE period LIKE '%now%'
				AND id_report_filter=$id_rep
				AND enabled = 1
			";
	$res = sql_query($qry);
	
	while($row = sql_fetch_assoc($res)){
		
		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=" . $row['id_report_schedule'];
		$res2 = sql_query($qry2);
		
		while(list($recipient) = sql_fetch_row($res2)){
			$recipients[] = $recipient; //idst of the recipients
		}
		
		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if(!empty($recipients_flat)){
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (" . implode(',', $recipients_flat) . ") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while(list($email) = sql_fetch_row($res3)) $output[] = $email;
			
			//registra la pianificazione tra quelle da eseguire
			$selected_schedules[] = [
				'id_report_schedule' => $row['id_report_schedule'],
				'period' => 'now'
			];
		}
	}
	
	
	//check for weekly
	$daynumber = date('w');
	$recipients = [];
	
	$qry = "
				SELECT * FROM %lms_report_schedule
				WHERE period LIKE '%week,$daynumber%'
				AND id_report_filter=$id_rep
				AND time < '$current_time'
				AND enabled = 1
				AND (last_execution is null OR last_execution < CURDATE())
			";
	$res = sql_query($qry);
	
	while($row = sql_fetch_assoc($res)){
		
		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=" . $row['id_report_schedule'];
		$res2 = sql_query($qry2);
		
		while(list($recipient) = sql_fetch_row($res2)){
			$recipients[] = $recipient;
		}
		
		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if(!empty($recipients_flat)){
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (" . implode(',', $recipients_flat) . ") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while(list($email) = sql_fetch_row($res3)) $output[] = $email;
			
			//registra la pianificazione tra quelle da eseguire
			$selected_schedules[] = [
				'id_report_schedule' => $row['id_report_schedule'],
				'period' => 'week'
			];
		}
	}
	
	//check for monthly
	$monthdaynumber = date('j'); //today's day of the month, 1-31
	$monthdays = date('t'); //amount of days in current month 28-31
	$recipients = [];
	
	$options = [];
	if($monthdays < 31 && $monthdaynumber == $monthdays){ //if it's the last day of tehe month
		for($i = 31; $i >= $monthdays; $i--){
			$options[] = "'month,$i'";
		}
	} else{
		$options[] = "'month,$monthdaynumber'";
	}
	
	$qry = "
			SELECT * FROM %lms_report_schedule
			WHERE period IN (" . implode(',', $options) . ")
			AND id_report_filter=$id_rep
			AND time < '$current_time'
			AND enabled = 1
			AND (last_execution is null OR last_execution < CURDATE())
		";
	$res = sql_query($qry);
	
	
	while($row = sql_fetch_assoc($res)){
		
		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=" . $row['id_report_schedule'];
		$res2 = sql_query($qry2);
		
		while(list($recipient) = sql_fetch_row($res2)){
			$recipients[] = $recipient;
		}
		
		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if(!empty($recipients_flat)){
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (" . implode(',', $recipients_flat) . ") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while(list($email) = sql_fetch_row($res3)) $output[] = $email;
			
			//registra la pianificazione tra quelle da eseguire
			$selected_schedules[] = [
				'id_report_schedule' => $row['id_report_schedule'],
				'period' => 'month'
			];
		}
	}
	
	return [
		'recipients' => array_unique($output),
		'schedules' => $selected_schedules
	];

}

function adaptFileName($fname) {
	return preg_replace("/[^A-Za-z0-9 ]/", "_", $fname).'_'.date('Y-m-d_H-i-s');
}
	
	/**
	 * Recursively deletes a directory and all its content
	 * @param string $dir Directory path to be deleted
	 * @return bool returns true if no errors
	 */
function recursive_delete_directory($dir){
	if(!file_exists($dir)) return true;
	if(!is_dir($dir)) return unlink($dir);
	foreach(scandir($dir) as $item){
		if($item == '.' || $item == '..') continue;
		if(!recursive_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) return false;
	}
	return rmdir($dir);
}

/**
 * Updates executed schedules: deletes one shot schedules and updates the value of last_execution for others
 * @param array $schedules array of schedule ids to be updated
 */
function update_schedules($schedules){
		
		foreach($schedules as $schedule){
			$id_report_schedule = $schedule['id_report_schedule'];
			$period = $schedule['period'];
			switch($period){
				case 'now':
					$qry = "DELETE FROM %lms_report_schedule WHERE id_report_schedule = $id_report_schedule";
					sql_query($qry);
					$qry = "DELETE FROM %lms_report_schedule_recipient WHERE id_report_schedule = $id_report_schedule";
					sql_query($qry);
					break;
				default:
					$qry = "UPDATE %lms_report_schedule SET last_execution = now() WHERE id_report_schedule = $id_report_schedule";
					sql_query($qry);
					break;
			}
		}
	}

//******************************************************************************


$report_persistence_days = Get::sett('report_persistence_days', 30);
$report_max_email_size = Get::sett('report_max_email_size_MB', 0);
$report_store_folder = Get::sett('report_storage_folder', '/files/common/report/');
$base_url = Get::sett('url', '');
$report_uuid_prefix = 'uuid';


require_once(_base_.'/lib/lib.upload.php');




require_once(_base_.'/lib/lib.mailer.php');
$mailer = DoceboMailer::getInstance();

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();


$path = _base_.'/files/tmp/';
$qry = "SELECT * FROM %lms_report_filter";
$res = sql_query($qry);
sl_open_fileoperations();


$log_opened = false;

//apply an execution lock by occupying port 9999
$lock_stream = @stream_socket_server('tcp://0.0.0.0:9999', $errno, $errmsg);
if($lock_stream){

	while ($row = sql_fetch_assoc($res)) {
		
		$recipients_data = getReportRecipients($row['id_filter']);
		$recipients = $recipients_data['recipients'];
	
		if (count($recipients)>0) {
			
			if(!$log_opened){
				report_log("STARTING REPORT EXECUTION ...");
				$log_opened = true;
			}
			
			$schedules = $recipients_data['schedules'];
	
			$data = unserialize( $row['filter_data'] ) ;
	
			$query_report = "SELECT class_name, file_name, report_name "
				." FROM %lms_report "
				." WHERE id_report = '".$data['id_report']."'";
			$re_report = sql_query($query_report);
			if($re_report && sql_num_rows($re_report)) {
	
				list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);
	
				require_once(_lms_.'/admin/modules/report/'.$file_name);
				$temp = new $class_name( $data['id_report'] );
				$temp->author = $row['author'];
	
				$tmpfile = adaptFileName($row['filter_name']).'.xls';
				
				$start_time = microtime(true);
				$file = sl_fopen('/tmp/'.$tmpfile, "w");
				fwrite($file, $temp->getXLS($data['columns_filter_category'], $data));
				fclose($file);
				$execution_time_secs = round(microtime(true) - $start_time, 0);
				$execution_time_secs = ltrim(sprintf('%02dh%02dm%02ds', floor($execution_time_secs / 3600), floor(($execution_time_secs / 60) % 60), ($execution_time_secs % 60)), '0hm');
				if($execution_time_secs == 's') $execution_time_secs = '0s';
				report_log($row['filter_name'] . ': Report generated in ' . $execution_time_secs);
				
				//Gets XLS size in MB
				clearstatcache($path . $tmpfile);
				$report_size = filesize($path . $tmpfile);
				
				//Checks if report should be sent by link or attachment
				if($report_size > $report_max_email_size * 1048576){
					
					$abs_report_folder = _base_ . $report_store_folder;
					$report_url = trim($base_url, '/') . "/$report_store_folder";
					
					//Create report storage folder if not exists
					if(!file_exists($abs_report_folder) || !is_dir($abs_report_folder)){
						mkdir($abs_report_folder, '0777', true);
					}
					
					//Cleans report storage folder from expired reports
					$now = time();
					$uuid_folders = glob($abs_report_folder . "$report_uuid_prefix*");
					foreach($uuid_folders as $uuid_folder){
						if(is_dir($uuid_folder)){
							$uuid_folder_time = filemtime($uuid_folder);
							$creation_time = $now - $uuid_folder_time;
							if($creation_time > $report_persistence_days * 24 * 60 * 60){
								$rm_result = recursive_delete_directory($uuid_folder);
							}
						}
						
					}
					
					//Computes an unique progressive ID and a token
					$uuid = uniqid($report_uuid_prefix . time());
					$token = uniqid();
					
					//Computes report filename
					$abs_report_folder .= "$uuid/$token/";
					$report_url .= "$uuid/$token/";
					mkdir($abs_report_folder, 0777, true);
					
					$async_report = $abs_report_folder . $tmpfile;
					$report_url .= rawurlencode($tmpfile);
					
					copy($path . $tmpfile, $async_report);
					
					//Sends an email containing the report link
					$subject = 'Sending scheduled report : ' . $row['filter_name'];
					$body = "You can download this report from <a href='$report_url'>here</a><br><br>
								WARNING: This report will be available for $report_persistence_days days, after that it will be deleted from our system and it will not be accessible anymore.";
					
					
					if(!$mailer->SendMail(Get::sett('sender_event'), //sender
						$recipients, //recipients
						$subject, //subject
						$body //body
					)){
						report_log($row['filter_name'] . ': Error while sending mail.' . $mailer->ErrorInfo);
					} else{
						report_log($row['filter_name'] . ': Mail sent to ' . implode(',', $recipients));
						
						update_schedules($schedules);
						
						report_log($row['filter_name'] . ': Schedule info updated');
					}
					
					
				}else{
					$mailer->Subject = 'Sending scheduled report : ' . $row['filter_name'];
					
					$subject = 'Sending scheduled report : ' . $row['filter_name'];
					$body = date('Y-m-d H:i:s');
					
					if(!$mailer->SendMail(Get::sett('sender_event'), //sender
						$recipients, //recipients
						$subject, //subject
						$body, //body
						$path . $tmpfile, $row['filter_name'] . '.xls', //
						false    //params
					)){
						report_log($row['filter_name'] . ': Error while sending mail.' . $mailer->ErrorInfo);
					} else{
						report_log($row['filter_name'] . ': Mail sent to ' . implode(',', $recipients));
						
						update_schedules($schedules);
						
						report_log($row['filter_name'] . ': Schedule info updated');
					}
				}
	
				//delete temp file
				unlink($path.$tmpfile.'');
			}
		}
	
	
	}
	
} else{
	report_log('There is an active lock, execution aborted');
}
sl_close_fileoperations();
//output log data


//------------------------------------------------------------------------------

// finalize
Boot::finalize();

//Removes lock file
fclose($lock_stream);

?>