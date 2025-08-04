<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'karung_product_categories'; // <-- Tambahkan ini untuk kejelasan
    protected $fillable = ['name']; // <-- Tambahkan ini
}