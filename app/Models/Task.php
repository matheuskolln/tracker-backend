<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Define the fillable fields
    protected $fillable = [
        'title',
        'description',
        'status', // "To Do", "In Progress", "Done"
        'user_id', // Link the task to a user
    ];

    // Define the relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
