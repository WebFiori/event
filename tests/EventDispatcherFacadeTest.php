<?php

namespace WebFiori\Event\Tests;

use PHPUnit\Framework\TestCase;
use WebFiori\Event\EventDispatcher;
use WebFiori\Event\EventDispatcherFacade;

class EventDispatcherFacadeTest extends TestCase {
    protected function setUp(): void {
        EventDispatcherFacade::reset();
    }
    /**
     * @test
     */
    public function testGetInstanceReturnsDispatcher() {
        $this->assertInstanceOf(EventDispatcher::class, EventDispatcherFacade::getInstance());
    }
    /**
     * @test
     */
    public function testSetInstance() {
        $custom = new EventDispatcher();
        EventDispatcherFacade::setInstance($custom);
        $this->assertSame($custom, EventDispatcherFacade::getInstance());
    }
    /**
     * @test
     */
    public function testResetCreatesNewInstance() {
        $first = EventDispatcherFacade::getInstance();
        EventDispatcherFacade::reset();
        $second = EventDispatcherFacade::getInstance();
        $this->assertNotSame($first, $second);
    }
    /**
     * @test
     */
    public function testFacadeDispatch() {
        $called = false;
        EventDispatcherFacade::listen(UserRegistered::class, function () use (&$called) {
            $called = true;
        });
        EventDispatcherFacade::dispatch(new UserRegistered('facade@test.com'));
        $this->assertTrue($called);
    }
    /**
     * @test
     */
    public function testFacadeListen() {
        SendWelcomeEmail::$lastEmail = '';
        EventDispatcherFacade::listen(UserRegistered::class, new SendWelcomeEmail());
        EventDispatcherFacade::dispatch(new UserRegistered('facade-reg@test.com'));
        $this->assertEquals('facade-reg@test.com', SendWelcomeEmail::$lastEmail);
    }
    /**
     * @test
     */
    public function testFacadeGetListeners() {
        EventDispatcherFacade::listen(UserRegistered::class, function () {
        });
        $this->assertCount(1, EventDispatcherFacade::getListeners(UserRegistered::class));
    }
    /**
     * @test
     */
    public function testFacadeGetListenerCount() {
        EventDispatcherFacade::listen(UserRegistered::class, function () {
        });
        EventDispatcherFacade::listen(OrderPlaced::class, function () {
        });
        $this->assertEquals(2, EventDispatcherFacade::getListenerCount());
    }
}
