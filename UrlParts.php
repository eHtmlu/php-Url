<?php

class UrlParts
{
   const SCHEME = 1;
   const USERINFO = 2;
   const HOST = 4;
   const PORT = 8;
   const PATH = 16;
   const QUERY = 32;
   const FRAGMENT = 64;
   const ALL = 127;
}

?>