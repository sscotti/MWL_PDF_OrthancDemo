<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $carrier_identifier
 * @property string     $carrier_name
 * @property string     $carrier_phone_ctry
 * @property string     $carrier_phone
 * @property string     $carrier_fax_ctry
 * @property string     $carrier_fax
 * @property string     $carrier_type
 * @property string     $carrier_website
 * @property string     $carrier_email
 * @property string     $carrier_address1
 * @property string     $carrier_address2
 * @property string     $carrier_city
 * @property string     $carrier_state
 * @property string     $carrier_country
 * @property string     $carrier_postal
 * @property string     $payer_id
 * @property string     $x12_partner
 * @property string     $claimform
 * @property int        $formpages
 */
class InsuranceCarriers extends Model
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
    protected $table = 'insurance_carriers';

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
        'carrier_identifier', 'carrier_name', 'carrier_phone_ctry', 'carrier_phone', 'carrier_fax_ctry', 'carrier_fax', 'carrier_type', 'carrier_website', 'carrier_email', 'carrier_address1', 'carrier_address2', 'carrier_city', 'carrier_state', 'carrier_country', 'carrier_postal', 'payer_id', 'x12_partner', 'claimform', 'formpages'
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
        'carrier_identifier' => 'string', 'carrier_name' => 'string', 'carrier_phone_ctry' => 'string', 'carrier_phone' => 'string', 'carrier_fax_ctry' => 'string', 'carrier_fax' => 'string', 'carrier_type' => 'string', 'carrier_website' => 'string', 'carrier_email' => 'string', 'carrier_address1' => 'string', 'carrier_address2' => 'string', 'carrier_city' => 'string', 'carrier_state' => 'string', 'carrier_country' => 'string', 'carrier_postal' => 'string', 'payer_id' => 'string', 'x12_partner' => 'string', 'claimform' => 'string', 'formpages' => 'int'
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
