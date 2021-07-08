<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $name
 * @property string     $fname
 * @property string     $lname
 * @property string     $mname
 * @property string     $email
 * @property Date       $dob
 * @property string     $patientid
 * @property string     $doctor_id
 * @property string     $reader_id
 * @property int        $user_active
 * @property string     $user_roles
 * @property int        $email_verified_at
 * @property string     $password
 * @property string     $two_factor_secret
 * @property string     $two_factor_recovery_codes
 * @property string     $remember_token
 * @property string     $current_team_id
 * @property string     $profile_photo_path
 * @property int        $created_at
 * @property int        $updated_at
 */
class Users extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

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

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public $timestamps = true;

    protected $attributes = [
        'name', 'fname', 'lname', 'mname', 'email', 'dob', 'patientid', 'doctor_id', 'reader_id', 'user_active', 'user_roles', 'email_verified_at', 'password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'current_team_id', 'profile_photo_path', 'created_at', 'updated_at'
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
        'name' => 'string', 'fname' => 'string', 'lname' => 'string', 'mname' => 'string', 'email' => 'string', 'dob' => 'date', 'patientid' => 'string', 'doctor_id' => 'string', 'reader_id' => 'string', 'user_active' => 'int', 'user_roles' => 'string', 'email_verified_at' => 'timestamp', 'password' => 'string', 'two_factor_secret' => 'string', 'two_factor_recovery_codes' => 'string', 'remember_token' => 'string', 'current_team_id' => 'string', 'profile_photo_path' => 'string', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'dob', 'email_verified_at', 'created_at', 'updated_at'
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
