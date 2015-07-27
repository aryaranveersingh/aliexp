<?php
ini_set('max_execution_time', -1);
ini_set('memory_limit', '9996M');
ini_set('post_max_size', '99996M');
ini_set('upload_max_filesize', '9996M');
/**
 * Database Handler Class
 * @param Username
 * @param password
 * @param Host name
 * @param Database schema
 * @param Enable/Disable Transactions
 * @param Set JSON Response
 * @param Driver to be used
 * 1) Mysqli
 * 2) PDO
 * @param Contains the Error/Success flag
 *
 */
class dbConnect {
	/**
	 * @param Username
	 */
	private $user;
	/**
	 * @param password
	 */
	private $passCode;
	/**
	 * @param Host name
	 */
	private $host;
	/**
	 * @param Database schema
	 */
	private $schema;
	/**
	 * @param Enable/Disable Transactions
	 */
	private $transaction_flag;
	/**
	 * @param Set JSON Response
	 */
	private $json_flag;
	/**
	 * @param Driver to be used
	 * 1) Mysqli
	 * 2) PDO
	 */

	private $drivers_flag;
	/**
	 * @param Hold the data
	 */
	private $data;
	/**
	 * @var Contains the Error/Success flag
	 */
	private $response_flag;
	/**
	 * @var Contains Error/Success response message
	 */
	private $response;
	/**
	 * @var Connection Object
	 */
	private $pdo_connect;
	/**
	 * @var SQL container
	 */

	public $insertion_id;
	public $count;
	public $sql;
	private $setresponse;
	private $config = array(1 => "mysqli_controller", 2 => "pdo_controller");

	public function __construct() {

			$this -> user = user;
			$this -> passCode = passcode;
			$this -> host = host;
			$this -> schema = schema;
			$this -> transaction_flag = enable_transaction;
			$this -> json_flag = force_json_object;
			$this -> drivers_flag = drivers_flag;
	        $this->EstablishConnection();

	}

	/**
	 * @param Establish database Connection
	 */
	function EstablishConnection() {
		if ($this -> drivers_flag == 1) {

		} else {

			try {
				$this -> pdo_connect = new PDO('mysql:host=' . $this -> host . ';dbname=' . $this -> schema, $this -> user, $this -> passCode,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

			} catch(PDOException $ex) {
				$this -> response_flag = $ex -> getCode();
				$this -> response = $ex -> getMessage() . " File : " . $ex -> getFile() . " on line :" . $ex -> getLine();
				$this -> setResponse();

			}

		}

	}

	/**
	 * @param set response data
	 */
	function setResponse() {

		if ($this -> setresponse == "1") {
			if ($this -> json_flag) {
				echo json_encode(array("status" => $this -> response_flag, "message" => $this -> response, "data" => $this -> data), JSON_FORCE_OBJECT);
			} else {
				echo json_encode(array("status" => $this -> response_flag, "message" => $this -> response, "data" => $this -> data));
			}
		}

	}

	/**
	 * @param Execute SQL statement
	 */
	function ExecuteQuery($setresponse) {
		$this -> setresponse = $setresponse;
		$Query_Type = explode(' ', $this -> sql);
		switch (strtolower($Query_Type[0])) {
			case 'select' :
				try {
				    $stmt = $this -> pdo_connect -> prepare($this -> sql);
					$stmt -> execute();
					$this -> count = $stmt -> rowCount();
					$row = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                    
					if ($this -> count > 0) {

						if ($this -> setresponse == "2") {
							return $row;
						} else {

							$this -> response_flag = 'Success';
							$this -> response = "Fetch successful";
							$this -> data = $row;
							$this -> setResponse();
						}

					} else {

						$this -> response_flag = 'Success';
						$this -> response = "No rows found matching your search criteria";
						$this -> data = "NULL";
						$this -> setResponse();

					}

				} catch(PDOException $ex) {
					$this -> response_flag = $ex -> getCode();
					$this -> response = $ex -> getMessage() . " File : " . $ex -> getFile() . " on line :" . $ex -> getLine();
					$this -> data = "NULL";
					$this -> setResponse();

				}

				break;
			case 'update' :
				try {
					$stmt = $this -> pdo_connect -> prepare($this -> sql);
					$stmt -> execute();
					$this -> count = $stmt -> rowCount();
					$this -> response_flag = 'Success';
					$this -> response = "Record was updated succesfully, Total ".$this -> count." rows affected by last statement";
					return $this -> count;
					$this -> setResponse();
				} catch(PDOException $ex) {
					$this -> response_flag = $ex -> getCode();
					$this -> response = $ex -> getMessage() . " File : " . $ex -> getFile() . " on line :" . $ex -> getLine();
					$this -> data = "NULL";
					$this -> setResponse();
				}

				break;
			case 'insert' :
				try {
					
					$stmt = $this -> pdo_connect -> prepare($this -> sql);
					$stmt -> execute();
					$count = $stmt -> rowCount();
					$this -> insertion_id = $this -> pdo_connect -> lastInsertId('Transaction_id');

					if ($count == 0) {

						$this -> response_flag = 'Error';
						$this -> response = "Failed to insert the record";
						$this -> data = $this -> sql;
						if ($this -> setresponse == "2") {
							$this -> setResponse();
						}
						
					} else {
						$this -> response_flag = 'Success';
						$this -> response = "$count Record was inserted succesfully";
						$this -> data = "NULL";
						if ($this -> setresponse == "2") {
							$this -> setResponse();
						}
						
					}
				} catch(PDOException $ex) {
					$this -> response_flag = $ex -> getCode();
					$this -> response = $ex -> getMessage() . " File : " . $ex -> getFile() . " on line :" . $ex -> getLine();
					$this -> data = "NULL";
					$this -> setResponse();
				}
				break;
			case 'delete' :
				try {
					$stmt = $this -> pdo_connect -> prepare($this -> sql);
					$stmt -> execute();
					return $stmt -> rowCount();
					$this -> response_flag = 'Success';
					$this -> response = "$count Record was deleted succesfully";
					$this -> data = "NULL";
					$this -> setResponse();
				} catch(PDOException $ex) {
					$this -> response_flag = $ex -> getCode();
					$this -> response = $ex -> getMessage() . " File : " . $ex -> getFile() . " on line :" . $ex -> getLine();
					$this -> data = "NULL";
					$this -> setResponse();
				}
				break;
		}

	}

}
