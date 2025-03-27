<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dbcons extends Model
{
    use HasFactory;
    protected $table = 'dbcons'; 
    public $timestamps = false;

    protected $fillable = [
        'CompanyName',
        'EncryptedPassword',
        'PlainTextPassword',
    ];

}
