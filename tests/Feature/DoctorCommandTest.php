<?php

namespace Tests\Feature;

use App\Support\TestingResponseFormatter;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DoctorCommandTest extends TestCase
{
    public function testDoctorCommandPassesWithDefaultConfiguration()
    {
        $this->artisan('laravel-controller:doctor')
            ->expectsOutputToContain('JOOservices Laravel Controller doctor')
            ->assertSuccessful();
    }

    public function testDoctorCommandOutputsJson()
    {
        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('"ok": true', $output);
        $this->assertStringContainsString('"name": "config"', $output);
    }

    public function testDoctorCommandDetectsInvalidFormatter()
    {
        config(['laravel-controller.response_formatter' => \stdClass::class]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('does not implement ResponseFormatter', $output);
    }

    public function testDoctorCommandAcceptsConfiguredFormatter()
    {
        config(['laravel-controller.response_formatter' => TestingResponseFormatter::class]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('resolves and implements ResponseFormatter', $output);
    }

    public function testDoctorCommandAcceptsDigitStringStatusTimeout()
    {
        config(['laravel-controller.status.checks_timeout_seconds' => '5']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);

        $this->assertSame(0, $exitCode);
    }

    public function testDoctorCommandRejectsInvalidStatusTimeout()
    {
        config(['laravel-controller.status.checks_timeout_seconds' => 'soon']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('integer or digit string', $output);
    }

    public function testDoctorCommandRejectsInvalidTraceHeader()
    {
        config(['laravel-controller.trace_id.header' => '']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('must be a non-empty string', $output);
    }
}
