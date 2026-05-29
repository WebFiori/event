<?php

/**
 * Example: Multiple listeners reacting to the same event.
 *
 * This demonstrates how a single event can trigger multiple independent
 * side-effects without the emitting code knowing about any of them.
 */
require_once __DIR__.'/../vendor/autoload.php';

use WebFiori\Event\EventDispatcher;

// --- Event ---
class OrderPlaced {
    public function __construct(
        public readonly int $orderId,
        public readonly string $customerEmail,
        public readonly float $total,
        public readonly array $items
    ) {
    }
}

// --- Listeners ---
// Each listener handles one concern. They don't know about each other.

class SendOrderConfirmation {
    public function handle(OrderPlaced $event): void {
        echo "  [Email] Confirmation sent to {$event->customerEmail}\n";
    }
}

class UpdateInventory {
    public function handle(OrderPlaced $event): void {
        foreach ($event->items as $item) {
            echo "  [Inventory] Decremented stock for: {$item}\n";
        }
    }
}

class NotifyWarehouse {
    public function handle(OrderPlaced $event): void {
        echo "  [Warehouse] Packing slip generated for order #{$event->orderId}\n";
    }
}

class RecordAnalytics {
    public function handle(OrderPlaced $event): void {
        echo "  [Analytics] Revenue tracked: \${$event->total}\n";
    }
}

// --- Setup ---
$dispatcher = new EventDispatcher();

// Register all listeners for the same event
$dispatcher->listen(OrderPlaced::class, new SendOrderConfirmation());
$dispatcher->listen(OrderPlaced::class, new UpdateInventory());
$dispatcher->listen(OrderPlaced::class, new NotifyWarehouse());
$dispatcher->listen(OrderPlaced::class, new RecordAnalytics());

// --- Dispatch ---
// The order service only does this one line.
// It doesn't know about emails, inventory, warehouse, or analytics.
echo "Order placed:\n";
$dispatcher->dispatch(new OrderPlaced(
    orderId: 1001,
    customerEmail: 'customer@example.com',
    total: 149.99,
    items: ['Widget A', 'Gadget B']
));
