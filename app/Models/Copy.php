<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copy extends Model
{
    use HasFactory;
    //nem id nevet adtuk a primary key-nek, ezért beállítjuk
    protected  $primaryKey = 'copy_id';

    protected $fillable = [
        'book_id',
        'hardcovered',
        'publication',
        'status'
    ];

    public function lending_c(){
        return $this->hasMany(Lending::class, 'copy_id', 'copy_id');
    }
}
