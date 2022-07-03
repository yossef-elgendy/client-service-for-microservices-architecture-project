<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use App\Models\Media;
use App\Traits\Helpers;
use Illuminate\Http\Request;

class MediaFileController extends Controller
{
    use Helpers;
    public function store($mediafile_data)
    {
        try {
            $mediafile = $mediafile_data['is_default'] ?
                $this->mediafileUploadDefault($mediafile_data) :
                $this->mediafileUpload($mediafile_data);

                if(! is_array($mediafile)) {
                    return $mediafile;
                }

                $mediafile = Media::create($mediafile);

                return 'success';

            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }

    public function update($mediafile_data, $id)
    {
        $mediafile_delete_response = $this->destroy($id, $mediafile_data['mediafile_type']);

        if($mediafile_delete_response !== 'success') {
            return $mediafile_delete_response;
        }

        $mediafile_store_response = $this->store($mediafile_data);

        if($mediafile_store_response !== 'success') {
            return $mediafile_store_response;
        }

        return 'success';
    }

    public function destroy($id, $type)
    {
        try {
                if(! $mediafile = Media::find($id)->where('type', '=', $type)->first()) {
                    return 'You can not delete this mediafile.';
                }

                if(! $mediafile->is_default) {
                    Storage::delete('public'.$mediafile->path);
                }

                $mediafile->delete();

                return 'success';

            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }

    public function destroy_all($model_id, $model_type)
    {
        try {
                $mediafiles = Media::where([
                    ['model_id', '=', $model_id],
                    ['model_type', '=', $model_type]
                ])->get();

                foreach($mediafiles as $mediafile) {
                    if(! $mediafile->is_default){
                        Storage::delete('public'.$mediafile->path);
                    }

                    $mediafile->delete();
                }

                return 'success';

                } catch (\Exception $e) {
                    return $e->getMessage();
                }
    }
}
