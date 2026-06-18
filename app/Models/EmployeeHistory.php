<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'action',
        'description',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
