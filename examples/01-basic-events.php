<?php

/**
 * Example: Basic event dispatching with callable and class-based listeners.
 *
 * This demonstrates:
 * 1. Defining events as plain classes (no interface needed)
 * 2. Registering callable listeners for quick inline reactions
 * 3. Registering class-based listeners for reusable, testable handlers
 * 4. Dispatching events to all registered listeners
 */
require_once __DIR__.'/../vendor/autoload.php';

use WebFiori\Event\EventDispatcher;

// --- Step 1: Define events ---
// Events are plain classes. They hold data about what happened.
// No interface or base class required.

class UserRegistered {
    public function __construct(
        public readonly string $email,
        public readonly string $name
    ) {
    }
}

class OrderPlaced {
    public function __construct(
        public readonly int $orderId,
        public readonly float $total
    ) {
    }
}

// --- Step 2: Define a class-based listener ---
// Implements ListenerInterface. The type hint on handle() determines
// which event this listener responds to.

class SendWelcomeEmail {
    public function handle(UserRegistered $event): void {
        echo "  → Sending welcome email to {$event->email}\n";
    }
}

class NotifyAdmin {
    public function handle(OrderPlaced $event): void {
        echo "  → Admin notified: Order #{$event->orderId} for \${$event->total}\n";
    }
}

// --- Step 3: Create dispatcher and register listeners ---
$dispatcher = new EventDispatcher();

// Class-based: register() infers the event from the handle() type hint
$dispatcher->listen(UserRegistered::class, new SendWelcomeEmail());
$dispatcher->listen(OrderPlaced::class, new NotifyAdmin());

// Callable: listen() requires explicit event class name
$dispatcher->listen(UserRegistered::class, function (UserRegistered $event) {
    echo "  → Logging: {$event->name} ({$event->email}) registered\n";
});

$dispatcher->listen(OrderPlaced::class, function (OrderPlaced $event) {
    echo "  → Clearing cache for order #{$event->orderId}\n";
});

// --- Step 4: Dispatch events ---
echo "Dispatching UserRegistered:\n";
$dispatcher->dispatch(new UserRegistered('john@example.com', 'John'));

echo "\nDispatching OrderPlaced:\n";
$dispatcher->dispatch(new OrderPlaced(42, 199.99));
