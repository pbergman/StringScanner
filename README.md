# StringScanner
This is a helper class for string tokenizer (lexical scanning operations). It`s PHP port from [StringScanner](http://docs.ruby-lang.org/en/2.1.0/StringScanner.html), which is a ruby library with the same name.

## Installation
The recommended way to install is [through composer](http://getcomposer.org).

```
{
    "require": {
        "pbergman/string-scanner": "@stable"

    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:pbergman/StringScanner.git"
        }
    ],
    "minimum-stability": "dev"
}
```

## Usage
```php
<?php
use StringScanner\StringScanner;

$s = new StringScanner('This is an example string')
$s.eos()              # -> false

$s.scan('/\w+/')      # -> "This"
$s.scan('/\w+/')      # -> nil
$s.scan('/\s+/')      # -> " "
$s.scan('/\s+/')      # -> nil
$s.scan('/\w+/')      # -> "is"
$s.eos()              # -> false

$s.scan('/\s+/')      # -> " "
$s.scan('/\w+/')      # -> "an"
$s.scan('/\s+/')      # -> " "
$s.scan('/\w+/')      # -> "example"
$s.scan('/\s+/')      # -> " "
$s.scan('/\w+/')      # -> "string"
$s.eos()              # -> true

$s.scan('/\s+/')      # -> null
$s.scan('/\w+/')      # -> null

```

for more info check [this](http://docs.ruby-lang.org/en/2.1.0/StringScanner.html) to see available methods.