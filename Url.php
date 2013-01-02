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
    * The parts of the initialized url as an array.
    * The indexes are equal to the constants of the UrlParts class.
    */
   private $tokens;

   /**
    * @var array Associative array within schemes as names and default ports as values
    */
   private $defaultPorts;

   /**
    * Initialize the tokens of a given Url, a given path or the current user request
    *
    * @param string $fileOrUrl (optional) A file or directory path to get the associated Url. Or an url as a string to initialize this Url. Or NULL (default) for the current user requested Url. The url as a string can also be a relative url which will interpreted as relative to the current user request.
    */
   public function __construct($fileOrUrl = null)
   {
      $this->defaultPorts = array(
         'http' => '80',
         'https' => '443',
         'ftp' => '21'
      );

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
      return $this->tokens[UrlParts::SCHEME];
   }

   /**
    * Gets the user information part of the url
    *
    * @return string User information part of the url
    */
   public function getUserinfo()
   {
      return $this->tokens[UrlParts::USERINFO];
   }

   /**
    * Gets an associative array of the user information part of the url
    *
    * @return array User information part of the url as an associative array with the indexes "username" and "password"
    */
   public function getUserinfoParameters()
   {
      $auth = explode(':', $this->tokens[UrlParts::USERINFO], 2);

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
      return $this->tokens[UrlParts::HOST];
   }

   /**
    * Gets the port part of the url
    *
    * @return string Port part of the url
    */
   public function getPort()
   {
      return $this->tokens[UrlParts::PORT];
   }

   /**
    * Gets the path part of the url
    *
    * @return string Path part of the url
    */
   public function getPath()
   {
      return $this->tokens[UrlParts::PATH];
   }

   /**
    * Gets the query part of the url
    *
    * @return string Query part of the url
    */
   public function getQuery()
   {
      return $this->tokens[UrlParts::QUERY];
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

      foreach (explode('&', $this->tokens[UrlParts::QUERY]) as $item)
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
      return $this->tokens[UrlParts::FRAGMENT];
   }

   /**
    * Gets the well formed url as a string
    *
    * @param $parts A combination of bit constances from the UrlParts class to define which parts of the url will be included (default: UrlParts::ALL)
    * @return string The whole well formed and correct formatted url
    */
   public function getUrl($parts = UrlParts::ALL)
   {
      $userinfo = explode(':', $this->tokens[UrlParts::USERINFO]);

      $authority =
         (($parts & UrlParts::USERINFO) === UrlParts::USERINFO && $this->tokens[UrlParts::USERINFO] ? $userinfo[0] . ($userinfo[1] === '' ? ':' : '') . '@' : '') .
         (($parts & UrlParts::HOST) === UrlParts::HOST && $this->tokens[UrlParts::HOST] ? $this->tokens[UrlParts::HOST] : '') .
         (($parts & UrlParts::PORT) === UrlParts::PORT && $this->tokens[UrlParts::PORT] && $this->tokens[UrlParts::PORT] != $this->defaultPorts[$this->tokens[UrlParts::SCHEME]] ? ':' . $this->tokens[UrlParts::PORT] : '');

      return
         (($parts & UrlParts::SCHEME) === UrlParts::SCHEME && $this->tokens[UrlParts::SCHEME] ? $this->tokens[UrlParts::SCHEME] . ':' : '') .
         ($authority ? '//' . $authority : '') .
         (($parts & UrlParts::PATH) === UrlParts::PATH ? $this->tokens[UrlParts::PATH] : '') .
         (($parts & UrlParts::QUERY) === UrlParts::QUERY && $this->tokens[UrlParts::QUERY] ? '?' . $this->tokens[UrlParts::QUERY] : '') .
         (($parts & UrlParts::FRAGMENT) === UrlParts::FRAGMENT && $this->tokens[UrlParts::FRAGMENT] ? '#' . $this->tokens[UrlParts::FRAGMENT] : '')
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
      $urlPartSequence = UrlParts::SCHEME | UrlParts::USERINFO | UrlParts::HOST | UrlParts::PORT;

      if ($this->getUrl($urlPartSequence) == $base->getUrl($urlPartSequence))
      {
         $basePath = explode('/', $base->getPath());
         $targPath = explode('/', $this->getPath());

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
    * set tokens based on the current user request (for {scheme},{userinfo},{host} and {port}) and a given file or directory (for {path}). The parts {query} and {fragment} are left empty.
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
      $this->tokens[UrlParts::PATH] = $this->resolvePath($path);
      $this->tokens[UrlParts::QUERY] = '';
      $this->tokens[UrlParts::FRAGMENT] = '';

      $this->normalizeTokens();
   }

   /**
    * set tokens based on a given url
    *
    * @param string $url The relative or absolute url which have to initialize. A relative url is interpreted as relative to the current user request.
    */
   private function initTokensByUrl($url)
   {
      $samehost = false;
      $current = new Url();

      preg_match('/^(?:([a-zA-Z][a-zA-Z0-9\+\-\.]*)\:)?(?:\/\/(?:([^\:\@]+)(\:[^\@]*)?\@)?([^\/\?\#\:]+)(?:\:([0-9]+))?)?([^\?\#]*)?(?:\?([^\#]*))?(?:\#(.*))?$/', $url, $match);

      if ($match)
      {
         $this->initTokensByCurrentRequest();

         if (!$match[4] || strtolower($match[4]) == $current->getHost())
         {
            $samehost = true;
         }
      }

      $this->tokens = $match ? array(
         UrlParts::SCHEME => $match[1] ? $match[1] : ($samehost ? $current->getScheme() : ''),
         UrlParts::USERINFO => ($match[2].$match[3] ? $match[2] . $match[3] : ($samehost ? $current->getUserinfo() : '')),
         UrlParts::HOST => $match ? $match[4] ? $match[4] : ($samehost ? $current->getHost() : '') : false,
         UrlParts::PORT => $match[5] ? $match[5] : ($samehost ? $current->getPort() : $this->defaultPorts[strtolower($match[1])]),
         UrlParts::PATH => $this->resolvePath($samehost && substr($match[6], 0, 1) != '/' ? preg_replace('/\/[^\/]*$/', '/', $current->getPath()).$match[6] : $match[6]),
         UrlParts::QUERY => $match[7],
         UrlParts::FRAGMENT => $match[8]
      ) : false;

      $this->normalizeTokens();
   }

   /**
    * set tokens based on the current user request
    */
   private function initTokensByCurrentRequest()
   {
      $this->tokens = array(
         UrlParts::SCHEME => substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')) . ($_SERVER["HTTPS"] && $_SERVER["HTTPS"] != 'off' ? 's' : ''),
         UrlParts::USERINFO => rawurlencode($_SERVER['PHP_AUTH_USER']) . ($_SERVER['PHP_AUTH_PW'] ? ':' . rawurlencode($_SERVER['PHP_AUTH_PW']) : ''),
         UrlParts::HOST => $_SERVER['SERVER_NAME'],
         UrlParts::PORT => $_SERVER['SERVER_PORT'],
         UrlParts::PATH => $this->resolvePath(strpos($_SERVER['REQUEST_URI'], '?') !== false ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']),
         UrlParts::QUERY => $_SERVER['QUERY_STRING'],
         UrlParts::FRAGMENT => ''
      );

      $this->normalizeTokens();
   }

   /**
    * normalize the parts of the url. Transforms scheme and host to lowercase and normalizes the encoded characters of path, query and fragment
    */
   private function normalizeTokens()
   {
      $this->tokens[UrlParts::SCHEME] = strtolower($this->tokens[UrlParts::SCHEME]);
      $this->tokens[UrlParts::HOST] = strtolower($this->tokens[UrlParts::HOST]);
      $this->tokens[UrlParts::PATH] = $this->normalizeEncodedCharacters($this->tokens[UrlParts::PATH]);
      $this->tokens[UrlParts::QUERY] = $this->normalizeEncodedCharacters($this->tokens[UrlParts::QUERY]);
      $this->tokens[UrlParts::FRAGMENT] = $this->normalizeEncodedCharacters($this->tokens[UrlParts::FRAGMENT]);
   }

   /**
    * Changes the url encoded characters (for example "%2f") to uppercase ("%2F") and decodes characters which are not need to be encoded.
    *
    * @param $str The string which have to normalize
    * @return string The normalized string
    */
   private function normalizeEncodedCharacters($str)
   {
      $pos = null;
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';
      $chunks = preg_split('/\%([0-9a-f]{2})/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
      for ($a = count($chunks) - 2; $a > 0; $a-=2)
      {
         $pos = strpos($chars, chr(hexdec($chunks[$a])));
         if ($pos !== false)
         {
            $chunks[$a] = $chars[$pos];
         }
         else
         {
            $chunks[$a] = '%' . strtoupper($chunks[$a]);
         }
      }
      return implode('', $chunks);
   }

   /**
    * removing dot-segments ".." and "." to make the path of the url more beautiful and shorter
    *
    * @param $path The path which we have to resolve
    * @return string The resolved path
    */
   private function resolvePath($path)
   {
      return preg_replace('/\/?[^\/]*\/\.\.\/?/', '/', str_replace('/./', '/', $path));
   }

   /**
    * detect the document_root on a more reliable way as usage of $_SERVER['DOCUMENT_ROOT']
    *
    * @return string The absolute path of the document root
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
}

?>