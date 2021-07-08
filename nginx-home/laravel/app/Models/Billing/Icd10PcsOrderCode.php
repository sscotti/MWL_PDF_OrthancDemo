<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $pcs_code
 * @property string     $short_desc
 * @property string     $long_desc
 * @property int        $active
 * @property int        $revision
 */
class Icd10PcsOrderCode extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'icd10_pcs_order_code';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pcs_id', 'pcs_code', 'valid_for_coding', 'short_desc', 'long_desc', 'active', 'revision'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'pcs_code' => 'string', 'short_desc' => 'string', 'long_desc' => 'string', 'active' => 'int', 'revision' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
