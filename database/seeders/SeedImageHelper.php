<?php

namespace Database\Seeders;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SeedImageHelper
{
    public static function makeImage(string $label, int $width = 800, int $height = 600): UploadedFile
    {
        $hash = crc32($label);
        $color = sprintf('#%06x', $hash & 0xFFFFFF);

        $image = (new ImageManager(new Driver))->create($width, $height)->fill($color);
        $tempPath = tempnam(sys_get_temp_dir(), 'seed-img-');
        $path = $tempPath.'.jpg';

        $image->save($path, quality: 85);

        $filename = Str::slug($label).'.jpg';

        return new UploadedFile($path, $filename, 'image/jpeg', null, true);
    }
}
