<?php

namespace App\Models\Exams;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $requested_procedure_id
 * @property string     $group_name
 * @property int        $exam_length
 * @property string     $exam_name
 * @property string     $modality
 * @property string     $code_type
 * @property string     $cpt
 * @property string     $linked_exams
 */
class Exams extends Model
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
    protected $table = 'exams';

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
        'requested_procedure_id', 'group_name', 'exam_length', 'exam_name', 'modality', 'code_type', 'cpt', 'linked_exams'
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
        'requested_procedure_id' => 'string', 'group_name' => 'string', 'exam_length' => 'int', 'exam_name' => 'string', 'modality' => 'string', 'code_type' => 'string', 'cpt' => 'string', 'linked_exams' => 'string'
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
