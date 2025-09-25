<?php

/**
 * Implements a basic WebSocket server for a chat application.
 *
 * This class handles WebSocket connections, handshakes, message framing (masking/unmasking),
 * and broadcasting messages to connected clients. It uses low-level socket functions.
 */
class ChatServer
{
	/**
	 * @var string Server host IP address.
	 */
	public $host = '127.0.0.1';
	/**
	 * @var int Server port number.
	 */
	public $port = 8888;	
	/**
	 * @var \Socket|null The main server socket resource.
	 */
	public $socket = NULL;
	/**
	 * @var array<int, \Socket> An array of connected client socket resources.
	 */
	public $clients = array();
	/**
	 * @var array<int, ChatClient> An array of ChatClient objects corresponding to connected clients.
	 */
	public $chatClients = array();
	/**
	 * @var int The maximum size of data to receive from a socket in a single chunk.
	 */
	public $maxDataSize = 65536;
	
	/**
	 * Constructor.
	 *
	 * Initializes the server socket, binds it to the specified host and port,
	 * and starts listening for incoming connections.
	 *
	 * @param string $host The IP address to bind the server to.
	 * @param int $port The port to listen on.
	 */
	public function __construct($host, $port)
	{
		$this->host = $host;
		$this->port = (int) $port;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// reuseable port
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		// bind socket to specified host
		socket_bind($this->socket, $this->host, $this->port);
		// listen to port
		socket_listen($this->socket);
		$this->clients = array($this->socket);
	}
	
	/**
	 * Runs the main server loop.
	 *
	 * @return void
	 */
	public function run()
	{
		$index = 0;
		$null = null; //null var
		while (true) 
		{
			// manage multiple connections
			$changed = $this->clients;
			// returns the socket resources in $changed array
			@socket_select($changed, $null, $null, 0, 10);
			// check for new socket
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
					while (@socket_recv($changeSocket, $buf, $this->maxDataSize, 0) >= 1) 
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
	 * @param ChatClient $clientChat The ChatClient object for the new connection.
	 * @param string $ip Remote address or IP address of the client.
	 * @param int $port Remote port or port number of the client.
	 * @return void
	 */
	public function onOpen($clientChat, $ip = '', $port = 0)
	{
		$username = $clientChat->sessions['username'] ?? 'A user';
		$chatroom = $clientChat->chatroom;
		$response = json_encode(array('type' => 'system', 'message' => $username . ' has connected.'));
		$this->sendBroadcast($response, $chatroom);
	}

	/**
	 * Method when a new client is disconnected
	 * @param ChatClient $clientChat The ChatClient object for the disconnected client.
	 * @param string $ip Remote address or IP address of the client.
	 * @param int $port Remote port or port number of the client.
	 * @return void
	 */
	public function onClose($clientChat, $ip = '', $port = 0)
	{
		$username = $clientChat->sessions['username'] ?? 'A user';
		$chatroom = $clientChat->chatroom;
		$response = json_encode(array('type' => 'system', 'message' => $username . ' has disconnected.'));
		$this->sendBroadcast($response, $chatroom);
	}

	/**
	 * Method when a client send the message
	 * @param ChatClient $clientChat The ChatClient object that sent the message.
	 * @param string $receivedText Text sent by the client.
	 * @param string $ip Remote address or IP address of the client.
	 * @param int $port Remote port or port number of the client.
	 * @return void
	 */
	public function onMessage($clientChat, $receivedText, $ip = '', $port = 0)
	{
		$tstMsg = json_decode($receivedText, true); //json decode
		if(isset($tstMsg) &&  !empty($tstMsg))
		{
			$userName = $tstMsg['name']; //sender name
			$userMessage = htmlspecialchars($tstMsg['message'], ENT_QUOTES, 'UTF-8'); //message text
			$chatroom = $tstMsg['chatroom']; // chatroom
			$responseText = json_encode(array('type' => 'usermsg', 'name' => $userName, 'message' => $userMessage));
			$this->sendBroadcast($responseText, $chatroom); //send data
		}
	}
	/**
	 * Method to send the broadcast message to all clients in a specific room.
	 * @param string $message Message to send to all clients.
	 * @param string $chatroom The room to broadcast to.
	 * @return void
	 */
	public function sendBroadcast($message, $chatroom)
	{
		$maskedMessage = $this->mask($message);
		foreach ($this->chatClients as $index => $client) 
		{
			// Send message only to clients in the same chatroom
			if ($client->chatroom === $chatroom) {
				$targetSocket = $this->clients[$index];
				if (isset($targetSocket)) {
					@socket_write($targetSocket, $maskedMessage, strlen($maskedMessage));
				}
			}
		}
	}

	/**
	 * Method to send message to a client
	 * @param \Socket $changeSocket Client socket resource.
	 * @param string $message Message to send to the client.
	 * @return void
	 */
	public function sendMessage($changeSocket, $message)
	{
		$maskedMessage = $this->mask($message);
		@socket_write($changeSocket, $maskedMessage, strlen($maskedMessage));
	}

	/**
	 * Unmask incoming framed message
	 * @param string $text Masked message from the client.
	 * @return string Unmasked plain text.
	 */
	public function unmask($text)
	{
		$length = ord($text[1]) & 127;
		if ($length == 126) 
		{
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} 
		else if ($length == 127) 
		{
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} 
		else 
		{
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) 
		{
			$text.= $data[$i] ^ $masks[$i % 4];
		}
		return $text;
	}

	/**
	 * Encode message for transfer to client
	 * @param string $text Plain text to be sent to the client.
	 * @return string Masked message ready for sending.
	 */
	public function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		if ($length <= 125) 
		{
			$header = pack('CC', $b1, $length);
		}
		else if ($length > 125 && $length < 65536)
		{ 
			$header = pack('CCn', $b1, 126, $length);
		}
		else if($length >= 65536)
		{
			$header = pack('CCNN', $b1, 127, $length);
		} 
		return $header . $text;
	}
    
	/**
	 * Handshake new client
	 * @param string $receivedHeader Request header sent by the client.
	 * @param \Socket $client_conn Client socket resource.
	 * @param string $host Host name of the websocket server.
	 * @param int $port Port number of the websocket server.
	 * @return void
	 */
	public function performHandshaking($receivedHeader, $clientConn, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $receivedHeader ?? '');
		foreach ($lines as $line) 
		{
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) 
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		if(isset($headers['Sec-WebSocket-Key']))
		{
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			//hand shaking header
			$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" 
				. "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" 
				. "WebSocket-Origin: $host\r\n" 
				. "WebSocket-Location: ws://$host:$port\r\n" 
				. "Sec-WebSocket-Accept: $secAccept\r\n"
				. "X-Engine: PlanetChat\r\n\r\n";
			socket_write($clientConn, $upgrade, strlen($upgrade));
		}
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
	}
}
