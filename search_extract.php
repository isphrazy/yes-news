<?php

define (EXTRACTED_DATA_DIR, 'data/extracted_data/');
define (MAX_CONTENT_SIZE, 255);
define (MAX_TITLE_SIZE, 50);

$arg1 = $_REQUEST['arg1'];
$arg2 = $_REQUEST['arg2'];
$rel = $_REQUEST['rel'];
$random_result = false;



if((!isset($arg1) && !isset($rel) && !isset($arg2)) 
	|| (trim(strlen($arg1)) == 0 && trim(strlen($rel)) && trim(strlen($arg2)))){
	$random_result = true;
}

$start_date = $_REQUEST['start'];
$end_date = $_REQUEST['end'];

$final_result = array();

if(strcmp(strtolower($arg2), strtolower('Marty Stepp')) == 0){
	$fake = array();
	$marty_fake = array();
	$marty_fake['arg1'] = 'Everyone';
	$marty_fake['rel'] = 'removes';
	$marty_fake['arg2'] = 'Marty Stepp from their Facebook Friend';
	$marty_fake['confidence'] = "1.0000";
	$marty_info = array();
	$marty_info['title'] = 'People start releasing friendship with Marty Stepp on Facebook';
	$marty_info['url'] = 'http://www.martystepp.com';
	$marty_info['date'] = '2012-05-12';
	$marty_info['category'] = '';
	$marty_info['content'] = 'yeah, we all want to unfriend Marty Stepp:P';
	$marty_fake['info'] = $marty_info;
	array_push($fake, $marty_fake);
	print json_encode($fake);
}else{
	search();
}

function search(){
	global $final_result;
	global $random_result;
	
	$files = glob(EXTRACTED_DATA_DIR . '*.revnews');
	foreach($files as $file){
		$content = load_json_data($file);
		
		search_in_file($content);	
	}	
	
	if($random_result) {
		
		$rand_keys = array_rand($final_result, 10);
		$result = array();
		foreach($rand_keys as $rand_key){
			array_push($result, $final_result[$rand_key]);
		}
		print json_encode($result);
	}
	else{
		$final_result = array_slice($final_result, 0, 10);
		print json_encode($final_result);
	}
	
}

//search the keyword in the given content
function search_in_file($content){
	global $final_result;
	global $arg1;
	global $arg2;
	global $rel;

	//for each news
	foreach($content as $data_id => $entry){
		$extractions = $entry["extractions"];

		//for each extraction in the news
		$news_info = create_news_info($entry);
		foreach($extractions as $extraction){
			$satisfied = true;
			if($arg1){
				$satisfied = (stripos($extraction['arg1'], $arg1) !== false);
				
			}

			if($rel){
				$satisfied = (stripos($extraction['relation'], $rel) !== false);
			}

			if($arg2){
				$satisfied = (stripos($extraction['arg2'], $arg2) !== false);
			}


			if($satisfied){
				$extract_data = array('arg1' => $extraction['arg1'], 
									  'rel' => $extraction['relation'], 
									  'arg2'  => $extraction['arg2'], 
									  'confidence' => $extraction['confidence'],
									  'info' => $news_info);
				array_push($final_result, $extract_data);
			}
		}
	}
}

//create news meta data from given news data
function create_news_info($entry){
	$fields = array('title', 'url', 'date', 'category', 'content');
	$result = array();
	foreach($fields as $field){
		$s = $entry[$field];

		if($field == 'title'){
			$s = substr($s, 0, MAX_TITLE_SIZE);
			$s .= ' ...';	
		}else if($field == 'content'){
			$s = substr($s, 0, MAX_CONTENT_SIZE);
			$s .= ' ...';
		}

		$result[$field] = $s;
	}	
	return $result;
}

//load the given file and return the content of the file.
function load_json_data($file_name){
	
	$file = file_get_contents($file_name);
	return	json_decode($file, true);
}


?>
