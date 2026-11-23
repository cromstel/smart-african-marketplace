<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Verifies that every API error response is written as a structured JSON log
 * line with requestId, level, channel, message, and metadata -- so errors are
 * traceable end to end.
 */
class StructuredLoggingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_api_error_response_produces_structured_json_log(): void
    {
        $logPath = $this->resetStructuredLog();

        $response = $this->getJson('/api/v1/products/999999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found.');

        $this->assertTrue(File::exists($logPath), 'structured.log was not created');

        $lines = array_filter(
            explode("\n", File::get($logPath)),
            fn ($line) => trim($line) !== ''
        );
        $this->assertNotEmpty($lines, 'no structured records written for the 404 response');
        foreach ($lines as $line) {
            $record = json_decode($line, true);
            $this->assertIsArray($record, 'structured.log line is not valid JSON: ' . $line);
            $this->assertArrayHasKey('requestId', $record);
            $this->assertArrayHasKey('level', $record);
            $this->assertArrayHasKey('channel', $record);
            $this->assertArrayHasKey('message', $record);
            $this->assertEquals('json', $record['channel']);
            // Every line must carry a requestId so errors are traceable end to end.
            $this->assertNotEmpty($record['requestId']);
        }
    }

    public function test_api_validation_error_also_logged(): void
    {
        $logPath = $this->resetStructuredLog();

        // The addresses endpoint lives behind auth:sanctum; without a user
        // the request 401s before validation ever runs.
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/addresses', ['type' => 'shipping'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name']);

        $this->assertTrue(File::exists($logPath));

        $records = array_filter(explode("\n", File::get($logPath)), fn ($line) => trim($line) !== '');
        $this->assertGreaterThanOrEqual(1, count($records), 'no structured records captured for validation error');

        foreach ($records as $record) {
            $data = json_decode($record, true);
            $this->assertArrayHasKey('requestId', $data);
            $this->assertArrayHasKey('level', $data);
            $this->assertArrayHasKey('channel', $data);
            $this->assertEquals('json', $data['channel']);
        }
    }

    /**
     * Truncate the shared log between tests instead of deleting it: on
     * Windows, unlinking a file that another handle (a previous test's
     * still-open Monolog stream) holds open leaves the path delete-pending,
     * and every subsequent fopen() fails with "Permission denied".
     */
    private function resetStructuredLog(): string
    {
        $logPath = storage_path('logs/structured.log');
        File::put($logPath, '');

        return $logPath;
    }
}
