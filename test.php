<?php
include 'pattern.php';

define('META_DATA_DIR', 'data/meta_data/');

print_head();

$json_keywords = array('title', 'imgAlt', 'imgTitle', 'content');
$json_importance_key_fields = array('title', 'url');
$garbage_words = array();
$words_weight = array();
	
get_words_weight(0, 0);

print_bottom();


//process the file with given time interval
//not support date in this version
function get_words_weight($start_date, $end_date){
	global $words_weight;
	
	$files = glob(META_DATA_DIR . "*.data");
	foreach($files as $file){
		//get_file_time($file);
		$json_file = load_meta_data_json($file);
		process_json_to_weight($json_file);
	}

	$words_weight = array_slice($words_weight, 0, 1);
	//uasort($words_weight, 'words_weight_comparision');

	//foreach($words_weight as $word => $word_info){
	//	print $word	. ": " . $word_info["count"] . "<br/>";
	//	print "\t" . var_dump($word_info["info"]) . "<br/>";
	//}

	print json_encode($words_weight);
	
	
}

//get words weight from given json file
function process_json_to_weight($json_file){
	global $json_keywords;
	global $words_weight;
	
	//for each news
	foreach($json_file as $news_data_id => $news_entry){
		//print var_dump($news_entry);

		$key_fields = get_key_fields($news_entry);
		//for each keyword in $json_keywords
		foreach($json_keywords as $keyword){
			scrape_keywords($key_fields, $news_entry[$keyword]);
			
		}
	}	
}

//compare two words
//function words_weight_comparision($a, $b){
	//$a => $info["count"] - $b => $info["count"];	
//}

//store the key fields in an associate array, and return it
function get_key_fields($news_entry){
	global $json_importance_key_fields;
	
	$key_fields	= array();
	foreach($json_importance_key_fields as $ji_key_field){
		$key_fields[$ji_key_field] = $news_entry[$ji_key_field];
	}
	return $key_fields;
	
}

//calculate the weight of the keywords in the given paragraph
function scrape_keywords($key_fields, $paragraph){
	global $words_weight;

	$words = preg_split("/[\n\r\t ,!.\?\'\";:()\[\]{}<>=]+/", $paragraph);
	foreach($words as $word){
		if(!isset($words_weight[$word])){
			$words_weight[$word] = array("count"=>1, "info"=>array($key_fields));
		}else{
			$words_weight[$word]["count"] ++;
			if(!in_array($key_fields, $words_weight[$word]["info"]))
				array_push($words_weight[$word]["info"], $key_fields);
		}
	}
}

//load the given json file into content
function load_meta_data_json($file_name){
	$meta_data_file = file_get_contents($file_name);
	return json_decode($meta_data_file, true);	
}

?>
