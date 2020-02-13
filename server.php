<?php

class Utility
{
	/**
	* Parse request header
	* @param $header Request header from client
	* @return Associated array of the request header
	*/
	public static function parseHeaders($headers)
	{
		$headers = str_replace("\n", "\r\n", $headers);
		$headers = str_replace("\r\r\n", "\r\n", $headers);
		$headers = str_replace("\r", "\r\n", $headers);
		$headers = str_replace("\r\n\n", "\r\n", $headers);
		$arr = explode("\r\n", $headers);
		$arr2 = array();
		foreach($arr as $idx=>$value)
		{
			if($idx > 0)
			{
				$arr3 = explode(": ", $value, 2);
				if(count($arr3) == 2)
				{
					$arr2[strtolower($arr3[0])] = $arr3[1];
				}
			}
		}
		return $arr2;
	}
	/**
	* Parse cookie
	* @param $cookieString Cookie from client
	* @return Associated array of the cookie
	*/
	public static function parseCookie($cookieString)
	{
		$cookieData = array();
		$arr = explode("; ", $cookieString);
		foreach($arr as $key=>$val)
		{
			$arr2 = explode("=", $val, 2);
			$cookieData[$arr2[0]] = $arr2[1];
		}
		return $cookieData;
	} 
	/**
	* Read cookie
	* @param $cookieData Associated array of the cookie
	* @return name Cooke name
	*/
	public static function readCookie($cookieData, $name)
	{
		$v0 = (isset($cookieData[$name."0"]))?($cookieData[$name."0"]):"";
		$v1 = (isset($cookieData[$name."1"]))?($cookieData[$name."1"]):"";
		$v2 = (isset($cookieData[$name."2"]))?($cookieData[$name."2"]):"";
		$v3 = (isset($cookieData[$name."3"]))?($cookieData[$name."3"]):"";
		$v  = strrev(str_rot13($v1.$v3.$v2.$v0));
		if($v=="")
		return md5(microtime().mt_rand(1,9999999));
		else 
		return $v;
	}
	/**
	* Get session data
	* @param $sessionID Session ID
	* @param $sessionSavePath Session save path
	* @param $prefix Prefix of the session file name
	* @return Asociated array contain session
	*/
	public static function getSessions($sessionID, $sessionSavePath = NULL, $prefix = "sess_")
	{
		$sessions = array();
		if($sessionSavePath === NULL)
		{
			$sessionSavePath = session_save_path();
		}
		$path = $sessionSavePath."/".$prefix.$sessionID;
		if(file_exists($path))
		{
			$session_text = file_get_contents($path);
			if($session_text != '')
			{
				$sessions = Utility::sessionDecode($session_text);
				return $sessions;
			}
		}
	}
	/**
	* Decode session data
	* @param sessionData Raw session data
	* @return Asociated array contain session
	*/
	public static function sessionDecode($sessionData) 
	{
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($sessionData)) 
		{
            if (!strstr(substr($sessionData, $offset), "|")) 
			{
                throw new Exception("invalid data, remaining: " . substr($sessionData, $offset));
            }
            $pos = strpos($sessionData, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($sessionData, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($sessionData, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
	/**
	* Decode binary session data
	* @param sessionData Raw session data
	* @return Asociated array contain session
	*/
	public static function sessionDecodeBinary($sessionData) 
	{
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($sessionData)) 
		{
            $num = ord($sessionData[$offset]);
            $offset += 1;
            $varname = substr($sessionData, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($sessionData, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}

class ChatClient{
	public $sessionID = '';
	public $sessions = array();
	public $userID = '';
	public $clientID = '';
	public $headers = array();
	public $cookies = array();
	public $ip = '';
	public $port = 0;
	
	/**
	* Constructor
	*/
	public function __construct($clientID, $headers = '', $ip = '', $port = 0)
	{
		$this->clientID = $clientID;
		if($headers != '')
		{
			$this->headers = Utility::parseHeaders($headers);
			$this->cookies = Utility::parseCookie($this->headers['cookie']);
			$this->sessionID = $this->cookies['PHPSESSID'];
			$this->sessions = Utility::getSessions($this->sessionID, session_save_path(), "sess_");
		}
		if($ip != '')
		{
			$this->ip = $ip;
		}
		if($port != 0)
		{
			$this->port = $port;
		}
	}
	/**
	* Login
	* @return boolean true if success and false if failed
	*/
	public function login()
	{
		$username = @$this->sessions['username'];
		$password = @$this->sessions['password'];
		$sql = "select * from user where username like '$username' and password = sha1('$password') ";
		return true;
	}
	/**
	* Convert object to JSON
	* JSON represent the object
	*/
	public function toString()
	{
		return json_encode($this);
	}
}

class ChatServer
{
	/**
	 * Server host
	*/
	public $host = '127.0.0.1';
	/**
	 * Server port
	 */
	public $port = 8888;	
	/**
	 * Socket
	 */
	public $socket = NULL;
	/**
	 * Client ports
	 */
	public $clients = array();
	/**
	 * Client object
	 */
	public $chatClients = array();
	
	/**
	* Constructor
	*/
	public function __construct($host, $port)
	{
		$this->host = $host;
		$this->port = $port;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// reuseable port
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		// bind socket to specified host
		socket_bind($this->socket, 0, $this->port);
		// listen to port
		socket_listen($this->socket);
		$this->clients = array($this->socket);
	}
	
	/**
	 * Run websocket server
	 */
	public function run()
	{
		$index = 0;
		$null = null; //null var
		while (true) 
		{
			//manage multipal connections
			$changed = $this->clients;
			//returns the socket resources in $changed array
			@socket_select($changed, $null, $null, 0, 10);
			//check for new socket
			if (in_array($this->socket, $changed)) 
			{
				$socketNew = socket_accept($this->socket); //accpet new socket
				$index++;
				$header = socket_read($socketNew, 1024); //read data sent by the socket
				$this->performHandshaking($header, $socketNew, $this->host, $this->port); //perform websocket handshake
				socket_getpeername($socketNew, $ip, $port); //get ip address of connected socket
				$chatClient = new ChatClient($index, $header, $ip, $port);
				if(isset($chatClient->sessions))
				{
					if($chatClient->login())
					{
						$this->clients[$index] = $socketNew; //add socket to client array
						$this->chatClients[$index] = $chatClient;
						$this->onOpen($chatClient, $ip);
					}
				}
				
				//make room for new socket
				$foundSocket = array_search($this->socket, $changed);
				unset($changed[$foundSocket]);
			}
			if(is_array($changed))
			{
				//loop through all connected sockets
				foreach ($changed as $index => $changeSocket) 
				{
					//check for any incomming data
					while (@socket_recv($changeSocket, $buf, 1024, 0) >= 1) 
					{
						$receivedText = $this->unmask($buf); //unmask data
						socket_getpeername($changeSocket, $ip, $port); //get ip address of connected socket
						$this->onMessage($this->chatClients[$index], $receivedText, $ip, $port);
						break 2; //exist this loop
					}
			
					$buf = @socket_read($changeSocket, 1024, PHP_NORMAL_READ);
					if ($buf === false) 
					{ 
						// check disconnected client
						// remove client for $clients array
						$foundSocket = array_search($changeSocket, $this->clients);
						@socket_getpeername($changeSocket, $ip, $port);
						$closeClient = $this->chatClients[$foundSocket];
						unset($this->clients[$foundSocket]);
						unset($this->chatClients[$foundSocket]);
						$this->onClose($closeClient, $ip, $port);
					}
				}
			}
		}
	}
	/**
	 * Method when a new client is connected
	 * @param $clientChat Chat client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onOpen($clientChat, $ip = '', $port = 0)
	{
		echo "onOpen();\r\n";
		echo "IP      = $ip;\r\n";
		echo "Client  = ";
		echo json_encode($clientChat, JSON_PRETTY_PRINT);
		echo "\r\n\r\n";
		$response = json_encode(array('type' => 'system', 'message' => ' disconnected'));
		$this->sendBroadcast($response);
	}
	/**
	 * Method when a new client is disconnected
	 * @param $clientChat Chat client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onClose($clientChat, $ip = '', $port = 0)
	{
		echo "onClose();\r\n";
		echo "IP      = $ip;\r\n";
		echo "Client  = ";
		echo json_encode($clientChat, JSON_PRETTY_PRINT);
		echo "\r\n\r\n";
		$response = json_encode(array('type' => 'system', 'message' => ' disconnected'));
		$this->sendBroadcast($response);
	}
	/**
	 * Method when a client send the message
	 * @param $clientChat Chat client
	 * @param $receivedText Text sent by the client
	 * @param $ip Remote adddress or IP address of the client 
	 * @param $port Remot port or port number of the client
	 */
	public function onMessage($clientChat, $receivedText, $ip = '', $port = 0)
	{
		echo "onMessage();\r\n";
		echo "Message = $receivedText;\r\n";
		echo "IP      = $ip;\r\n";
		echo "Client  = ";
		echo json_encode($clientChat, JSON_PRETTY_PRINT);
		echo "\r\n\r\n";

		$tst_msg = json_decode($receivedText, true); //json decode
		if(count($tst_msg))
		{
			$user_name = $tst_msg['name']; //sender name
			$user_message = htmlspecialchars($tst_msg['message'], ENT_QUOTES); //message text
			$response_text = json_encode(array('type' => 'usermsg', 'name' => $user_name, 'message' => $user_message));
			$this->sendBroadcast($response_text); //send data
		}
	}
	/**
	 * Method to send the broadcast message to all client
	 * @param $message Message to sent to all client
	 */
	public function sendBroadcast($message)
	{
		$maskedMessage = $this->mask($message);
		foreach ($this->clients as $changeSocket) 
		{
			@socket_write($changeSocket, $maskedMessage, strlen($maskedMessage));
		}
	}
	/**
	 * Method to send message to a client
	 * @param $changeSocket Client socket
	 * @param $message Message to sent to all client
	 * @return string Masked message
	 */
	public function sendMessage($changeSocket, $message)
	{
		$maskedMessage = $this->mask($message);
		@socket_write($changeSocket, $maskedMessage, strlen($maskedMessage));
	}
	/**
	 * Unmask incoming framed message
	 * @param $text Masked message
	 * @return string Plain text
	 */
	public function unmask($text)
	{
		$length = ord($text[1]) & 127;
		if ($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} elseif ($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$text.= $data[$i] ^ $masks[$i % 4];
		}
		return $text;
	}
	/**
	 * Encode message for transfer to client
	 * @param $text Plain text to be sent to the client
	 * @return string Masked message
	 */
	public function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		if ($length <= 125) $header = pack('CC', $b1, $length);
		elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
		elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
		return $header . $text;
	}
	/**
	 * Handshake new client
	 * @param $recevedHeader Request header sent by the client
	 * @param $client_conn Client connection
	 * @param $host Host name of the websocket server
	 * @param $port Port number of the websocket server
	 */
	public function performHandshaking($recevedHeader, $client_conn, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $recevedHeader);
		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}
	
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		//hand shaking header
		$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" 
			. "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" 
			. "WebSocket-Origin: $host\r\n" 
			. "WebSocket-Location: ws://$host:$port\r\n" 
			. "Sec-WebSocket-Accept: $secAccept\r\n"
			. "X-Engine: PlanetChat\r\n\r\n";
		socket_write($client_conn, $upgrade, strlen($upgrade));
	}
	/**
	 * Convert UTF-8 to 8 bits HTML Entity code
	 * @param $string String to be converted
	 * @return string 8 bits HTML Entity code
	 */
	public function UTF8ToEntities($string){
		if (!@ereg("[\200-\237]",$string) && !@ereg("[\241-\377]",$string))
			return $string;
		$string = preg_replace("/[\302-\375]([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\340-\375].([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\360-\375]..([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\370-\375]...([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\374-\375]....([\001-\177])/","&#65533;\\1",$string);
		$string = preg_replace("/[\300-\301]./", "&#65533;", $string);
		$string = preg_replace("/\364[\220-\277]../","&#65533;",$string);
		$string = preg_replace("/[\365-\367].../","&#65533;",$string);
		$string = preg_replace("/[\370-\373]..../","&#65533;",$string);
		$string = preg_replace("/[\374-\375]...../","&#65533;",$string);
		$string = preg_replace("/[\376-\377]/","&#65533;",$string);
		$string = preg_replace("/[\302-\364]{2,}/","&#65533;",$string);
		$string = preg_replace(
			"/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/e",
			"'&#'.((ord('\\1')&7)<<18 | (ord('\\2')&63)<<12 |".
			" (ord('\\3')&63)<<6 | (ord('\\4')&63)).';'",
		$string);
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
		"'&#'.((ord('\\1')&15)<<12 | (ord('\\2')&63)<<6 | (ord('\\3')&63)).';'",
		$string);
		$string = preg_replace("/([\300-\337])([\200-\277])/e",
		"'&#'.((ord('\\1')&31)<<6 | (ord('\\2')&63)).';'",
		$string);
		$string = preg_replace("/[\200-\277]/","&#65533;",$string);
		return $string;
	}
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		socket_close($this->sock);
	}
}

$host = '127.0.0.1'; //host
$port = '8889'; //port

$server = new ChatServer($host, $port);
$server->run();


?>
