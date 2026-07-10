<?php

namespace App\Services;

use FFMpeg;
use App\Models\Upload;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class VideoCompressionService
{
    public function compressVideo(Upload $upload)
    {
        $path = $upload->file_name;
        $fileName = $upload->file_original_name;
        $newPath = 'uploads/all/videos/compressed/' . $fileName . '_compressed.mp4';
        FFMpeg::fromDisk('local')
            ->open($path)
            ->export()
            // ->onProgress(function ($percentage) {
            //     echo "{$percentage}% transcoded\n";
            // })
            ->toDisk('local')
            ->inFormat((new X264)->setKiloBitrate(800))
            ->addFilter('-vf', 'scale=1280:-2') // keep aspect ratio automatically
            ->addFilter('-preset', 'medium')    // balance between speed & quality
            ->save($newPath);

        $upload->update([
            'file_name' => $newPath,
            'file_size' => Storage::disk('local')->size($newPath),
        ]);

        Cache::forget('uni_uploaded_file_' . $upload->id);

        if(get_setting('delete_original_video_after_compression', 0) == 1){
            Storage::disk('local')->delete($path);
        }

        // if (get_setting('video_thumbnail_generation', 0) == 1) {
        //     $thumbnailPath = 'uploads/all/videos/thumbnails/' . pathinfo($fileName, PATHINFO_FILENAME) . '_thumb.jpg';
        //     FFMpeg::fromDisk('local')
        //         ->open($newPath)
        //         ->getFrameFromSeconds(5) // Generate thumbnail at 5 seconds
        //         ->export()
        //         ->toDisk('local')
        //         ->save($thumbnailPath);
        // }

        return $upload;
    }
}
