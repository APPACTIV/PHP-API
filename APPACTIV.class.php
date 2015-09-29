<?php

abstract class FieldType{
	const STRING = 1;
	const TEXT = 2;
	const INTEGER = 3;
	const DECIMAL = 4;
	const MONEY = 5;
	const BIGINT = 6;
	const BOOLEAN = 7;
	const DATETIME = 8;
	const INTLIST = 9;
}

abstract class FieldConstraint{
	const NONE = 1;
	const RECOMMENDED = 2;
	const REQUIRED = 3;
}

abstract class LookupType{
	const NONE = 0;
	const LOOKUPLIST = 1;
	const TABLEVIEW = 2;
	const USERLIST = 3;
	const GROUPLIST = 4;
	const MULTISELECTLIST = 5;
}

class appactiv{

	var $_serverKey;
	var $_userKey;
	var $_url;

	var $last_error;

	public function __construct($url, $serverKey, $userKey){
		$this->_url = $url;
		if(substr($this->_url,-1) != "/")
			$this->_url .= "/";
		$this->_serverKey = $serverKey;
		$this->_userKey = $userKey;
	}

	public function search($entity, Array $params = null, $page = 1, $perpage = 10, $sortorder = "id"){
		if($params == null) $params = array();
		$query = http_build_query(array_merge(array("page" => $page, "perpage" => $perpage, "sortorder" => $sortorder), $params));
		$result = $this->executeRequest($entity.'?'.$query, 'GET');
		return $result ? json_decode($result, true) : false;
	}

	public function details($entity, $id){
		$result = $this->executeRequest($entity.'/'.$id, 'GET');
		return $result ? json_decode($result, true) : false;
	}

	public function create($entity, Array $params){
		$result = $this->executeRequest($entity, 'POST', json_encode($params));
		return $result ? json_decode($result, true) : false;
	}

	public function update($entity, $id, Array $params){
		$result = $this->executeRequest($entity.'/'.$id, 'PUT', json_encode($params));
		return $result ? true : false;
	}

	public function delete($entity, $id){
		$result = $this->executeRequest($entity.'/'.$id, 'DELETE');
		return $result ? true : false;
	}
	public function count($entity){
		$result = $this->executeRequest($entity.'/count', 'GET');
		return $result ? json_decode($result, true) : false;
	}

	public function lookups($entity, $fieldname){
		$result = $this->executeRequest($entity.'/Lookups?fieldname='.$fieldname, 'GET');
		return $result ? json_decode($result, true) : false;
	}

	public function fields($entity){
		$result = $this->executeRequest($entity.'/Fields', 'GET');
		return $result ? json_decode($result, true) : false;	
	}


	private function executeRequest($path, $request_type, $data = null){
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$this->_url."api/".$path);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($ch,CURLOPT_USERAGENT, "APPACTIV-API-PHP");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$header = array();
		$header[] = "ServerApiKey: ".$this->_serverKey;
		$header[] = "UserApiKey: ".$this->_userKey;

		switch($request_type){
			case 'GET':
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				if($data != null){
					$header[] = 'Content-Type: application/json';
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}
				break;
			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				if($data != null){
					$header[] = 'Content-Type: application/json';
					$header[] = 'Content-Length: '.strlen($data);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
		}

		curl_setopt($ch,CURLOPT_HTTPHEADER, $header);

		$result = curl_exec($ch);
		if(!curl_errno($ch)){
			$header = curl_getinfo($ch);
			if($header['http_code'] != 200 && $header['http_code'] != 204){
				$this->last_error = $header;
				$result = false;
			}
		}
		else{
			$this->last_error = curl_error($ch);
			$result = false;
		}

		curl_close($ch);

		return $result;
	}
}
