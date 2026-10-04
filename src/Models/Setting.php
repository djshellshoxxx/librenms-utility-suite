<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Models;

use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    protected $table = 'lus_settings';
    protected $fillable = ['key', 'value'];
}
