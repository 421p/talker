<?php


namespace Talker\Talker;


use React\Promise\PromiseInterface;

interface TalkerInterface
{
    /**
     * @param string $text
     *
     * @param string $locale
     *
     * @return PromiseInterface
     */
    public function say(string $text, string $locale): PromiseInterface;
}