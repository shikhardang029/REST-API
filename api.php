<?php
error_reporting(0);
require_once ("Rest.inc.php");

/*
		Add This extension for localhost checking :
			Chrome Extension : Advanced REST client Application
			URL : https://chrome.google.com/webstore/detail/hgmloofddffdnphfgcellkdfbfbjeloo
		
		I used the below table for demo purpose.
		
		CREATE TABLE IF NOT EXISTS `users` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_fullname` varchar(25) NOT NULL,
		  `user_email` varchar(50) NOT NULL,
		  `user_password` varchar(50) NOT NULL,
		  `user_status` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

*/

class API extends REST
{
	public $data = "";
	const DB_SERVER = "localhost";//Put your server name
	const DB_USER = "root";//Put your database username
	const DB_PASSWORD = "";//Put your database password
	const DB = "users";//Put your database name

	private $db = NULL;

	public function __construct()
	{
		parent::__construct();//Initate parent construtor
		$this->dbConnect();//Initiate Database connection. Here we are calling the function written below

	}

	//Database connection
	//This function is to connect with the database
	private function dbConnect()
	{
		$this->db = mysqli_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD,"users");
		if($this->db)
		{	
			mysqli_select_db($this->db,"users");
		}
	}

	//Public method for access api 
	//This method dynamically call the method based on the query string.
	public function processApi()
	{
		$func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
		if((int)method_exists($this, $func) > 0)
		{
			$this->$func();
		}
		else
		{
			$this->response('',404);
		}
		// If the method not exist with in this class, reponse would be "Page not found".
	}
	
	private function login(){
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			
			$email = $this->_request['email'];		
			$password = $this->_request['password'];
			
			// Input validations
			if(!empty($email) and !empty($password))
			{

				if(filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					$sql = mysqli_query($this->db,"SELECT user_id, user_fullname, user_email FROM users WHERE user_email = '$email' AND user_password = '$password' LIMIT 1");
					
					if(mysqli_num_rows($sql) > 0)
					{
						$result = mysqli_fetch_array($sql,MYSQL_ASSOC);
						
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
			}
			
			// If invalid inputs "Bad Request" status message and reason
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}



	private function insert()
	{
		// Cross Validations if the request method is POST else it will return  "Not Acceptable" status
		if($this->get_request_method() !="POST")
		{
			$this->response('',406);
		}

		$id = $this->_request['id'];
		$name = $this->_request['name'];
		$email = $this->_request['email'];
		$password = $this->_request['password'];
		$status = $this->_request['status'];

		//Input validation
		if(!empty($name) and !empty($status) and !empty($email) and !empty($password))
		{
			if(filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$sql = mysqli_query($this->db,"INSERT INTO `users` (`user_id`, `user_fullname`, `user_email`, `user_password`, `user_status`) VALUES ('$id', '$name', '$email', '$password', '$status');");
			
				
				if($sql)
				{
					$success = array('status' => "Success", "msg" => "Successfully inserted");
					$this->response($this->json($success),200);
				}
				else
				{
					$this->response('',204); // If no records "No Content" status
				}
			}
		}
	//If invaild inputs "Bad Request" status message and reason
	$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
	$this->response($this->json($error), 400);
	}


	private function users()
	{
		
		if($this->get_request_method() != "GET")
		{
			$this->reponse('',406);// Cross Validations if the method is GET else it will return  "Not Acceptable" status
		}
		
		$sql = mysqli_query($this->db,"SELECT user_id, user_fullname, user_email FROM users WHERE user_status=1");//sqlquery to select all the records in the database
		
		if(mysqli_num_rows($sql)>0)//If their are records in the database then it will enter this loop
		{
			$result=array();
			while ($rlt=mysqli_fetch_array($sql,MYSQL_ASSOC)) 
			{
				$result[]=$rlt;//Inserting the records into the array 
			}
			$this->response($this->json($result),200);//If everything is good then send an header as "OK" and return the list of the users in JSON format
		}		
		$this->response('',204);//If their is no records found in the database then "No contents" status is given back
	}



	private function deleteUser()
	{
		if($this->get_request_method() != "DELETE")
		{
			$this->response('',406);
		}
		
		$id=(int)$this->_request['id'];

		if($id>0)
		{
			$sqlDelete="SELECT user_id FROM users WHERE user_id=$id";
			mysqli_query($this->db,$sqlDelete);
			$success=array('status' => "Success","msg" => "Successfully one record deleted" );
			$this->response($this->json($success),200);
		}
		else
		{
			$this->reponse('',204);
		}
	}



	//Encode the array into JSON
	private function json($data)
	{
		if(is_array($data))
		{
			return json_encode($data);
		}
	}
}



//Initiate Library 
$api=new API;
$api->processApi();
?>