#!/usr/bin/python3
# -*- coding: utf-8 -*-
from datetime import datetime
from io import BytesIO


import os
import tempfile
import json
import hl7 # https://python-hl7.readthedocs.io/en/latest/
import mysql.connector
import requests
from pyorthanc import Orthanc # https://pypi.org/project/pyorthanc/
import pydicom # https://github.com/pydicom/pydicom, sudo python3 -m pip install pydicom
from pydicom.datadict import dictionary_keyword
from pydicom import dcmread, dcmwrite
from pydicom.filebase import DicomFileLike
from pynetdicom import AE, evt, VerificationPresentationContexts, AllStoragePresentationContexts, ModalityPerformedPresentationContexts, build_context, debug_logger  #  sudo python3 -m pip install pynetdicom, https://github.com/pydicom/pynetdicom
from pynetdicom.sop_class import VerificationSOPClass, ModalityWorklistInformationFind, ModalityPerformedProcedureStepSOPClass, ModalityPerformedProcedureStepNotificationSOPClass, ModalityPerformedProcedureStepRetrieveSOPClass, CTImageStorage, MRImageStorage, UltrasoundImageStorage, UltrasoundMultiframeImageStorage
from pynetdicom.dimse import DIMSEServiceProvider


# ModalityWorklistInformationFind:  1.2.840.10008.5.1.4.31
# ModalityPerformedProcedureStepNotificationSOPClass
# 1.2.840.10008.3.1.2.3.5
# ModalityPerformedProcedureStepRetrieveSOPClass
# 1.2.840.10008.3.1.2.3.4
# ModalityPerformedProcedureStepSOPClass
# 	1.2.840.10008.3.1.2.3.3
	
from pydicom.dataset import Dataset, FileDataset, FileMetaDataset
from pydicom.uid import ExplicitVRLittleEndian, generate_uid
from pynetdicom.status import Status
from inspect import getmembers
from pprint import pprint
import time
import logging

# Database Connection
MY_DB = None # DB Connection, fetched via get_DB method
orthanc = Orthanc('http://pacs:8042')

def get_DB():
    global MY_DB
    try:
        MY_DB = mysql.connector.connect(host="mysql_db", port = 3306, user="demo",password="demo",database="orthanc_ris")
        return MY_DB

    except mysql.connector.Error as err:

        # MySQL server is not avaable
        print(err)
        print("Error Code:", err.errno)
        print("SQLSTATE", err.sqlstate)
        print("Message", err.msg)
        return None

logging.basicConfig(filename="mpps.log", level=logging.DEBUG)
managed_instances = {}

#     'N-CREATE-RQ': (
#         'CommandGroupLength', 'AffectedSOPClassUID', 'CommandField',
#         'MessageID', 'CommandDataSetType', 'AffectedSOPInstanceUID'
#     ),
#     'N-CREATE-RSP': (
#         'CommandGroupLength', 'AffectedSOPClassUID', 'CommandField',
#         'MessageIDBeingRespondedTo', 'CommandDataSetType', 'Status',
#         'AffectedSOPInstanceUID',
#         'ErrorID', 'ErrorComment'
#     ),
#     'N-SET-RQ': (
#         'CommandGroupLength', 'RequestedSOPClassUID', 'CommandField',
#         'MessageID', 'CommandDataSetType', 'RequestedSOPInstanceUID'
#     ),
#     'N-SET-RSP': (
#         'CommandGroupLength', 'AffectedSOPClassUID', 'CommandField',
#         'MessageIDBeingRespondedTo', 'CommandDataSetType', 'Status',
#         'AffectedSOPInstanceUID',
#         'AttributeIdentifierList', 'ErrorComment', 'ErrorID'
#     ),
# 


def dataset_to_bytes(dataset):

    # create a buffer
    with BytesIO() as buffer:
        # create a DicomFileLike object that has some properties of DataSet
        memory_dataset = DicomFileLike(buffer)
        # write the dataset to the DicomFileLike object
        dcmwrite(memory_dataset, dataset)
        # to read from the object, you have to rewind it
        memory_dataset.seek(0)
        # read the contents as bytes
        return memory_dataset.read()
        
def bytes_to_dataset(blob):

    # you can just read the dataset from the byte array
    dataset = dcmread(BytesIO(blob), force=True)
    # do some interesting stuff
    dataset.is_little_endian = False
    return dataset
        
        
#   METHOD TO CONSTRUCT DATASET FROM JSON, SEE SAMPLE.

def getMWLFromJSON(MWLDict, DataSet):
    
    for key, value in MWLDict.items():
        if (isinstance(value, str)):
            setattr(DataSet, key, value)
        else: # must be a list or sequence
            # setattr(mwlDataSet, key, Dataset())
            sequence = []
            # Create the Sequence Blank Dataset
            for i in range(len(value)):
                sequenceSet = Dataset()
                sequenceSet = getMWLFromJSON(value[i], sequenceSet)
                sequence.append(sequenceSet)
            setattr(DataSet, key, sequence)
    return DataSet
    
def GetRequestParams(event):

    # PresentationContextTuple(context_id=1, abstract_syntax='1.2.840.10008.5.1.4.31', transfer_syntax='1.2.840.10008.1.2')
    # {'Event': 'EVT_C_FIND', 'TimeStamp': '2021-07-05 15:46:00', 'MessageID': '1', 'AbstractSyntax': '1.2.840.10008.5.1.4.31', 'TransferSyntax': '1.2.840.10008.1.2', 'CallingApplicationName': b'FINDSCU         ', 'CallingApplicationAddress': '172.18.0.1'}
    params = dict()
    params['Event'] =  event.event.name
    params['TimeStamp'] =  event.timestamp.strftime('%Y-%m-%d %H:%M:%S')
    params['MessageID'] =  str(event.context.context_id)
    params['AbstractSyntax'] =  str(event.context.abstract_syntax)
    # 1.2.840.10008.5.1.4.31	Modality Worklist Information Model – FIND
    params['TransferSyntax'] =  str(event.context.transfer_syntax)
    params['CallingApplicationName'] =  event.assoc.requestor.ae_title
    params['CallingApplicationAddress'] =  event.assoc.requestor.address
    print(params)
    return params
    
# findscu -W 192.168.0.100 104 -k 0008,0050="*", to test from CLI

# METHOD TO SEARCH THE MWL TABLE IN THE DB BY SEARCH CRITERIA AND RETURN A LIST OF

def search_mwl():
    conn = get_DB()
    if (conn):
        mycursor = conn.cursor()
        mwlset = []
        try:
            mycursor.execute("SELECT Dataset from mwl")
            results = mycursor.fetchall()
            print("Results from MWL Query:  " + str(len(results)))
            mycursor.close()
            return results
        
        except mysql.connector.Error as err:
    
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
    else:
        print("Database Not Available")
        return False

def handle_find(event):

    print("In Pynet Dicom,C_FIND") 
    requestparams = GetRequestParams(event)
    if (requestparams['AbstractSyntax'] != '1.2.840.10008.5.1.4.31'):
        print("NOT an FINDModalityWorklistInformationModel Query")
    else:
        print("Processing FINDModalityWorklistInformationModel")
        ds = event.identifier
        for_orthanc = dict()
        print(recurse(ds, for_orthanc))
        results = search_mwl()
        if(results):
            for result in results:
                # print(result), prints them to the stdout, terminals
                # 0 is the MWL, 1 is the Dataset
                dataset = bytes_to_dataset(result[0])
                yield (0xFF00, dataset) # sends them via DICOM PROTOCOL, see the log.
            return
        else:
            yield 0xC000, None
            return
           
def handle_create(event):

    # MPPS' N-CREATE request must have an *Affected SOP Instance UID*
    
    logging.debug("In Pynet Dicom, N-CREATE")
    requestparams = GetRequestParams(event)
    req = event.request
    
    if req.AffectedSOPInstanceUID is None:
        # Failed - invalid attribute value
        print("Returning 0x0106, No AffectedSOPInstanceUID")
        return 0x0106, None
    # Can't create a duplicate SOP Instance
    if req.AffectedSOPInstanceUID in managed_instances:
        # Failed - duplicate SOP Instance
        print("returning 0x0111, duplicate SOP Instance")
        return 0x0111, None

    # The N-CREATE request's *Attribute List* dataset
    attr_list = event.attribute_list

    # Performed Procedure Step Status must be 'IN PROGRESS'
    if "PerformedProcedureStepStatus" not in attr_list:
        # Failed - missing attribute
        print("returning 0x0120, PerformedProcedureStepStatus not in Attribute List")
        return 0x0120, None
    if attr_list.PerformedProcedureStepStatus.upper() != 'IN PROGRESS':  # Discontinued or Completed
        print("returning 0x0106, Not IN PROGRESS")
        return 0x0106, None

    # Skip other tests...

    # Create a Modality Performed Procedure Step SOP Class Instance
    #   DICOM Standard, Part 3, Annex B.17
    print("Getting DataSet")
    ds = Dataset()
    
    # Add the SOP Common module elements (Annex C.12.1)
    print("Setting SOP UIDs")
    ds.SOPClassUID = ModalityPerformedProcedureStepSOPClass
    ds.SOPInstanceUID = req.AffectedSOPInstanceUID
    
    # Update with the requested attributes
    print("Updating Attributes")
    ds.update(attr_list)
    
    # Add the dataset to the managed SOP Instances
    managed_instances[req.AffectedSOPInstanceUID] = ds
    
    # Generate ShortNameJSON format from Dataset (named_tags) and save the attr_list (dataset_in, the mwlquery for a matching MWL by accession, the dataset_out and shortname version of that to DB)
    ds.is_little_endian = True
    ds.is_implicit_VR = True
    jsondict = dict()
    named_tags = recurse(ds,jsondict)
    print("Returning 0x0000 and DataSet")
    logging.debug(ds)
    # Return status, dataset
    print("DatabaseInsert")
    # Work in Progress here
    mwlquery = MWLGetListForAccession("CMACC00000002") # ds.ScheduledProcedureStepSequence[0].AccessionNumber, ds.to_json()
    Insert_N_CREATE_Request(attr_list.to_json(), mwlquery, ds.to_json(), named_tags, req.MessageID)
    return 0x0000, ds #https://pydicom.github.io/pynetdicom/stable/reference/generated/pynetdicom._handlers.doc_handle_create.html#pynetdicom._handlers.doc_handle_create
    
# PROCEDURE TO CONVERT TO SIMPLER JSON FORMAT WITH SHORT NAMES AND VALUES ONLY, SIMILAR TO WHAT ORTHANC USES FOR MWL

def recurse(ds, jsondict):
    
    for elem in ds:
        print(elem)
        if elem.VR == 'SQ':
        
            jsondict[dictionary_keyword(elem.tag)] = []
            tempdict = dict()
            [recurse(item, tempdict) for item in elem]
            jsondict[dictionary_keyword(elem.tag)].append(tempdict)

        else:
            shortname = dictionary_keyword(elem.tag)
            jsondict[shortname] = elem.value
            
    return jsondict

# Implement the evt.EVT_N_SET handler

def handle_set(event):

    requestparams = GetRequestParams(event)
    req = event.request
    if req.RequestedSOPInstanceUID not in managed_instances:
        # Failure - SOP Instance not recognised
        return 0x0112, None

    ds = managed_instances[req.RequestedSOPInstanceUID]

    # The N-SET request's *Modification List* dataset
    mod_list = event.attribute_list

    # Skip other tests...
    old_ds = ds
    ds.update(mod_list)
    print("Updated Mod List")
    print(mod_list)
    logging.debug("Updated Mod List")
    logging.debug(mod_list)
    # Return status, dataset
    Insert_N_SET_Request(req.RequestedSOPInstanceUID, req.MessageID, old_ds.to_json(), mod_list.to_json(), ds.to_json(), '0x0000')
    return 0x0000, ds # 0x0000
    
# Implement the evt.EVT_N_SET handler
def handle_echo(event):

    print(event.assoc) #  <Association(AcceptorThread@20210704002812, started daemon 140308686964480)>
    print(event.event.description) # InterventionEvent(name='EVT_C_ECHO', description='C-ECHO request received')
    print(event.timestamp) #2021-07-04 00:29:59.168226
    print(event.message_id) # Unique number per client, int
    # print(event.args) # No args
#         req = event.request
#         print(req)
    return Status.SUCCESS # 0x0000  
    
def MWLGetListForAccession(accesssion):


    # Get the "Missing" Tags, can be customized, this is what is in our standard MWL file, could add others.
    response = dict()
    searchtags = dict()
    searchtags['AccessionNumber'] = ""
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
    results = orthanc.post_request('http://pacs:8042/modalities/SELF/find-worklist', searchtags) 
    return results
    
# FUNCTION TO HANDLE SPECIAL CASES, LIKE FOR PersonName Object      
def safe_serialize(obj):
  default = lambda o: o.family_name + '^' + o.given_name + '^' + o.middle_name + '^' + o.name_prefix + '^' + o.name_suffix
  return json.dumps(obj, default=default) 
  
    
# FUNCTION TO HANDLE INSERTING DATA REGARDING N_CREATE INTO A DEDICATED DATABASE FOR THIS SERVER, COULD USE OTHER DB AS WELL
def Insert_N_CREATE_Request(dataset_in, mwl, dataset_out, named_tags, MessageID):

    #  Grab these from the ScheduledStepAttributesSequence, would be interesting to use more than one ScheduledStepAttributesSequence actually, if possible
    AccessionNumber = named_tags['ScheduledStepAttributesSequence'][0]['AccessionNumber']
    StudyInstanceUID = named_tags['ScheduledStepAttributesSequence'][0]['StudyInstanceUID']
    named_tags = safe_serialize(named_tags)
    conn = get_DB()
    if (conn):
    
        mycursor = conn.cursor()
        mwl = mwl[0] # it is passed in as an array, need to do some checking there.
        mwl = json.dumps(mwl,sort_keys = True)
        print(mwl)

        try:
            mycursor.execute("INSERT INTO n_create (AccessionNumber, StudyInstanceUID, dataset_in, mwl, dataset_out, MessageID, named_tags) VALUES (%s, %s, %s, %s, %s, %s, %s)", (AccessionNumber, StudyInstanceUID, dataset_in, mwl , dataset_out, MessageID, named_tags))
            conn.commit()
            print("Inserting N_CREATE" + str(mycursor.rowcount))
            mycursor.close()
        
        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
    else:
        print("No DB Connection")
        
# FUNCTION TO HANDLE INSERTING DATA REGARDING N_SET Request
def Insert_N_SET_Request(AffectedSOPInstanceUID, MessageID, managed_instance, mod_list, response, response_status):

    conn = get_DB()
    if (conn):
        mycursor = conn.cursor()
        try:
            mycursor.execute("INSERT INTO n_set (AffectedSOPInstanceUID, MessageID, managed_instance, mod_list, response, response_status) VALUES (%s, %s, %s, %s, %s, %s)", (AffectedSOPInstanceUID, MessageID, managed_instance, mod_list, response, response_status))
            conn.commit()
            print("Inserting N_CREATE" + str(mycursor.rowcount))
            mycursor.close()
    
        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
    else:
        print("No DB Connection")
        
    
handlers = [(evt.EVT_C_ECHO, handle_echo),(evt.EVT_N_CREATE, handle_create), (evt.EVT_N_SET, handle_set), (evt.EVT_C_FIND, handle_find)]

# Initialise the Application Entity and specify the listen port
ae = AE()
# Add the supported presentation context
ae.add_supported_context(VerificationSOPClass)
ae.add_supported_context(ModalityWorklistInformationFind)
ae.add_supported_context(ModalityPerformedProcedureStepSOPClass)
ae.add_supported_context(ModalityPerformedProcedureStepNotificationSOPClass)
ae.add_supported_context(ModalityPerformedProcedureStepRetrieveSOPClass)
ae.add_supported_context(CTImageStorage)
ae.add_supported_context(MRImageStorage)
ae.ae_title = 'MPPS_SERVER'
print (ae.ae_title)
# Start listening for incoming association requests
ae.start_server(('python_mpps', 11112), block=True, evt_handlers=handlers)
print ("Started MPPS Server:  AET")

# https://gitlab.physmed.chudequebec.ca/gacou54/pyorthanc/blob/master/pyorthanc/orthanc.py
# ORTHANC_CONFIG = orthanc.get_request('http://pacs:8042/get-configs/ALL') # This is a plug-in route in my orthanc python plug-in, the pyorthanc return a dict unless return_as_bytes is True
#     pprint(ORTHANC_CONFIG)
# TEST = MWLGetListForAccession("CMACC00000002")
# pprint(TEST)


# MWLDict = {
# 
#     "Modality": "MR",
#     "Allergies": "",
#     "PatientID": "CM0000001",
#     "Occupation": "Occupation",
#     "PatientSex": "M",
#     "PatientName": "Person^Test1^",
#     "PatientSize": "",
#     "ImageComments": "Tech:  SP",
#     "MedicalAlerts": "",
#     "OperatorsName": "SP",
#     "PatientWeight": "",
#     "PatientAddress": "^^City^State^Postal^Country",
#     "AccessionNumber": "CMACC00000002",
#     "PatientComments": "PatientComments",
#     "PatientBirthDate": "20010101",
#     "StudyDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST",
#     "StudyInstanceUID": "1.3.6.1.4.1.56016.1.1.1.48.1625271194",
#     "SpecificCharacterSet": "ISO_IR 192",
#     "ReferringPhysicianName": "Last^First^Middle^Prefix^Suffix",
#     "ReferencedStudySequence": [],
#     "AdditionalPatientHistory": "test",
#     "PatientTelecomInformation": "Phone^WPN^PH^",
#     "AdmittingDiagnosesDescription": "",
#     "RequestedProcedureDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST",
#     "RequestedProcedureCodeSequence": [
#         {
#             "CodeValue": "70551",
#             "CodeMeaning": "[\"70551\"]",
#             "CodingSchemeDesignator": "CPT4"
#         }
#     ],
#     "ScheduledProcedureStepSequence": [
#         {
#             "Modality": "MR",
#             "AccessionNumber": "CMACC00000002",
#             "RequestedProcedureID": "0001",
#             "ScheduledStationAETitle": "DVT",
#             "ScheduledProcedureStepID": "0001",
#             "ScheduledProtocolCodeSequence": [
#                 {
#                     "CodeValue": "70551",
#                     "CodeMeaning": "[\"70551\"]",
#                     "CodingSchemeDesignator": "CPT4"
#                 }
#             ],
#             "ScheduledProcedureStepStartDate": "20210704",
#             "ScheduledProcedureStepStartTime": "110000",
#             "ScheduledProcedureStepDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST"
#         }
#     ],
#     "ReferringPhysicianIdentificationSequence": [
#         {
#             "InstitutionName": "InstitutionName",
#             "PersonTelephoneNumbers": "Phone^WPN^PH^mail@mail.com",
#             "PersonIdentificationCodeSequence": [
#                 {
#                     "CodeValue": "0001",
#                     "CodeMeaning": "Local Code",
#                     "CodingSchemeDesignator": "L"
#                 }
#             ]
#         }
#     ]
# }