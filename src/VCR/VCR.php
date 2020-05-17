<?php

namespace VCR;

/**
 * Singleton interface to a Videorecorder.
 *
 * @method static Configuration configure()
 * @method static void insertCassette(string $cassetteName)
 * @method static void turnOn()
 * @method static void turnOff()
 * @method static void eject()
 */
class VCR
{
    /**
     * Always allow to do HTTP requests and add to the cassette. Default mode.
     */
    public const MODE_NEW_EPISODES = 'new_episodes';

    /**
     * Only allow new HTTP requests when the cassette is newly created.
     */
    public const MODE_ONCE = 'once';

    /**
     * Treat the fixtures as read only and never allow new HTTP requests.
     */
    public const MODE_NONE = 'none';

    /**
     * @param string $method
     * @param mixed[] $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        $instance = VCRFactory::get(Videorecorder::class);
        /** @var callable $callback */
        $callback = [$instance, $method];

        return call_user_func_array($callback, $parameters);
    }
}
