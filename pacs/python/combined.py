#!/usr/bin/python3
# -*- coding: utf-8 -*-
# Script is basically the same as for the other instances except for the first few methods:
# Read the dynamic values from the orthanc.json file, so the scriopt can be static.
# 1. MarkOrderAsCompleted has database='RIS' for production database for pacs-2, Now using config file.
# 2. OnChange has /modalities/PACS2/find-worklist for MWL finds to the same server, SELF
# 3. MWLGetList has /modalities/PACS2/find-worklist for MWL finds to the same server.  SELF

import inspect
import numbers
import json
import io
import orthanc
import base64 # part of python, https://docs.python.org/3/library/base64.html
import pdfkit # https://pypi.org/project/pdfkit/, sudo python3 -m pip install pdfkit
from pdfkit import configuration
from pdfkit import from_string
import pydicom # https://github.com/pydicom/pydicom, sudo python3 -m pip install pydicom
from pydicom.dataset import Dataset, FileDataset, FileMetaDataset
import hl7 # https://python-hl7.readthedocs.io/en/latest/

import re
import platform
import logging
import os
import sys
import smtplib
import ssl
from email.mime.text import MIMEText
import subprocess
import shutil
import mysql.connector #  sudo python3 -m pip install mysql-connector-python  https://pypi.org/project/mysql-connector-python/
import datetime
from datetime import datetime
import requests # for sending CURL to Orthanc endpoint, https://www.w3schools.com/python/module_requests.asp, https://www.w3schools.com/python/ref_requests_post.asp, sudo python3 -m pip install requests
import pprint # pretty printer


ORTHANC_CONFIG = json.loads(orthanc.GetConfiguration())

if ('Worklists' in ORTHANC_CONFIG and 'Database' in ORTHANC_CONFIG['Worklists'] and ORTHANC_CONFIG ['Worklists']['Database']):
    WORKLIST_DIR = str(json.loads(orthanc.GetConfiguration())['Worklists']['Database']) + '/'
else:
    WORKLIST_DIR = False
print("Worklist:")  
print( WORKLIST_DIR if  WORKLIST_DIR else "No Worklist Defined")

pp = pprint.PrettyPrinter(indent=4)


def MWLGetListForAccession(accesssion):


    # Get the "Missing" Tags, can be customized, this is what is in our standard MWL file, could add others.
    response = dict()
    searchtags = dict()
    searchtags['AccessionNumber'] = accesssion
    searchtags['AdditionalPatientHistory'] = ""
    searchtags['AdmittingDiagnosesDescription'] = ""
    searchtags['Allergies'] = ""
    searchtags['ImageComments'] = "" # use for the Tech's Initials
    # searchtags['InstitutionName'] = "" # set by the MRI unit
    # searchtags['InstitutionalDepartmentName'] = "" # set by the MRI unit
    # searchtags['Manufacturer'] = ""
    # searchtags['ManufacturerModelName'] = ""
    searchtags['MedicalAlerts'] = ""
    searchtags['Modality'] = ""
    searchtags['Occupation'] = ""
    searchtags['OperatorsName'] = ""
    searchtags['PatientAddress'] = ""
    searchtags['PatientBirthDate'] = ""
    searchtags['PatientComments'] = ""
    searchtags['PatientID'] = ""
    searchtags['PatientName'] = ""
    searchtags['PatientSex'] = ""
    searchtags['PatientSize'] = ""
    searchtags['PatientTelecomInformation'] = ""
    searchtags['PatientWeight'] = ""
    searchtags['ReferencedStudySequence'] = []
    searchtags['ReferringPhysicianIdentificationSequence'] = []
    searchtags['ReferringPhysicianName'] = ""
    searchtags['RequestedProcedureCodeSequence'] = []
    searchtags['RequestedProcedureDescription'] = ""
    searchtags['ScheduledProcedureStepSequence'] = []
    searchtags['SpecificCharacterSet'] = ""
    searchtags['StudyDescription'] = ""
    searchtags['StudyInstanceUID'] = ""
    
    print(searchtags)
    results = orthanc.RestApiPost('/modalities/SELF/find-worklist', json.dumps(searchtags)) 
    return results
    

# Triggered when a Study is Stable, checks for a MWL file.  Updates tags from the MWL, reconstructs the study, and then deletes MWL and marks as complete in DB.

def MarkOrderAsCompleted(AccessionNumber):

    RISDB = json.loads(orthanc.GetConfiguration()).get('RISDB', False)
    print("RIS Database")
    print(RISDB)
    if (RISDB is False):
        print("RISDB is not defined in the orthanc.json config file !, Bypassed marking complete.")
    else:
        print(RISDB)
        mydb = mysql.connector.connect(host=RISDB['host'], port = RISDB['port'], user=RISDB['user'],password=RISDB['password'],database=RISDB['database'])
        mycursor = mydb.cursor()
        try:
            mycursor.execute(RISDB["Mark_Complete_Query"], ("CM", AccessionNumber))
            mydb.commit()
            print("Marking as Complete" + str(mycursor.rowcount))
            mycursor.close()
        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            
# METADATA SYSTEM TAGS FOR INSTANCES

#     ReceptionDate: records when a DICOM instance was received by Orthanc. Similarly, LastUpdate records, for each patient/study/series, the last time a DICOM instance was added to this resource.
#     RemoteAET: records the AET of the modality that has sent some DICOM instance to Orthanc using the DICOM protocol.
#     ModifiedFrom and AnonymizedFrom: hold from which original resource, a resource was modified or anonymized. The presence of this metadata indicates that the resource is the result of a modification or anonymization that was carried on by Orthanc.
#     Origin: records through which mechanism the instance was received by Orthanc (may be Unknown, DicomProtocol, RestApi, Plugins, or Lua).
#     IndexInSeries records the expected index of a DICOM instance inside its parent series. Conversely, ExpectedNumberOfInstances associates to each series, the number of DICOM instances this series is expected to contain. This information is not always available.
#     Starting with Orthanc 1.2.0, TransferSyntax and SopClassUid respectively stores the transfer syntax UID and the SOP class UID of DICOM instances, in order to speed up the access to this information.
#     RemoteIP (new in Orthanc 1.4.0): The IP address of the remote SCU (for REST API and DICOM protocol).
#     CalledAET (new in Orthanc 1.4.0): The AET that was called by the SCU, which normally matches the AET of Orthanc (for DICOM protocol).
#     HttpUsername (new in Orthanc 1.4.0): The username that created the instance (for REST API).
#     PixelDataOffset (new in Orthanc 1.9.1): Offset (in bytes) of the Pixel Data DICOM tag in the DICOM file, if available.


def OnChange(changeType, level, resource):


    
    if changeType == orthanc.ChangeType.NEW_INSTANCE:
    
        print('A new instance was uploaded: %s' % resource)
        print(WORKLIST_DIR)
        simpletags = json.loads(orthanc.RestApiGet('/instances/%s/simplified-tags' % resource))
        # pp.pprint(simpletags)
        # Don't want to do this unless there is an Accession number, and a Worklist Directory, could add config option to enable this or not.
        if ('AccessionNumber' in simpletags and simpletags['AccessionNumber'] and WORKLIST_DIR):
        
            Origin = orthanc.RestApiGet('/instances/%s/metadata/Origin' % resource).decode()
            
            print(Origin)
            # Modifies only the tags that are specified below, could be a config in orthanc.json
            if (Origin != 'Plugins'):
            
                print ("Modifying instance " + str(resource) + ' via Plugin from MWL with Origin' + Origin )
                MWL = MWLGetListForAccession(simpletags['AccessionNumber'])
                MWL = json.loads(MWL)
                
                if (len(MWL) > 0):
                
                    MWL = MWL[0]
                    print("MWL File:")
                    print(json.dumps(MWL, indent = 3))
                    command = dict()
                    command['Force'] = True
                    command['Replace'] = dict()
                    command['Replace']['OperatorsName'] = MWL['OperatorsName']
                    command['Replace']['StudyDescription'] = MWL['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepDescription']
                    command['Replace']['PatientAddress'] = MWL['PatientAddress']
                    command['Replace']['PatientTelecomInformation'] = MWL['PatientTelecomInformation']
                    command['Replace']['MedicalAlerts'] = MWL['MedicalAlerts']
                    command['Replace']['Allergies'] = MWL['Allergies']
                    command['Replace']['ReferringPhysicianIdentificationSequence'] = MWL['ReferringPhysicianIdentificationSequence']
                    command['Replace']['StudyID'] = MWL['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepID']
                    command['Keep'] = ["StudyInstanceUID", "SeriesInstanceUID","SOPInstanceUID"]
                    print("Modification Params")
                    print(json.dumps(command, indent = 3))
                    modified = orthanc.RestApiPost('/instances/' + resource + '/modify', json.dumps(command)) # the raw instances, bytes be
                    modifiedId = json.loads(orthanc.RestApiPost('/instances/', modified))['ID'] # This should just overwrite the existing instance.
                    print(modifiedId)
                    #reconstruct = orthanc.RestApiPost('/studies/' + resource + '/reconstruct','{"Asynchronous":true}')
                    #print(reconstruct)
            
                    if ('StudyInstanceUID' in simpletags and simpletags['StudyInstanceUID'] is not None):
        
                        print ('Modified Insstance has StudyInstanceUID:  ' + simpletags['StudyInstanceUID'])
                else:
                    print("Uploaded Instance has no MWL for the AccessionNumber")
                        
    elif changeType == orthanc.ChangeType.STABLE_STUDY:
    
        study = json.loads(orthanc.RestApiGet('/studies/%s' % resource))
        accession = study['MainDicomTags']['AccessionNumber']
        reconstruct = orthanc.RestApiPost('/studies/' + resource + '/reconstruct','{"Asynchronous":true}')
        if (WORKLIST_DIR and accession):
            filenamewl = WORKLIST_DIR + accession  + '.wl'
            print("Study Stable, MWLfile:  " + filenamewl)
            if (os.path.exists(str(filenamewl))):
    #             filenamewl = PathToWorklist + study['MainDicomTags']['AccessionNumber'] + '.wl'
    #             print(filenamewl)
    #             print('Study Stable, MWL exists, modifying study.')
    #             mwlquery = dict()
    #             mwlquery["AccessionNumber"] = study['MainDicomTags']['AccessionNumber']
    #             mwlquery["ScheduledProcedureStepSequence"] = ''
    #             mwlquery["OperatorsName"] = ''
    #             mwlquery["PatientTelecomInformation"] = ''
    #             mwlquery["PatientAddress"] = ''
    #             mwlquery["ReferringPhysicianIdentificationSequence"] = ''
    #             mwlquery["MedicalAlerts"] = ''
    #             mwlquery["Allergies"] = ''
    #             MWL = json.loads(orthanc.RestApiPost('/modalities/SELF/find-worklist',json.dumps(mwlquery)))
    #             #os.remove(filenamewl) # Don't remove it for now, have the tech do that manually.
    #             # print(MWL)
    #             # Should be just one, if any
    #             MWL = MWL[0]
    #             command = dict()
    #             command['Force'] = True
    #             command['Replace'] = dict()
    #             command['Replace']['OperatorsName'] = MWL['OperatorsName']
    #             command['Replace']['StudyDescription'] = MWL['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepDescription']
    #             command['Replace']['PatientTelecomInformation'] = MWL['PatientTelecomInformation']
    #             command['Replace']['PatientAddress'] = MWL['PatientAddress']
    #             command['Replace']['ReferringPhysicianIdentificationSequence'] = MWL['ReferringPhysicianIdentificationSequence']
    #             command['Replace']['MedicalAlerts'] = MWL['MedicalAlerts']
    #             command['Replace']['Allergies'] = MWL['Allergies']
    #             command['Replace']['StudyID'] = MWL['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepID']
    #             command['Keep'] = ["StudyInstanceUID", "SeriesInstanceUID","SOPInstanceUID"]
    #             #print(command)
    #             modified = json.loads(orthanc.RestApiPost('/studies/' + resource + '/modify', json.dumps(command)))
    #             #print(modified)
    #             reconstruct = orthanc.RestApiPost('/studies/' + resource + '/reconstruct','{"Asynchronous":true}')
    #             #print(reconstruct)
                print("Marking Complete, Accession:  " + str(accession))
                MarkOrderAsCompleted(accession)
                SendNotification("STUDY UPLOADED", "A study with the following tags has had images uploaded:  " + json.dumps(study, indent = 3))
            
            else:
                print("Study Stable, No MWL")
        else:
            print("Either no MWL folder or no Accession for Study")
            
    elif changeType == orthanc.ChangeType.ORTHANC_STARTED:
    
        orthanc.LogWarning('DICOM server Started, in Python Plug-In')

    elif changeType == orthanc.ChangeType.ORTHANC_STOPPED:
    
        orthanc.LogWarning('Stopping Orthanc, in Plug-In')
        
orthanc.RegisterOnChangeCallback(OnChange)

# FOR COMPLETING A STUDY USING EXTRA DATA FROM THE MWL.
# [Tech] => 1:SDS
# [Study] => stdClass Object
#     (
#         [ID] => 0a7ca6bf-d9b4b791-0386fc6f-f093b735-b5a17618
#         [IsStable] => 1
#         [LastUpdate] => 20210521T172024
#         [MainDicomTags] => stdClass Object
#             (
#                 [AccessionNumber] => DEVACC00000062
#                 [InstitutionName] => Cayman Medical Ltd.
#                 [ReferringPhysicianName] => 0001:Scotti^Stephen
#                 [StudyDate] => 20210520
#                 [StudyDescription] => Daily QC
#                 [StudyID] => 0
#                 [StudyInstanceUID] => 1.3.6.1.4.1.56016.1.1.154.1621531310
#                 [StudyTime] => 122526
#             )
# 
#         [ParentPatient] => 4a46f887-cbc8e696-54c9d8ff-8b225177-acbf3a81
#         [PatientMainDicomTags] => stdClass Object
#             (
#                 [PatientBirthDate] => 20210415
#                 [PatientID] => 5388.668940371
#                 [PatientName] => QC^Esaote G-scan
#                 [PatientSex] => M
#             )
# 
#         [Series] => Array
#             (
#                 [0] => eda6bc3b-da63b15d-1c1c6aff-11e7aef6-4e75753b
#             )
# 
#         [Type] => Study
#     )
# 
# Used TransferSyntax: Little Endian Explicit
# (0008,0005) CS [ISO_IR 192]                             #  10, 1 SpecificCharacterSe  IN ESAOTE
# (0008,0050) SH [DEVACC00000023]                         #  14, 1 AccessionNumber IN ESAOTE
# (0008,0060) CS [MR]                                     #   2, 1 Modality ALSO IN ScheduledProcedureStepSequence
# (0008,0090) PN [0003:Gaudreault^Pascal^^Dr.^^M.D.]      #  34, 1 ReferringPhysicianName  IN ESAOTE
# (0008,0096) SQ (Sequence with explicit length #=1)      # 146, 1 ReferringPhysicianIdentificationSequence
#   (fffe,e000) na (Item with explicit length #=3)          # 138, 1 Item
#     (0008,0080) LO [Cayman Medical Ltd.]                    #  20, 1 InstitutionName
#     (0040,1101) SQ (Sequence with explicit length #=1)      #  48, 1 PersonIdentificationCodeSequence
#       (fffe,e000) na (Item with explicit length #=3)          #  40, 1 Item
#         (0008,0100) SH [0003]                                   #   4, 1 CodeValue
#         (0008,0102) SH [L]                                      #   2, 1 CodingSchemeDesignator
#         (0008,0104) LO [Local Code]                             #  10, 1 CodeMeaning
#       (fffe,e00d) na (ItemDelimitationItem for re-encoding)   #   0, 0 ItemDelimitationItem
#     (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
#     (0040,1103) LO [KY-^WPN^PH^dr.pascalgaudreault@outlook.com] #  42, 1 PersonTelephoneNumbers
#   (fffe,e00d) na (ItemDelimitationItem for re-encoding)   #   0, 0 ItemDelimitationItem
# (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
# (0008,1030) LO [MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST] #  42, 1 StudyDescription
# (0008,1070) PN [OperatorsName]                          #  14, 1 OperatorsName
# (0008,1080) LO (no value available)                     #   0, 0 AdmittingDiagnosesDescription  IN ESAOTE
# (0008,1110) SQ (Sequence with explicit length #=0)      #   0, 1 ReferencedStudySequence
# (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
# (0010,0010) PN [Scotti^Stephen^D]                       #  16, 1 PatientName  IN ESAOTE
# (0010,0020) LO [DEV0000028]                             #  10, 1 PatientID  IN ESAOTE
# (0010,0030) DA [19571116]                               #   8, 1 PatientBirthDate  IN ESAOTE
# (0010,0040) CS [M]                                      #   2, 1 PatientSex  IN ESAOTE
# (0010,1020) DS (no value available)                     #   0, 0 PatientSize  IN ESAOTE
# (0010,1030) DS (no value available)                     #   0, 0 PatientWeight  IN ESAOTE
# (0010,1040) LO [^^Minneapolis^MN^55414^US]              #  26, 1 PatientAddress
# (0010,2000) LO (no value available)                     #   0, 0 MedicalAlerts
# (0010,2110) LO (no value available)                     #   0, 0 Allergies
# (0010,2155) LT [-^WPN^PH^]                              #  10, 1 PatientTelecomInformation
# (0010,2180) SH (no value available)                     #   0, 0 Occupation  IN ESAOTE
# (0010,21b0) LT [No Order]                               #   8, 1 AdditionalPatientHistory  IN ESAOTE
# (0010,4000) LT (no value available)                     #   0, 0 PatientComments  IN ESAOTE
# (0020,000d) UI [1.3.6.1.4.1.56016.0.1.0.56.1624322066]  #  38, 1 StudyInstanceUID
# (0020,4000) LT [ImageComments]                          #  14, 1 ImageComments
# (0032,1060) LO [MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST] #  42, 1 RequestedProcedureDescription
# (0032,1064) SQ (Sequence with explicit length #=1)      #  52, 1 RequestedProcedureCodeSequence   IN ESAOTE
#   (fffe,e000) na (Item with explicit length #=3)          #  44, 1 Item
#     (0008,0100) SH [70551]                                  #   6, 1 CodeValue
#     (0008,0102) SH [CPT4]                                   #   4, 1 CodingSchemeDesignator
#     (0008,0104) LO [["70551"]]                              #  10, 1 CodeMeaning
#   (fffe,e00d) na (ItemDelimitationItem for re-encoding)   #   0, 0 ItemDelimitationItem
# (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
# (0040,0100) SQ (Sequence with explicit length #=1)      # 218, 1 ScheduledProcedureStepSequence  IN ESAOTE
#   (fffe,e000) na (Item with explicit length #=8)          # 210, 1 Item
#     (0008,0050) SH [DEVACC00000023]                         #  14, 1 AccessionNumber
#     (0008,0060) CS [MR]                                     #   2, 1 Modality
#     (0040,0001) AE [SCOTTI_CUSTOM]                          #  14, 1 ScheduledStationAETitle
#     (0040,0002) DA [20210520]                               #   8, 1 ScheduledProcedureStepStartDate
#     (0040,0003) TM [122500]                                 #   6, 1 ScheduledProcedureStepStartTime
#     (0040,0007) LO [MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST] #  42, 1 ScheduledProcedureStepDescription
#     (0040,0008) SQ (Sequence with explicit length #=1)      #  52, 1 ScheduledProtocolCodeSequence
#       (fffe,e000) na (Item with explicit length #=3)          #  44, 1 Item
#         (0008,0100) SH [70551]                                  #   6, 1 CodeValue
#         (0008,0102) SH [CPT4]                                   #   4, 1 CodingSchemeDesignator
#         (0008,0104) LO [["70551"]]                              #  10, 1 CodeMeaning
#       (fffe,e00d) na (ItemDelimitationItem for re-encoding)   #   0, 0 ItemDelimitationItem
#     (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem
#     (0040,0009) SH [0001]                                   #   4, 1 ScheduledProcedureStepID
#   (fffe,e00d) na (ItemDelimitationItem for re-encoding)   #   0, 0 ItemDelimitationItem
# (fffe,e0dd) na (SequenceDelimitationItem for re-encod.) #   0, 0 SequenceDelimitationItem


# Function to basically overwrite a study with tags from an MWL file, useful to assign a study without an order to an order, or outside study to an order.
# Request body has the Tech, the Study Tags and the MWL tags.  Seems to work if the old study is deleted, probably because command['Keep'] = ['SeriesInstanceUID','SOPInstanceUID'] and StudyInstanceID is changed.

def AssignMWL(output, uri, **request):

    response = dict()
    query = json.loads(request['body'])
    studyuuid = query['Study']['ID']
    MWL = query['MWL']
    Tech = query['Tech']
    command = dict()
    command['Replace'] = dict()
    command['Force'] = True
    for (key, value) in  MWL.items():
        print(key)
        print(value)
        if (key == 'ScheduledProcedureStepSequence'):  key = 'RequestAttributesSequence'
        if (key != 'RequestedProcedureCodeSequence'):
            command['Replace'][key] = value
    # delete the MWL, otherwise the OnChange script will execute.
    WORKLIST_DIR = json.loads(orthanc.GetConfiguration())['Worklists']['Database']
    filenamewl = WORKLIST_DIR + '/' + MWL['AccessionNumber'] + '.wl'
    if (os.path.exists(filenamewl)):  os.remove(filenamewl)
    command['Replace']['OperatorsName'] = Tech
    if ('AccessionNumber' in MWL): command['Replace']['AccessionNumber'] = MWL['AccessionNumber']
    if ('AdditionalPatientHistory' in MWL): command['Replace']['AdditionalPatientHistory'] = MWL['AdditionalPatientHistory']
    if ('AdmittingDiagnosesDescription' in MWL): command['Replace']['AdmittingDiagnosesDescription'] = MWL['AdmittingDiagnosesDescription']
    if ('Allergies' in MWL): command['Replace']['Allergies'] = MWL['Allergies']
    if ('ImageComments' in MWL): command['Replace']['ImageComments'] = MWL['ImageComments']
    if ('MedicalAlerts' in MWL): command['Replace']['MedicalAlerts'] = MWL['MedicalAlerts']
    if ('Modality' in MWL): command['Replace']['Modality'] = MWL['Modality']
    if ('Occupation' in MWL): command['Replace']['Occupation'] = MWL['Occupation']
    if ('PatientAddress' in MWL): command['Replace']['PatientAddress'] = MWL['PatientAddress']
    if ('PatientBirthDate' in MWL): command['Replace']['PatientBirthDate'] = MWL['PatientBirthDate']
    if ('PatientComments' in MWL): command['Replace']['PatientComments'] = MWL['PatientComments']
    if ('PatientID' in MWL): command['Replace']['PatientID'] = MWL['PatientID']
    if ('PatientName' in MWL): command['Replace']['PatientName'] = MWL['PatientName']
    if ('PatientSex' in MWL): command['Replace']['PatientSex'] = MWL['PatientSex']
    if ('PatientTelecomInformation' in MWL): command['Replace']['PatientTelecomInformation'] = MWL['PatientTelecomInformation']
    if ('PatientWeight' in MWL): command['Replace']['PatientWeight'] = MWL['PatientWeight']
    if ('ReferringPhysicianIdentificationSequence' in MWL): command['Replace']['ReferringPhysicianIdentificationSequence'] = MWL['ReferringPhysicianIdentificationSequence']
    if ('ReferringPhysicianName' in MWL): command['Replace']['ReferringPhysicianName'] = MWL['ReferringPhysicianName']
    if ('RequestedProcedureCodeSequence' in MWL): command['Replace']['RequestedProcedureCodeSequence'] = MWL['RequestedProcedureCodeSequence']
    if ('StudyDescription' in MWL): command['Replace']['StudyDescription'] = MWL['StudyDescription']
    if ('StudyInstanceUID' in MWL): command['Replace']['StudyInstanceUID'] = MWL['StudyInstanceUID']
    command['Keep'] = ['SeriesInstanceUID','SOPInstanceUID']
    print(json.dumps(command))
    imagecount = 0
    for seriesuuid in query['Study']['Series']:
        series = json.loads(orthanc.RestApiGet('/series/%s' % seriesuuid))
        instances = series['Instances']
        for instanceuuid in instances:
            imagecount = imagecount + 1
            modified = orthanc.RestApiPost('/instances/' + instanceuuid + '/modify', json.dumps(command))
            saved = orthanc. RestApiPost('/instances/', modified)
    response["status"] = str(imagecount) + " instances modified"
    delete = orthanc.RestApiDelete('/studies/' + studyuuid)
    response["delete"] = delete
    MarkOrderAsCompleted(MWL['AccessionNumber'])
    output.AnswerBuffer(json.dumps(response), 'application/json')
    

orthanc.RegisterRestCallback('/studies/AssignMWL', AssignMWL)



# SEVERAL PACKAGES HERE

# 1. curl http://localhost:8042/pydicom/af1c0b10-c44ac936-74aa66e8-0c4463e0-2e98c65e
#    Demo for using Pydicom, can be extended.  Bascially returns a dump2dcm.

# 2. curl http://localhost:8042/get-configs/ALL
#    Gets Orthanc config file params, all or by a particular group;

# 3. curl http://localhost:8042/sendemail -d '{"subject":"This is a test","body":"string"}'
#    Sends an email.  Could be extended for HTML mails and other features, also callable from with the script.

# 4. curl http://localhost:8042/studies/page -d '{"Query":{"PatientName":"**","PatientBirthDate":"","PatientSex":"","PatientID":"","AccessionNumber":"","StudyDescription":"**","ReferringPhysicianName":"**","StudyDate":""},"Level":"Study","Expand":true,"MetaData":{},"pagenumber":1,"itemsperpage":5,"sortparam":"StudyDate","reverse":1,"widget":1}'
#    PAGINATION SCRIPT, SEE BELOW FOR DETAILS

# 5. curl -k -X POST -d '["AccessionNumber"]' http://localhost:8042/mwl/file/delete
#    Deletes .wl file in the Worklists Folder, could be extended to delete multiple from array of values

# 6. curl --request POST --url http://localhost:8042/mwl/file/make --data '{"MediaStorageSOPClassUID":"MediaStorageSOPClassUID","CharSet":"CharSet","AccessionNumber":"AccessionNumber","Modality":"Modality","RequestingPhysician":"RequestingPhysician","PatientName":"PatientName","PatientID":"PatientID","PatientBirthDate":"PatientBirthDate","PatientSex":"PatientSex","MedicalAlerts":"MedicalAlerts","Allergies":"Allergies","AdditionalPatientHistory":"AdditionalPatientHistory","StudyInstanceUID":"StudyInstanceUID","RequestingPhysician":"RequestingPhysician","RequestedProcedureDescription":"RequestedProcedureDescription","ScheduleStationAETitle":"ScheduleStationAETitle","ScheduledProcedureStepStartDate":"ScheduledProcedureStepStartDate","ScheduledProcedureStepStartTime":"ScheduledProcedureStepStartTime","RequestedProcedureID":"RequestedProcedureID","RequestedProcedurePriority":"RequestedProcedurePriority"}'

#    Creates a MWL .wl files and a .txt file in the Worlists Folder with a name equal to the Accession Number.  Can be
#    customized to meet needs

# 7. curl -k http://localhost:8042/pdfkit/htmltopdf -d '{"html":"This is a test","method":"string"}'
#    Creates a PDF file from raw HTML string using wkhtmltopdf pdfkit wrapper.  Requires the pip module and wkhtmltopdf installed on the system.  The Python Plugin has to be compiled with that PIP module also.

# 8. curl -k http://localhost:8042/studies/arrayIDs -d '["6efb3ff2-4cd16ca1-35cdb247-2d1c5f78-d6ba584e","79de0218-30258875-1adaa569-f71944db-a88eef7c"]'
#    Gets Array of Expanded study data for an array of uuid's, including the instance count and modalities

# 9. curl -k https://cayman.medical.ky/pacs-1/patient/studycounts -d '["DEV0000001","DEV0000002"]'
#    Gets study counts for a patient ID, or array of ID's

# GET DUMP2DCM VIA PYDICOM FOR INSTANCE.

# http://localhost:8042/pydicom/af1c0b10-c44ac936-74aa66e8-0c4463e0-2e98c65e

def DecodeInstance(output, uri, **request):

    if request['method'] == 'GET':
        # Retrieve the instance ID from the regular expression (*)
        instanceId = request['groups'][0]
        # Get the content of the DICOM file
        f = orthanc.GetDicomForInstance(instanceId)
        # Parse it using pydicom
        dicom = pydicom.dcmread(io.BytesIO(f))
        # Return a string representation the dataset to the caller
        output.AnswerBuffer(str(dicom), 'text/plain')
    else:
        output.SendMethodNotAllowed('GET')

orthanc.RegisterRestCallback('/pydicom/(.*)', DecodeInstance)  # (*)


# GET ORTHANC CONFIGURATION
# curl https://sias.dev:8000/api/get-configs/ALL
# curl https://sias.dev:8000/api/get-configs/DicomModalities

def OnRest(output, uri, **request):

    try:
        config = json.loads(orthanc.GetConfiguration())
        param = request['groups'][0]
        if (param != "ALL"):
            value = config[param]
        else:
            value = config
        print(json.dumps(value, indent = 3))
        output.AnswerBuffer(json.dumps(value, indent = 3), 'application/json')

    except Exception as e:
        response = dict()
        response['error'] = str(e)
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/get-configs/(.*)', OnRest)


# EMAIL NOTIFICATION FUNCTION, can be called from within script or via REST callback
# curl http://localhost:8042/sendemail -d '{"subject":"This is a test","body":"string"}'

def SendNotification(subject, body):

    msg = MIMEText(body)
    msg['Subject'] = subject
    msg['From'] = ""
    msg['To'] = ""
#     msg['Cc'] = ""
    context = ssl.create_default_context()
    server = smtplib.SMTP_SSL('', 465, context)
    server.login("", '')
    server.sendmail("", "", msg.as_string())
    server.quit()

def SendEmail(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    response = dict()
    payload = json.loads(request['body'])
    subject = payload['subject']
    body = payload['body']
    try:
        SendNotification(subject, body)
        response['status'] = "SUCCESS"
    except Exception as e:
        response['status'] = str(e)
    output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
orthanc.RegisterRestCallback('/sendemail', SendEmail)

# BEGINNING OF STUDIES/PAGE, TAGROUP IS NICE TO HAVE JUST GENERALLY.

# curl http://localhost:8042/studies/page -d '{"Query":{"PatientName":"**","PatientBirthDate":"","PatientSex":"","PatientID":"","AccessionNumber":"","StudyDescription":"**","ReferringPhysicianName":"**","StudyDate":""},"Level":"Study","Expand":true,"MetaData":{},"pagenumber":1,"itemsperpage":5,"sortparam":"StudyDate","reverse":1,"widget":1}'

# Typical response is like below, where the first element of the array is the "pagination object", and the following elements are the Expanded Study data, with imagecount and modalities added.  Modalities is an array.

# [
#    {
#       "count": 11,
#       "widget": "<div data-url = \"/studies/page \" class = \"paginator\"><a data-page = \"1\" class = \"pageactive\" href=\"\">1</a><a data-page = \"2\" class = \"\"  href=\"\">2</a><a data-page = \"3\" class = \"\"  href=\"\">3</a><a data-page = \"4\" class = \"\"  href=\"\">4</a><a data-page = \"5\" class = \"\"  href=\"\">5</a><a data-page = \"6\" class = \"\"  href=\"\">6</a> ... <a data-page = \"11\" class = \"\" href=\"\">11</a><span class = \"totalperpage\"> Total per page:  1</span></div>",
#       "results": 1,
#       "pagenumber": 1,
#       "offset": 0,
#       "limit": 1
#    },
#    {
#       "IsStable": true,
#       "LastUpdate": "20200629T170852",
#       "PatientMainDicomTags": {
#          "PatientBirthDate": "19571116",
#          "PatientSex": "M",
#          "PatientID": "DEV0000001",
#          "OtherPatientIDs":"OtherPatientIDs",
#          "PatientName": "SCOTTI^STEPHEN^D^^"
#       },
#       "Series": [
#          "e46bfef4-2b166666-468cc957-4b942aa8-3a5c6ef8"
#       ],
#       "modalities": [
#          "CR"
#       ],
#       "ParentPatient": "fa21ff2d-33e9b60a-daedf6a0-64d018da-682fd0a4",
#       "MainDicomTags": {
#          "AccessionNumber": "DEVACC00000006",
#          "StudyDate": "20190829",
#          "StudyDescription": "XR HIP LT 1 VW",
#          "InstitutionName": "MHealth CSC",
#          "ReferringPhysicianName": "0002^Talanow^Roland",
#          "RequestingPhysician": "2VASKE^SHANNON^M^^",
#          "StudyTime": "090425",
#          "StudyID": "UC4839619",
#          "StudyInstanceUID": "2.16.840.1.114151.1052214956401694179114379854103077382390190829"
#       },
#       "Type": "Study",
#       "ID": "e8263ed6-56adfc56-a9951260-db8c21f3-c78d7103",
#       "imagecount": 1,
#       "Metadata": {
#          "LastUpdate": "20200629T170852"
#       }
#    }
# ]

def GetTagGroupFromKey(key):

    lookup =    {

    "AccessionNumber": "MainDicomTags",
    "StudyDate": "MainDicomTags",
    "AccessionNumber": "MainDicomTags",
    "StudyDescription": "MainDicomTags",
    "InstitutionName": "MainDicomTags",
    "ReferringPhysicianName": "MainDicomTags",
    "RequestingPhysician": "MainDicomTags",
    "StudyTime": "MainDicomTags",
    "StudyID": "MainDicomTags",
    "StudyInstanceUID": "MainDicomTags",
    "PatientBirthDate": "PatientMainDicomTags",
    "PatientSex": "PatientMainDicomTags",
    "PatientID": "PatientMainDicomTags",
    "PatientName": "PatientMainDicomTags"
    }
    return lookup[key]

# returns the path for a study from the answers loop, used to construct and path for the metadata query.

def GetPath(resource):
    return '/studies/%s' % resource['ID']

# Function to recursively search down tag hierarchy to find matches from the query['Tags'] passed in to studies/find
# Skip the modality tag because that takes too long for a common search, handled further down  in code when modalities
# and image counts are caluculated.
# Not currently used.
def CheckTagLevel(tags,dictlist):

        print(tags)
        print(dictlist)
        for tagitem, value in dictlist:

            if (isinstance(value, str)):
                if tagitem in tags:
                    print(tags[tagitem] + ' ' + value)
                    if (tags[tagitem] != value):
                        return False
                    else:
                        continue
                else:
                    return False
            elif (tagitem in tags.keys()):  #must be a dict
                if (CheckTagLevel(tags[tagitem][0],value.items()) == True):
                    continue
                else:
                    return False
            else:
                return False
        return True


def FindWithMetadata(output, uri, **request):

    # The "/tools/find" route expects a POST method
    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        response = dict();
        # Check the Level and Generate an error response if not a Study
        # Parse the query provided by the user, and backup the "Expand" field
        query = json.loads(request['body'])
        if query['Level'] != "Study":
            response["error"] = "Can only Query Studies"
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
        elif ('pagenumber' not in query) or ('itemsperpage' not in query) or not (isinstance(query['pagenumber'], int)) or not (isinstance(query['itemsperpage'], int)):
            response["error"] = "Page Number and/or Items Per Page Error"
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
        else:
            userprofilejwt = json.loads(request['headers']['userprofilejwt'])
# userprofilejwt
# {
# 'doctor_id': '0001',
# 'ip': '172.18.0.1',
# 'patientid': '0001',
# 'reader_id': '0001',
# 'user_email': '',
# 'user_id': '1',
# 'user_name': '',
# 'user_roles': [1, 2, 3, 4, 5, 6, 7, 8]
# } 
            # For loading all studies for a PatientID, zero out the Referring Physician and del the flag
            reader_id = userprofilejwt.get('reader_id') or ''
            if ('LoadALL' in query['Query']):
            
                response['ReferringPhysicianName'] = "Set to *"
                query['Query']['ReferringPhysicianName'] = "*"
                response['PatientID'] = "Set to"  + str(query['Query']['PatientID'])
                del query['Query']['LoadALL']
            # If they have a reader_id they have global access, leave query unchanged
            elif (reader_id != '' and reader_id is not None):
                response['ReferringPhysicianName'] = "Reader, Unchanged"
                response['PatientID'] = "Reader, Unchanged"
            else:
                doctor_id = userprofilejwt.get('doctor_id') or ''
                if (doctor_id != '' and doctor_id is not None):
                    query['Query']['ReferringPhysicianName'] = str(doctor_id) + ':*'
                    response['ReferringPhysicianName'] = "Set To " + str(doctor_id) + ':*'
                else:
                    response['ReferringPhysicianName'] = "Unchanged"

                PatientID = userprofilejwt.get('patientid') or ''
                if (PatientID != '' and PatientID is  not None):
                    query['Query']['PatientID'] = str(PatientID)
                    response['PatientID'] = "Set To " + str(PatientID)
                else:
                    response['PatientID'] = "Unchanged"
            # pprint.pprint(query)  
            # Allows specifying the Modality in the Query, or using the Tag ["0008,0060"].  Modality is handled as an exception at the Series Level instead of searching tags.           
            modality = False
            if 'Tags' in query and "0008,0060" in query['Tags']:
                modality  = query['Tags']["0008,0060"]
                del query['Tags']["0008,0060"]
            if 'Modality' in query['Query']:
                modality  = query['Query']['Modality']
                del query['Query']['Modality']
            if 'Expand' in query:
                originalExpand = query['Expand']
            else:
                originalExpand = False

            # Call the core "/tools/find" route
            query['Expand'] = True
            answers = orthanc.RestApiPost('/tools/find', json.dumps(query))

            # Loop over the matching resources, bypass the Metadata filtering if there are no params specified (i.e. len)

            filteredAnswers = []
            # Used mostly for Study Level Metatags, ReportStatusJSON - 1024 and OutsideStudyFlag - 1025 to filter on the Report Statuses at the Study Level.
            if 'MetaData' in query and len(query['MetaData']) > 0:

                for answer in json.loads(answers):

                    try:
                        # Read the metadata that is associated with the resource
                        # Check whether the metadata matches the regular expressions
                        # that were provided in the "Metadata" field of the user request
                        metadata = json.loads(orthanc.RestApiGet('%s/metadata?expand' % GetPath(answer)))
                        for (name, pattern) in query['MetaData'].items():
                            print("name" + name + "  pattern" + pattern)
                            if name in metadata:
                                value = metadata[name]
                            else:
                                value = ''
                            if re.match(pattern, value) == None:
                                break

                            # If all the metadata matches the provided regular
                            # expressions, add the resource to the filtered answers

                            if originalExpand:
                                answer['Metadata'] = metadata
                                filteredAnswers.append(answer)
                            else: 
                                filteredAnswers.append(answer['ID'])
                    except:
                        # The resource was deleted since the call to "/tools/find"
                        pass
            else:
                filteredAnswers = json.loads(answers)
            filteredAnswers2 = filteredAnswers
            #query['Tags']
            #added section to allow searching on any instance tag at primary or secondary level in hierarchy, for SQ tags
            if 'Tags' in query and len(query['Tags']) > 0:
                filteredAnswers2 = []
                for answer in filteredAnswers:
                    instances = json.loads(orthanc.RestApiGet('/studies/' + answer["ID"] + '/instances'))
                    instance = instances[0]["ID"]
                    tags = json.loads(orthanc.RestApiGet('/instances/'+ instance + '/tags?short'))

                    matchedall = CheckTagLevel(tags, query['Tags'].items())

                    if (matchedall == True):
                        answer['matchinginstance'] = instance
                        simplifiedtags = json.loads(orthanc.RestApiGet('/instances/'+ instance + '/simplified-tags'))
                        answer['simplifiedtags'] = simplifiedtags
                        filteredAnswers2.append(answer)
                        
            studies = []
            # Section that gets the instance counts, the modalities, and filters by modalities at the Series Level, not using Tags.  Tag searches Take Forever with a lot of studies.
            for study in filteredAnswers2:
                modalities = []
                imagecount = 0
                for seriesuuid in study['Series']:
                    series = json.loads(orthanc.RestApiGet('/series/%s' % seriesuuid))
                    # print series
                    imagecount = imagecount + len(series['Instances'])
                    if series['MainDicomTags']['Modality'] not in modalities:
                        modalities.append(series['MainDicomTags']['Modality'])
                
                study['imagecount'] = imagecount
                study['modalities'] = modalities
                study.pop('Series', None) # Get rid of the Series since not needed in response.
                # print(modality if modality else "Modality Not Searched For")
                if modality:
                    if modality in study['modalities']:
                        studies.append(study)
                else:
                    studies.append(study)
                    
            # Just used the tools/find results if no Metadata
            #    curl -s https://demo.orthanc-server.com/studies/27f7126f-4f66fb14-03f4081b-f9341db2-53925988/instances | grep '"ID"' | head -n1
            # The globals are used in the GetSortParam function for the taggroup, sortparam and reverse
            # sortparam can be omitted, in which case it defaults to StudyDate
            global param
            if 'sortparam' in query:
                param = query['sortparam']
            else:
                param = 'StudyDate'
            global taggroup
            taggroup = GetTagGroupFromKey(query['sortparam'])
            global reverse
            reverse = query['reverse']
            # Sort the studies according to the "StudyDate" DICOM tag
            studiessorted = sorted(studies, key = GetSortParam, reverse=reverse)
            count = len(studiessorted)
            #default for pagenumber
            pagenumber = 1
            if 'pagenumber' in query:
                pagenumber = query['pagenumber']
            #default for itemsperpage if not in the query, which it should be
            itemsperpage = 10
            if 'itemsperpage' in query:
                itemsperpage = query['itemsperpage']
            limit = itemsperpage
            offset = (pagenumber -1) * itemsperpage
            #offset = 0
            #if 'offset' in query:
            #    offset = query['offset']
            #limit = 0
            #if 'limit' in query:
            #    limit = query['limit']
            # Truncate the list of studies
            # Pass in 0 for limit if you want to just want to list all from the offset
            if limit == 0:
                studiessorted = studiessorted[offset : ]
            else:
                studiessorted = studiessorted[offset : offset + limit]
                
            url = '/studies/page'

            # Return the truncated list of studies

            widget = ""
            if 'widget' in query:
                widget = CreateWidget(limit, pagenumber, url , count)
                #studies.insert(0,{"paginationwidget":widget})
            studiessorted.insert(0, {"widget": widget, "results":len(studiessorted), "limit":limit, "offset":offset, "pagenumber":pagenumber, "count":count,"PatientID":response['PatientID'],"ReferringPhysicianName":response['ReferringPhysicianName']})
            # Return the filtered answers in the JSON format
            if int(platform.python_version_tuple()[0]) < 3:
                logging.warning("Suggest using Python 3.x.x, using:  " + platform.python_version())
            else:
                print(platform.python_version_tuple()[0])
                logging.warning("Suggest using Python 3.x.x, using:  " + platform.python_version())
            output.AnswerBuffer(json.dumps(studiessorted, indent = 3), 'application/json')

#param is the tag to sortby
#taggroup is the taggroup for the param
#defined as globals in FindWithMetadata

def ceildiv(a, b):
    return -(-a // b)
# Could extend this such that the passed in widget id/number is a selection of preconfigured widgets for pagination, since it include HTML markup.
def CreateWidget(limit, pagenumber, url, count):

    total_pages = ceildiv(count, limit);
    links = '<div data-url = "' + url +' " class = "paginator">'
    if (total_pages >= 1 and pagenumber <= total_pages):
        active = "";
        if pagenumber == 1:
            active = "pageactive"
        links += '<a data-page = "1" class = "' + active  + '" href="">1</a>'
        active = ""
        i = max(2, pagenumber - 5)
        if i > 2:
            links += " ... "
        for i in range(i, min(pagenumber + 6, total_pages)):
            if pagenumber == i:
                active = "pageactive"
            links += '<a data-page = "' + str(i) + '" class = "' + active  + '"  href="">' + str(i) + '</a>'
            active = ""
        if i != total_pages:
            links += " ... "
        if pagenumber == total_pages:
            active = "pageactive"
        links += '<a data-page = "' + str(total_pages) + '" class = "' + active + '" href="">' + str(total_pages) + '</a>'

    links += '<span class = "totalperpage"> Total per page:  ' + str(limit) + '</span>'
    links += '</div>'
    # Sends an e-mail
    # SendNotification("/studies/find request", "Just an notification")
    return links

def GetSortParam(study):
    if param in study[taggroup]:
        return study[taggroup][param]
    else:
        return ''

orthanc.RegisterRestCallback('/studies/page', FindWithMetadata)


# BEGINNING OF /mwl/file/delete

# curl -k -X POST -d '["AccessionNumber"]' http://localhost:8042/mwl/file/delete

def  DeleteMWLByAccession(output, uri, **request):
    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        response = dict();
        try:
            data = json.loads(request['body'])
            # the accession_number to delete, for the filename
            accession = data[0]
            WORKLIST_DIR = json.loads(orthanc.GetConfiguration())['Worklists']['Database'] + '/'
            filenametxt = WORKLIST_DIR + accession + '.txt'
            filenamewl = WORKLIST_DIR + accession + '.wl'
            if os.path.exists(filenametxt):
                os.remove(filenametxt)
                response['filenametxt'] = "true"
            else:
                response['filenametxt'] = "false"
            if os.path.exists(filenamewl):
                os.remove(filenamewl)
                response['filenamewl'] = "true"
            else:
                response['filenamewl'] = "false"
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

        except Exception as e:

            response['error'] = str(e)
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/mwl/file/delete(.*)', DeleteMWLByAccession)


# BEGINNING OF /mwl/file/make, MWL MAKER.

# Sample curl calls, JSON vs. HL7, RAW isn't setup, can just pass the shortnames without RAW since HL7 won't be a key.

# curl --request POST --url http://localhost:8042/mwl/file/make --data '{"RAW":{"MediaStorageSOPClassUID":"MediaStorageSOPClassUID","CharSet":"CharSet","AccessionNumber":"AccessionNumber","Modality":"Modality","RequestingPhysician":"RequestingPhysician","PatientName":"PatientName","PatientID":"PatientID","PatientBirthDate":"PatientBirthDate","PatientSex":"PatientSex","MedicalAlerts":"MedicalAlerts","Allergies":"Allergies","AdditionalPatientHistory":"AdditionalPatientHistory","StudyInstanceUID":"StudyInstanceUID","RequestingPhysician":"RequestingPhysician","RequestedProcedureDescription":"RequestedProcedureDescription","ScheduleStationAETitle":"ScheduleStationAETitle","ScheduledProcedureStepStartDate":"ScheduledProcedureStepStartDate","ScheduledProcedureStepStartTime":"ScheduledProcedureStepStartTime","RequestedProcedureID":"RequestedProcedureID","RequestedProcedurePriority":"RequestedProcedurePriority"},"HL7":""}'

# curl -k http://localhost:8042/mwl/file/make -d {'HL7': 'MSH|^~\\&|RIS^cayman.medical.ky^DNS|Cayman Medical Ltd.^cayman.medical.ky^DNS|RIS-ORTHANC^cayman.medical.ky^DNS|RIS-ORTHANC^cayman.medical.ky^DNS|20210621211347||OMI^O23^OMI_O23|2021062121134754936|P|2.8|1||AL|AL|CYM|UNICODE|en\rPID|1|DEV0000028|DEV0000028^^^Cayman Medical Ltd.^MR|DEV0000028|Scotti^Stephen^D||19571116|M|alias||^^Minneapolis^MN^55414^US|US|US-^PRN^PH^|-^WPN^PH^|Language|S||DEV0000028^^^Cayman Medical Ltd.^MR\rPV1|1|O|||||0003^Gaudreault^Pascal^^^Dr.^M.D.|0003^Gaudreault^Pascal^^^Dr.^M.D.|||||||||||||||||||||||||||||||Cayman Medical Ltd.|||||20210621081500\rORC|SC|DEVACC00000008|DEVACC00000008||SC||20210621081500||20210621211347|||0003^Gaudreault^Pascal^^^Dr.^M.D.||KY-^WPN^PH^dr.pascalgaudreault@outlook.com|20210621211347\rOBR|1|DEVACC00000008|DEVACC00000008|0001^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^LB^{"id":"1","requested_procedure_id":"0001","group_name":"Neuro","exam_length":"60","exam_name":"MRI BRAIN \\/ BRAIN STEM - WITHOUT CONTRAST","modality":"MR","code_type":"CPT4","cpt":"70551","linked_exams":"[\\"70551\\"]","total_fee":1380.6,"technical_fee":1169.28,"professional_fee":315}^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^L|R|20210621211347|||||||No Order|||0003^Gaudreault^Pascal^^^Dr.^M.D.|KY-^WPN^PH^dr.pascalgaudreault@outlook.com|||||||RAD|||20210621081500|||||||||20210621081500||||||||0001^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^LB^{"id":"1","requested_procedure_id":"0001","group_name":"Neuro","exam_length":"60","exam_name":"MRI BRAIN \\/ BRAIN STEM - WITHOUT CONTRAST","modality":"MR","code_type":"CPT4","cpt":"70551","linked_exams":"[\\"70551\\"]","total_fee":1380.6,"technical_fee":1169.28,"professional_fee":315}^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^L|0001^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^LB^{"id":"1","requested_procedure_id":"0001","group_name":"Neuro","exam_length":"60","exam_name":"MRI BRAIN \\/ BRAIN STEM - WITHOUT CONTRAST","modality":"MR","code_type":"CPT4","cpt":"70551","linked_exams":"[\\"70551\\"]","total_fee":1380.6,"technical_fee":1169.28,"professional_fee":315}^MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST^L\rIPC|DEVACC00000008|0001|1.3.6.1.4.1.56016.0.1.1.25.1624328027|setScheduledProcedureStepId|MR|0001|setScheduledStationName|setScheduledProcedureStepLocation|NmrEsaote\r'}



#{
#    "mwlfilename": "AccessionNumber.wl",
#    "message": "MWL File Written:  AccessionNumber"
#    "errors": 0,
#    "txtfilename": "AccessionNumber.txt",
#    "txtfile": ""
#}

def CreateAndSave(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:

        query = json.loads(request['body'])
        print(query)
        pathtodump2dcm = shutil.which("dump2dcm")

        if not os.path.exists(WORKLIST_DIR):
            os.makedirs(WORKLIST_DIR)

        charset = "ISO_IR 192"
        #  Could just pass in JSON, which would be a lot easier actually, no need for HL7, or either one.
        if "HL7" in query:
        
            message = hl7.parse(query['HL7'])
            query = dict()
            query['AccessionNumber'] = str(message.segments('OBR')[0][3]) #field 3 of first obr
            query['Modality'] = str(message.segments('IPC')[0][5])  #field 4 of first IPC
            query['InstitutionName'] = str(message.segment('PV1')[39]) #field 39 of first PV!
            query['InstitutionAddress'] = "Address" #field 39 of first PV!
            query['ReferringPhysicianName'] = str(message.segment('PV1')[8][0][0]) + ':' + str(message.segment('PV1')[8][0][1]) + '^' + str(message.segment('PV1')[8][0][2]) + '^' + str(message.segment('PV1')[8][0][3]) + '^' + str(message.segment('PV1')[8][0][5]) # + '^' + str(message.segment('PV1')[8][0][4]) + '^' + str(message.segment('PV1')[8][0][6]) # ReferringPhysicianName, might need to truncate the last 2.
            query['PatientName'] = str(message.segment('PID')[5]) #field 5
            query['PatientID'] = str(message.segment('PID')[2]) #field 2, first R, component 0
            query['PatientBirthDate'] = str(message.segment('PID')[7])
            query['PatientSex'] = str(message.segment('PID')[8])
            query['PatientAddress'] = str(message.segment('PID')[11])
#             query['PatientTelephoneNumbers'] = str(message.segment('PID')[13])
            query['PatientTelecomInformation'] = str(message.segment('PID')[14])
            query['MedicalAlerts'] = "" # message.segments('OBR')[0][8]
            query['Allergies'] = "" # message.segments('OBR')[0][8]
            query['AdditionalPatientHistory'] = str(message.segments('OBR')[0][13]) # message.segments('OBR')[0][8];
            query['StudyInstanceUID'] = str(message.segments('IPC')[0][3]) #
            query['RequestingPhysician'] = str(message.segment('PV1')[8]) # RequestingPhysician
            query['RequestedProcedureDescription'] = str(message.segments('OBR')[0][4][0][1])
            query['ScheduledProcedureStepDescription'] = query['RequestedProcedureDescription']
            query['ScheduleStationAETitle'] = str(message.segments('IPC')[0][9])
            query['ScheduledProcedureStepStartDate'] = str(message.segments('OBR')[0][36])[:8]
            query['ScheduledProcedureStepStartTime'] = str(message.segments('OBR')[0][36])[-6:]
            query['RequestedProcedureID'] = str(message.segments('OBR')[0][4][0][0])
            query['ScheduledProcedureStepID'] = query['RequestedProcedureID']
            query['RequestedProcedurePriority'] =str(message.segments('OBR')[0][5])
            query['MediaStorageSOPClassUID'] = "NONE" # might need this
            query['PhysicianIDforSequence'] =str(message.segment('PV1')[8][0][0])
            query['PersonTelephoneNumbers'] =str(message.segments('OBR')[0][17])
            query['PersonTelecomInformation'] = str(message.segments('OBR')[0][17])
            
            query['OBR4JSON'] = str(message.segments('OBR')[0][4][0][3])
            decoded = json.loads(query['OBR4JSON'])
            query['LocalExamCode'] = decoded['requested_procedure_id']
            
            query['PatientSize'] = ''
            query['PatientWeight'] = ''
            query['Occupation'] = 'Occupation'
            query['PatientComments'] = 'PatientComments'
            query['OperatorsName'] = 'Tech^SP' # supposedly the Tech, but looks like Image Comments are Used.
            query['ImageComments'] = 'Tech:  SP'
            
            # convert to format that is compatible with JSON input also, if sent as RAW JSON it will also work.
            
            query['ScheduledProcedureStepSequence'] = dict()
            query['ScheduledProcedureStepSequence'][0] = dict()
            query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'] = dict()
            query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0] = dict()
            

            query['ScheduledProcedureStepSequence'][0]['AccessionNumber'] = query['AccessionNumber']
            query['ScheduledProcedureStepSequence'][0]['Modality'] = query['Modality']
            query['ScheduledProcedureStepSequence'][0]['ScheduleStationAETitle'] = query['ScheduleStationAETitle']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartDate'] = query['ScheduledProcedureStepStartDate']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartTime'] = query['ScheduledProcedureStepStartTime']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepDescription'] = query['ScheduledProcedureStepDescription']
            
            query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeValue'] = decoded['cpt']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodingSchemeDesignator'] = decoded['code_type']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeMeaning'] = decoded['linked_exams']
            query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepID'] = query['ScheduledProcedureStepID']
            
            query['ReferringPhysicianIdentificationSequence'] = dict()
            query['ReferringPhysicianIdentificationSequence'][0] = dict()
            query['ReferringPhysicianIdentificationSequence'][0]['InstitutionName'] = query['InstitutionName']
            query['ReferringPhysicianIdentificationSequence'][0]['PersonIdentificationCodeSequence'] = dict()
            query['ReferringPhysicianIdentificationSequence'][0]['PersonIdentificationCodeSequence'][0] = dict()
            query['ReferringPhysicianIdentificationSequence'][0]['PersonIdentificationCodeSequence'][0]['CodeValue'] = query['PhysicianIDforSequence']
            query['ReferringPhysicianIdentificationSequence'][0]['PersonTelephoneNumbers'] = query['PersonTelephoneNumbers']

        # If the request is raw JSON in the specified format we'll skip the HL7 decoding and everything should still work.
        print(json.dumps(query))
        mwl = [];

        # THESE FOLLOW THE ESAOTE G-SCAN QUERY

        mwl.append("# Dicom-Data-Set")
        mwl.append("(0002,0010) UI =LittleEndianExplicit                     # TransferSyntaxUID")
        mwl.append("")
        mwl.append("(0008,0005) CS [" +charset + "]                  # SpecificCharacterSet")
        mwl.append("(0008,0050) SH [" + query['AccessionNumber'] + "]          # AccessionNumber")
        mwl.append("(0008,0060) CS [" + query['Modality'] + "]           # Modality")
#         mwl.append("(0008,0080) LO [" + query['InstitutionName'] + "]          # InstitutionName")     # NOT IN QUERY, supplied by modality
#         mwl.append("(0008,0081) ST [" + query['InstitutionAddress'] + "]       # InstitutionAddress")  # NOT IN QUERY, supplied by modality
        
        
        # PN format is last,first,middle,prefix,suffix, HL7 format is "ID^LAST^FIRST^MIDDLE^SUFFIX^PREFIX^DEGREE"
        # Want this to be ID:Last^First^Middle^Prefix^Suffix for DICOM
        mwl.append("(0008,0090) PN [" + query['ReferringPhysicianName'] + "]  # ReferringPhysicianName")
        
        #ReferringPhysicianIdentificationSequence BEGIN
        mwl.append("(0008,0096) SQ (Sequence with explicit length)              # ReferringPhysicianIdentificationSequence")
        mwl.append("(fffe,e000) na (Item with explicit length)                  # Item")
        mwl.append("(0008,0080) LO [" + query['ReferringPhysicianIdentificationSequence'][0]['InstitutionName'] + "]           # InstitutionName")
        mwl.append("(0040,1101) SQ (Sequence with explicit length)              # PersonIdentificationCodeSequence")
        mwl.append("(fffe,e000) na (Item with explicit length)                  # Item")
        mwl.append("(0008,0100) SH [" + query['ReferringPhysicianIdentificationSequence'][0]['PersonIdentificationCodeSequence'][0]['CodeValue'] + "]    # CodeValue")
        mwl.append("(0008,0102) SH [L]           # CodingSchemeDesignator")
        mwl.append("(0008,0104) LO [Local Code]           # CodeMeaning")
        mwl.append("(fffe,e00d) na (ItemDelimitationItem for re-encoding)       # ItemDelimitationItem")
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem for re-encoding)   # SequenceDelimitationItem")
        mwl.append("(0040,1103) LO [" + query['ReferringPhysicianIdentificationSequence'][0]['PersonTelephoneNumbers'] + "]    # PersonTelephoneNumbers")
        mwl.append("(fffe,e00d) na (ItemDelimitationItem for re-encoding)       # ItemDelimitationItem")
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem for re-encoding.)  # SequenceDelimitationItem")
        #ReferringPhysicianIdentificationSequence END
        
        mwl.append("(0008,1030) LO [" + query['RequestedProcedureDescription'] + "]      # StudyDescription")
        if ('OperatorsName' in query):  mwl.append("(0008,1070) PN [" + query['OperatorsName'] + "]   # OperatorsName")  #OperatorsName
        mwl.append("(0008,1080) LO []                 #  22, 1 AdmittingDiagnosesDescription")
        
        mwl.append("(0008,1110) SQ (Sequence)              # ReferencedStudySequence")        
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem)             # SequenceDelimitationItem")  
              
        mwl.append("(0010,0010) PN [" + query['PatientName'] + "]              # PatientName")
        mwl.append("(0010,0020) LO [" + query['PatientID'] + "]                # PatientID")
        mwl.append("(0010,0030) DA [" + query['PatientBirthDate'] + "]         # PatientBirthDate")
        mwl.append("(0010,0040) CS [" + query['PatientSex'] + "]               # PatientSex")
        
        if ('PatientSize' in query):  mwl.append("(0010,1020) DS [" + query['PatientSize'] + "] #  PatientSize")
        if ('PatientWeight' in query):  mwl.append("(0010,1030) DS [" + query['PatientWeight'] + "]   #  PatientWeight")
        
        mwl.append("(0010,1040) LO [" + query['PatientAddress'] + "]               # PatientAddress")
        mwl.append("(0010,2155) LT [" + query['PatientTelecomInformation'] + "]    # PatientTelecomInformation")
        
        mwl.append("(0010,2000) LO [" + query['MedicalAlerts'] + "]            # MedicalAlerts")
        mwl.append("(0010,2110) LO [" + query['Allergies'] + "]                # Allergies")
        if ('Occupation' in query):  mwl.append("(0010,2180) SH [" + query['Occupation'] + "]   #  Occupation")
        mwl.append("(0010,21B0) LT [" + query['AdditionalPatientHistory'] + "] # AdditionalPatientHistory")
        if ('PregnancyStatus' in query):
            mwl.append("(0010,21C0) US [" + query['PregnancyStatus'] + "]   #  PregnancyStatus") #  0001 - no, 0002 - possibly pregnant, 0003 -yes, 0004 - unknown
        else:
            mwl.append("(0010,21C0) US [0004]   #  PregnancyStatus")
        
        if ('PatientComments' in query):  mwl.append("(0010,4000) LT [" + query['PatientComments'] + "]   #  PatientComments")
        mwl.append("(0020,000d) UI [" + query['StudyInstanceUID'] + "]         # StudyInstanceUID") # Not sure if this belongs here or in the SPSS, or both.
        mwl.append("(0032,1060) LO [" + query['RequestedProcedureDescription'] + "]        #  RequestedProcedureDescription")       
        
        #RequestedProcedureCodeSequence BEGIN, these are the same as the ScheduledProtocolCodeSequence
        mwl.append("(0032,1064) SQ (Sequence with undefined length)        # RequestedProcedureCodeSequence")
        mwl.append("(fffe,e000) na (Item)        # Item")
        mwl.append("(0008,0100) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeValue'] + "]                             # CodeValue")
        mwl.append("(0008,0102) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodingSchemeDesignator'] + "]                # CodingSchemeDesignator")
        mwl.append("(0008,0104) LO [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeMeaning'] + "]                # CodeMeaning")
        mwl.append("(fffe,e00d) na (ItemDelimitationItem)                  # ItemDelimitationItem")
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem)             # SequenceDelimitationItem")
        #RequestedProcedureCodeSequence END
        
        if ('Special​Needs' in query):
            mwl.append("(0038,0050) LO [" + query['Special​Needs'] + "]   #  Special​Needs")
        else:
            mwl.append("(0038,0050) LO [""]   #  Special​Needs")
        
        #ScheduledProcedureStepSequence BEGIN
        mwl.append("(0040,0100) SQ (Sequence)              # ScheduledProcedureStepSequence")
        mwl.append("(fffe,e000) na (Item)                  # Item")
        mwl.append("(0008,0050) SH [" + query['ScheduledProcedureStepSequence'][0]['AccessionNumber'] + "]          # AccessionNumber")
        mwl.append("(0008,0060) CS [" + query['ScheduledProcedureStepSequence'][0]['Modality'] + "]           # Modality")
#         mwl.append("(0020,000d) UI [" + query['StudyInstanceUID'] + "]         # StudyInstanceUID")
#         mwl.append("(0032,1060) LO [" + query['RequestedProcedureDescription'] + "]        #  RequestedProcedureDescription")
        mwl.append("(0040,0001) AE [" +  query['ScheduledProcedureStepSequence'][0]['ScheduleStationAETitle'] + "]    # ScheduledStationAETitle")
        mwl.append("(0040,0002) DA [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartDate'] + "]     # ScheduledProcedureStepStartDate")
        mwl.append("(0040,0003) TM [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartTime'] + "]     # ScheduledProcedureStepStartTime")
        mwl.append("(0040,0007) LO [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepDescription'] + "]       # ScheduledProcedureStepDescription")
        
        #ScheduledProtocolCodeSequence BEGIN
        mwl.append("(0040,0008) SQ (Sequence with undefined length)        # ScheduledProtocolCodeSequence")
        mwl.append("(fffe,e000) na (Item)        # Item")
        mwl.append("(0008,0100) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeValue'] + "]                              # CodeValue")
        mwl.append("(0008,0102) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodingSchemeDesignator'] + "]                 # CodingSchemeDesignator")
        mwl.append("(0008,0104) LO [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProtocolCodeSequence'][0]['CodeMeaning'] + "]                 # CodeMeaning")
        mwl.append("(fffe,e00d) na (ItemDelimitationItem)                  # ItemDelimitationItem")
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem)             # SequenceDelimitationItem")
        #ScheduledProtocolCodeSequence END
        
        mwl.append("(0040,0009) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepID'] + "]           # ScheduledProcedureStepID")
        mwl.append("(0040,1001) SH [" + query['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepID'] + "]  #  RequestedProcedureID")
        mwl.append("(fffe,e00d) na (ItemDelimitationItem for re-encoding)   # ItemDelimitationItem")
        mwl.append("(fffe,e0dd) na (SequenceDelimitationItem for re-encod.) # SequenceDelimitationItem")

        #ScheduledProcedureStepSequence END
        
        mwl.append("(0020,4000) LT [" + query['ImageComments'] + "]   # ImageComments")

        try:
            errorstatus = False
            response = dict()
            filename = WORKLIST_DIR + '/' + query['AccessionNumber']
            returnedtext = ""
            original = sys.stdout

            with open(filename + ".txt", 'w+') as filehandle:
            
                # set the new output channel
                sys.stdout = filehandle
                for line in mwl:
                    returnedtext = returnedtext + str(line) + "\n"
                    print(line)
                # restore the old output channel
                sys.stdout = original
                filehandle.close()
                subprocess.Popen(pathtodump2dcm +' -F +te ' + filename + ".txt " + filename + ".wl", shell = True)
                # raise Exception("Testing Error.") 
                
        except Exception as e:
        
            errorstatus = True
            response['error'] =  str(e)
            response['status'] = 'Problem with MWL:  ' + query['AccessionNumber']

    if errorstatus == False:
        response['status'] = 'MWL File Written  ' + query['AccessionNumber']
        response['error'] =  "OK"
    output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/mwl/file/make', CreateAndSave)

# Format for Server Error in response
# HttpError    "Internal Server Error"
# HttpStatus    500
# Message    "Error encountered within the plugin engine"
# Method    "POST"
# OrthancError    "Error encountered within the plugin engine"
# OrthancStatus    1
# Uri    "/pdfkit/htmltopdf"

# BEGINNING OF PDF FROM HTML

# curl -k http://localhost:8042/pdfkit/htmltopdf -d '{
# 	"method": "html",
# 	"html": "ReportHTML",
# 	"base64": "GenratedFromHTML",
# 	"title": "PRELIM",
# 	"studyuuid": "d467a091-3d8dcf04-b8f466da-c60261b2-e2afe5c8",
# 	"return": 0,
# 	"attach": 1,
# 	"author": "1:Stephen Douglas Scotti "
# }'

# curl -k http://localhost:8042/pdfkit/htmltopdf -d '{"method":"base64","title":"BASE64 TO PDF","studyuuid":"e6596260-fdf91aa9-0257a3c2-4778ebda-f2d56d1b","base64":"JVBER . . .","return":1,"attach":1}'
# Modality is non-standard for REPORT, with the status PRELIM, FINAL, ADDENDUM, OperatorsName is "ID"
# 1.2.840.10008.5.1.4.1.1.104.1 is SOP CLASS for Encapsulated PDF IOD /  "SOPClassUID":"1.2.840.10008.5.1.4.1.1.104.1"
def attachbase64pdftostudy(query):

    attachresponse = dict()

    if query['studyuuid'] != "":
        # print(json.dumps(query))
        query = '{"Tags" : {"Modality":"SR", "Manufacturer": "REPORT", "OperatorsName":"' + query['author'] + '", "SeriesDescription":"' + query['title'] + '","SOPClassUID":"1.2.840.10008.5.1.4.1.1.104.1"},"Content" : "data:application/pdf;base64,' + query['base64'] + '", "Parent":"' + query['studyuuid']+ '"}'
        temp = orthanc.RestApiPost('/tools/create-dicom',query)
        attachresponse['status'] = json.loads(temp)
        attachresponse['error'] = "false"
    else:
        attachresponse['error'] = "Missing UUID for parent study."
        
    return attachresponse;

def getpdf(query, output):

    response = dict()

    if query['method'] == "html":

        try:
            options = {
                'page-size': 'Letter',
                'margin-top': '0.75in',
                'margin-right': '0.75in',
                'margin-bottom': '0.75in',
                'margin-left': '0.75in',
                'footer-center': 'Page [page] of [toPage], [date]',
                'encoding': 'utf-8'
            }
            pathtobinary = shutil.which("wkhtmltopdf")
            config = pdfkit.configuration(wkhtmltopdf=pathtobinary)
            pdf = pdfkit.from_string(query['html'], False,options=options)
            encoded = base64.b64encode(pdf).decode()
            # If attach flag is 1 then attach it to the studyuuid

            if query['attach'] == 1:
                query['base64'] = encoded
                response['attachresponse'] = attachbase64pdftostudy(query)
            if query['return'] == 1:
                response['base64'] = encoded
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

        except Exception as e:

            response['error'] = str(e)
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

    elif query['method'] == "base64":
        response['attachresponse'] = attachbase64pdftostudy(query)
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
    else:
        response['error'] = "Invalid Method"
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

def HTMLTOPDF(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        #print(request['body'])
        query = json.loads(request['body']) # allows control characters ?
        pdf = getpdf(query, output)

orthanc.RegisterRestCallback('/pdfkit/htmltopdf', HTMLTOPDF)

# GETS ALL STUDIES FROM AN ARRAY OF STUDY ID'S, also add the actual instances count and modalities in the study

# e.g. curl -k http://localhost:8042/studies/arrayIDs -d '["27b02380-dfb9f47d-66ff2a89-4cb337ce-93f9789c"]'

def getStudiesByIDArray(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        answers = []
        studies = json.loads(request['body'])
        for uuid in studies:
            study = json.loads(orthanc.RestApiGet('/studies/' + uuid))
            modalities = []
            imagecount = 0
            for series in study['Series']:
                seriesdata = json.loads(orthanc.RestApiGet('/series/%s' % series))
                imagecount = imagecount + len(seriesdata['Instances'])
                if seriesdata['MainDicomTags']['Modality'] not in modalities:
                    modalities.append(seriesdata['MainDicomTags']['Modality'])
            study['imagecount'] = imagecount
            study['modalities'] = modalities
            answers.append(study)
        output.AnswerBuffer(json.dumps(answers, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/studies/arrayIDs', getStudiesByIDArray)


# GETS STUDYCOUNT for an array of patientid's
# e.g. curl -k http://localhost:8042/patient/studycounts -d '["DEV0000001","DEV0000002"]'
# Returns e.g.:  {"DEV0000001": 4, "DEV0000002": 3}

def getPatientStudyCounts(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        answers = dict();
        patients = json.loads(request['body'])
        for patient in patients:
            query = '{"Level":"Study","Expand":false,"Query":{"PatientID":"' + patient +  '"}}'
            answers[patient] = len(json.loads(orthanc.RestApiPost('/tools/find',query)))
        output.AnswerBuffer(json.dumps(answers, indent = 3), 'application/json')
orthanc.RegisterRestCallback('/patient/studycounts', getPatientStudyCounts)

# GETS STUDYCOUNT for an array of referrer ID's.
# e.g. curl -k http://localhost:8042/referrer/studycounts -d '["0001","0002"]'
# Returns e.g.:  {"DEV0000001": 4, "DEV0000002": 3}

def getReferrerStudyCounts(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        answers = dict();
        referrers = json.loads(request['body'])
        for referrer in referrers:
            query = '{"Level":"Study","Expand":false,"Query":{"ReferringPhysicianName":"' + referrer +  '"}}'
            answers[referrer] = len(json.loads(orthanc.RestApiPost('/tools/find',query)))
        output.AnswerBuffer(json.dumps(answers, indent = 3), 'application/json')
orthanc.RegisterRestCallback('/referrer/studycounts', getReferrerStudyCounts)



# DISABLE FOR Now, Generates Documentation on Startup

# for (name, obj) in inspect.getmembers(orthanc):
#     if inspect.isroutine(obj):
#         print('Function %s():\n  Documentation: %s\n' % (name, inspect.getdoc(obj)))
# 
#     elif inspect.isclass(obj):
#         print('Class %s:\n  Documentation: %s' % (name, inspect.getdoc(obj)))
# 
#         # Loop over the members of the class
#         for (subname, subobj) in inspect.getmembers(obj):
#             if isinstance(subobj, numbers.Number):
#                 print('  - Enumeration value %s: %s' % (subname, subobj))
#             elif (not subname.startswith('_') and
#                 inspect.ismethoddescriptor(subobj)):
#                 print('  - Method %s(): %s' % (subname, inspect.getdoc(subobj)))
#         print('')