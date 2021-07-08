function IncomingWorklistRequestFilter(query, origin)

    PrintRecursive(query)
    PrintRecursive(origin)
    query['0008,0096'] = {} -- ReferringPhysicianIdentificationSequence
    query['0008,1030'] = "" -- StudyDescription
    query['0008,1070'] = ""  -- OperatorsName
    query['0020,4000'] = "" --ImageComments
    query['0040,0100'][1]['0008,0050'] =  ""  -- ScheduledProcedureStepSequence AccessionNumber

    return query
  
end

--# Dicom-Data-Set
--# Used TransferSyntax: Little Endian Implicit
--(0008,0050) SH (no value available)                     #   0, 0 AccessionNumber
--(0008,0090) PN (no value available)                     #   0, 0 ReferringPhysicianName
--(0008,1080) LO (no value available)                     #   0, 0 AdmittingDiagnosesDescription
--(0008,1110) SQ (Sequence with explicit length #=0)      #   0, 1 ReferencedStudySequence
--(fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
--(0010,0010) PN (no value available)                     #   0, 0 PatientName
--(0010,0020) LO (no value available)                     #   0, 0 PatientID
--(0010,0030) DA (no value available)                     #   0, 0 PatientBirthDate
--(0010,0040) CS (no value available)                     #   0, 0 PatientSex
--(0010,1020) DS (no value available)                     #   0, 0 PatientSize
--(0010,1030) DS (no value available)                     #   0, 0 PatientWeight
--(0010,2180) SH (no value available)                     #   0, 0 Occupation
--(0010,21b0) LT (no value available)                     #   0, 0 AdditionalPatientHistory
--(0010,4000) LT (no value available)                     #   0, 0 PatientComments
--(0020,000d) UI (no value available)                     #   0, 0 StudyInstanceUID
--(0032,1060) LO (no value available)                     #   0, 0 RequestedProcedureDescription
--(0032,1064) SQ (Sequence with explicit length #=0)      #   0, 1 RequestedProcedureCodeSequence
--(fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
--(0040,0100) SQ (Sequence with undefined length #=1)     # u/l, 1 ScheduledProcedureStepSequence
--  (fffe,e000) na (Item with undefined length #=7)         # u/l, 1 Item
--    (0008,0060) CS [MR]                                     #   2, 1 Modality
--    (0040,0001) AE [NmrEsaote]                              #  10, 1 ScheduledStationAETitle
--    (0040,0002) DA [20210507-20210507]                      #  18, 1 ScheduledProcedureStepStartDate
--    (0040,0003) TM (no value available)                     #   0, 0 ScheduledProcedureStepStartTime
--    (0040,0007) LO (no value available)                     #   0, 0 ScheduledProcedureStepDescription
--    (0040,0008) SQ (Sequence with explicit length #=0)      #   0, 1 ScheduledProtocolCodeSequence
--    (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
--    (0040,0009) SH (no value available)                     #   0, 0 ScheduledProcedureStepID
--  (fffe,e00d) na (ItemDelimitationItem)                   #   0, 0 ItemDelimitationItem
--(fffe,e0dd) na (SequenceDelimitationItem)               #   0, 0 SequenceDelimitationItem
--(0040,1001) SH (no value available)                     #   0, 0 RequestedProcedureID
--
--T0507 14:29:08.618308 OrthancPlugins.cpp:5186] (plugins) Calling service 7003 from plugin /usr/share/orthanc/plugins/libModalityWorklists.so
--T0507 14:29:08.618534 OrthancPlugins.cpp:5186] (plugins) Calling service 21 from plugin /usr/share/orthanc/plugins/libModalityWorklists.so
--I0507 14:29:08.619279 PluginsManager.cpp:172] (plugins) Received worklist query from remote modality NmrEsaote:
--{
--   "0008,0005" : "ISO_IR 192",
--   "0008,0050" : "",
--   "0008,0090" : "",
--   "0008,1080" : "",
--   "0008,1110" : [],
--   "0010,0010" : "",
--   "0010,0020" : "",
--   "0010,0030" : "",
--   "0010,0040" : "",
--   "0010,1020" : "",
--   "0010,1030" : "",
--   "0010,2180" : "",
--   "0010,21b0" : "",
--   "0010,4000" : "",
--   "0020,000d" : "",
--   "0032,1060" : "",
--   "0032,1064" : [],
--   "0040,0100" : [
--      {
--         "0008,0060" : "MR",
--         "0040,0001" : "NmrEsaote",
--         "0040,0002" : "20210507-20210507",
--         "0040,0003" : "",
--         "0040,0007" : "",
--         "0040,0008" : [],
--         "0040,0009" : ""
--      }
--   ],
--   "0040,1001" : ""
--}