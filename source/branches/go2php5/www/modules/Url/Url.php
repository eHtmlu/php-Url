<?php

/**
 *     Project: tulebox
 *      Author: Helmut Wandl <www.wandls.net>
 *
 *         $Id$
 *
 * Description: Class to handle urls.
 */
class Url implements IUrl
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
    * @param mixed $fileOrUrlOrInstance (optional) NULL (default) for the current user requested Url. Or a string which represents the url itself (relative urls will interpreted as relative to current user request). Or an instance of SplFileObject or SplFileInfo which represents an existing file or directory to initialise the associated url. Or an object instance with the IUrl interface to copy that url.
    */
   public function __construct($fileOrUrlOrInstance = null)
   {
      $this->defaultPorts = array(
         'http' => '80',
         'https' => '443',
         'ftp' => '21'
      );

      $this->setUrl($fileOrUrlOrInstance);
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
   public function getUserInfo()
   {
      return $this->tokens[UrlParts::USERINFO];
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
    * @param bool $fallbackToDefault The port can be automatically. If you want to know whether it is, set this to FALSE (the return value for automatically will be NULL). Otherwise the return value will be the default value of current scheme if there is no other port defined.
    * @return string Port part of the url
    */
   public function getPort($fallbackToDefault = true)
   {
      return $this->tokens[UrlParts::PORT] === null && $this->defaultPorts[$this->tokens[UrlParts::SCHEME]] ? ($fallbackToDefault ? $this->defaultPorts[$this->tokens[UrlParts::SCHEME]] : null) : $this->tokens[UrlParts::PORT];
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
    * Gets the query part of the url as a string
    *
    * @return string Query part of the url
    */
   public function getQuery()
   {
      return $this->tokens[UrlParts::QUERY];
   }

   /**
    * Gets the query part of the url as an associative array. It's what the global $_GET variable is for the current requested url.
    * Like $_GET this method also supports multidimensional results.
    *
    * @return array An associative (and multidimensional where applicable) array of the query parameters
    */
   public function getQueryParameters()
   {
      parse_str($this->tokens[UrlParts::QUERY], $param);

      if (get_magic_quotes_gpc())
      {
         $param = $this->stripSlashesRecursive($param);
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
    * @param $urlParts A combination of bit constances from the UrlParts class to define which parts of the url will be included (default: UrlParts::ALL)
    * @return string The whole well formed and correct formatted url
    */
   public function getUrl($urlParts = UrlParts::ALL)
   {
      $userInfo = explode(':', $this->tokens[UrlParts::USERINFO]);

      $authority =
         (($urlParts & UrlParts::USERINFO) === UrlParts::USERINFO && $this->tokens[UrlParts::USERINFO] ? $userInfo[0] . (count($userInfo) == 2 && $userInfo[1] === '' ? ':' : '') . '@' : '') .
         (($urlParts & UrlParts::HOST) === UrlParts::HOST && $this->tokens[UrlParts::HOST] ? $this->tokens[UrlParts::HOST] : '') .
         (($urlParts & UrlParts::PORT) === UrlParts::PORT && $this->tokens[UrlParts::PORT] && $this->tokens[UrlParts::PORT] != $this->defaultPorts[$this->tokens[UrlParts::SCHEME]] ? ':' . $this->tokens[UrlParts::PORT] : '');

      return
         (($urlParts & UrlParts::SCHEME) === UrlParts::SCHEME && $this->tokens[UrlParts::SCHEME] ? $this->tokens[UrlParts::SCHEME] . ':' : '') .
         ($authority ? '//' . $authority : '') .
         (($urlParts & UrlParts::PATH) === UrlParts::PATH ? $this->tokens[UrlParts::PATH] : '') .
         (($urlParts & UrlParts::QUERY) === UrlParts::QUERY && $this->tokens[UrlParts::QUERY] ? '?' . $this->tokens[UrlParts::QUERY] : '') .
         (($urlParts & UrlParts::FRAGMENT) === UrlParts::FRAGMENT && $this->tokens[UrlParts::FRAGMENT] ? '#' . $this->tokens[UrlParts::FRAGMENT] : '')
         ;
   }

   /**
    * Returns the relative path based on a given origin url
    *
    * @param Url $base The origin url from which the relative path starts.
    * @return string The relative path from the given url to the current used url.
    */
   public function getRelativePath(IUrl $base)
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

         $basePathCount = count($basePath);
         $result = str_repeat('../', $basePathCount > 0 ? $basePathCount - 1 : 0) . implode('/', $targPath);
      }

      return $result;
   }

   /**
    * Sets the scheme to a given value
    *
    * @param string $scheme The new scheme value
    * @return object The current Url instance ($this)
    */
   public function setScheme($scheme)
   {
      if (!preg_match('/^[a-z][a-z0-9\+\-\.]*$/i', $scheme))
      {
         throw new Exception('Can\'t set scheme. "' . $scheme . '" is not a valid format for scheme.');
      }

      $this->tokens[UrlParts::SCHEME] = strtolower($scheme);

      return $this;
   }

   /**
    * Sets the user info to a given value
    *
    * @param string $userInfo The new user info value (empty by default)
    * @return object The current Url instance ($this)
    */
   public function setUserInfo($userInfo = '')
   {
      if (strpos($userInfo, '@') !== false)
      {
         throw new Exception('Can\'t set userInfo. The character "@" is not allowed.');
      }

      $this->tokens[UrlParts::USERINFO] = $userInfo;

      return $this;
   }

   /**
    * Sets the host to a given value
    *
    * @param string $host The new host value
    * @return object The current Url instance ($this)
    */
   public function setHost($host)
   {
      $host = strtolower($host);

      // is ipv6?
      if ($host[0] == '[' && $host[strlen($host)-1] == ']' && ($ipv6 = @inet_pton(substr($host, 1, -1))) !== false)
      {
         $host = '[' . inet_ntop($ipv6) . ']';
      }
      // is ipv4
      elseif (count($ipv4 = explode('.', $host)) == 4 && ctype_digit($ipv4[0]) && $ipv4[0] < 256 && ctype_digit($ipv4[1]) && $ipv4[1] < 256 && ctype_digit($ipv4[2]) && $ipv4[2] < 256 && ctype_digit($ipv4[3]) && $ipv4[3] < 256)
      {
         $host = implode('.', $ipv4);
      }
      // is not domain name?
      elseif (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)*$/', $host))
      {
         throw new Exception('Can\'t set host. "' . $host . '" is not a valid format for host.');
      }

      $this->tokens[UrlParts::HOST] = $host;

      return $this;
   }

   /**
    * Sets the port to a given value
    *
    * @param string $port The new port value (automatically port by default)
    * @return object The current Url instance ($this)
    */
   public function setPort($port = null)
   {
      if ($port !== null && !ctype_digit($port))
      {
         throw new Exception('Can\'t set port. "' . $port . '" is not a valid port value.');
      }

      $this->tokens[UrlParts::PORT] = $port;

      return $this;
   }

   /**
    * Sets the path to a given value
    *
    * @param string $fragment The new fragment value (root path '/' by default)
    * @return object The current Url instance ($this)
    */
   public function setPath($path = '/')
   {
      if (strpos($path, '?') !== false || strpos($path, '#') !== false)
      {
         throw new Exception('Can\'t set path. The characters "?" and "#" are not allowed.');
      }

      $result = array();
      foreach (explode('/', $this->resolvePath($path[0] != '/' ? preg_replace('/\/[^\/]*$/', '/', $this->getPath()).$path : $path)) as $chunk)
      {
         $result[] = rawurlencode(urldecode($chunk));
      }

      $this->tokens[UrlParts::PATH] = implode('/', $result);

      return $this;
   }

   /**
    * Sets the query to a given value
    *
    * @param string $query The new query value as a string (empty by default)
    * @return object The current Url instance ($this)
    */
   public function setQuery($query = '')
   {
      if (strpos($query, '#') !== false)
      {
         throw new Exception('Can\'t set query. The character "#" is not allowed.');
      }

      $result = array();
      foreach (explode('&', $query) as $chunk)
      {
         if (!$chunk) continue;
         $chunk = explode('=', $chunk);
         $result[] = rawurlencode(urldecode($chunk[0])) . '=' . rawurlencode(urldecode($chunk[1]));
      }

      $this->tokens[UrlParts::QUERY] = implode('&', $result);

      return $this;
   }

   /**
    * Adds, changes and removes query parameters by a given associative array.
    *
    * @param array $parameters An associative (and multidimensional) array with the names and values you will add, change or remove. To remove a parameter set the value to NULL.
    * @return object The current Url instance ($this)
    */
   public function setQueryParameters($parameters)
   {
      $this->setQuery(http_build_query($this->mergeRecursive($this->getQueryParameters(), $parameters)));

      return $this;
   }

   /**
    * Sets the fragment to a given value
    *
    * @param string $fragment The new fragment value (empty by default)
    * @return object The current Url instance ($this)
    */
   public function setFragment($fragment = '')
   {
      $fragment = rawurlencode(urldecode($fragment));

      $this->tokens[UrlParts::FRAGMENT] = $fragment;

      return $this;
   }

   /**
    * Set the url based on the current user request, an other Url instance, a given file or directory path or a given url string
    *
    * @param mixed $fileOrUrlOrInstance Like the first parameter of __construct
    * @return object The current Url instance ($this)
    */
   public function setUrl($fileOrUrlOrInstance = null)
   {
      // if undefined -> get the current requested url
      if ($fileOrUrlOrInstance === null)
      {
         $this->setUrlByCurrentRequest();
      }
      // if is an instance of Url -> copy the url from the given instance
      elseif ($fileOrUrlOrInstance instanceof Url)
      {
         $this->setUrlByInstance($fileOrUrlOrInstance);
      }
      // if is an instance of SplFileObject -> get the url which points to the given file
      elseif ($fileOrUrlOrInstance instanceof SplFileObject)
      {
         $this->setUrlByFilePath((STRING) $fileOrUrlOrInstance);
      }
      // if is an instance of SplFileInfo -> get the url which points to the given file or directory
      elseif ($fileOrUrlOrInstance instanceof SplFileInfo)
      {
         if ($filePath = $fileOrUrlOrInstance->getRealPath())
         {
            $this->setUrlByFilePath($filePath);
         }
         else
         {
            throw new Exception('The given instance of SplFileInfo do not represents an existing file or directory.');
         }
      }
      // if is a string anyway -> evaluate as an url string
      elseif (is_string($fileOrUrlOrInstance))
      {
         $this->setUrlByUrlString($fileOrUrlOrInstance);
      }
      else
      {
         throw new Exception('Can not set url because the given parameter is not valid.');
      }

      return $this;
   }

   /**
    * Set the url based on the current user request
    *
    * @return object The current Url instance ($this)
    */
   public function setUrlByCurrentRequest()
   {
      $this->setScheme(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')) . ($_SERVER["HTTPS"] && strtolower($_SERVER["HTTPS"]) != 'off' ? 's' : ''));
      $this->setUserInfo(rawurlencode($_SERVER['PHP_AUTH_USER']) . ($_SERVER['PHP_AUTH_PW'] ? ':' . rawurlencode($_SERVER['PHP_AUTH_PW']) : ''));
      $this->setHost($_SERVER['SERVER_NAME']);
      $this->setPort($this->defaultPorts[$this->getScheme()] == $_SERVER['SERVER_PORT'] ? null : $_SERVER['SERVER_PORT']);
      $this->setPath(strpos($_SERVER['REQUEST_URI'], '?') !== false ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']);
      $this->setQuery($_SERVER['QUERY_STRING']);
      $this->setFragment('');

      return $this;
   }

   /**
    * Set the url based on an other Url instance
    *
    * @param object $instance An object instance with IUrl interface
    * @return object The current Url instance ($this)
    */
   public function setUrlByInstance(IUrl $instance)
   {
      $this->setScheme($instance->getScheme());
      $this->setUserInfo($instance->getUserInfo());
      $this->setHost($instance->getHost());
      $this->setPort($instance->getPort(false));
      $this->setPath($instance->getPath());
      $this->setQuery($instance->getQuery());
      $this->setFragment($instance->getFragment());

      return $this;
   }

   /**
    * Set the url based on a given file or directory path
    *
    * @param string $fileOrDirname The file or directory path which have to initialize
    * @return object The current Url instance ($this)
    */
   public function setUrlByFilePath($fileOrDirname)
   {
      $fileOrDirname = realpath($fileOrDirname);
      $documentRoot = $this->getDocumentRoot();

      if (strpos($fileOrDirname, $documentRoot) === 0)
      {
         $fileOrDirname = substr($fileOrDirname, strlen($documentRoot));
      }

      $this->setUrlByCurrentRequest();
      $this->setPath(str_replace(DIRECTORY_SEPARATOR, '/', $fileOrDirname) . ( is_dir($documentRoot . $fileOrDirname) ? '/' : '' ));
      $this->setQuery('');
      $this->setFragment('');

      return $this;
   }

   /**
    * Set the url based on a given url string
    *
    * @param string $urlString The relative or absolute url which have to initialize. A relative url is interpreted as relative to the current user request.
    * @return object The current Url instance ($this)
    */
   public function setUrlByUrlString($urlString)
   {
      if (!preg_match('/^(?:([a-zA-Z][a-zA-Z0-9\+\-\.]*)\:)?(?:\/\/(?:([^\:\@]+)(\:[^\@]*)?\@)?(\[[0-9a-fA-F\:]+\]|[^\/\?\#\:]+)(?:\:([0-9]+))?)?([^\?\#]*)?(?:\?([^\#]*))?(?:\#(.*))?$/', $urlString, $match))
      {
         throw new Exception('Can\'t initialize url "' . $urlString . '".');
      }

      $this->setUrlByCurrentRequest();

      // reset scheme if is defined
      if ($match[1])
      {
         $this->setScheme($match[1]);
      }

      // reset authority if the authority part or the scheme part is defined
      if ($match[4] || $match[1])
      {
         $this->setUserInfo($match[2] . $match[3]);
         $this->setHost($match[4]);
         $this->setPort($match[5] ? $match[5] : null);
      }

      // reset path if the path part or other parts which the path part is based on are defined
      if ($match[6] || $match[4] || $match[1])
      {
         $this->setPath($match[6] ? $match[6] : '/');
      }

      // reset query if the query part or other parts which the query part is based on are defined
      if ($match[7] || $match[6] || $match[4] || $match[1])
      {
         $this->setQuery($match[7] ? $match[7] : '');
      }

      // reset fragment
      $this->setFragment($match[8]);

      return $this;
   }

   /**
    * removing dot-segments ".." and "." to make the path of the url more beautiful and shorter
    *
    * @param $path The path which we have to resolve
    * @return string The resolved path
    */
   private function resolvePath($path)
   {
      $path = explode('/', $path);

      for ($a = 0; $a < count($path); $a++)
      {
         switch($path[$a])
         {
            case '..':
               if ($a > 1)
               {
                  array_splice($path, $a - 1, 1);
                  $a--;
               }
            case '.':
               if ($a + 1 == count($path))
               {
                  $path[$a] = '';
               }
               else
               {
                  array_splice($path, $a, 1);
                  $a--;
               }
         }
      }

      return implode('/', $path);
   }

   /**
    * Strips slashes from the specified string (or array of strings).
    *
    * @param mixed $value The value to be processed.
    */
   private function stripSlashesRecursive(&$value)
   {
      $value = is_array($value)
         ? array_map(array($this, 'stripSlashesRecursive'), $value)
         : stripslashes($value);

      return $value;
   }

   /**
    * Merge two arrays recursive and return one new array (used to add and change query parameters).
    * This method is a little different from the php built in array_merge_recursive function. This
    * method overwrites a string of $array1 with a string of $array2 instead of combines them to an
    * array. And if anywhere in $array2 is the value NULL, the entry with this value will be removed.
    *
    * @param mixed $array1 The start array to merge
    * @param mixed $array2 The second array to merge (values of this array will overwrite values of $array1)
    * @return mixed A new array within the values of $array1 and $array2
    */
   private function mergeRecursive($array1, $array2)
   {
      if (is_array($array2))
      {
         $result = is_array($array1) ? $array1 : array();

         foreach ($array2 as $k => $v)
         {
            if ($v === null)
            {
               if (isset($result[$k]))
               {
                  unset($result[$k]);
               }
            }
            else
            {
               $v = isset($array1[$k]) ? $this->mergeRecursive($array1[$k], $array2[$k]) : $array2[$k];

               if (is_int($k))
               {
                  $result[] = $v;
               }
               else
               {
                  $result[$k] = $v;
               }
            }
         }
      }
      else
      {
         $result = $array2;
      }

      return $result;
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