<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * TXA segment class
 * Ref: https://hl7-definition.caristix.com/v2/HL7v2.8/Segments/TXA
 */
class TXA extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('TXA', $fields);
    }
    
    
    //  *** SETTERS ***
    
    // 4 characters
    public function setSetID($value, int $position = 1)
    {
        return $this->setField($position, $value);
    }
    
    /*
    AR	Autopsy report	
	CD	Cardiodiagnostics	
	CN	Consultation	
	DI	Diagnostic imaging	
	DS	Discharge summary	
	ED	Emergency department report	
	HP	History and physical examination	
	OP	Operative report	
	PC	Psychiatric consultation	
	PH	Psychiatric history and physical examination	
	PN	Procedure note	
	PR	Progress note	
	SP	Surgical pathology	
	TS	Transfer summary
	*/
    public function setDocumentType($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }
    
	/*
	AP	Other application data, typically uninterpreted binary data (HL7 V2.3 and later)	
	AU	Audio data (HL7 V2.3 and later)	
	FT	Formatted text (HL7 V2.2 only)	
	IM	Image data (HL7 V2.3 and later)	
	multipart	MIME multipart package	
	NS	Non-scanned image (HL7 V2.2 only)	
	SD	Scanned document (HL7 V2.2 only)	
	SI	Scanned image (HL7 V2.2 only)	
	TEXT	Machine readable text document (HL7 V2.3.1 and later)	
	TX	Machine readable text document (HL7 V2.2 only)
	*/    
    public function setDocumentContentPresentation($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }
    
    // Date of the exam
    public function setActivityDateTime($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setPrimaryActivityProvider($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }
    
    public function setOriginationDateTime($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }
    
    public function setTranscriptionDateTime($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }
    
    public function setEditDateTime($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }
    
    public function setOriginatorCodeName($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }
    
    public function setAssignedDocumentAuthenticator($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }
    
    public function setTranscriptionistCodeName($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }
    
    public function setUniqueDocumentNumber($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }
    
    public function setParentDocumentNumber($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }
    
    public function setPlacerOrderNumber($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }
    
    public function setFillerOrderNumber($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }
    
    public function setUniqueDocumentFileName($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentCompletionStatus($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentConfidentialityStatus($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentAvailabilityStatus($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentStorageStatus($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentChangeReason($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }
    
    public function setAuthenticationPerson($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }
    
    public function setDistributedCopies($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }
    
    public function setFolderAssignment($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }
    
    public function setDocumentTitle($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }
    
    public function setAgreedDueDateTime($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }
    
    // GETTERS
    
    // 4 characters
    public function getSetID($value, int $position = 1)
    {
        return $this->getField($position, $value);
    }
    
    // 30 characters
    /*
    AR	Autopsy report	
	CD	Cardiodiagnostics	
	CN	Consultation	
	DI	Diagnostic imaging	
	DS	Discharge summary	
	ED	Emergency department report	
	HP	History and physical examination	
	OP	Operative report	
	PC	Psychiatric consultation	
	PH	Psychiatric history and physical examination	
	PN	Procedure note	
	PR	Progress note	
	SP	Surgical pathology	
	TS	Transfer summary
	*/

    public function getDocumentType($value, int $position = 2)
    {
        return $this->getField($position, $value);
    }
    
	/*
	AP	Other application data, typically uninterpreted binary data (HL7 V2.3 and later)	
	AU	Audio data (HL7 V2.3 and later)	
	FT	Formatted text (HL7 V2.2 only)	
	IM	Image data (HL7 V2.3 and later)	
	multipart	MIME multipart package	
	NS	Non-scanned image (HL7 V2.2 only)	
	SD	Scanned document (HL7 V2.2 only)	
	SI	Scanned image (HL7 V2.2 only)	
	TEXT	Machine readable text document (HL7 V2.3.1 and later)	
	TX	Machine readable text document (HL7 V2.2 only)
	*/    
    public function getDocumentContentPresentation($value, int $position = 3)
    {
        return $this->getField($position, $value);
    }
    
    // Date of the exam
    public function getActivityDateTime($value, int $position = 4)
    {
        return $this->getField($position, $value);
    }

    public function getPrimaryActivityProvider($value, int $position = 5)
    {
        return $this->getField($position, $value);
    }
    
    public function getOriginationDateTime($value, int $position = 6)
    {
        return $this->getField($position, $value);
    }
    
    public function getTranscriptionDateTime($value, int $position = 7)
    {
        return $this->getField($position, $value);
    }
    
    public function getEditDateTime($value, int $position = 8)
    {
        return $this->getField($position, $value);
    }
    
    public function getOriginatorCodeName($value, int $position = 9)
    {
        return $this->getField($position, $value);
    }
    
    public function getAssignedDocumentAuthenticator($value, int $position = 10)
    {
        return $this->getField($position, $value);
    }
    
    public function getTranscriptionistCodeName($value, int $position = 11)
    {
        return $this->getField($position, $value);
    }
    
    public function getUniqueDocumentNumber($value, int $position = 12)
    {
        return $this->getField($position, $value);
    }
    
    public function getParentDocumentNumber($value, int $position = 13)
    {
        return $this->getField($position, $value);
    }
    
    public function getPlacerOrderNumber($value, int $position = 14)
    {
        return $this->getField($position, $value);
    }
    
    public function getFillerOrderNumber($value, int $position = 15)
    {
        return $this->getField($position, $value);
    }
    
    public function getUniqueDocumentFileName($value, int $position = 16)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentCompletionStatus($value, int $position = 17)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentConfidentialityStatus($value, int $position = 18)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentAvailabilityStatus($value, int $position = 19)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentStorageStatus($value, int $position = 20)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentChangeReason($value, int $position = 21)
    {
        return $this->getField($position, $value);
    }
    
    public function getAuthenticationPerson($value, int $position = 22)
    {
        return $this->getField($position, $value);
    }
    
    public function getDistributedCopies($value, int $position = 23)
    {
        return $this->getField($position, $value);
    }
    
    public function getFolderAssignment($value, int $position = 24)
    {
        return $this->getField($position, $value);
    }
    
    public function getDocumentTitle($value, int $position = 25)
    {
        return $this->getField($position, $value);
    }
    
    public function getAgreedDueDateTime($value, int $position = 26)
    {
        return $this->getField($position, $value);
    }
    
}

?>
