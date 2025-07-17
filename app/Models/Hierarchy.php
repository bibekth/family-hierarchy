<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Hierarchy extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function getAvatarAttribute($value)
    {
        if ($value == null) {
            return '';
        }

        return asset($value);
    }

    public function getSexAttribute($value)
    {
        if ($value == "M") {
            return 'Male';
        } elseif( $value == "F"){
            return 'Female';
        }

        return 'Unknown';
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($hierarchy) {
            if (empty($hierarchy->slug)) {
                $hierarchy->slug = static::generateUniqueSlug($hierarchy->name);
            }
        });
    }

    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Hierarchy::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function father()
    {
        return $this->belongsTo(Hierarchy::class, 'father_id', 'id');
    }

    public function mother()
    {
        return $this->belongsTo(Hierarchy::class, 'mother_id', 'id');
    }

    public function spouse()
    {
        return $this->belongsTo(Hierarchy::class, 'spouse_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function children()
    {
        // This will get all children where this person is either father or mother
        return $this->hasMany(Hierarchy::class, 'father_id')
            ->orWhere('mother_id', $this->id)
            ->with('spouse'); // Optionally load spouse for each child
    }

    public static function loadFamilyTree($person, $depth = 3)
    {
        if ($depth <= 0) return $person;

        return $person->load([
            'father' => function ($query) use ($depth) {
                $query->with(['father' => function ($q) use ($depth) {
                    Hierarchy::loadFamilyTree($q, $depth - 1);
                }, 'mother' => function ($q) use ($depth) {
                    Hierarchy::loadFamilyTree($q, $depth - 1);
                }, 'spouse']);
            },
            'mother' => function ($query) use ($depth) {
                $query->with(['father' => function ($q) use ($depth) {
                    Hierarchy::loadFamilyTree($q, $depth - 1);
                }, 'mother' => function ($q) use ($depth) {
                    Hierarchy::loadFamilyTree($q, $depth - 1);
                }, 'spouse']);
            },
            'spouse',
            'children' => function ($query) use ($depth) {
                $query->with(['children' => function ($q) use ($depth) {
                    Hierarchy::loadFamilyTree($q, $depth - 1);
                }, 'spouse']);
            }
        ]);
    }
}
