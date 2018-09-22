<?php


namespace Talker\Talker\Response;


class ResourceMp3File implements Mp3File
{
    private $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    public function getContent(): string
    {
        $data = stream_get_contents($this->stream);

        rewind($this->stream);

        return $data;
    }

    /**
     * @return mixed
     */
    public function getStream()
    {
        return $this->stream;
    }
}