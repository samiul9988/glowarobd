<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'job_post_applications';

    protected $guarded = [];

    protected $casts = [
        'status' => JobApplicationStatus::class,
        'submitted_values' => 'array',
        'field_snapshot' => 'array',
        'uploaded_attachments' => 'array',
        'notes' => 'array',
        'logs' => 'array',
        'matching_score' => 'integer',
        'shortlisted' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
