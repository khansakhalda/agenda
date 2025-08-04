<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'description',
    'priority',
    'status',
    'due_date',
  ];

  protected $casts = [
    'due_date' => 'date',
  ];

  // Priority constants
  const PRIORITY_LOW = 'low';
  const PRIORITY_MEDIUM = 'medium';
  const PRIORITY_HIGH = 'high';

  // Status constants
  const STATUS_PENDING = 'pending';
  const STATUS_IN_PROGRESS = 'in_progress';
  const STATUS_COMPLETED = 'completed';

  public function getPriorityColorAttribute()
  {
    return match ($this->priority) {
      'high' => 'text-red-600 bg-red-100',
      'medium' => 'text-yellow-600 bg-yellow-100',
      'low' => 'text-green-600 bg-green-100',
      default => 'text-gray-600 bg-gray-100',
    };
  }

  public function getStatusColorAttribute()
  {
    return match ($this->status) {
      'completed' => 'text-green-600 bg-green-100',
      'in_progress' => 'text-blue-600 bg-blue-100',
      'pending' => 'text-gray-600 bg-gray-100',
      default => 'text-gray-600 bg-gray-100',
    };
  }

  public function getIsOverdueAttribute()
  {
    return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
  }
}