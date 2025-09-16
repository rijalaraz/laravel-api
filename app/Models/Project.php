<?php

namespace App\Models;

use App\Policies\ProjectPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(ProjectPolicy::class)]
class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'rate',

        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tasks()
    {
         return $this->hasMany(Task::class, 'project_id', 'id');
    }
}
