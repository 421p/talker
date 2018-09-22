# Talker
Text to speech using Google TTS

## Installing

```
composer require 421p/talker
```

## Usage

```php
use React\EventLoop\Factory;
use Talker\Talker\GoogleSpeechTalker;
use Talker\Talker\Response\Mp3File;

require __DIR__.'/vendor/autoload.php';

$loop = Factory::create();

$talker = new GoogleSpeechTalker($loop);

[$hours, $minutes] = explode(':', date('H:i'));

$time = sprintf(
    'Current time is %d hours %d minutes', $hours, $minutes
);

$talker->say($time, GoogleSpeechTalker::LOCALE_EN_US)->then(function (Mp3File $file) {
    file_put_contents(__DIR__.'/test.mp3', $file->getContent());
});

$loop->run();
```
