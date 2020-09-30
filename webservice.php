<?php 
/**
 * Modificación de clase para web services
 * MAT
 */
class WebService
{
	private $method;
	private $uri;
	private $data;

	function __construct($method, $uri, $data = "", $parameters = "")
	{
		$this->method = strtolower($method);
		$this->data = $data;
		$this->uri = $uri;		
	}
	public function send_get()
	{
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => ($this->data!='') ? $this->uri .= '?'.$this->data : $this->uri ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_RETURNTRANSFER => TRUE
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
	}
	public function sendt_post()
	{
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->uri,
            CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS=> $this->data,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_RETURNTRANSFER => TRUE
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
	}
	public function result()
	{
		return $this->validate();
	}
	public function validate()
	{
		$response = "";
		switch ($this->method) {
			case 'post':
				$response = $this->sendt_post();
				break;
			case 'get':
				$response = $this->send_get();
				break;
			default:
				$response = null;
				break;
		}
		return $response;
	}
	public function debug()
	{
		try {
			echo "<pre>";
				print_r($this->validate());
			echo "</pre>";
		} catch (Exception $e) {
			echo "Error: ".$e;
		}
		exit;
	}
}

?>