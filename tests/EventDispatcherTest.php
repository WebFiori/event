<?php

namespace WebFiori\Event\Tests;

use PHPUnit\Framework\TestCase;
use WebFiori\Event\EventDispatcher;

// Test events
class UserRegistered {
    public function __construct(public readonly string $email) {
    }
}

class OrderPlaced {
    public function __construct(public readonly int $orderId, public readonly float $total) {
    }
}

// Test listeners
class SendWelcomeEmail {
    public static string $lastEmail = '';

    public function handle(UserRegistered $event): void {
        self::$lastEmail = $event->email;
    }
}

class LogOrder {
    public static int $lastOrderId = 0;

    public function handle(OrderPlaced $event): void {
        self::$lastOrderId = $event->orderId;
    }
}

class EventDispatcherTest extends TestCase {
    private EventDispatcher $dispatcher;

    protected function setUp(): void {
        $this->dispatcher = new EventDispatcher();
        SendWelcomeEmail::$lastEmail = '';
        LogOrder::$lastOrderId = 0;
    }
    /**
     * @test
     */
    public function testDispatchCallsCallableListener() {
        $called = false;
        $this->dispatcher->listen(UserRegistered::class, function (UserRegistered $e) use (&$called) {
            $called = true;
        });

        $this->dispatcher->dispatch(new UserRegistered('test@example.com'));
        $this->assertTrue($called);
    }
    /**
     * @test
     */
    public function testDispatchCallsClassBasedListener() {
        $listener = new SendWelcomeEmail();
        $this->dispatcher->listen(UserRegistered::class, $listener);

        $this->dispatcher->dispatch(new UserRegistered('user@example.com'));
        $this->assertEquals('user@example.com', SendWelcomeEmail::$lastEmail);
    }
    /**
     * @test
     */
    public function testDispatchCallsMultipleListeners() {
        $count = 0;
        $this->dispatcher->listen(UserRegistered::class, function () use (&$count) {
            $count++;
        });
        $this->dispatcher->listen(UserRegistered::class, function () use (&$count) {
            $count++;
        });

        $this->dispatcher->dispatch(new UserRegistered('a@b.com'));
        $this->assertEquals(2, $count);
    }
    /**
     * @test
     */
    public function testDispatchWithNoListenersDoesNothing() {
        // Should not throw
        $this->dispatcher->dispatch(new UserRegistered('nobody@listens.com'));
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testListenersIsolatedByEventClass() {
        $this->dispatcher->listen(UserRegistered::class, function () {
            SendWelcomeEmail::$lastEmail = 'called';
        });

        $this->dispatcher->dispatch(new OrderPlaced(1, 99.99));
        $this->assertEquals('', SendWelcomeEmail::$lastEmail);
    }
    /**
     * @test
     */
    public function testListenWithClassBasedListener() {
        $listener = new SendWelcomeEmail();
        $this->dispatcher->listen(UserRegistered::class, $listener);

        $this->dispatcher->dispatch(new UserRegistered('class@example.com'));
        $this->assertEquals('class@example.com', SendWelcomeEmail::$lastEmail);
    }
    /**
     * @test
     */
    public function testListenMultipleListenersForDifferentEvents() {
        $this->dispatcher->listen(UserRegistered::class, new SendWelcomeEmail());
        $this->dispatcher->listen(OrderPlaced::class, new LogOrder());

        $this->dispatcher->dispatch(new UserRegistered('multi@test.com'));
        $this->dispatcher->dispatch(new OrderPlaced(42, 100.0));

        $this->assertEquals('multi@test.com', SendWelcomeEmail::$lastEmail);
        $this->assertEquals(42, LogOrder::$lastOrderId);
    }
    /**
     * @test
     */
    public function testGetListeners() {
        $this->dispatcher->listen(UserRegistered::class, function () {
        });
        $this->dispatcher->listen(UserRegistered::class, function () {
        });

        $this->assertCount(2, $this->dispatcher->getListeners(UserRegistered::class));
        $this->assertCount(0, $this->dispatcher->getListeners(OrderPlaced::class));
    }
    /**
     * @test
     */
    public function testGetListenerCount() {
        $this->assertEquals(0, $this->dispatcher->getListenerCount());
        $this->dispatcher->listen(UserRegistered::class, function () {
        });
        $this->dispatcher->listen(OrderPlaced::class, function () {
        });
        $this->assertEquals(2, $this->dispatcher->getListenerCount());
    }
    /**
     * @test
     */
    public function testReset() {
        $this->dispatcher->listen(UserRegistered::class, function () {
        });
        $this->dispatcher->reset();
        $this->assertEquals(0, $this->dispatcher->getListenerCount());
    }
    /**
     * @test
     */
    public function testEventDataPassedToListener() {
        $receivedTotal = 0.0;
        $this->dispatcher->listen(OrderPlaced::class, function (OrderPlaced $e) use (&$receivedTotal) {
            $receivedTotal = $e->total;
        });

        $this->dispatcher->dispatch(new OrderPlaced(1, 59.99));
        $this->assertEquals(59.99, $receivedTotal);
    }
    /**
     * @test
     */
    public function testListenersCalledInRegistrationOrder() {
        $order = [];
        $this->dispatcher->listen(UserRegistered::class, function () use (&$order) {
            $order[] = 'first';
        });
        $this->dispatcher->listen(UserRegistered::class, function () use (&$order) {
            $order[] = 'second';
        });

        $this->dispatcher->dispatch(new UserRegistered('order@test.com'));
        $this->assertEquals(['first', 'second'], $order);
    }
    /**
     * @test
     */
    public function testListenerReceivesExactEventInstance() {
        $original = new OrderPlaced(99, 250.0);
        $received = null;
        $this->dispatcher->listen(OrderPlaced::class, function (OrderPlaced $e) use (&$received) {
            $received = $e;
        });

        $this->dispatcher->dispatch($original);
        $this->assertSame($original, $received);
    }
    /**
     * @test
     */
    public function testDispatchSameEventMultipleTimes() {
        $count = 0;
        $this->dispatcher->listen(UserRegistered::class, function () use (&$count) {
            $count++;
        });

        $this->dispatcher->dispatch(new UserRegistered('a@b.com'));
        $this->dispatcher->dispatch(new UserRegistered('c@d.com'));
        $this->dispatcher->dispatch(new UserRegistered('e@f.com'));
        $this->assertEquals(3, $count);
    }
    /**
     * @test
     */
    public function testListenerCanMutateEventState() {
        $event = new class {
            public int $value = 0;
        };

        $this->dispatcher->listen(get_class($event), function ($e) {
            $e->value += 10;
        });
        $this->dispatcher->listen(get_class($event), function ($e) {
            $e->value *= 2;
        });

        $this->dispatcher->dispatch($event);
        $this->assertEquals(20, $event->value);
    }
    /**
     * @test
     */
    public function testGetListenersReturnsEmptyForUnregisteredEvent() {
        $this->assertEquals([], $this->dispatcher->getListeners('NonExistent\\Event'));
    }
    /**
     * @test
     */
    public function testResetClearsAllEventsCompletely() {
        $this->dispatcher->listen(UserRegistered::class, function () {});
        $this->dispatcher->listen(OrderPlaced::class, function () {});
        $this->assertEquals(2, $this->dispatcher->getListenerCount());

        $this->dispatcher->reset();
        $this->assertEquals(0, $this->dispatcher->getListenerCount());
        $this->assertEquals([], $this->dispatcher->getListeners(UserRegistered::class));
        $this->assertEquals([], $this->dispatcher->getListeners(OrderPlaced::class));
    }
    /**
     * @test
     */
    public function testMixedCallableAndClassListeners() {
        $results = [];
        $this->dispatcher->listen(UserRegistered::class, function (UserRegistered $e) use (&$results) {
            $results[] = 'callable:' . $e->email;
        });
        $this->dispatcher->listen(UserRegistered::class, new SendWelcomeEmail());

        $this->dispatcher->dispatch(new UserRegistered('mixed@test.com'));
        $this->assertEquals(['callable:mixed@test.com'], $results);
        $this->assertEquals('mixed@test.com', SendWelcomeEmail::$lastEmail);
    }
}
