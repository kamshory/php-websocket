<?php

/**
 * A collection of static helper methods for parsing web-related data.
 *
 * This class provides utilities for parsing HTTP headers, cookies, and
 * decoding PHP session data from files. It is used by other classes to
 * extract meaningful information from raw request and session data.
 */
class Utility
{
	/**
	* Parse request header
	* @param string $headers Request header from client
	* @return array<string, string> Associated array of the request header
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
	* @param string $cookieString Cookie from client
	* @return array<string, string> Associated array of the cookie
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
	* @param array<string, string> $cookieData Associated array of the cookie
	* @param string $name Cookie name
	* @return string Cookie value or a new md5 hash
	*/
	public static function readCookie($cookieData, $name)
	{
		$v0 = (isset($cookieData[$name."0"]))?($cookieData[$name."0"]):"";
		$v1 = (isset($cookieData[$name."1"]))?($cookieData[$name."1"]):"";
		$v2 = (isset($cookieData[$name."2"]))?($cookieData[$name."2"]):"";
		$v3 = (isset($cookieData[$name."3"]))?($cookieData[$name."3"]):"";
		$v  = strrev(str_rot13($v1.$v3.$v2.$v0));
		if($v=="")
		{
			return md5(microtime().mt_rand(1,9999999));
		}
		else 
		{
			return $v;
		}
	}

	/**
	* Get session data
	* @param string $sessionID Session ID
	* @param string|null $sessionSavePath Session save path
	* @param string $prefix Prefix of the session file name
	* @return array|null Associated array containing session data or null on failure
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
		return null;
	}
    
	/**
	* Decode session data
	* @param string $sessionData Raw session data
	* @return array Associated array containing session data
	* @throws Exception if session data is invalid.
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
	* @param string $sessionData Raw session data
	* @return array Associated array containing session data
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

    /**
	 * Convert UTF-8 to 8 bits HTML Entity code
	 * @param string $string String to be converted.
	 * @return string String with UTF-8 characters converted to HTML numeric entities.
	 */
	public static function utf8ToEntities($string)
	{
		// Quick check for any non-7-bit ASCII characters. If none, no conversion is needed.
        // Prefer the modern, safe, and efficient mbstring extension if it's available.
        if (function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
        }
        else
        {
            // Fallback implementation if mbstring is not available.
            // This replaces the deprecated and insecure preg_replace with the /e modifier.
            if (!preg_match('/[\x80-\xFF]/', $string)) {
                return $string;
            }

            // The following regexes are part of the original logic to handle invalid UTF-8 sequences.
            $string = preg_replace("/[\302-\375]([\001-\177])/","&#65533;\\1", $string);
            $string = preg_replace("/[\340-\375].([\001-\177])/","&#65533;\\1", $string);
            $string = preg_replace("/[\360-\375]..([\001-\177])/","&#65533;\\1", $string);
            $string = preg_replace("/[\370-\375]...([\001-\177])/","&#65533;\\1", $string);
            $string = preg_replace("/[\374-\375]....([\001-\177])/","&#65533;\\1", $string);
            $string = preg_replace("/[\300-\301]./", "&#65533;", $string);
            $string = preg_replace("/\364[\220-\277]../", "&#65533;", $string);
            $string = preg_replace("/[\365-\367].../","&#65533;", $string);
            $string = preg_replace("/[\370-\373]..../","&#65533;", $string);
            $string = preg_replace("/[\374-\375]...../","&#65533;", $string);
            $string = preg_replace("/[\376-\377]/","&#65533;", $string);
            $string = preg_replace("/[\302-\364]{2,}/","&#65533;", $string);

            // Convert valid multi-byte sequences to HTML entities.
            $string = preg_replace_callback("/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/",
                function($m) { return '&#' . (((ord($m[1]) & 7) << 18) | ((ord($m[2]) & 63) << 12) | ((ord($m[3]) & 63) << 6) | (ord($m[4]) & 63)) . ';'; }, $string);
            $string = preg_replace_callback("/([\340-\357])([\200-\277])([\200-\277])/",
                function($m) { return '&#' . (((ord($m[1]) & 15) << 12) | ((ord($m[2]) & 63) << 6) | (ord($m[3]) & 63)) . ';'; }, $string);
            $string = preg_replace_callback("/([\300-\337])([\200-\277])/",
                function($m) { return '&#' . (((ord($m[1]) & 31) << 6) | (ord($m[2]) & 63)) . ';'; }, $string);

            // Replace any remaining invalid bytes.
            $string = preg_replace("/[\200-\277]/","&#65533;", $string);
            return $string;
        }
	}
}
