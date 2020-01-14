<?php
namespace Application\lib;

use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Slim\Http\UploadedFile;

require_once __DIR__.'/../../../vendor/autoload.php';

class FFMpegManager {
    public static function generate_thumbnail(UploadedFile $upload_movie) {
        try {
            $file_name = random(20).'.jpg';
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/local/bin/ffprobe',
                'timeout' => 3600,
                'ffmpeg.threads' => 12
            ]);
            $video = $ffmpeg->open($upload_movie->file);
            $video
                ->filters()
                ->resize(new Dimension(320, 240))
                ->synchronize();
            $video
                ->frame(TimeCode::fromSeconds(10))
                 ->save(__DIR__.'/../../../tmp/'.$file_name);
            return $file_name;
        } catch (Exception $e) {
            return false;
        }
    }
}