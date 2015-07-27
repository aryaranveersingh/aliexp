<?php

include 'scraper.lib.php';

function getInfo($url){

	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_COOKIEJAR,"aliexpress-browserconfig.txt"); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE,"aliexpress-browserconfig.txt");
	// curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata); 
	// curl_setopt ($ch, CURLOPT_POST, TRUE); 
	$result = curl_exec ($ch); 
	curl_close($ch);
	return $result;
}

function readConfig(){
	$stores = array();
	$reader = fopen('scrape_stores.csv', 'r+');
	while ($rows = fgetcsv($reader)) {
		$store_url = $rows[0];
		$store_name = $rows[1];
		$store_id = $rows[2];
		$store_up_product = $rows[3];
		$store_min_order = $rows[4];
		$time_sync = $rows[5];
		$stores[] = array("store_url"=>$store_url,"store_name"=>$store_name,"store_id"=>$store_id,"store_min_order"=>$store_min_order,"store_up_product"=>$store_up_product,"time_sync"=>$time_sync);
	}
	return $stores;
}

function writeOut($a){
	echo "\n -- ".$a." -- \n";
}

function getchStoreInfo($config){
	$scraperURLS = array();
	foreach ($config as $indx => $storex) {
		extract($storex);
		for ($x=1; $x <= 6; $x++) { 
		echo "\n".	$store_url = 'http://pt.aliexpress.com/store/'.$store_id.'/search/'.$x.'.html?origin=n';
			writeOut($store_url);
			$html = file_get_html($store_url);
			if($html -> find('#list-items')){
				$mainurls = $html -> find('ul#list-items',0);
				$urlcontainer = $mainurls -> find('li.item');
			}
			elseif ($html -> find('.items4')) {
				$mainurls = $html -> find('ul.items4',0);
				$urlcontainer = $mainurls -> find('li.lin4');
			}
			elseif ($html -> find('.items-list')) {
				$mainurls = $html -> find('ul.items-list',0);
				if(count($mainurls -> find('li.list-item')) > 0 ){
					$urlcontainer = $mainurls -> find('li.list-item');
				}
				elseif (count($mainurls -> find('.item')) > 0) {
					$urlcontainer = $mainurls -> find('li.item');
				}
			}
			
			foreach ($urlcontainer as $key => $links) {
				if($links -> find('h3')){
					$url = $links -> find('h3',0) -> find('a',0);

					$scraperURLS[] = array_merge(array("producturl"=>$url -> href,"productid" => extractProductId($url -> href)),$storex);
				}
				elseif ($links -> find('.deat')) {
					$url = $links -> find('deat',0) -> find('a',0);
					$scraperURLS[] = array_merge(array("producturl"=>$url -> href,"productid" => extractProductId($url -> href)),$storex);
					
				}
			}
			print_r($scraperURLS);
		}
	}

	return $scraperURLS;
}

function extractProductId($url){
	$productid = explode("/",$url);
	$key = count($productid) - 1;
	$productid = $productid[$key];
	$productid = explode('_',$productid);
	$productid = $productid[1];
	$productid = explode('.',$productid);
	$productid = $productid[0];
	return $productid;
}

function getShippingCostToBrazil($pid){
	$url = "http://freight.aliexpress.com/ajaxFreightCalculateService.htm?callback=jsonp&productid=".$pid.'&country=BR';
	$json = getInfo($url);
	$json = str_replace(")", "", str_replace("jsonp(", "", $json));
	$json = json_decode($json);
	return $json;
}

function scraperX($collection){
	foreach ($collection as $key => $value) {
		fetchProductInfo($value);
	}
}

function writeCSV($cat,$obj){

	if(!file_exists('scrapedData/'.$cat.'-aliexpress.csv')){
		$fhandle = fopen('scrapedData/'.$cat.'-aliexpress.csv', 'a+');	
		fputcsv($fhandle, array('Store ID','Category','Shipping Cost To Brazil','ordersCount','productRating','productName','discountPrice','promocodes','productPrice','colors','sizes','description','images','thumbs','Description Images'));	
	}	
	else{
		$fhandle = fopen('scrapedData/'.$cat.'-aliexpress.csv', 'a+');
	}
	
	fputcsv($fhandle, $obj);
	fclose($fhandle);
}

function fetchProductInfo($collection){
	extract($collection);
	echo "\n".$producturl;
	$html = file_get_html($producturl);

	$data =  getShippingCostToBrazil($productid);
	$shippingCostToBrazil = $data -> freight[0] -> status;

	$ordersCount = str_replace("pedidos","",$html -> find('.orders-count',0) -> plaintext);
	$ordersCount = explode(",", $ordersCount);
	$ordersCount = trim($ordersCount[0]);
	if(intval($store_min_order) > intval($ordersCount)){
		echo $productid." $ordersCount - $store_min_order is less then the threshold. Skipping product...";
		return false;
	}
	$productRating = trim(str_replace("Average Star Rating:","",$html -> find('.ui-rating-star',0) -> title));
	$productName = trim(html_entity_decode($html -> find('.product-name',0) -> plaintext));
	$images = array();
	$thumbs = array();
	$productImages = $html -> find('.image-nav',0);
	foreach ($productImages -> find('img') as $key => $value) {
		$thumbs[] = $value -> src;
		$imgLink = str_replace(".jpg_50x50.jpg", ".jpg", $value -> src);
		$images[] = $imgLink;
	}
	$images = implode(";", $images);
	$thumbs = implode(";", $thumbs);
	$discountPrice = trim($html -> find('.ui-cost',0) -> plaintext);
	$promos = $html -> find('.store-promotion-discount-list',0) -> find('ul',0) ->find('li');
	$promocodes = array();
	foreach ($promos as $pk => $pv) {
		$promocodes[] = html_entity_decode($pv -> plaintext);
	}
	$key = count($promocodes) - 1;
	unset($promocodes[$key]);
	$promocodes = trim(implode(" | ",$promocodes)); 
	$productPrice = trim($html -> find('#sku-price',0) -> plaintext);
	$colors = array();
	if($html -> find('#sku-color',0)){
		$colorsvariations = $html -> find('#sku-color',0) -> find('a');
		foreach ($colorsvariations as $ck => $cv) {
			$colors[] = $cv -> title;
		}
		$colors = implode("| ", $colors);
	}
	else{
		$colors = "";
	}
	

	$sizes = array();
	if($html -> find('.product-info-size',0)){
		$colorsvariations = $html -> find('.product-info-size',0) -> find('a');
		foreach ($colorsvariations as $ck => $cv) {
			$sizes[] = $cv -> plaintext;
		}
		$sizes = implode("| ", $sizes);
	}
	else{
		$sizes = "";
	}

	$description = $html -> find('#custom-description',0);
	$otherImages = array();
	$otherImage = $description -> find('img');
	foreach ($otherImages as $ik => $iv) {
		$otherImages[] = $iv -> src;
	}
	if ($html -> find('.ui-breadcrumb'))
		$category = $html -> find('.ui-breadcrumb h2');
	if ($html -> find('.col-main'))
		$category = $html -> find('.m-sop-crumb',0) -> find('b');
	$key = count($category) - 1;
	$category = str_replace(" ","_",trim($category[$key] -> plaintext));
	$otherImages = implode(";", $otherImages);
	$description = html_entity_decode($description -> plaintext);
	print_r(array($store_id,$category,$shippingCostToBrazil,$ordersCount,$productRating,$productName,$discountPrice,$promocodes,$productPrice,$colors,$sizes,$description,$images,$thumbs,$otherImages));
	writeCSV($category,array($store_id,$category,$shippingCostToBrazil,$ordersCount,$productRating,$productName,$discountPrice,$promocodes,$productPrice,$colors,$sizes,$description,$images,$thumbs,$otherImages));
}



$storeconfig = readConfig();

$productURL = getchStoreInfo($storeconfig);

scraperX($productURL);

