<?php

namespace App\Models\Readers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $reader_id
 * @property string     $reader_gateway_destination
 * @property string     $sender_ns_name
 * @property string     $sender_gateway_destination
 * @property string     $reports_email
 * @property string     $specialty
 * @property string     $doc_firstname
 * @property string     $doc_lastname
 * @property string     $doc_middlename
 * @property string     $doc_suffix
 * @property string     $doc_address1
 * @property string     $doc_city
 * @property string     $doc_state
 * @property string     $doc_country
 * @property string     $doc_postal
 * @property string     $doc_npi
 * @property string     $doc_license_id
 * @property string     $doc_license_country
 * @property string     $tax_id
 * @property string     $ssn
 * @property string     $doc_phone_country
 * @property string     $doc_phone_number
 * @property string     $providertype
 * @property string     $dea
 * @property string     $taxonomy
 * @property string     $notes
 * @property string     $orthanc_server_ids
 */
class TeleradContracts extends Model
{

    protected $connection= 'mysql2';


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'telerad_contracts';

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
        'reader_id', 'reader_gateway_destination', 'sender_ns_name', 'sender_gateway_destination', 'reports_email', 'specialty', 'doc_firstname', 'doc_lastname', 'doc_middlename', 'doc_suffix', 'doc_address1', 'doc_address2', 'doc_city', 'doc_state', 'doc_country', 'doc_postal', 'doc_npi', 'doc_license_id', 'doc_license_country', 'tax_id', 'ssn', 'doc_phone_country', 'doc_phone_number', 'providertype', 'dea', 'taxonomy', 'notes', 'orthanc_server_ids'
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
        'reader_id' => 'string', 'reader_gateway_destination' => 'string', 'sender_ns_name' => 'string', 'sender_gateway_destination' => 'string', 'reports_email' => 'string', 'specialty' => 'string', 'doc_firstname' => 'string', 'doc_lastname' => 'string', 'doc_middlename' => 'string', 'doc_suffix' => 'string', 'doc_address1' => 'string', 'doc_city' => 'string', 'doc_state' => 'string', 'doc_country' => 'string', 'doc_postal' => 'string', 'doc_npi' => 'string', 'doc_license_id' => 'string', 'doc_license_country' => 'string', 'tax_id' => 'string', 'ssn' => 'string', 'doc_phone_country' => 'string', 'doc_phone_number' => 'string', 'providertype' => 'string', 'dea' => 'string', 'taxonomy' => 'string', 'notes' => 'string', 'orthanc_server_ids' => 'string'
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
