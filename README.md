# WebFiori Event

A lightweight event dispatcher library for PHP.

<p align="center">
  <a href="https://github.com/WebFiori/event/actions"><img src="https://github.com/WebFiori/event/actions/workflows/php84.yaml/badge.svg?branch=main"></a>
  <a href="https://codecov.io/gh/WebFiori/event">
    <img src="https://codecov.io/gh/WebFiori/event/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_event">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_event&metric=alert_status" />
  </a>
  <a href="https://github.com/WebFiori/event/releases">
      <img src="https://img.shields.io/github/release/WebFiori/event.svg?label=latest" />
  </a>
  <a href="https://packagist.org/packages/webfiori/event">
      <img src="https://img.shields.io/packagist/dt/webfiori/event?color=light-green">
  </a>
</p>

## Supported PHP Versions

This library requires **PHP 8.1 or higher**.

|                                                                                        Build Status                                                                                        |
|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/event/actions/workflows/php81.yaml"><img src="https://github.com/WebFiori/event/actions/workflows/php81.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/event/actions/workflows/php82.yaml"><img src="https://github.com/WebFiori/event/actions/workflows/php82.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/event/actions/workflows/php83.yaml"><img src="https://github.com/WebFiori/event/actions/workflows/php83.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/event/actions/workflows/php84.yaml"><img src="https://github.com/WebFiori/event/actions/workflows/php84.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/event/actions/workflows/php85.yaml"><img src="https://github.com/WebFiori/event/actions/workflows/php85.yaml/badge.svg?branch=main"></a>  |

## Features

- **Simple API** — `listen()` and `dispatch()`, nothing else to learn
- **Callable listeners** — use closures for quick inline reactions
- **Class-based listeners** — implement `ListenerInterface` for reusable, testable handlers
- **Static facade** (`EventDispatcherFacade`) for quick usage without DI
- **Events are plain classes** — no interface or base class required
- **Zero dependencies** — requires only PHP 8.1+

## Installation

```bash
composer require webfiori/event
```

## Usage

### Define Events

Events are plain classes that hold data about what happened:

```php
class UserRegistered {
    public function __construct(
        public readonly string $email,
        public readonly string $name
    ) {}
}

class OrderPlaced {
    public function __construct(
        public readonly int $orderId,
        public readonly float $total
    ) {}
}
```

### Register Listeners

```php
use WebFiori\Event\EventDispatcher;

$dispatcher = new EventDispatcher();

// Callable listener (inline)
$dispatcher->listen(UserRegistered::class, function (UserRegistered $event) {
    echo "Welcome {$event->name}!\n";
});

// Class-based listener (duck typing — full type hinting on handle())
class SendWelcomeEmail {
    public function handle(UserRegistered $event): void {
        mail($event->email, 'Welcome!', "Hi {$event->name}");
    }
}

$dispatcher->listen(UserRegistered::class, new SendWelcomeEmail());
```

### Dispatch Events

```php
$dispatcher->dispatch(new UserRegistered('john@example.com', 'John'));
// All listeners registered for UserRegistered are called
```

### Static Facade

```php
use WebFiori\Event\EventDispatcherFacade;

EventDispatcherFacade::listen(OrderPlaced::class, function (OrderPlaced $event) {
    echo "Order #{$event->orderId} placed!\n";
});

EventDispatcherFacade::dispatch(new OrderPlaced(42, 99.99));
```

## API

### `EventDispatcher`

| Method | Description |
|--------|-------------|
| `listen(string $eventClass, callable\|ListenerInterface $listener)` | Register a listener for an event |
| `dispatch(object $event)` | Dispatch event to all registered listeners |
| `getListeners(string $eventClass): array` | Get listeners for an event |
| `getListenerCount(): int` | Total registered listeners |
| `reset(): void` | Remove all listeners |

### `EventDispatcherFacade`

Static wrapper. Same methods as `EventDispatcher` plus `getInstance()`, `setInstance()`, `reset()`.

### Listener Classes (Duck Typing)

Any class with a public `handle()` method can be a listener. Type-hint the parameter with the specific event class for full IDE support:

```php
class MyListener {
    public function handle(SomeEvent $event): void {
        // Full autocomplete on $event
    }
}
```

## Design Decisions

- **Events are plain classes** — no marker interface needed. Any object can be an event.
- **Listeners use duck typing** — any class with a `handle()` method works. No interface to implement, full type hinting on the event parameter.
- **Listeners are registered explicitly** — `listen(EventClass, listener)`. No magic auto-discovery in the library (the framework handles that).
- **Listeners execute in registration order** — predictable, no priority system.
- **No event propagation stopping** — all listeners always run. Keep it simple.

## License

MIT
