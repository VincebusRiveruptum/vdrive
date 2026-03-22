<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\File;

class Folder extends Model
{
    /** @use HasFactory<\Database\Factories\FolderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function getPhysicalPathAttribute()
    {
        $parts = [];
        $current = $this;
        while ($current) {
            // Slugify directory names specifically for filesystem safety
            array_unshift($parts, \Illuminate\Support\Str::slug($current->name, '-', 'es'));
            $current = $current->parent;
        }
        return "users/{$this->user_id}/files/" . implode('/', $parts);
    }
}
