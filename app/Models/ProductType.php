<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    protected $table = 'karung_product_types'; // <-- Tambahkan ini
    protected $fillable = ['name']; // <-- Tambahkan ini
}