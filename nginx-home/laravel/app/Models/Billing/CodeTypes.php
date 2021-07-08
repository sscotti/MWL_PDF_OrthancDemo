<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $ct_key
 * @property int        $ct_id
 * @property int        $ct_seq
 * @property int        $ct_mod
 * @property string     $ct_just
 * @property string     $ct_mask
 * @property boolean    $ct_fee
 * @property boolean    $ct_rel
 * @property boolean    $ct_nofs
 * @property boolean    $ct_diag
 * @property boolean    $ct_active
 * @property string     $ct_label
 * @property boolean    $ct_external
 * @property boolean    $ct_claim
 * @property boolean    $ct_proc
 * @property boolean    $ct_term
 * @property boolean    $ct_problem
 * @property boolean    $ct_drug
 */
class CodeTypes extends Model
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
    protected $table = 'code_types';

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
        'ct_key', 'ct_id', 'ct_seq', 'ct_mod', 'ct_just', 'ct_mask', 'ct_fee', 'ct_rel', 'ct_nofs', 'ct_diag', 'ct_active', 'ct_label', 'ct_external', 'ct_claim', 'ct_proc', 'ct_term', 'ct_problem', 'ct_drug'
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
        'ct_key' => 'string', 'ct_id' => 'int', 'ct_seq' => 'int', 'ct_mod' => 'int', 'ct_just' => 'string', 'ct_mask' => 'string', 'ct_fee' => 'boolean', 'ct_rel' => 'boolean', 'ct_nofs' => 'boolean', 'ct_diag' => 'boolean', 'ct_active' => 'boolean', 'ct_label' => 'string', 'ct_external' => 'boolean', 'ct_claim' => 'boolean', 'ct_proc' => 'boolean', 'ct_term' => 'boolean', 'ct_problem' => 'boolean', 'ct_drug' => 'boolean'
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
