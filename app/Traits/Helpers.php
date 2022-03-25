<?php

namespace App\Traits;

use App\Models\Media;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait Helpers
{
    public function mediafilePrepare($mediafile, $mediafile_type)
    {
        $specs = Media::TYPE[$mediafile_type];

        $img = Image::make($mediafile)
        ->fit($specs['width'], $specs['height'])
        ->encode($specs['ext'], 75);

        $img_name = md5(time()) . '.' . $specs['ext'];

        return [
        'img' => $img,
        'img_name' => $img_name,
        ];
    }

    public function mediafileUpload($mediafile_data)
    {
        try {
        $path = '/uploads/'. Media::MODEL_TYPE[$mediafile_data['model_type']] .'/'.$mediafile_data['mediafile_type'];

        if($mediafile_data['is_default']) {
            $path = $path . '/' . Media::DEFAULT_IMAGE_NAME[$mediafile_data['model_type']];

            return [
            'path' => $path,
            'type' => $mediafile_data['mediafile_type'],
            'model_type' => $mediafile_data['model_type'],
            'model_id' => $mediafile_data['model_id'],
            'is_default' => $mediafile_data['is_default']
            ];
        }

        $prepared_img = $this->mediafilePrepare(
            $mediafile_data['mediafile'],
            $mediafile_data['mediafile_type']
        );

        $path = 'public' . $path . '/' . $prepared_img['img_name'];

        Storage::put(
            $path,
            $prepared_img['img'],
        );

        return [
            'path' => str_replace('public', '', $path),
            'type' => $mediafile_data['mediafile_type'],
            'model_type' => $mediafile_data['model_type'],
            'model_id' => $mediafile_data['model_id'],
            'is_default' => $mediafile_data['is_default']
        ];
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public function mediafileDownload($mediafile)
    {
        $url = $mediafile ? config('filesystems.disks.public.url').$mediafile->path : null;

        return $url;
    }
}
