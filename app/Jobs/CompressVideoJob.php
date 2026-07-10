<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Services\VideoCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CompressVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;
    public $backoff = 300; // 5 minutes between retries

    protected $upload;
    public function __construct(Upload $upload) {
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     */
    public function handle(VideoCompressionService $compressionService): void
    {
        try {
            Log::channel('video')->info('Starting video compression', [
                'upload_id' => $this->upload->id,
                'file' => $this->upload->file_name
            ]);

            $compressedVersion = $compressionService->compressVideo($this->upload);

            Log::channel('video')->info('Video compression completed', [
                'upload_id' => $this->upload->id,
                'version' => $compressedVersion
            ]);

        } catch (\Exception $e) {
            Log::channel('video')->error('Video compression failed', [
                'upload_id' => $this->upload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            echo "Error during video compression: " . $e->getMessage() . "\n";

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('video')->error('Video compression job failed permanently', [
            'upload_id' => $this->upload->id,
            'error' => $exception->getMessage()
        ]);
    }
}
