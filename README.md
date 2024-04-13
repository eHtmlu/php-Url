# Why this Url PHP class exists
Dealing with URL strings in PHP is complicated and, depending on the requirements, several different checks or variables are needed to put together the required URL. In addition, care must always be taken to ensure that the URL parameters are encoded correctly and so on.

The Url class solves all of these problems and makes dealing with URLs extremely easy.

# Examples

## 1. Get URL of current request
Getting the URL of the current request is the easiest thing ever. Assumed your server is accessed with the URL `https://www.example.com/current/path`
```
echo new Url();
// -> https://www.example.com/current/path
```

## 2. Add query parameters
Assumed your server is accessed with the URL `https://www.example.com/current/path?name1=value%201`
```
$url = new Url();

$url->setQueryParameters([
  'name2' => 'value 2',
  'name3' => 'value 3',
]);

echo $url;
// -> https://www.example.com/current/path?name1=value%201&name2=value%202&name3=value%203
```

## 3. Get URL to a javascript file with version parameter
Assumed your server is accessed with the URL `https://www.example.com/`
```
$filepath = __DIR__ . '/path/to/javascript/file.js';

$url = new Url(new SplFileInfo($filepath));

$url->setQueryParameters([
  'version' => filemtime($filepath),
]);

echo $url; // -> https://www.example.com/path/to/javascript/file.js?version=1713032631
```

# Installation
Just add the files to your project and include them in this order:
```
require_once 'UrlParts.php';
require_once 'IUrl.php';
require_once 'Url.php';
```


# Usage

## Create a new Url instance
There are various ways to create an Url instance
```
// Get Url instance of current requested URL
$url = new Url();

// Get Url instance from other Url instance
$url = new Url($url);

// Get Url instance from SplFileObject instance - the document root is automatically taken into account
$url = new Url(new SplFileObject('path/to/file.txt'));

// Get Url instance from SplFileInfo instance - the document root is automatically taken into account
$url = new Url(new SplFileInfo('path/to/file.txt'));

// Get Url instance from URL string
$url = new Url('https://www.example.com/any/path');

// Get Url instance from absolute URL strings - assume "https://www.example.com/any/path" is requested
$url = new Url('//www.example.com/any/path');   // -> https://www.example.com/any/path
$url = new Url('/any/path');                    // -> https://www.example.com/any/path

// Get Url instance from relative URL string - assume "https://www.example.com/any/path" is requested
$url = new Url('other/path');                   // -> https://www.example.com/any/other/path
$url = new Url('../other/path');                // -> https://www.example.com/other/path
$url = new Url('../other/../path');             // -> https://www.example.com/path
```

## Reading methods

### Get only parts of the URL
```
$url = new Url('https://www.example.com/any/path?name1=value%201&name2=value%202#theFragment');

$url->getScheme();
// -> "https"

$url->getUserInfo();
// -> ""
// for "https://username:password@www.example.com/" the result of getUserInfo() would be "username:password"

$url->getHost();
// -> "www.example.com"

$url->getPort($fallbackToDefault = true);
// -> "443"

$url->getPath();
// -> "/any/path"

$url->getQuery();
// -> "name1=value%201&name2=value%202"

$url->getQueryParameters();
// -> ['name1' => 'value 1', 'name2' => 'value 2']

$url->getFragment();
// -> "theFragment"
```

### Get the whole URL as a string
```
$url = new Url('https://www.example.com/any/path?name1=value%201&name2=value%202#theFragment');

$url->getUrl();
// -> "https://www.example.com/any/path?name1=value%201&name2=value%202#theFragment"

// If the instance is used like a string, it will be automatically converted
echo '<a href="' . $url . '">link</a>';
echo $url;
```

### Get the relative path based on a different Url
```
$url = new Url('https://www.example.com/any/path?name1=value%201&name2=value%202#theFragment');

$baseUrl = new Url('https://www.example.com/other/path');

$url->getRelativePath($baseUrl);
// -> "../any/path"
```

## Writing methods

### Change only parts of the URL
```
$url = new Url('https://www.example.com/any/path?name1=value%201&name2=value%202#theFragment');

$url->setScheme('http');
// -> "http://www.example.com/any/path?name1=value%201&name2=value%202#theFragment"

$url->setUserInfo('user:pass');
// -> "http://user:pass@www.example.com/any/path?name1=value%201&name2=value%202#theFragment

$url->setHost('example.com');
// -> "http://user:pass@example.com/any/path?name1=value%201&name2=value%202#theFragment

$url->setPort('8080');
// -> "http://user:pass@example.com:8080/any/path?name1=value%201&name2=value%202#theFragment

$url->setPath('/other/path');
// -> "http://user:pass@example.com:8080/other/path?name1=value%201&name2=value%202#theFragment

$url->appendPath('/deeper');
// -> "http://user:pass@example.com:8080/other/path/deeper?name1=value%201&name2=value%202#theFragment

$url->setQuery('');
// -> "http://user:pass@example.com:8080/other/path/deeper#theFragment

$url->setQueryParameters(['name1' => 'value 1', 'name2' => 'value 2']);
// -> "http://user:pass@example.com:8080/other/path/deeper?name1=value%201&name2=value%202#theFragment

$url->setFragment('otherFragment');
// -> "http://user:pass@example.com:8080/other/path/deeper?name1=value%201&name2=value%202#otherFragment
```


### Reset the whole URL
The `setUrl()` method works exactly like [creating a new instance](https://github.com/eHtmlu/php-Url/new/master?filename=README.md#create-an-instance)
It accepts omitted parameters or a string or an instance of `Url`, `SplFileInfo` or `SplFileObject`
```
$url->setUrl(); // Resets it to the URL of the current request

$url->setUrl('https://www.example.com/');

$url->setUrl(new Url('https://www.example.com/'));

$url->setUrl(new SplFileInfo('path/to/file.txt'));

$url->setUrl(new SplFileObject('path/to/file.txt'));
```

There are some additional, more specific methods to reset the whole URL
```
$url->setUrlByCurrentRequest();
// Same as $url->setUrl();

$url->setUrlByInstance(new Url('https://www.example.com/'));
// Same as $url->setUrl(new Url('https://www.example.com/'));

$url->setUrlByFilePath('path/to/file.txt');
// An alternative to $url->setUrl(new SplFileInfo('path/to/file.txt'))
// or $url->setUrl(new SplFileObject('path/to/file.txt'))

$url->setUrlByUrlString('https://www.example.com/');
// Same as $url->setUrl('https://www.example.com/');
```
