<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemComment extends Model
{
    protected $fillable = ['store_item_id', 'user_id', 'user_name', 'comment'];

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
