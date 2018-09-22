<?php


namespace Talker\Talker;


use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Talker\Talker\Response\ResourceMp3File;

class GoogleSpeechTalker implements TalkerInterface
{
    public const LOCALE_EN_GB = 'en_gb';
    public const LOCALE_EN_AU = 'en_au';
    public const LOCALE_EN_US = 'en_us';
    public const LOCALE_RU    = 'ru';

    private const BASE_URL = 'http://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=%s&tl=%s';

    /**
     * @var LoopInterface
     */
    private $loop;

    private $client;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->client = new Client($loop);
    }

    /**
     * @param string $text
     *
     * @param string $locale
     *
     * @return PromiseInterface
     */
    public function say(string $text, string $locale): PromiseInterface
    {
        $defer = new Deferred();

        $promises = [];

        foreach (explode('\n', wordwrap($text, 100)) as $chunk) {
            $promises[] = $this->doRequest($chunk, $locale);
        }

        \React\Promise\all($promises)->then(
            function (array $files) use ($defer) {

                $resultStream = fopen('php://memory', 'rw');

                /** @var ResourceMp3File $file */
                foreach ($files as $file) {
                    stream_copy_to_stream($file->getStream(), $resultStream);
                }

                rewind($resultStream);

                $defer->resolve(new ResourceMp3File($resultStream));
            }
        );

        return $defer->promise();
    }

    private function doRequest(string $text, string $locale): PromiseInterface
    {
        $defer = new Deferred();

        $request = $this->client->request(
            'GET',
            $this->getRequestUrl(urlencode($text), $locale)
        );

        $request->on(
            'response',
            function (Response $response) use ($defer) {
                $stream = fopen('php://memory', 'rw');

                $response->on(
                    'data',
                    function ($chunk) use ($stream) {
                        fwrite($stream, $chunk);
                    }
                );

                $response->on(
                    'end',
                    function () use ($stream, $defer) {
                        rewind($stream);

                        $defer->resolve(new ResourceMp3File($stream));
                    }
                );
            }
        );

        $request->end();

        return $defer->promise();
    }

    private function getRequestUrl(string $text, string $locale)
    {
        return sprintf(self::BASE_URL, $text, $locale);
    }
}