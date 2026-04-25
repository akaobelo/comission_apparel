<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachUpload extends Model
{
    protected $fillable = ['user_id', 'image_path', 'description', 'status'];

    public function coach()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
