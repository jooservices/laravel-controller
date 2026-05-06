<?php

namespace JOOservices\LaravelController\Console\Commands;

use Illuminate\Console\Command;
use JOOservices\LaravelController\Contracts\ResponseFormatter;
use Throwable;

class LaravelControllerDoctorCommand extends Command
{
    protected $signature = 'laravel-controller:doctor {--json : Output diagnostics as JSON}';

    protected $description = 'Inspect JOOservices Laravel Controller package configuration and bindings.';

    public function handle(): int
    {
        $checks = $this->checks();
        $hasFailures = collect($checks)->contains(static fn (array $check): bool => $check['ok'] === false);

        if ($this->option('json') === true) {
            $this->line((string) json_encode([
                'ok' => ! $hasFailures,
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $hasFailures ? self::FAILURE : self::SUCCESS;
        }

        $this->components->info('JOOservices Laravel Controller doctor');

        foreach ($checks as $check) {
            $message = sprintf('%s: %s', $check['name'], $check['message']);

            if ($check['ok']) {
                $this->components->info($message);

                continue;
            }

            $this->components->error($message);
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, array{name: string, ok: bool, message: string, details?: mixed}>
     */
    private function checks(): array
    {
        return [
            $this->configLoadedCheck(),
            $this->formatterCheck(),
            $this->traceIdHeaderCheck(),
            $this->statusConfigCheck(),
            $this->envelopeKeysCheck(),
            $this->publishedConfigCheck(),
            $this->providerBindingsCheck(),
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function configLoadedCheck(): array
    {
        $config = config('laravel-controller');

        return [
            'name' => 'config',
            'ok' => is_array($config),
            'message' => is_array($config) ? 'loaded' : 'not loaded',
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function formatterCheck(): array
    {
        $formatterClass = config('laravel-controller.response_formatter');

        if ($formatterClass === null || $formatterClass === '') {
            return [
                'name' => 'response_formatter',
                'ok' => true,
                'message' => 'default formatter',
            ];
        }

        if (! is_string($formatterClass) || ! class_exists($formatterClass)) {
            return [
                'name' => 'response_formatter',
                'ok' => false,
                'message' => 'configured formatter class does not exist',
            ];
        }

        try {
            $formatter = app($formatterClass);
        } catch (Throwable $exception) {
            return [
                'name' => 'response_formatter',
                'ok' => false,
                'message' => 'configured formatter cannot be resolved: ' . $exception->getMessage(),
            ];
        }

        return [
            'name' => 'response_formatter',
            'ok' => $formatter instanceof ResponseFormatter,
            'message' => $formatter instanceof ResponseFormatter
                ? 'resolves and implements ResponseFormatter'
                : 'does not implement ResponseFormatter',
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function traceIdHeaderCheck(): array
    {
        $header = config('laravel-controller.trace_id.header', 'X-Trace-ID');

        return [
            'name' => 'trace_id.header',
            'ok' => is_string($header) && trim($header) !== '',
            'message' => is_string($header) && trim($header) !== '' ? $header : 'must be a non-empty string',
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function statusConfigCheck(): array
    {
        $status = config('laravel-controller.status');

        if (! is_array($status)) {
            return [
                'name' => 'status',
                'ok' => false,
                'message' => 'status config must be an array',
            ];
        }

        $checks = $status['checks'] ?? [];
        $timeout = $status['checks_timeout_seconds'] ?? null;

        return [
            'name' => 'status',
            'ok' => is_array($checks) && is_int($timeout),
            'message' => is_array($checks) && is_int($timeout)
                ? 'valid'
                : 'checks must be an array and checks_timeout_seconds must be an integer',
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function envelopeKeysCheck(): array
    {
        $keys = config('laravel-controller.keys', []);
        $required = ['success', 'code', 'message', 'data', 'errors', 'meta', 'trace_id', 'warnings'];

        if (! is_array($keys)) {
            return [
                'name' => 'envelope_keys',
                'ok' => false,
                'message' => 'keys config must be an array',
            ];
        }

        $missing = array_values(array_diff($required, array_keys($keys)));

        return [
            'name' => 'envelope_keys',
            'ok' => $missing === [],
            'message' => $missing === [] ? 'all required keys configured' : 'missing: ' . implode(', ', $missing),
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function publishedConfigCheck(): array
    {
        $path = config_path('laravel-controller.php');

        return [
            'name' => 'published_config',
            'ok' => true,
            'message' => file_exists($path) ? 'published at ' . $path : 'not published; package defaults are active',
        ];
    }

    /**
     * @return array{name: string, ok: bool, message: string}
     */
    private function providerBindingsCheck(): array
    {
        return [
            'name' => 'service_provider',
            'ok' => app()->bound('config'),
            'message' => app()->bound('config') ? 'application container is available' : 'config binding missing',
        ];
    }
}
