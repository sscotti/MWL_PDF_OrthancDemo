<?php

namespace App\Models\Orthanc;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $AET
 * @property string     $nginx_admin_url
 * @property string     $api_url
 * @property string     $osimis_viewer_link
 * @property string     $server_check
 * @property string     $server_name
 * @property string     $osimis_viewer_name
 * @property string     $domain
 */
class OrthancHosts extends Model
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
    protected $table = 'orthanc_hosts';

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
        'AET', 'nginx_admin_url', 'api_url', 'osimis_viewer_link', 'server_check', 'server_name', 'osimis_viewer_name', 'domain', 'stone_viewer'
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
        'AET' => 'string', 'nginx_admin_url' => 'string', 'api_url' => 'string', 'osimis_viewer_link' => 'string', 'server_check' => 'string', 'server_name' => 'string', 'osimis_viewer_name' => 'string', 'domain' => 'string', 'stone_viewer' => 'int'
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
