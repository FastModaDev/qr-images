<?php

namespace FastModaDev\QrImages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementGiftCard extends Model
{

    use HasFactory;

    protected $table = 'management_gift_cards';

    protected $fillable = [
        'value',
        'code',
        'saldo',
    ];


}
