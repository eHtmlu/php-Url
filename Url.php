<?php

/**
 *     Project: tulebox
 *      Author: Helmut Wandl <www.wandls.net>
 *
 *         $Id$
 *
 * Description: Class to handle urls.
 */
class Url
{
   /**
    * The parts of the initialized url as a name -> value object.
    */
   private $tokens;

   /**
    * The document root of the server (generated on a saver way as only to get it by $_SERVER['DOCUMENT_ROOT'] )
    */
   private $DOCUMENT_ROOT;

   /**
    * Initialize the tokens of a given Url, a given path or the current user request
    *
    * @param string $fileOrUrl (optional) A file or directory path to get the associated Url. Or an url as a string to initialize this Url. Or NULL (default) for the current user requested Url. The url as a string can also be a relative url which will interpreted as relative to the current user request.
    */
   public function __construct($fileOrUrl = null)
   {
        $this->DOCUMENT_ROOT = realpath((getenv('DOCUMENT_ROOT') && preg_match('/^'.preg_quote(realpath(getenv('DOCUMENT_ROOT'))).'/', realpath(__FILE__))) ? getenv('DOCUMENT_ROOT') : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__))));

      if ($fileOrUrl === null)
      {
         $this->initTokensByCurrentRequest();
      }
      elseif (file_exists($fileOrUrl))
      {
         $this->initTokensByFile($fileOrUrl);
      }
      else
      {
         $this->initTokensByUrl($fileOrUrl);
      }

      $this->updateAdditionalTokens();
   }

   /**
    * Returns a string with all url parts which are desired by a given format string.
    * The format string can include the following characters:
    *
    * s -> {scheme}      (examples: "http", "https")
    *
    * S -> {scheme}:     (examples: "http:", "https:")
    *                    If {scheme} is empty, the result of this shortcut is also empty.
    *
    * u -> {user}        (examples: "admin", "administrator")
    *
    * k -> {pass}        (examples: "1234", "h1ghSecurePa$$w0rd!")
    *
    * h -> {host}        (examples: "localhost", "example.com", "www.example.com", "subdomain.example.com")
    *
    * o -> {port}        (examples: "8080", "80")
    *
    * O -> :{port}       (examples: ":8080", "")
    *                    If {port} is empty or the default port of {scheme}, the result of this shortcut is empty.
    *
    * p -> {path}        (examples: "subdir/filename.htm", "fileInRootDirectory.htm")
    *
    * P -> /{path}       (examples: "/subdir/filename.htm", "/fileInRootDirectory.htm")
    *
    * q -> {query}       (examples: "name1=value1&name2=value2", "sid=0987654321")
    *
    * Q -> ?{query}      (examples: "?name1=value1&name2=value2", "?sid=0987654321")
    *                    If {query} is empty, the result of this shortcut is also empty.
    *
    * f -> {fragment}    (examples: "top", "content")
    *
    * F -> #{fragment}   (examples: "#top", "#content")
    *                    If {fragment} is empty, the result of this shortcut is also empty.
    *
    *
    * l -> {user}:{pass}                    (examples: "admin:1234", "administrator:h1ghSecurePa$$w0rd!")
    *                                       The colon will be only returned if {pass} is not empty. So if {user} and {pass} are empty, the result of this shortcut is also empty.
    *
    * L -> {user}:{pass}@                   (examples: "admin:1234@", "administrator:h1ghSecurePa$$w0rd!@")
    *                                       The delimiter characters will be only returned if needed. If {user} and {pass} are empty, the result of this shortcut is also empty.
    *
    * a -> {user}:{pass}@{host}:{port}      (examples: "admin:1234@localhost:8080", "administrator:h1ghSecurePa$$w0rd!@example.com:80")
    *                                       The delimiter characters will be only returned if needed. If {user} and {pass} are empty and the port is the default port of {scheme}, the result of this shortcut only contains "{host}".
    *
    * A -> //{user}:{pass}@{host}:{port}    (examples: "//admin:1234@localhost:8080", "//administrator:h1ghSecurePa$$w0rd!@example.com:80")
    *                                       The delimiter characters will be only returned if needed. If {user} and {pass} are empty and the port is the default port of {scheme}, the result of this shortcut only contains "//{host}".
    *
    * @param string $format A string which defines the content of the result string by character shortcuts. The default value is "SAPQF" which represents a complete url.
    * @return string The result of the parsed format string
    */
   public function get($format = 'SAPQF')
   {
      $tokens = preg_split('/([sSukhoOpPqQfFlLaA])/', $format, -1, PREG_SPLIT_DELIM_CAPTURE);
      for ($i = 1; $i < count($tokens); $i += 2)
      {
         $tokens[$i] = $this->tokens->{$tokens[$i]};
      }
      return implode($tokens);
   }

   /**
    * Returns the relative path based on a given origin url
    *
    * @param Url $base The origin url from which the relative path starts.
    * @return string The relative path from the given url to the current used url.
    */
   public function getRelativePath(Url $base)
   {
      $result = false;

      if ($this->get('SA') == $base->get('SA'))
      {
         $basePath = explode('/', $base->get('P'));
         $targPath = explode('/', $this->get('P'));

         while (isset($basePath[0]) && isset($targPath[0]) && $basePath[0] == $targPath[0])
         {
            array_shift($basePath);
            array_shift($targPath);
         }

         $result = str_repeat('../', count($basePath) - 1) . implode('/', $targPath);
      }

      return $result;
   }

   /**
    * set main tokens based on the current user request (for {scheme},{user},{pass},{host} and {port}) and a given file or directory (for {path}). The parts {query} and {fragment} are left empty.
    *
    * @param string $fileOrDirname The file or directory path which have to initialize
    */
   private function initTokensByFile($fileOrDirname)
   {
      $fileOrDirname = realpath($fileOrDirname);

      if (strpos($fileOrDirname, $this->DOCUMENT_ROOT) === 0)
      {
         $fileOrDirname = substr($fileOrDirname, strlen($this->DOCUMENT_ROOT));
      }

      $path = str_replace(DIRECTORY_SEPARATOR, '/', $fileOrDirname) . ( is_dir($this->DOCUMENT_ROOT . $fileOrDirname) ? '/' : '' );
      $this->initTokensByCurrentRequest();
      $this->tokens->P = $this->resolvePath($path);
      $this->tokens->q = '';
      $this->tokens->f = '';
   }

   /**
    * set main tokens based on a given url
    *
    * @param string $url The relative or absolute url which have to initialize. A relative url is interpreted as relative to the current user request.
    */
   private function initTokensByUrl($url)
   {
      $samehost = false;

      preg_match('/^(?:([a-z]+)\:|)(?:\/\/(?:([^\:\@]+)(?:\:([^\:\@]+)|)\@|)([^\/\:]+)|)(?:\:([0-9]+)|)([^\?\#]*)(?:\?([^\#]*)|)(?:\#(.*)|)$/', $url, $match);

      if ($match && ($current = $this->initTokensByCurrentRequest()) && (!$match[4] || $match[4] == $current->h))
      {
         $samehost = true;
      }

      $this->tokens = $match ? (object) array(
         's' => $match[1] ? $match[1] : ($samehost ? $current->s : ''),
         'u' => $match[2] ? $match[2] : ($samehost ? $current->u : ''),
         'k' => $match[3] ? $match[3] : ($samehost ? $current->k : ''),
         'h' => $match[4] ? $match[4] : ($samehost ? $current->h : ''),
         'o' => $match[5] ? $match[5] : ($samehost ? $current->o : $this->getDefaultPort($match[1])),
         'P' => $this->resolvePath($samehost && substr($match[6], 0, 1) != '/' ? preg_replace('/\/[^\/]*$/', '/', $current->P).$match[6] : $match[6]),
         'q' => $match[7],
         'f' => $match[8]
      ) : false;
   }

   /**
    * set main tokens based on the current user request
    */
   private function initTokensByCurrentRequest()
   {
      return ($this->tokens = (object) array(
         's' => substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')) . ($_SERVER["HTTPS"] && $_SERVER["HTTPS"] != 'off' ? 's' : ''),
         'u' => $_SERVER['PHP_AUTH_USER'],
         'k' => $_SERVER['PHP_AUTH_PW'],
         'h' => $_SERVER['SERVER_NAME'],
         'o' => $_SERVER['SERVER_PORT'],
         'P' => $this->resolvePath(strpos($_SERVER['REQUEST_URI'], '?') !== false ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']),
         'q' => $_SERVER['QUERY_STRING'],
         'f' => ''
      ));
   }

   /**
    * set a view additional tokens based on the main tokens
    */
   private function updateAdditionalTokens()
   {
      $this->tokens->S = $this->tokens->s ? $this->tokens->s.':' : '';
      $this->tokens->O = $this->tokens->o && $this->tokens->o != $this->getDefaultPort($this->tokens->s) ? ':'.$this->tokens->o : '';
      $this->tokens->p = $this->tokens->P && substr($this->tokens->P, 0, 1) == '/' ? substr($this->tokens->P, 1) : $this->tokens->P;
      $this->tokens->Q = $this->tokens->q ? '?'.$this->tokens->q : '';
      $this->tokens->F = $this->tokens->f ? '#'.$this->tokens->f : '';

      $this->tokens->l = $this->tokens->u.':'.$this->tokens->k;
      $this->tokens->L = $this->tokens->u || $this->tokens->k ? $this->tokens->u.':'.$this->tokens->k.'@' : '';
      $this->tokens->a = $this->tokens->L.$this->tokens->h.$this->tokens->O;
      $this->tokens->A = $this->tokens->L.$this->tokens->h.$this->tokens->O ? '//'.$this->tokens->L.$this->tokens->h.$this->tokens->O : '';
   }

   /**
    * gets the default port of a given scheme
    */
   private function getDefaultPort($scheme)
   {
      $result = null;
      switch ($scheme)
      {
         case 'http': $result = '80'; break;
         case 'https': $result = '443'; break;
      }
      return $result;
   }

   /**
    * eliminates double slashes and unneeded directory changes ("../")
    */
   private function resolvePath($path)
   {
      return preg_replace('/\/+/', '/', preg_replace('/\/?[^\/]+\/\.\.\/?/', '/', $path));
   }
}

?>