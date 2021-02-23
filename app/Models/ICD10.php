<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ICD10 extends Model
{
    use HasFactory;

    protected $table = "lib_icd10tm";
}
