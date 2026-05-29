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
 * Core event dispatcher.
 *
 * Manages listener registration and event dispatching. Supports both
 * callable listeners and class-based listeners implementing ListenerInterface.
 */
class EventDispatcher {
    /**
     * @var array Registered listeners indexed by event class name.
     */
    private array $listeners = [];
    /**
     * Dispatch an event to all registered listeners.
     *
     * All listeners registered for the event's class will be called
     * in the order they were registered.
     *
     * @param object $event The event object to dispatch.
     */
    public function dispatch(object $event): void {
        $class = get_class($event);

        foreach ($this->listeners[$class] ?? [] as $listener) {
            if (is_callable($listener)) {
                $listener($event);
            } else {
                $listener->handle($event);
            }
        }
    }
    /**
     * Returns the total number of registered listeners across all events.
     *
     * @return int
     */
    public function getListenerCount(): int {
        $count = 0;

        foreach ($this->listeners as $eventListeners) {
            $count += count($eventListeners);
        }

        return $count;
    }
    /**
     * Returns all listeners registered for a specific event class.
     *
     * @param string $eventClass The fully qualified event class name.
     *
     * @return array Array of listeners (callables and/or ListenerInterface instances).
     */
    public function getListeners(string $eventClass): array {
        return $this->listeners[$eventClass] ?? [];
    }

    /**
     * Register a listener for a specific event class.
     *
     * @param string $eventClass The fully qualified class name of the event.
     * @param callable|object $listener The listener to invoke when the event is dispatched.
     */
    public function listen(string $eventClass, callable|object $listener): void {
        $this->listeners[$eventClass][] = $listener;
    }
    /**
     * Remove all registered listeners.
     */
    public function reset(): void {
        $this->listeners = [];
    }
}
