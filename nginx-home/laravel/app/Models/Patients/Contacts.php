<?php

namespace App\Models\Patients;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int        $contact_id
 * @property string     $mrn
 * @property string     $fname
 * @property string     $lname
 * @property string     $mname
 * @property string     $mobile_phone_ctry
 * @property string     $mobile_phone
 * @property string     $alt_mobile_phone_ctry
 * @property string     $alt_mobile_phone
 * @property string     $email
 * @property string     $alt_email
 * @property string     $address_1
 * @property string     $address_2
 * @property string     $city
 * @property string     $state
 * @property string     $country
 * @property string     $postal
 * @property string     $notes
 * @property string     $relationship
 */
class Contacts extends Model
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
    protected $table = 'contacts';

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
        'contact_id', 'mrn', 'fname', 'lname', 'mname', 'mobile_phone_ctry', 'mobile_phone', 'alt_mobile_phone_ctry', 'alt_mobile_phone', 'email', 'alt_email', 'address_1', 'address_2', 'city', 'state', 'country', 'postal', 'notes', 'relationship'
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
        'contact_id' => 'int', 'mrn' => 'string', 'fname' => 'string', 'lname' => 'string', 'mname' => 'string', 'mobile_phone_ctry' => 'string', 'mobile_phone' => 'string', 'alt_mobile_phone_ctry' => 'string', 'alt_mobile_phone' => 'string', 'email' => 'string', 'alt_email' => 'string', 'address_1' => 'string', 'address_2' => 'string', 'city' => 'string', 'state' => 'string', 'country' => 'string', 'postal' => 'string', 'notes' => 'string', 'relationship' => 'string'
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
