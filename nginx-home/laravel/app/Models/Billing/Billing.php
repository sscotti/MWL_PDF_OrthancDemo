<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property DateTime   $date
 * @property string     $code_type
 * @property string     $code
 * @property int        $provider_id
 * @property int        $user
 * @property string     $groupname
 * @property boolean    $authorized
 * @property int        $encounter
 * @property string     $code_text
 * @property boolean    $billed
 * @property boolean    $activity
 * @property int        $payer_id
 * @property int        $bill_process
 * @property DateTime   $bill_date
 * @property DateTime   $process_date
 * @property string     $process_file
 * @property string     $modifier
 * @property int        $units
 * @property string     $justify
 * @property string     $target
 * @property int        $x12_partner_id
 * @property string     $ndc_info
 * @property string     $notecodes
 * @property string     $external_id
 * @property string     $pricelevel
 * @property string     $revenue_code

LARAVEL ADDS updated_at (dateime) and created_at (timestamp) BY DEFAULT FOR ALL MODELS.
NO NEED TO SPECIFY THEM IN THE $FILLABLE DECLARATION, ALTHOUGH THEY NEED TO BE IN THE DATABASE

 * @property string     $revenue_code
  * @property string     $revenue_code

 */
class Billing extends Model
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
    protected $table = 'billing';

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
        'date', 'code_type', 'code', 'pid', 'provider_id', 'user', 'groupname', 'authorized', 'encounter', 'code_text', 'billed', 'activity', 'payer_id', 'bill_process', 'bill_date', 'process_date', 'process_file', 'modifier', 'units', 'fee', 'justify', 'target', 'x12_partner_id', 'ndc_info', 'notecodes', 'external_id', 'pricelevel', 'revenue_code'
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
        'date' => 'datetime', 'code_type' => 'string', 'code' => 'string', 'provider_id' => 'int', 'user' => 'int', 'groupname' => 'string', 'authorized' => 'boolean', 'encounter' => 'int', 'code_text' => 'string', 'billed' => 'boolean', 'activity' => 'boolean', 'payer_id' => 'int', 'bill_process' => 'int', 'bill_date' => 'datetime', 'process_date' => 'datetime', 'process_file' => 'string', 'modifier' => 'string', 'units' => 'int', 'justify' => 'string', 'target' => 'string', 'x12_partner_id' => 'int', 'ndc_info' => 'string', 'notecodes' => 'string', 'external_id' => 'string', 'pricelevel' => 'string', 'revenue_code' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date', 'bill_date', 'process_date'
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
