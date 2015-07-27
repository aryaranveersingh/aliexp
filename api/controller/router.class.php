<?php

class requestRouter {

    private $params;
    private $registry;
    private $requestMethod;
    private $is_child;
    private $mainResponseArray;
    public function __construct($registry) {

        //error_reporting(1);
        $this -> registry = $registry;
        $this -> requestMethod = $_SERVER['REQUEST_METHOD'];
        // echo __SITE_PATH;
        $this -> params = explode("/",str_replace("/aliexpress/api/", "", $_SERVER['REQUEST_URI']));
        $this -> route();
    }

    function route(){
        switch ($this -> requestMethod) {
            case 'GET':
                switch ($this -> params[0]) {
                    case 'loadStores':
                        $this -> loadStores();
                    
                }
                break;
            
            case 'POST':
                switch ($this -> params[0]) {
                    case 'addStore':
                        $this -> addStore();
                    break;
                    case 'updatestore':
                        $this -> updatestore();
                    break;
                    case 'deletestore':
                        $this -> deletestore();
                    break;
                    
                }
                break;
        }
    }
    private function loadStores(){
       
        $this -> registry -> db -> sql = "SELECT * from import_store;";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $countofrows = $this -> registry -> db -> count;

        if(empty($rows['data'])) {
            $rows["status"] = FALSE ;
            $rows["message"] = "No records found!" ;
        }
        else{
            $rows["status"] = TRUE ;
            $rows["message"] = $countofrows." matching rows found.";
        }
        header("HTTP/1.1 200 Ok");
        echo json_encode($rows,JSON_NUMERIC_CHECK);
    }

    private function addStore(){
        $data = json_decode($_POST['store']);
        $storeurl = $data[0] -> value;
        $storeid = $data[1] -> value;
        $storename = $data[2] -> value;
        $storesmallorder = $data[3] -> value;
        $storeupproduct = $data[4] -> value;
        $storetimesync = $data[5] -> value;
        $this -> registry -> db -> sql = "INSERT INTO import_store(`storeurl`, `storeid`, `storename`, `ordercount`, `up_product`, `timesync`) VALUES('$storeurl','$storeid','$storename','$storesmallorder','$storeupproduct','$storetimesync');";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        header("HTTP/1.1 200 Ok");
        $this -> registry -> db -> sql = "SELECT * from import_store;";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $countofrows = $this -> registry -> db -> count;
        $rows["insert_id"] = $this -> registry -> db -> insertion_id;
        echo json_encode($rows,JSON_NUMERIC_CHECK);
    }

    private function updatestore(){
        $data = json_decode($_POST['store']);
        $storeid = $_POST['storeid'];
        $storeurl = $data[0] -> value;
        $storename = $data[1] -> value;
        $storesmallorder = $data[2] -> value;
        $storeupproduct = $data[3] -> value;
        $storetimesync = $data[4] -> value;
        $this -> registry -> db -> sql = "UPDATE import_store set storeurl = '$storeurl' , storename = '$storename' , ordercount = '$storesmallorder' , up_product = '$storeupproduct' , timesync ='$storetimesync' where storeid = '$storeid'";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $this -> registry -> db -> sql = "SELECT * from import_store;";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $countofrows = $this -> registry -> db -> count;
        $rows["insert_id"] = $this -> registry -> db -> insertion_id;

        header("HTTP/1.1 200 Ok");
        echo json_encode($rows,JSON_NUMERIC_CHECK);
    }

    private function deletestore(){
        $storeid = $_POST['storeid'];
        $this -> registry -> db -> sql = "DELETE FROM import_store where storeid = '$storeid'";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $this -> registry -> db -> sql = "SELECT * from import_store;";
        $rows["data"] = $this -> registry -> db -> ExecuteQuery("2");
        $countofrows = $this -> registry -> db -> count;

        header("HTTP/1.1 200 Ok");
        echo json_encode($rows,JSON_NUMERIC_CHECK);
    }

    
}
?>
