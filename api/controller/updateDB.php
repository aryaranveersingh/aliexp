<?php


include 'registry.class.php';
include 'pdo_controller.php';
include '../includes/dbconfig.php';

$registry = new Registry();

$registry -> db = new dbConnect(1);

$globalCounter = 2;
$mainResponseArray = array();
// Needed for Manager STatus Update 
// $registry -> db -> sql = "SELECT * FROM master_users where superid = 3";
// Need for Accrediation Update
$registry -> db -> sql = "SELECT r.*,m.* FROM master_users m, vacc_reportsbymgrs r where r.eid = m.eid and r.ref_id like '1%' ";
$employees = $registry -> db -> ExecuteQuery("2");

// UpdateHeirarchy($employees);
// UpdateManager($employees);


foreach ($employees as $key => $value) {
	echo "\nUpdating record no $key For ".$value['displayName'];
	getReportDetails($value, $value['is_manager']);	
}

// updateAccrediationStatus();

function UpdateManager($employees){
	global $registry;
	global $globalCounter;
	$localcounter = 1;
	$tmpNewEmployees = array();
	foreach($employees as $key => $value){
		$eid = $value['eid'];
		$superid = $value['superid'];
		$registry -> db -> sql = "SELECT (case when count(1) = 0 then FALSE else TRUE end) flag FROM master_users where superid = $eid";
		$tmp_employees = $registry -> db -> ExecuteQuery("2");
		echo "\nIs Manager $eid ? ---- ". $flag = $tmp_employees[0]['flag'];
		if($flag)
		{
				echo "Updating as manager..";
				$registry -> db -> sql = "UPDATE vacc_reportsbymgrs SET is_manager = 1 where eid = $eid;";
				$tmp_up_employees = $registry -> db -> ExecuteQuery("2");
		}
		else{
				echo "Updating as employee..";
				$registry -> db -> sql = "UPDATE vacc_reportsbymgrs SET is_manager = 0 where eid = $eid;";
				$tmp_up_employees = $registry -> db -> ExecuteQuery("2");
		}
		
		
	}
}

function UpdateHeirarchy($employees){
	global $registry;
	global $globalCounter;
	$localcounter = 1;
	$tmpNewEmployees = array();
	foreach($employees as $key => $value){
		$eid = $value['eid'];
		$superid = $value['superid'];
		echo "updating record for ".$value['displayName']."\n\n";
		$registry -> db -> sql = "SELECT ref_id FROM vacc_reportsbymgrs where eid = $superid";
		$ref_id = $registry -> db -> ExecuteQuery("2");
		$ref_id = $ref_id[0]['ref_id'];

		$registry -> db -> sql = "INSERT INTO vacc_reportsbymgrs(ref_id,eid) VALUES('$ref_id.$localcounter',$eid)";
		$ref_id = $registry-> db -> ExecuteQuery("2");

		$localcounter++;
		$registry -> db -> sql = "SELECT * FROM master_users where superid = $eid";
		$tmp_employees = $registry -> db -> ExecuteQuery("2");
		if(!empty($tmp_employees))
		{
				echo "Updating as manager..";
				$registry -> db -> sql = "UPDATE vacc_reportsbymgrs SET is_manager = 1 where eid = $eid;";
				$tmp_up_employees = $registry -> db -> ExecuteQuery("2");
				UpdateHeirarchy($tmp_employees);
		}
	}
}

function getReportDetails($value){
		global $registry;
		global $mainResponseArray;
        $registry -> db -> sql = "SELECT COUNT(1) pending,d.user_id FROM vacc_docs d, vacc_review_queue r WHERE d.user_id = ".$value['eid']." and d.id = r.doc_id";
        $rows["pending"] = $registry -> db -> ExecuteQuery("2");
        $registry -> db -> sql = "SELECT COUNT(1) rejected,d.user_id FROM vacc_docs d, vacc_doc_reviews r WHERE d.user_id = ".$value['eid']." and d.id = r.doc_id and r.rating < 3 GROUP BY d.user_id";
        $rows["rejected"] = $registry -> db -> ExecuteQuery("2");
        $registry -> db -> sql = "SELECT (case when COUNT(1) = null then 0 else count(1) end) approved,d.user_id FROM vacc_docs d, vacc_doc_reviews r WHERE d.user_id = ".$value['eid']." and d.id = r.doc_id and r.rating > 2 GROUP BY d.user_id";
        $rows["approved"] = $registry -> db -> ExecuteQuery("2");
        $registry -> db -> sql = "SELECT COUNT(1) uploaded,d.user_id FROM vacc_docs d WHERE d.user_id = ".$value['eid'];
        $rows["uploaded"] = $registry -> db -> ExecuteQuery("2");
        $registry -> db -> sql = "SELECT dc.id, dc.description, dc.title,dc.url, dc.submission_date FROM vacc_docs dc where dc.user_id = ".$value['eid']." and submission_date = (SELECT max(dc.submission_date) FROM vacc_docs dc where dc.user_id = ".$value['eid'].") order by dc.submission_date DESC;";
        $rows["latest_video"] = $registry -> db -> ExecuteQuery("2");
        $latest_video =  is_null($rows["latest_video"][0]) ? 0 : $rows["latest_video"][0]['url'];
        $pending = is_null($rows["pending"][0]['pending']) ? 0 : $rows["pending"][0]['pending'];
        $approved = is_null($rows["approved"][0]['approved']) ? 0 : $rows["approved"][0]['approved'];
        $rejected = is_null($rows["rejected"][0]['rejected']) ? 0 : $rows["rejected"][0]['rejected'];
        $uploaded = is_null($rows["uploaded"][0]['uploaded']) ? 0 : $rows["uploaded"][0]['uploaded'];
        //$mainResponseArray[]
        $tmp = array("eid"=>$value['eid'],"video" => $latest_video,"mgr_id" => $value['superid'],"pending" => $pending,"approved" => $approved ,"rejected" => $rejected ,"uploaded" => $uploaded);
        updateAccrediationStatus($tmp);
}

function updateAccrediationStatus($value){
	global $registry;
	global $mainResponseArray;
		extract($value);
		echo "\n\n". $registry -> db -> sql = "INSERT INTO  accrediationStatus(eid,latest,rejected,approved,pending,uploaded) VALUES($eid,'$video',$rejected,$approved,$pending,$uploaded)";
        $rows["pending"] = $registry -> db -> ExecuteQuery("2");
}