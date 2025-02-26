<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shop extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'name', 'area', 'genre', 'description', 'image_path'];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
