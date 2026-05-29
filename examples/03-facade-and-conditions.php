<?php

/**
 * Example: Using the static facade and conditional logic in listeners.
 *
 * This demonstrates:
 * - Using EventDispatcherFacade for global access
 * - Listeners with conditional logic (only react under certain conditions)
 * - Mixing callable and class-based listeners
 */
require_once __DIR__.'/../vendor/autoload.php';

use WebFiori\Event\EventDispatcherFacade;

// --- Events ---
class PaymentReceived {
    public function __construct(
        public readonly string $customerId,
        public readonly float $amount,
        public readonly string $method
    ) {
    }
}

// --- Listeners ---

// Only triggers for large payments
class FraudCheck {
    public function handle(PaymentReceived $event): void {
        if ($event->amount > 1000) {
            echo "  [Fraud] Large payment flagged for review: \${$event->amount}\n";
        }
    }
}

// Only triggers for specific payment methods
class NotifyFinanceTeam {
    public function handle(PaymentReceived $event): void {
        if ($event->method === 'wire_transfer') {
            echo "  [Finance] Wire transfer received from {$event->customerId}: \${$event->amount}\n";
        }
    }
}

// --- Setup using facade (no need to pass dispatcher around) ---
EventDispatcherFacade::listen(PaymentReceived::class, new FraudCheck());
EventDispatcherFacade::listen(PaymentReceived::class, new NotifyFinanceTeam());

// Callable listener for logging all payments
EventDispatcherFacade::listen(PaymentReceived::class, function (PaymentReceived $event) {
    echo "  [Log] Payment: \${$event->amount} via {$event->method} from {$event->customerId}\n";
});

// --- Dispatch different scenarios ---
echo "Small card payment:\n";
EventDispatcherFacade::dispatch(new PaymentReceived('cust-1', 50.00, 'credit_card'));

echo "\nLarge wire transfer:\n";
EventDispatcherFacade::dispatch(new PaymentReceived('cust-2', 5000.00, 'wire_transfer'));

echo "\nLarge card payment:\n";
EventDispatcherFacade::dispatch(new PaymentReceived('cust-3', 2500.00, 'credit_card'));
