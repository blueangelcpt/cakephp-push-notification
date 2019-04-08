<?php
App::uses('Component', 'Controller');
class FCMComponent extends Component {

	private $TAG = 'FCM';

	public function sendMessage($apiKey, $registrationIDs, $message) {
		$fields = array(
			'to' => $registrationIDs,
			'title' => 'PayToday'
		);
		$fields = array_merge($fields, $message);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: key=' . $apiKey, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		curl_close($ch);
		$this->log(array('response' => $result, 'request' => json_encode($fields)), $this->TAG);
		return $result;
	}
}
