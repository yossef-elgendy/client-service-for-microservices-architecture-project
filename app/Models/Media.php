<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_files';

    protected $fillable = [
        'path',
        'type',
        'model_type',
        'model_id'
      ];

      const TYPE = [
        'profile_image' => [
          'width' => '100',
          'height' => '100',
          'ext' => 'jpg',
        ],
        'child_image' => [
          'width' => '100',
          'height' => '100',
          'ext' => 'jpg',
        ],
      ];

      const MODEL_TYPE = [
        'client',
        'child',
      ];

      const DEFAULT_IMAGE_NAME = [
        'client'  => 'default_client.png',
        'child'    => 'default_child.png',
      ];

      public function mediafileable()
      {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
      }
}
