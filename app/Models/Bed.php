<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class Bed.
 * 
 * @author  Victorio Putra Saritan <victorio.422023022@civitas.ukrida.ac.id>
 * 
 * @OA\Schema(
 *     description="Bed model",
 *     title="Bed model",
 *     required={"title", "author"},
 *     @OA\Xml(
 *         name="Bed"
 *     )
 * )
 */

 
class Bed extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'beds';
    protected $fillable = [
        'product_name',
        'brand',
        'type',
        'cover',
        'description',
        'price',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public function data_adder(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
