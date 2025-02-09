<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'created_by', // Criador da tarefa
        'start_date',
        'end_date',
        'working',
        'time-spent'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
