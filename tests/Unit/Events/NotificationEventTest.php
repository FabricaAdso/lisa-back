<?php

namespace Tests\Unit\Events;

use Tests\TestCase;
use App\Models\Notification;
use App\Events\NotificationEvent;
use Illuminate\Broadcasting\PrivateChannel;

class NotificationEventTest extends TestCase
{
    public function test_event_is_broadcastable()
    {
        $notification = Notification::factory()->make();
        $event = new NotificationEvent($notification);
        
        $this->assertInstanceOf(PrivateChannel::class, $event->broadcastOn()[0]);
    }

    public function test_broadcast_channel_is_correct()
    {
        $notification = Notification::factory()->make(['user_id' => 1]);
        $event = new NotificationEvent($notification);
        
        $this->assertEquals(
            new PrivateChannel('notifications.1'),
            $event->broadcastOn()[0]
        );
    }

    public function test_broadcast_event_name_is_correct()
    {
        $notification = Notification::factory()->make();
        $event = new NotificationEvent($notification);
        
        $this->assertEquals('notification.received', $event->broadcastAs());
    }

    public function test_broadcast_payload_contains_expected_data()
    {
        $notification = Notification::factory()->make([
            'id' => 123,
            'title' => 'Test Notification',
            'message' => 'This is a test',
            'type' => 'info',
            'data' => ['key' => 'value']
        ]);
        
    }
}