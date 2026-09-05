<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Feature;

use App\Support\TestingResponseFormatter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\PendingCommand;
use JOOservices\LaravelController\Tests\Support\UnresolvableResponseFormatter;
use JOOservices\LaravelController\Tests\TestCase;
use RuntimeException;
use stdClass;

final class DoctorCommandTest extends TestCase
{
    public function testDoctorCommandPassesWithDefaultConfiguration(): void
    {
        $command = $this->artisan('laravel-controller:doctor');
        self::assertInstanceOf(PendingCommand::class, $command);
        $command->expectsOutputToContain('JOOservices Laravel Controller doctor')
            ->assertSuccessful();
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandOutputsJson(): void
    {
        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('"ok": true', $output);
        self::assertStringContainsString('"name": "config"', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandDetectsInvalidFormatter(): void
    {
        config(['laravel-controller.response_formatter' => stdClass::class]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('does not implement ResponseFormatter', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandAcceptsConfiguredFormatter(): void
    {
        config(['laravel-controller.response_formatter' => TestingResponseFormatter::class]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('resolves and implements ResponseFormatter', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandAcceptsDigitStringStatusTimeout(): void
    {
        config(['laravel-controller.status.checks_timeout_seconds' => '5']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);

        self::assertSame(0, $exitCode);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandRejectsInvalidStatusTimeout(): void
    {
        config(['laravel-controller.status.checks_timeout_seconds' => 'soon']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('integer or digit string', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandRejectsInvalidTraceHeader(): void
    {
        config(['laravel-controller.trace_id.header' => '']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('must be a non-empty string', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandDetectsMissingFormatterClass(): void
    {
        config(['laravel-controller.response_formatter' => 'App\\Missing\\DoesNotExist']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('configured formatter class does not exist', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandDetectsUnresolvableFormatter(): void
    {
        config([
            'laravel-controller.response_formatter' => UnresolvableResponseFormatter::class,
        ]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('configured formatter cannot be resolved', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandRejectsNonArrayStatusConfig(): void
    {
        config(['laravel-controller.status' => 'bad']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('status config must be an array', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandRejectsNonArrayEnvelopeKeys(): void
    {
        config(['laravel-controller.keys' => 'bad']);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('keys config must be an array', $output);
    }

    /**
     * @throws RuntimeException
     */
    public function testDoctorCommandReportsMissingEnvelopeKeys(): void
    {
        config([
            'laravel-controller.keys' => [
                'success' => 'success',
                'code' => 'code',
            ],
        ]);

        $exitCode = Artisan::call('laravel-controller:doctor', ['--json' => true]);
        $output = Artisan::output();

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('missing:', $output);
    }

    public function testDoctorCommandHumanOutputShowsErrorsForFailures(): void
    {
        config(['laravel-controller.trace_id.header' => '']);

        $command = $this->artisan('laravel-controller:doctor');
        self::assertInstanceOf(PendingCommand::class, $command);
        $command->assertFailed();
    }
}
