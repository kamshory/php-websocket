<?php

/**
 * Represents a client connected to the WebSocket server.
 *
 * This class holds information about a single client, including their
 * session data, connection details (IP, port), and parsed request headers/cookies.
 */
class ChatClient
{
	/** @var string The client's PHP session ID. */
	public $sessionID = '';
	/** @var array<string, mixed>|null The client's session data. */
	public $sessions = array();
	/** @var string The user ID associated with the client after login. */
	public $userID = '';
	/** @var int|string The unique identifier for this client connection. */
	public $clientID = '';
	/** @var array<string, string> Parsed request headers from the client. */
	public $headers = array();
	/** @var array<string, string> Parsed cookie data from the client. */
	public $cookies = array();
	/** @var string The IP address of the client. */
	public $ip = '';
	/** @var int The port of the client connection. */
	public $port = 0;
	
	/**
	 * Constructor.
	 *
	 * @param int|string $clientID The unique identifier for the client.
	 * @param string $headers The raw HTTP headers from the client's handshake request.
	 * @param string $ip The client's IP address.
	 * @param int $port The client's port.
	 * @param string $session_cookie_name The name of the cookie holding the session ID.
	 */
	public function __construct($clientID, $headers = '', $ip = '', $port = 0, $session_cookie_name = 'PHPSESSID')
	{
		$this->clientID = $clientID;
		if($headers != '')
		{
			$this->headers = Utility::parseHeaders($headers);
			if(isset($this->headers['cookie']))
			{
				$this->cookies = Utility::parseCookie($this->headers['cookie']);
				if(isset($this->cookies[$session_cookie_name]))
				{
					$this->sessionID = $this->cookies[$session_cookie_name];
					$this->sessions = Utility::getSessions($this->sessionID);
				}
			}
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
	 *
	 * @return string A JSON representation of the object.
	 */
	public function toString()
	{
		return json_encode($this);
	}
}