<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $mrn
 * @property DateTime   $date_time
 * @property Date       $date
 * @property Date       $initial_cycle_date
 * @property Date       $due_date
 * @property int        $effective_interest_rate
 */
class Invoices extends Model
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
    protected $table = 'invoices';

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
        'mrn', 'date_time', 'date', 'initial_cycle_date', 'due_date', 'balance', 'effective_interest_rate'
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
        'mrn' => 'string', 'date_time' => 'datetime', 'date' => 'date', 'initial_cycle_date' => 'date', 'due_date' => 'date', 'effective_interest_rate' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_time', 'date', 'initial_cycle_date', 'due_date'
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
