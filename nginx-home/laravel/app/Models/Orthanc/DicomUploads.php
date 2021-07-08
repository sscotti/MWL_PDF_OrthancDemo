<?php

namespace App\Models\Orthanc;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $uploader_id
 * @property string     $uploader_name
 * @property string     $orthanc_uuid
 * @property string     $StudyInstanceUID
 * @property string     $AccessionNumber
 * @property Date       $StudyDate
 * @property string     $ReferringPhysicianName
 * @property string     $InstitutionName
 * @property string     $StudyDescription
 * @property string     $PatientID
 * @property string     $OtherPatientIDs
 * @property string     $PatientName
 * @property Date       $PatientBirthDate
 * @property string     $PatientSex
 * @property string     $Modality
 * @property int        $upload_datetime
 */
class DicomUploads extends Model
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
    protected $table = 'dicom_uploads';

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
        'uploader_id', 'uploader_name', 'orthanc_uuid', 'StudyInstanceUID', 'AccessionNumber', 'StudyDate', 'StudyTime', 'ReferringPhysicianName', 'InstitutionName', 'StudyDescription', 'PatientID', 'OtherPatientIDs', 'PatientName', 'PatientBirthDate', 'PatientSex', 'ImagesInAcquisition', 'Modality', 'upload_datetime'
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
        'uploader_id' => 'string', 'uploader_name' => 'string', 'orthanc_uuid' => 'string', 'StudyInstanceUID' => 'string', 'AccessionNumber' => 'string', 'StudyDate' => 'date', 'ReferringPhysicianName' => 'string', 'InstitutionName' => 'string', 'StudyDescription' => 'string', 'PatientID' => 'string', 'OtherPatientIDs' => 'string', 'PatientName' => 'string', 'PatientBirthDate' => 'date', 'PatientSex' => 'string', 'Modality' => 'string', 'upload_datetime' => 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'StudyDate', 'PatientBirthDate', 'upload_datetime'
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
