<?php

namespace App\Models\Inventory;

use App\Models\ERPUser;
use App\Models\Inventory\CSDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CSHeader extends Model
{
    use HasFactory;

    protected $table = 'TBLINVCOUNT_HEADER';
    public $timestamps = false;
    protected $fillable = [
        'STATUS',
    ];

    public function details(){
        return $this->hasMany( CSDetails::class,'CNTHEADER_ID','CNTHEADER_ID');
    }

    public function user(){
        return $this->belongsTo(ERPUser::class,'USERID','USERID');
    }
}
