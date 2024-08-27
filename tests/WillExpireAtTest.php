<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use TeHelper;

class WillExpireAtTest extends TestCase
{


    public function test_will_expire_at_within_90_minutes()
    {
        $due_time = Carbon::now()->addMinutes(50)->format('Y-m-d H:i:s');
        $created_at = Carbon::now()->format('Y-m-d H:i:s');

        $expected = Carbon::parse($due_time)->format('Y-m-d H:i:s');

        $this->assertEquals($expected, TeHelper::willExpireAt($due_time, $created_at));
    }


    public function test_will_expire_at_within_24_hours()
    {
        $due_time = Carbon::now()->addHours(23)->format('Y-m-d H:i:s');
        $created_at = Carbon::now()->format('Y-m-d H:i:s');

        $expected = Carbon::parse($created_at)->addMinutes(90)->format('Y-m-d H:i:s');

        $this->assertEquals($expected, TeHelper::willExpireAt($due_time, $created_at));
    }

    public function test_will_expire_at_within_72_hours()
    {
        $due_time = Carbon::now()->addHours(48)->format('Y-m-d H:i:s');
        $created_at = Carbon::now()->format('Y-m-d H:i:s');

        $expected = Carbon::parse($created_at)->addHours(16)->format('Y-m-d H:i:s');

        $this->assertEquals($expected, TeHelper::willExpireAt($due_time, $created_at));
    }


    public function test_will_expire_at_after_72_hours()
    {
        $due_time = Carbon::now()->addHours(100)->format('Y-m-d H:i:s');
        $created_at = Carbon::now()->format('Y-m-d H:i:s');

        $expected = Carbon::parse($due_time)->subHours(48)->format('Y-m-d H:i:s');

        $this->assertEquals($expected, TeHelper::willExpireAt($due_time, $created_at));
    }
}
