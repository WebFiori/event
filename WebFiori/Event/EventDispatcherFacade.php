<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Event;

/**
 * A static facade for the EventDispatcher.
 *
 * Provides a convenient static API that delegates to a default EventDispatcher instance.
 * For dependency injection or multiple dispatchers, use EventDispatcher directly.
 */
class EventDispatcherFacade {
    /**
     * @var EventDispatcher|null The default dispatcher instance.
     */
    private static ?EventDispatcher $inst = null;
    /**
     * @see EventDispatcher::dispatch()
     */
    public static function dispatch(object $event): void {
        self::getInstance()->dispatch($event);
    }
    /**
     * Returns the default EventDispatcher instance, creating it lazily if needed.
     *
     * @return EventDispatcher
     */
    public static function getInstance(): EventDispatcher {
        if (self::$inst === null) {
            self::$inst = new EventDispatcher();
        }

        return self::$inst;
    }
    /**
     * @see EventDispatcher::getListenerCount()
     */
    public static function getListenerCount(): int {
        return self::getInstance()->getListenerCount();
    }
    /**
     * @see EventDispatcher::getListeners()
     */
    public static function getListeners(string $eventClass): array {
        return self::getInstance()->getListeners($eventClass);
    }
    /**
     * @see EventDispatcher::listen()
     */
    public static function listen(string $eventClass, callable|object $listener): void {
        self::getInstance()->listen($eventClass, $listener);
    }
    /**
     * Destroys the default EventDispatcher instance.
     */
    public static function reset(): void {
        self::$inst = null;
    }
    /**
     * Replaces the default EventDispatcher instance.
     *
     * @param EventDispatcher $dispatcher The dispatcher to use as default.
     */
    public static function setInstance(EventDispatcher $dispatcher): void {
        self::$inst = $dispatcher;
    }
}
