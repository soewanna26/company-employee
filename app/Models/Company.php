<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i A',
        'updated_at' => 'datetime:Y-m-d H:i A',
    ];

    public function employees()
    {
        $this->hasMany(Employee::class, 'company_id','id');
    }
}
