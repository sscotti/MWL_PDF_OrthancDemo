<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $mrn
 * @property string     $payment_accession
 * @property string     $ins_id
 * @property string     $source
 * @property string     $description
 * @property Date       $date
 * @property string     $amount
 * @property string     $currency
 * @property string     $note
 * @property int        $quantity
 * @property string     $type
 * @property string     $coded_exam
 */
class Payments extends Model
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
    protected $table = 'payments';

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
        'mrn', 'payment_accession', 'ins_id', 'source', 'description', 'date', 'amount', 'currency', 'note', 'quantity', 'type', 'coded_exam'
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
        'mrn' => 'string', 'payment_accession' => 'string', 'ins_id' => 'string', 'source' => 'string', 'description' => 'string', 'date' => 'date', 'amount' => 'string', 'currency' => 'string', 'note' => 'string', 'quantity' => 'int', 'type' => 'string', 'coded_exam' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date'
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
