<?php

namespace App\Jobs;

use App\Models\CoreFacility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CfWebsiteHealthCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $facilityId;

    public function __construct(int $facilityId)
    {
        $this->facilityId = $facilityId;
    }

    public function handle(): void
    {
        // get facility
        $facility = CoreFacility::find($this->facilityId);

        if (! $facility || empty($facility->web_address)) {
            return;
        }

        // get url
        $url = $facility->web_address;
        $httpStatusCode = null;
        $displayStatus = 'Unknown Error';
        $detailedError = null;

        try {
            // make request
            $response = Http::timeout(10)->get($url);
            $httpStatusCode = $response->status();

            // handle response
            if ($response->successful()) {
                $displayStatus = 'Accessible';
                $detailedError = 'HTTP '.$httpStatusCode;
            } elseif ($response->redirect()) {
                $displayStatus = 'Redirect';
                $detailedError = 'HTTP '.$httpStatusCode.': Redirect to '.$response->header('Location');
            } elseif ($response->clientError()) {
                $displayStatus = 'Broken Link (Client Error)';
                $detailedError = 'HTTP '.$httpStatusCode.': '.$this->getClientErrorMessage($httpStatusCode);
            } elseif ($response->serverError()) {
                $displayStatus = 'Server Error';
                $detailedError = 'HTTP '.$httpStatusCode.': '.$this->getServerErrorMessages($httpStatusCode);
            } else {
                $displayStatus = 'Unexpected HTTP Status';
                $detailedError = 'HTTP '.$httpStatusCode;
            }
        } catch (ConnectionException $e) {
            $httpStatusCode = 0;
            $displayStatus = 'Connection Failed';
            $detailedError = 'Could not connect to host. Possible network issue or server is down.';
        } catch (\Throwable $e) {
            $httpStatusCode = -1;
            $displayStatus = 'System Error';
            $detailedError = 'An unexpected error occurred: '.$e->getMessage();
        } finally {
            // update facility information
            $facility->web_checked_at = now();
            $facility->web_status = $httpStatusCode;
            $facility->web_status_desc = $displayStatus;
            $facility->web_status_error = $detailedError;
            $facility->save();
        }
    }

    // helper to get client error messages
    protected function getClientErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden (Permission Denied)',
            404 => 'Not Found (Page does not exist)',
            405 => 'Method Not Allowed',
            429 => 'Too Many Requests (Rate Limited)',
            default => 'Client error'
        };
    }

    // helper to get server error messages
    protected function getServerErrorMessages(int $statusCode): string
    {
        return match ($statusCode) {
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable (Server temporarily down)',
            504 => 'Gateway Timeout',
            default => 'Server error'
        };
    }
}
