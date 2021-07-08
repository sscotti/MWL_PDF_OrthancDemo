<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $RXCUI
 * @property string     $LAT
 * @property string     $TS
 * @property string     $LUI
 * @property string     $STT
 * @property string     $SUI
 * @property string     $ISPREF
 * @property string     $RXAUI
 * @property string     $SAUI
 * @property string     $SCUI
 * @property string     $SDUI
 * @property string     $SAB
 * @property string     $TTY
 * @property string     $CODE
 * @property string     $STR
 * @property string     $SRL
 * @property string     $SUPPRESS
 * @property string     $CVF
 */
class RXNCONSO extends Model
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
    protected $table = 'RXNCONSO';

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
        'RXCUI', 'LAT', 'TS', 'LUI', 'STT', 'SUI', 'ISPREF', 'RXAUI', 'SAUI', 'SCUI', 'SDUI', 'SAB', 'TTY', 'CODE', 'STR', 'SRL', 'SUPPRESS', 'CVF'
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
        'RXCUI' => 'string', 'LAT' => 'string', 'TS' => 'string', 'LUI' => 'string', 'STT' => 'string', 'SUI' => 'string', 'ISPREF' => 'string', 'RXAUI' => 'string', 'SAUI' => 'string', 'SCUI' => 'string', 'SDUI' => 'string', 'SAB' => 'string', 'TTY' => 'string', 'CODE' => 'string', 'STR' => 'string', 'SRL' => 'string', 'SUPPRESS' => 'string', 'CVF' => 'string'
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
