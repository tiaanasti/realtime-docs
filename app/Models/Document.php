<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }
}