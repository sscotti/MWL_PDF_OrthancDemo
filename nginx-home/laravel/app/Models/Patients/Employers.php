<?php

namespace App\Models\Patients;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int        $employer_id
 * @property string     $mrn
 * @property string     $employer_name
 * @property string     $contact_lname
 * @property string     $contact_fname
 * @property string     $mobile_phone_ctry
 * @property string     $mobile_phone_suffix
 * @property string     $email
 * @property string     $address_1
 * @property string     $address_2
 * @property string     $city
 * @property string     $state
 * @property string     $country
 * @property string     $postal
 * @property string     $notes
 */
class Employers extends Model
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
    protected $table = 'employers';

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
        'employer_id', 'mrn', 'employer_name', 'contact_lname', 'contact_fname', 'mobile_phone_ctry', 'mobile_phone_suffix', 'email', 'address_1', 'address_2', 'city', 'state', 'country', 'postal', 'notes'
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
        'employer_id' => 'int', 'mrn' => 'string', 'employer_name' => 'string', 'contact_lname' => 'string', 'contact_fname' => 'string', 'mobile_phone_ctry' => 'string', 'mobile_phone_suffix' => 'string', 'email' => 'string', 'address_1' => 'string', 'address_2' => 'string', 'city' => 'string', 'state' => 'string', 'country' => 'string', 'postal' => 'string', 'notes' => 'string'
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
