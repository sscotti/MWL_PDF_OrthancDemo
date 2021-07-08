<?php

namespace App\Models\Referrers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $license_country
 * @property int        $license_id
 * @property string     $license_notes
 * @property string     $license_number
 * @property string     $license_provider_id
 * @property string     $license_provider_identifier
 * @property string     $license_state
 * @property string     $license_type
 */
class ProviderLicenses extends Model
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
    protected $table = 'provider_licenses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'license_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'license_country', 'license_id', 'license_notes', 'license_number', 'license_provider_id', 'license_provider_identifier', 'license_state', 'license_type'
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
        'license_country' => 'string', 'license_id' => 'int', 'license_notes' => 'string', 'license_number' => 'string', 'license_provider_id' => 'string', 'license_provider_identifier' => 'string', 'license_state' => 'string', 'license_type' => 'string'
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
