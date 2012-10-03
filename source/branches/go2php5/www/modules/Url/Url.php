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
    * The host of the initialized url
    */
   private $host;

   /**
    * The parts (other than host) of the initialized url as an array.
    * The indexes are equal to the constants of the URLPARTS class.
    */
   private $tokens;

   /**
    * The delimiter characters of the url parts as an array
    * The indexes are equal to the constants of the URLPARTS class.
    */
   private $delimiter = array(
      URLPARTS::SCHEME => array(false, ':'),
      URLPARTS::AUTHENTICATION => array(false, '@'),
      URLPARTS::PORT => array(':', false),
      URLPARTS::PATH => array('/', false),
      URLPARTS::QUERY => array('?', false),
      URLPARTS::FRAGMENT => array('#', false)
   );

   /**
    * Initialize the tokens of a given Url, a given path or the current user request
    *
    * @param string $fileOrUrl (optional) A file or directory path to get the associated Url. Or an url as a string to initialize this Url. Or NULL (default) for the current user requested Url. The url as a string can also be a relative url which will interpreted as relative to the current user request.
    */
   public function __construct($fileOrUrl = null)
   {
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
   }

   /**
    * Gets the scheme part of the url
    *
    * @return string Scheme part of the url
    */
   public function getScheme()
   {
      return $this->tokens[URLPARTS::SCHEME];
   }

   /**
    * Gets the authentication part of the url
    *
    * @return string Authentication part of the url
    */
   public function getAuthentication()
   {
      return $this->tokens[URLPARTS::AUTHENTICATION];
   }

   /**
    * Gets an associative array of the authentication part of the url
    *
    * @return array Authentication part of the url as an associative array with the indexes "username" and "password"
    */
   public function getAuthenticationParameters()
   {
      $auth = explode(':', $this->tokens[URLPARTS::AUTHENTICATION], 2);

      return array(
      	'username' => urldecode($auth[0]),
      	'password' => urldecode($auth[1])
      );
   }

   /**
    * Gets the host part of the url
    *
    * @return string Host part of the url
    */
   public function getHost()
   {
      return $this->host;
   }

   /**
    * Gets the port part of the url
    *
    * @return string Port part of the url
    */
   public function getPort()
   {
      return $this->tokens[URLPARTS::PORT];
   }

   /**
    * Gets the path part of the url (without the root slash)
    *
    * @return string Path part of the url (without the root slash)
    */
   public function getPath()
   {
      return $this->tokens[URLPARTS::PATH];
   }

   /**
    * Gets the query part of the url
    *
    * @return string Query part of the url
    */
   public function getQuery()
   {
      return $this->tokens[URLPARTS::QUERY];
   }

   /**
    * Gets an associative array of the query part of the url. Like the global $_GET variable of the current request
    *
    * @todo arrays should be interpreted - equal to $_GET variable of PHP (example: //localhost/?name[]=1&name[]=2 )
    *
    * @return array An associative array of the query parameters
    */
   public function getQueryParameters()
   {
      $param = array();

      foreach (explode('&', $this->tokens[URLPARTS::QUERY]) as $item)
      {
         $item = explode('=', $item, 2);
         $param[urldecode($item[0])] = urldecode($item[1]);
      }

      return $param;
   }

   /**
    * Gets the fragment part of the url
    *
    * @return string Fragment part of the url
    */
   public function getFragment()
   {
      return $this->tokens[URLPARTS::FRAGMENT];
   }

   /**
    * Gets the whole well formed and correct url as a string
    *
    * @return string The whole well formed and correct formatted url
    */
   public function getUrl()
   {
      return $this->getUrlPartSequence(URLPARTS::SCHEME | URLPARTS::AUTHENTICATION | URLPARTS::PORT | URLPARTS::PATH | URLPARTS::QUERY | URLPARTS::FRAGMENT);
   }

   /**
    * Returns one part of the url based on a given bit constant of the URLPARTS class
    *
    * @param int $part One bit constant of URLPARTS class
    * @param mixed $delimiter The delimiter character of the url part will be (on TRUE) or will be not (on FALSE) in the result. If is NULL (default) the delimiter will only be in the result if the url part is not empty.
    * @return string The part of the url which is associated with the bit constance in the $part parameter
    */
   public function getUrlPart($part, $delimited = null)
   {
      return $delimited === true || $delimited === null && $this->tokens[$part]
             ? $this->delimiter[$part][0] . $this->tokens[$part] . $this->delimiter[$part][1]
             : $this->tokens[$part];
   }

   /**
    * Gets a well formed and correct url as a string, based on bit constants of the URLPARTS class
    *
    * @param int $parts An integer based on the combination of bit constants from the URLPARTS class
    * @return string A well formed and correct formatted url
    */
   // @todo der port wird hier ungefragt weggelassen, wenn es sich um den default port des schemas handelt. Dies sollte noch überlegt werden ob das gut ist.
   public function getUrlPartSequence($parts = 0)
   {
      return (($parts & 1) === 1 ? $this->getUrlPart(URLPARTS::SCHEME) : '') .
             '//' .
             (($parts & 2) === 2 ? $this->getUrlPart(URLPARTS::AUTHENTICATION) : '') .
             $this->host .
             (($parts & 4) === 4 ? ($this->getUrlPart(URLPARTS::PORT, false) != $this->getDefaultPort($this->getUrlPart(URLPARTS::SCHEME, false)) ? $this->getUrlPart(URLPARTS::PORT) : '') : '') .
             '/' .
             (($parts & 8) === 8 ? $this->getUrlPart(URLPARTS::PATH, false) : '') .
             (($parts & 16) === 16 ? $this->getUrlPart(URLPARTS::QUERY) : '') .
             (($parts & 32) === 32 ? $this->getUrlPart(URLPARTS::FRAGMENT) : '')
      ;
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
      $urlPartSequence = URLPARTS::SCHEME | URLPARTS::AUTHENTICATION | URLPARTS::PORT;

      if ($this->getUrlPartSequence($urlPartSequence) == $base->getUrlPartSequence($urlPartSequence))
      {
         $basePath = explode('/', $base->getUrlPart(URLPARTS::PATH, false));
         $targPath = explode('/', $this->getUrlPart(URLPARTS::PATH, false));

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
    * set tokens based on the current user request (for {scheme},{authentication},{host} and {port}) and a given file or directory (for {path}). The parts {query} and {fragment} are left empty.
    *
    * @param string $fileOrDirname The file or directory path which have to initialize
    */
   private function initTokensByFile($fileOrDirname)
   {
      $fileOrDirname = realpath($fileOrDirname);
      $documentRoot = $this->getDocumentRoot();

      if (strpos($fileOrDirname, $documentRoot) === 0)
      {
         $fileOrDirname = substr($fileOrDirname, strlen($documentRoot));
      }

      $path = str_replace(DIRECTORY_SEPARATOR, '/', $fileOrDirname) . ( is_dir($documentRoot . $fileOrDirname) ? '/' : '' );
      $this->initTokensByCurrentRequest();
      $this->tokens[URLPARTS::PATH] = $this->resolvePath($path);
      $this->tokens[URLPARTS::QUERY] = '';
      $this->tokens[URLPARTS::FRAGMENT] = '';
   }

   /**
    * set tokens based on a given url
    *
    * @param string $url The relative or absolute url which have to initialize. A relative url is interpreted as relative to the current user request.
    */
   private function initTokensByUrl($url)
   {
      $samehost = false;

      preg_match('/^(?:([a-z]+)\:|)(?:\/\/(?:([^\:\@]+)(?:\:([^\:\@]+)|)\@|)([^\/\:]+)|)(?:\:([0-9]+)|)([^\?\#]*)(?:\?([^\#]*)|)(?:\#(.*)|)$/i', $url, $match);

      if ($match)
      {
         $this->initTokensByCurrentRequest();

         if (!$match[4] || $match[4] == $this->host)
         {
            $samehost = true;
         }
      }

      $this->host = $match ? ($match[4] ? $match[4] : ($samehost ? $this->host : '')) : false;
      $this->tokens = $match ? array(
         URLPARTS::SCHEME => strtolower($match[1] ? $match[1] : ($samehost ? $this->tokens[URLPARTS::SCHEME] : '')),
         URLPARTS::AUTHENTICATION => ($match[2].$match[3] ? $match[2] . ($match[3] ? ':' . $match[3] : '') : ($samehost ? $this->tokens[URLPARTS::AUTHENTICATION] : '')),
         URLPARTS::PORT => $match[5] ? $match[5] : ($samehost ? $this->tokens[URLPARTS::PORT] : $this->getDefaultPort($match[1])),
         URLPARTS::PATH => $this->resolvePath($samehost && substr($match[6], 0, 1) != '/' ? preg_replace('/\/[^\/]*$/', '/', $this->tokens[URLPARTS::PATH]).$match[6] : $match[6]),
         URLPARTS::QUERY => $match[7],
         URLPARTS::FRAGMENT => $match[8]
      ) : false;
   }

   /**
    * set tokens based on the current user request
    */
   private function initTokensByCurrentRequest()
   {
      $this->host = $_SERVER['SERVER_NAME'];
      return ($this->tokens = array(
         URLPARTS::SCHEME => substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')) . ($_SERVER["HTTPS"] && $_SERVER["HTTPS"] != 'off' ? 's' : ''),
         URLPARTS::AUTHENTICATION => rawurlencode($_SERVER['PHP_AUTH_USER']) . ($_SERVER['PHP_AUTH_PW'] ? ':' . rawurlencode($_SERVER['PHP_AUTH_PW']) : ''),
         URLPARTS::PORT => $_SERVER['SERVER_PORT'],
         URLPARTS::PATH => $this->resolvePath(strpos($_SERVER['REQUEST_URI'], '?') !== false ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']),
         URLPARTS::QUERY => $_SERVER['QUERY_STRING'],
         URLPARTS::FRAGMENT => ''
      ));
   }

   /**
    * gets the default port of a given scheme to omit the port in returned url strings if possible
    */
   private function getDefaultPort($scheme)
   {
      $result = null;
      switch ($scheme)
      {
         case 'http': $result = '80'; break;
         case 'https': $result = '443'; break;
         case 'ftp': $result = '21'; break;
      }
      return $result;
   }

   /**
    * detect the document_root on a more reliable way as usage of $_SERVER['DOCUMENT_ROOT']
    */
   private function getDocumentRoot()
   {
      static $result = null;

      if ($result === null)
      {
         if (($envDocRoot = getenv('DOCUMENT_ROOT')) && ($envDocRoot = realpath($envDocRoot)) && strpos(realpath(__FILE__), $envDocRoot) === 0)
         {
            $result = $envDocRoot;
         }
         elseif (($pos = strpos($absolutePath = realpath(basename($localPath = str_replace('/', DIRECTORY_SEPARATOR, getenv('SCRIPT_NAME')))), $localPath)) > 0 && $pos === strlen($absolutePath) - strlen($localPath))
         {
            $result = substr($absolutePath, 0, $pos);
         }
         elseif (($pos = strpos($absolutePath = realpath(getenv('SCRIPT_FILENAME')), $localPath = str_replace('/', DIRECTORY_SEPARATOR, getenv('SCRIPT_NAME')))) > 0 && $pos == strlen($absolutePath) - strlen($localPath))
         {
            $result = substr($absolutePath, 0, $pos);
         }
      }

      return $result;
   }

   /**
    * eliminates double slashes and unneeded directory changes ("xyz/../") to make the path part of the url more beautiful and shorter
    */
   private function resolvePath($path)
   {
      return ltrim(preg_replace('/\/?[^\/]+\/\.\.\/?/', '/', preg_replace('/\/+/', '/', $path)), '/');
   }
}

?>