<?php

/**
 * Example: Decoupling application layers with events.
 *
 * This demonstrates a realistic scenario where:
 * - A service performs its core logic
 * - Side-effects are handled by listeners
 * - Adding new behavior requires zero changes to the service
 */
require_once __DIR__.'/../vendor/autoload.php';

use WebFiori\Event\EventDispatcher;

// --- Events ---
class UserCreated {
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $role
    ) {
    }
}

class UserDeleted {
    public function __construct(
        public readonly int $userId,
        public readonly string $reason
    ) {
    }
}

// --- Service (core logic only, no side-effects) ---
class UserService {
    private EventDispatcher $events;
    private array $users = [];
    private int $nextId = 1;

    public function __construct(EventDispatcher $events) {
        $this->events = $events;
    }

    public function createUser(string $email, string $role = 'user'): int {
        $id = $this->nextId++;
        $this->users[$id] = ['email' => $email, 'role' => $role];

        // Service dispatches event — doesn't know who's listening
        $this->events->dispatch(new UserCreated($id, $email, $role));

        return $id;
    }

    public function deleteUser(int $id, string $reason = 'requested'): void {
        unset($this->users[$id]);
        $this->events->dispatch(new UserDeleted($id, $reason));
    }
}

// --- Listeners (each handles one concern) ---
class SendWelcomeEmail {
    public function handle(UserCreated $event): void {
        echo "  [Email] Welcome email sent to {$event->email}\n";
    }
}

class AssignDefaultPermissions {
    public function handle(UserCreated $event): void {
        echo "  [Permissions] Default '{$event->role}' permissions assigned to user #{$event->userId}\n";
    }
}

class NotifyAdminOnNewUser {
    public function handle(UserCreated $event): void {
        if ($event->role === 'admin') {
            echo "  [Admin] Alert: New admin user created (#{$event->userId})\n";
        }
    }
}

class CleanupUserData {
    public function handle(UserDeleted $event): void {
        echo "  [Cleanup] Removing data for user #{$event->userId} (reason: {$event->reason})\n";
    }
}

class AuditLog {
    public function handle(UserDeleted $event): void {
        echo "  [Audit] User #{$event->userId} deleted: {$event->reason}\n";
    }
}

// --- Wiring ---
$dispatcher = new EventDispatcher();

$dispatcher->listen(UserCreated::class, new SendWelcomeEmail());
$dispatcher->listen(UserCreated::class, new AssignDefaultPermissions());
$dispatcher->listen(UserCreated::class, new NotifyAdminOnNewUser());
$dispatcher->listen(UserDeleted::class, new CleanupUserData());
$dispatcher->listen(UserDeleted::class, new AuditLog());

// --- Usage ---
$service = new UserService($dispatcher);

echo "Creating regular user:\n";
$id1 = $service->createUser('user@example.com');

echo "\nCreating admin user:\n";
$id2 = $service->createUser('admin@example.com', 'admin');

echo "\nDeleting user:\n";
$service->deleteUser($id1, 'account closed');
