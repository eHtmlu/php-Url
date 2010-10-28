<?php

class mod_pop3 extends mod
{
	// as long as the connection is established, this holds the socket handle
	var $connection = false;

	// every message that is sent to or read from the pop3 server is appended to
	// this variable
	var $log = '';

	// if the server does support secure apop authentification, this contains
	// the apop timestamp banner
	var $apop = false;


	function mod_pop3()
	{
	}


	/**
	 * Creates and returns a new pop3 object. The connection is established
	 * automatically. In case of an error, false is returned.
	 *
	 * @param string username	the username that will be used for authentification
	 * @param string password	the password
	 * @param string host		hostname of the server
	 * @param int port			(optional) port number of the pop3 service,
	 * defaults to the default pop3 port number (110)
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_new($username, $password, $host, $port = 110)
	{
		$pop3 = new mod_pop3();

		if($pop3->_connect($host, $port))
		{
			if($pop3->_authenticate($username, $password)) return $pop3;
			else $pop3->disconnect();
		}

		return false;
	}


	/**
	 * private
	 *
	 * Connects to the given pop3 server. Examination of the server response
	 * tells us whether the server does support apop authentification.
	 *
	 * @param string host	hostname of the server
	 * @param int port		portname of the pop3 service
	 *
	 * @return bool			true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _connect($host, $port)
	{
		if($this->connection = fsockopen($host, $port, $error_number, $error_string, $this->config('connection_timeout')))
		{
			if($this->_check($response = $this->_readline()))
			{
				if(preg_match('/<.+?>/', $response, $matches)) $this->apop = $matches[0];
				return true;
			}
		}
		return false;
	}


	/**
	 * Closes the pop3 connection.
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
    function disconnect()
    {
        $this->_write('QUIT');
        $this->_readline();

		fclose($this->connection);
		$this->connection = false;
    }


	/**
	 * private
	 *
	 * Authentificates the connection using the given username and password. If
	 * the server supports apop authentification (encrypted), it is used.
	 * Otherwise _authenticate() defaults to standard plain password
	 * transmission.
	 *
	 * @param string username	the username
	 * @param string password	the password
	 *
	 * @return bool				true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _authenticate($username, $password)
	{
		if($this->apop)
		{
			$digested_password = md5($this->apop . $password);
			$this->_write("APOP $username $digested_password");
			if($this->_check($this->_readline())) return true;
		}

		$this->_write("USER $username");
		if(!$this->_check($this->_readline())) return false;

		$this->_write("PASS $password");
		if(!$this->_check($this->_readline())) return false;

		return true;
	}


	/**
	 * private ???
	 *
	 * Issues a STAT command to the server. The server will respond with the
	 * number of messages stored and their total size.
	 *
	 * Example response: '+OK 2 1353'
	 * This means that there are two messages available, having 1353 octets
	 * (bytes) total. _STAT() would return array(2, 1353) in that case.
	 *
	 * @return mixed	an array containing the number of messages available and
	 * their total byte count, or false on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _STAT()
	{
		$this->_write('STAT');
		if(!$this->_check($response = $this->_readline())) return false;

		$response = split(' ', $response);

		return array($response[1], $response[2]);
	}


	/**
	 * private ???
	 *
	 * Issues a LIST command to the server. The server will respond with more
	 * detailed information about available messages.
	 *
	 * Example (C - client, S - server):
	 *		C: LIST
	 *		S: +OK 2 messages (320 octets)
	 *		S: 1 120
	 *		S: 2 200
	 *	_LIST() would return array(array(1, 120), array(2, 200)) in that case.
	 *
	 * @return mixed	this array contains an array of the temporary message id
	 * and byte count for every message stored on the server, or false on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _LIST()
	{
		$this->_write('LIST');
		if(!$this->_check($response = $this->_readline())) return false;


		while($response = $this->_readline())
		{
			if(rtrim($response) === '.') break;
			$items[] = explode(' ', $response);
		}

		return $items;
	}


	/**
	 * private
	 *
	 * Checks whether $response is a valid pop3 server response. A valid
	 * response has to start with '+OK'.
	 *
	 * @param string response	the server response string to check
	 *
	 * @return bool				true if the response is valid, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _check($response)
	{
		return (strncmp('+OK', $response, 3)) ? false : true;
	}








	/**
	 * Returns the raw message with the temporary message id $id
	 *
	 * @param int id	the temporary id of the massed, as received by e.g. a
	 * LIST command
	 *
	 * @return mixed	the raw email message, or false on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function get_raw_message($id)
	{
		$this->_write('RETR ' . $id);
		if(!$this->_check($response = $this->_readline())) return false;

		return $this->_read_multiline();
	}


	function get_message($id)
	{
		list($header, $body) = explode("\r\n\r\n", $this->get_raw_message($id), 2);

		$header = str_replace(array("\r\n ", "\r\n\t"), array(' ', "\t"), $header);
		$header = explode("\r\n", $header);
		$headers = array();

		foreach($header as $h)
		{
			$h = explode(':', trim($h), 2);
			if($h[0] == 'From' || $h[0] == 'To' || $h[0] == 'Date' || $h[0] == 'Subject') $headers[$h[0]] = mb_decode_mimeheader($h[1]);
		}

		return $headers;
	}


/*
	function get_message($id)
	{
		list($header, $body) = explode("\r\n\r\n", $this->get_raw_message($id), 2);

		$header = str_replace(array("\r\n ", "\r\n\t"), array(' ', "\t"), $header);
		$header = explode("\r\n", $header);
		$headers = array();

		foreach($header as $h)
		{
			$h = explode(':', trim($h), 2);
#this destroys multiple headers with the same name
			$headers[$h[0]] = mb_decode_mimeheader($h[1]);
		}

		return array($headers, $body);
	}
*/


	/**
	 * private
	 *
	 * Used internally to read server responses that are no longer than one
	 * line. The maximum number of bytes to be read can be limited. Lines
	 * received are appended to the log.
	 *
	 * @param int bytes		(optional) maximum number of bytes to read
	 *
	 * @return string		the server response line
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _readline($bytes = 1000)
    {
		$line = fgets($this->connection, $bytes);

		$this->log .= $line;

		return $line;
    }


	/**
	 * private
	 *
	 * Used internally to read server responses that are longer than one
	 * line. Lines received are appended to the log.
	 *
	 * @return string		the server multiline response
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _read_multiline()
	{
		$buffer = '';

		while($line = $this->_readline())
		{
			if(rtrim($line) === '.') break;
			if(substr($line, 0, 2) == '..') $line = substr($line, 1);
			$buffer .= $line;
		}

		return $buffer;
	}


	/**
	 * private
	 *
	 * This method is used internally to send messages to the pop3 server.
	 * Messages sent are appended to the log.
	 *
	 * @param string message	message to be sent to the server
	 *
	 * @return mixed			the number of bytes transmitted, or false on
	 * error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _write($message)
	{
		$this->log .= $message . "\r\n";
		return fwrite($this->connection, $message . "\r\n");
	}


	/**
	 * Returns the log, which contains the complete dialogue between the pop3
	 * server and this script.
	 *
	 * @return string	the session log
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function getlog()
	{
		return $this->log;
	}

}

?>
