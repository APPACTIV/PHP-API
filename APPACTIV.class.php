<?php

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

	public function search($entity, Array $params = null){
		$data = $params == null ? "" : json_encode($params);
		$result = $this->executeRequest($entity, 'GET', $data);
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
