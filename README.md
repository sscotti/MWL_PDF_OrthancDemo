TO BUILD:

1.  Clone the directory.
2.  Copy sample.env to .env and configure.
3.  Copy sample.env_pacs to .env_pacs and configure.
4.  Use the generate-tls.sh file in the tls folder to create some server-crt.pem and server-key.pem files for SSL.
5.  Copy the crt and key to orthanc.crt and orthanc.key in the tls_dicom folder to enable DICOM TLS.
6.  Combine those in combined.crt.pem in the tls_dicom folder to enable SSL for the REST API and Explorer.
7.  There might be a way to enable the CA files as well so that the certificate verifies, or just use your own.

8.  Change to the root of the folder and execute:  sudo docker-compose up --build (-d optionally).
9.  Verify no errors.

To initialize the orthanc_ris DB:

-- Create syntax for TABLE 'mwl'
CREATE TABLE `mwl` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `completed` tinyint(1) DEFAULT NULL,
  `AccessionNumber` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ScheduledProcedureStepStartDate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AET` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MWLJSON` json DEFAULT NULL,
  `Dataset` blob,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'n_create'
CREATE TABLE `n_create` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AccessionNumber` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MessageID` int unsigned DEFAULT NULL,
  `dataset_in` json DEFAULT NULL,
  `mwl` json DEFAULT NULL,
  `dataset_out` json DEFAULT NULL,
  `named_tags` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'n_set'
CREATE TABLE `n_set` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AffectedSOPInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MessageID` int DEFAULT NULL,
  `managed_instance` json DEFAULT NULL,
  `mod_list` json DEFAULT NULL,
  `response` json DEFAULT NULL,
  `response_status` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;_

Orthanc is accessible on:

http://localhost:8042

PHPMyAdmin is accessible on:

http://localhost:11080
user: root, password:  root

PGAdmin is accessible on:

http://localhost:5050
user: sscotti@sias.dev, password:  postgres

Explorer2:

http://localhost:8042/ui/app/#/


To test creating a MWL from JSON use:


curl -k -X POST  https://localhost:8042/mwl/create_from_json -d '{"AccessionNumber":"CMACC00000002","AdditionalPatientHistory":"test","AdmittingDiagnosesDescription":"","Allergies":"","ImageComments":"Tech:  TEST","MedicalAlerts":"TEST","Modality":"MR","Occupation":"","OperatorsName":"Tech^SDS","PatientAddress":"^^Vienna^OS^1160^AT","PatientBirthDate":"20010101","PatientComments":"","PatientID": "PatientID","PatientName":"Person^Test^","PatientSex": "M","PatientSize":"","PatientTelecomInformation":"^^^","PatientWeight":"","ReferringPhysicianIdentificationSequence":[  {"InstitutionName": "InstitutionName","PersonIdentificationCodeSequence":[{"CodeMeaning":"Local Code","CodeValue":"0001","CodingSchemeDesignator":"L"}],"PersonTelephoneNumbers":"^PN^^"}],"ReferringPhysicianName":"0001:Scotti^Stephen^Douglas^Dr.","ScheduledProcedureStepSequence":[{"Modality": "MR","ScheduledProcedureStepDescription":"MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST","ScheduledProcedureStepID": "0001","ScheduledProcedureStepStartDate":"20210704","ScheduledProcedureStepStartTime":"110000","ScheduledProtocolCodeSequence":[{"CodeMeaning": "","CodeValue": "70551","CodingSchemeDesignator": "C4"}],"ScheduledStationAETitle": "NmrEsaote"}],"SpecificCharacterSet":"ISO_IR 192","StudyInstanceUID":""}'


To test an MWL query using findscu use the following.

findscu  localhost 4242 -W  +tla -ic -v -d -k "AccessionNumber" \
-k "Modality" \
-k "InstitutionName" \
-k "ReferringPhysicianName" \
-k "ReferencedStudySequence[0]" \
-k "ReferencedPatientSequence[0]" \
-k "PatientName" \
-k "PatientID" \
-k "PatientBirthDate" \
-k "PatientSex" \
-k "PatientAge" \
-k "PatientWeight" \
-k "MedicalAlerts" \
-k "Allergies" \
-k "PregnancyStatus" \
-k "StudyInstanceUID" \
-k "StudyID" \
-k "RequestingPhysician" \
-k "RequestedProcedureDescription" \
-k "RequestedProcedureCodeSequence[0]" \
-k "AdmissionID" \
-k "SpecialNeeds" \
-k "CurrentPatientLocation" \
-k "PatientState" \
-k "ScheduledProcedureStepSequence[0]" \
-k "RequestedProcedureID" \
-k "RequestedProcedurePriority" \
-k "PatientTransportArrangements" \
-k "ConfidentialityConstraintOnPatientDataDescription"

To Store a PDF:

curl -k https://localhost:8042/pdfkit/htmltopdf -d '{"method":"html","title":"HTML To PDF", "author": "Stephen D. Scotti", "studyuuid":"0cc9fb82-726d3dfc-e6f2b353-e96558d7-986cbb2c","html":"JVBER . . .","return":1,"attach":1}'

curl -k https://localhost:8042/pdfkit/htmltopdf -d '{"method":"html","title":"HTML To PDF", "author": "Stephen D. Scotti", "studyuuid":"0cc9fb82-726d3dfc-e6f2b353-e96558d7-986cbb2c","html":"Basically put any valid HTML, including CSS that can be rendered by wkhtmltopdf","return":1,"attach":1}'
