<?php

namespace App\Models\Studies;

use Illuminate\Database\Eloquent\Model;

/**
 * @property DateTime   $created_at
 * @property string     $patient_name
 * @property string     $share_note
 * @property string     $study_date
 * @property string     $study_description
 * @property DateTime   $updated_at
 * @property string     $uuid
 */
class SharedStudies extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shared_studies';

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
        'created_at', 'patient_name', 'server', 'share_note', 'shared_by', 'shared_with', 'study_date', 'study_description', 'study_json', 'updated_at', 'uuid'
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
        'created_at' => 'datetime', 'patient_name' => 'string', 'share_note' => 'string', 'study_date' => 'string', 'study_description' => 'string', 'updated_at' => 'datetime', 'uuid' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    // Relations ...
}
