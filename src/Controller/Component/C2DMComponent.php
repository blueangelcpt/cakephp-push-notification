<?php
App::uses('Component', 'Controller');
class C2DMComponent extends Component {

	private $TAG = 'C2DM';

	public function sendMessage($apiKey, $registrationIDs, $message) {
		$fields = array(
			'to' => $registrationIDs,
			'title' => 'PayToday'
		);
		$fields = array_merge($fields, $message);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: key=' . $apiKey, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		curl_close($ch);
		/*
		  App::uses('HttpSocket', 'Network/Http');
		  $HttpSocket = new HttpSocket();
		  $result = $HttpSocket->request(array(
		  'uri' => 'https://gcm-http.googleapis.com/gcm/send',
		  'headers' => array(
		  'Authorization: key=' . $apiKey,
		  'Content-Type: application/json'
		  ),
		  'body' => $fields
		  ));
		 */
		$this->log(array('response' => $result, 'request' => $fields), $this->TAG);
		return $result;
	}

	function googleAuthenticate($username, $password, $source = 'Company-AppName-Version', $service = 'ac2dm') {
		$this->log(array('username' => $username, 'password' => $password, 'source' => $source, 'service' => $service), $this->TAG);
		@session_start();
		if (isset($_SESSION['google_auth_id']) && $_SESSION['google_auth_id'] != null) {
			return $_SESSION['google_auth_id'];
		}
		$ch = curl_init();
		if (!$ch) {
			return false;
		}
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/accounts/ClientLogin');
		$post_fields = 'accountType=' . urlencode('HOSTED_OR_GOOGLE')
			. '&Email=' . urlencode($username)
			. '&Passwd=' . urlencode($password)
			. '&source=' . urlencode($source)
			. '&service=' . urlencode($service);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		$this->log(array('response' => $response), $this->TAG);
		curl_close($ch);
		if (strpos($response, '200 OK') === false) {
			return false;
		}
		preg_match('/(Auth=)([\w|-]+)/', $response, $matches);
		if (!$matches[2]) {
			return false;
		}
		$_SESSION['google_auth_id'] = $matches[2];
		return $matches[2];
	}

	function sendMessageToPhone($authCode, $deviceRegistrationId, $msgType, $messageText) {
		$this->log(array(
			'authCode' => $authCode,
			'deviceRegistrationId' => $deviceRegistrationId,
			'msgType' => $msgType,
			'messageText' => $messageText
			), $this->TAG);
		$headers = array('Authorization: GoogleLogin auth=' . $authCode);
		$data = array(
			'registration_id' => $deviceRegistrationId,
			'collapse_key' => $msgType,
			'data.message' => $messageText //TODO Add more params with just simple data instead           
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://android.apis.google.com/c2dm/send');
		if ($headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);
		$this->log(array('response' => $response), $this->TAG);
		curl_close($ch);
		return $response;
	}
}
