<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'name',
        'amount',
        'expense_date',
        'notes',
        'attachment_path',
        'user_id',
    ];

    /**
     * Mendapatkan kategori biaya yang terkait dengan biaya ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Mendapatkan pengguna yang mencatat biaya ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}