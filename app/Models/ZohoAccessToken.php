<?php
namespace App\Models;

use App\Components\Profiler\Adapters\Eloquent\EloquentProfiler;

use Illuminate\Database\Eloquent\Model;

class ZohoAccessToken extends Model 
{

     protected $table = 'zohoauthtoken';
  
}
