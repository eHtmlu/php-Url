<?php

/**
 *     Project: tulebox
 *      Author: Helmut Wandl <www.wandls.net>
 *
 *         $Id: IUrl.php 355 2013-01-02 03:17:10Z hw $
 *
 * Description: Interface for Url.
 */
interface IUrl
{
   public function __construct($fileOrUrlOrInstance = null);

   public function getScheme();
   public function getUserInfo();
   public function getHost();
   public function getPort($fallbackToDefault = true);
   public function getPath();
   public function getQuery();
   public function getQueryParameters();
   public function getFragment();

   public function getUrl($urlParts = UrlParts::ALL);
   public function getRelativePath(IUrl $base);

   public function setScheme($scheme);
   public function setUserInfo($userInfo = '');
   public function setHost($host);
   public function setPort($port = null);
   public function setPath($path = '/');
   public function setQuery($query = '');
   public function setQueryParameters($parameters);
   public function setFragment($fragment = '');

   public function setUrl($fileOrUrlOrInstance = null);
   public function setUrlByCurrentRequest();
   public function setUrlByInstance(IUrl $instance);
   public function setUrlByFilePath($fileOrDirname);
   public function setUrlByUrlString($urlString);
}

?>