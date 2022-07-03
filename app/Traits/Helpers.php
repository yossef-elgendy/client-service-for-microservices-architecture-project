<?php

namespace App\Traits;

use App\Http\Controllers\Api\MediaFileController;
use App\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait Helpers
{
    function mediafilesManage($method, array $mediafiles)
    {
        $mediafile_controller = new MediaFileController();
       
        foreach ($mediafiles as $mediafile) {
            $mediafile_data = [
                'mediafile' => $mediafile['mediafile'],
                'mediafile_type' => $mediafile['mediafile_type'],
                'model_id' => $mediafile['model_id'],
                'model_type' => $mediafile['model_type'],
                'is_default' => $mediafile['is_default'],
            ];
            
            if ($method == 'store') {
                $mediafile_response = $mediafile_controller->store($mediafile_data);
            } elseif ($method == 'update') {
                $mediafile_response = $mediafile_controller->update($mediafile_data, $mediafile['id']);
            } elseif($method == 'destroy') {
                $mediafile_response = $mediafile_controller->destroy($mediafile['id'], $mediafile['mediafile_type']);
        }

        if ($mediafile_response !== 'success') {
            $errors[] = $mediafile_response;
        }
    }

        return $errors ?? 'success';
    }

function mediafilePrepare($mediafile, $mediafile_type)
{
  $specs = Media::TYPE[$mediafile_type];

  $img = Image::make($mediafile)
    ->resize($specs['width'], $specs['height'], function ($constraint) {
      $constraint->aspectRatio();
      $constraint->upsize();
    })
    // ->fit($specs['width'], $specs['height'])
    ->encode($specs['ext'], 75);

  $img_name = md5(time()) . '.' . $specs['ext'];

  return [
    'img' => $img,
    'img_name' => $img_name,
  ];
}

function mediafileUploadDefault($mediafile_data)
{
  $path = 'uploads/' . $mediafile_data['model_type'] . '/' . $mediafile_data['mediafile_type'];
  $path = $path . '/' . Media::DEFAULT_IMAGE_NAME[$mediafile_data['model_type']];

  return [
    'path' => $path,
    'type' => $mediafile_data['mediafile_type'],
    'model_type' => $mediafile_data['model_type'],
    'model_id' => $mediafile_data['model_id'],
    'is_default' => 1,
  ];
}

function mediafileUpload($mediafile_data)
{
  try {
    $path = 'uploads/' . $mediafile_data['model_type'] . '/' . $mediafile_data['mediafile_type'];

    $prepared_img = $this->mediafilePrepare(
      $mediafile_data['mediafile'],
      $mediafile_data['mediafile_type']
    );

    $path = $path . '/' . $prepared_img['img_name'];

    // Storage::put(
    //   $path,
    //   $prepared_img['img'],
    // );

    $storage = app('firebase.storage');
    $bucket = $storage->getBucket();
    $bucket->upload(
      $prepared_img['img'],
      ['name' => $path]
    );

    return [
      'path' => $path,
      // 'path' => str_replace('', '', $path),
      'type' => $mediafile_data['mediafile_type'],
      'model_type' => $mediafile_data['model_type'],
      'model_id' => $mediafile_data['model_id'],
      'is_default' => 0
    ];
  } catch (\Exception $e) {
    return $e->getMessage();
  }
}

    function mediafileDownload($mediafile)
    {
        // $url = $mediafile ? config('filesystems.disks.public.url') . $mediafile->path : null;

        $storage = app('firebase.storage');
        $bucket = $storage->getBucket();
        $object = $bucket->object($mediafile->path);
        $url = $object->signedUrl(now()->addHour());

        return $url;
    }
}
