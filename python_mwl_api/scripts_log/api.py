#!/usr/bin/python3
# -*- coding: utf-8 -*-
from datetime import datetime
from io import BytesIO
import os
from flask import Flask, jsonify
from flask import request, json
from flask import abort
import os
import tempfile
import logging

import hl7 # https://python-hl7.readthedocs.io/en/latest/
import mysql.connector
import requests
from pyorthanc import Orthanc # https://pypi.org/project/pyorthanc/
import pydicom # https://github.com/pydicom/pydicom, sudo python3 -m pip install pydicom
from pydicom.datadict import dictionary_keyword
from pydicom import dcmread, dcmwrite
from pydicom.filebase import DicomFileLike
from pydicom.dataset import Dataset, FileDataset, FileMetaDataset
from pydicom.uid import ExplicitVRLittleEndian, generate_uid
from inspect import getmembers
from pprint import pprint
import time

# # ModalityWorklistInformationFind:  1.2.840.10008.5.1.4.31
# # ModalityPerformedProcedureStepNotificationSOPClass
# # 1.2.840.10008.3.1.2.3.5
# # ModalityPerformedProcedureStepRetrieveSOPClass
# # 1.2.840.10008.3.1.2.3.4
# # ModalityPerformedProcedureStepSOPClass
# # 	1.2.840.10008.3.1.2.3.3
#
# curl http://192.168.0.100:5000
# curl -X POST -H "Content-Type: application/json" http://192.168.0.100:5000/mwl/create_from_json -d '{"ScheduledProcedureStepSequence": [{"Modality": "MR"}]}'
# curl -X POST -H "Content-Type: application/json" http://192.168.0.100:5000/mwl/create_from_json -d '{"ScheduledProcedureStepSequence: [{"Modality": "MR"}]}'  Bad JSON
# curl -X POST -H "Content-Type: application/json" http://192.168.0.100:5000/mwl/create_from_json -d '["test", "test"]'
# curl -X POST -F 'username=davidwalsh' -F 'password=something' http://192.168.0.100:5000/mwl/create_from_json Sample form data
logging.basicConfig(filename="api.log", level=logging.DEBUG)

# PYDICOM METHODS
# CREATES RAW DATA TO STORE DATASET IN DB, from Dataset object
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
        
# CONVERTS BYTES BACK INTO A DATASET, I.E. FOR RETRIEVING FROM DATABASE       
def bytes_to_dataset(blob):

    # you can just read the dataset from the byte array
    dataset = dcmread(BytesIO(blob), force=True)
    # do some interesting stuff
    dataset.is_little_endian = False
    return dataset
    
# METHOD TO CONSTRUCT DATASET FROM JSON, SEE SAMPLE, PASS IN the JSON for the Dataset and a Blank Dataset

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
    
#         
# FUNCTION TO HANDLE SPECIAL CASES, LIKE FOR PersonName Object, this is in the other MPPS container, named_tags = safe_serialize(named_tags) to deal with PN objects, for later.     
    def safe_serialize(obj):
      default = lambda o: o.family_name + '^' + o.given_name + '^' + o.middle_name + '^' + o.name_prefix + '^' + o.name_suffix
      return json.dumps(obj, default=default) 
    
# PROCEDURE TO CONVERT TO SIMPLER JSON FORMAT WITH SHORT NAMES AND VALUES ONLY, SIMILAR TO WHAT ORTHANC USES FOR MWL
# TAKES A DATASET and CREATES JSON OBJECT WITH SHORT NAMES FOR KEYS
# 
def recurse(ds, jsondict):
    
    for elem in ds:

        if elem.VR == 'SQ':
        
            jsondict[dictionary_keyword(elem.tag)] = []
            tempdict = dict()
            [recurse(item, tempdict) for item in elem]
            jsondict[dictionary_keyword(elem.tag)].append(tempdict)

        else:
            shortname = dictionary_keyword(elem.tag)
            jsondict[shortname] = elem.value
            
    return jsondict
    
# TO CHECK IF VALID JSON
def is_json(myjson):
  try:
    json_object = json.loads(myjson)
  except ValueError as e:
    return False
  return json_object


# API SERVER SECTION, FLASK, MIGRATE TO SOME PRODUCTION VERSION LATER ?
# PREPROCESS REQUESTS A BIT
# RETURNS 4 PARAMS, hasdata, hasJSON, hasForm and the data (dict for JSON, request.form for form).

def filter_post(_request):

    print(_request.headers.get('Host')) # 192.168.0.100:5000
    print(_request.headers.get('Content-Type'))
    print(_request.data)
    print(_request.args)
    print(_request.form)
    print(_request.endpoint)
    print(_request.method)
    print(_request.remote_addr)
    data = _request.data
    form = _request.form
    if (not data and not form):
        return False, False, False, False
    else:
        isJSON = False
        isForm = False
        if (is_json(data) and _request.headers.get('Content-Type') == 'application/json'):
            isJSON = True
            data = json.loads(data)
        elif (_request.form): # assuming 'application/x-www-form-urlencoded
            isForm = True
            data = _request.form
        return True, isJSON, isForm, data
        
# Database Connection
MY_DB = None # DB Connection, fetched via get_DB method

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

# Save Dataset to DB
def SaveDatasetDB(JSON, dataset):

    conn = get_DB()
    if (conn):
        mycursor = MY_DB.cursor()
        datasetBytes = dataset_to_bytes(dataset)
        AccessionNumber = ""
        StudyInstanceUID = ""
        ScheduledProcedureStepStartDate = ""
        AET = ""
        response = dict()
        if ('StudyInstanceUID' in JSON): StudyInstanceUID = JSON['StudyInstanceUID'] # Not sure if this belongs is SPSS
        if ('ScheduledProcedureStepSequence' in JSON):
            if ('AccessionNumber' in JSON['ScheduledProcedureStepSequence'][0]):
                AccessionNumber = JSON['ScheduledProcedureStepSequence'][0]['AccessionNumber']
            if ('ScheduledProcedureStepStartDate' in JSON['ScheduledProcedureStepSequence'][0]):
                ScheduledProcedureStepStartDate = JSON['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartDate']
            if ('ScheduledStationAETitle' in JSON['ScheduledProcedureStepSequence'][0]):
                AET = JSON['ScheduledProcedureStepSequence'][0]['ScheduledStationAETitle']
        JSONinsert = json.dumps(JSON,sort_keys = True)
        try:
            mycursor.execute("INSERT INTO mwl (AccessionNumber, StudyInstanceUID, ScheduledProcedureStepStartDate, AET, MWLJSON, Dataset) VALUES (%s, %s, %s, %s, %s, %s)", (AccessionNumber, StudyInstanceUID, ScheduledProcedureStepStartDate, AET, JSONinsert, datasetBytes))
            MY_DB.commit()
            print("Inserting MWL Sample" + str(mycursor.rowcount))
            mycursor.close()
        
        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
            response['DB'] = False
            return response
        response['DB'] = True
        if (not AccessionNumber):
            response['filename'] = "test.wl"
        else:
            response['filename'] = AccessionNumber + '.wl'
        print("Sample Saved to DB")
        return response
    else:
        response['DB'] = False
        return response
    
def SaveDatasetFolder(JSON, dataset):

    dt = datetime.now()

# create orthanc requests handler, need to have a config for that.
orthanc = Orthanc('http://pacs:8042')

# INSTANTIATE FLASK AND DEFINE ROUTES AND HANDLERS

app = Flask(__name__)
print(__name__)

# we define the route /, JUST FOR TESTING
# curl http://192.168.0.100:5000
@app.route('/')
def welcome():
    # return a json
    return jsonify({'status': 'api working'})

     
# ROUTE TO ACCEPT POSTED JSON AND THEN TO CREATE AN MWL FILE FROM THAT.  SAVE TO DB AND WRITE TO FILESYSTEM

# Use JSON LINT online to edit and view. https://jsonlint.com/
# curl -X POST -H "Content-Type: application/json" http://localhost:5000/mwl/create_from_json -d '{"Modality": "MR","Allergies": "","PatientID": "CM0000001","Occupation": "Occupation","PatientSex": "M","PatientName": "Person^Test1^","PatientSize": "","ImageComments": "Tech:  SP","MedicalAlerts": "","OperatorsName": "SP","PatientWeight": "","PatientAddress": "^^City^State^Postal^Country","AccessionNumber": "CMACC00000002","PatientComments": "PatientComments","PatientBirthDate": "20010101","StudyDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST","StudyInstanceUID": "1.3.6.1.4.1.56016.1.1.1.48.1625271194","SpecificCharacterSet": "ISO_IR 192","ReferringPhysicianName": "Last^First^Middle^Prefix^Suffix","ReferencedStudySequence": [],"AdditionalPatientHistory": "test","PatientTelecomInformation": "Phone^WPN^PH^","AdmittingDiagnosesDescription": "","RequestedProcedureDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST","RequestedProcedureCodeSequence": [{"CodeValue": "70551","CodeMeaning": "[\"70551\"]","CodingSchemeDesignator": "CPT4"}],"ScheduledProcedureStepSequence": [{"Modality": "MR","AccessionNumber": "CMACC00000002","RequestedProcedureID": "0001","ScheduledStationAETitle": "DVT","ScheduledProcedureStepID": "0001","ScheduledProtocolCodeSequence": [{"CodeValue": "70551","CodeMeaning": "[\"70551\"]","CodingSchemeDesignator": "CPT4"}],"ScheduledProcedureStepStartDate": "20210704","ScheduledProcedureStepStartTime": "110000","ScheduledProcedureStepDescription": "MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST"}],"ReferringPhysicianIdentificationSequence": [{"InstitutionName": "InstitutionName","PersonTelephoneNumbers": "Phone^WPN^PH^mail@mail.com","PersonIdentificationCodeSequence": [{"CodeValue": "0001","CodeMeaning": "Local Code","CodingSchemeDesignator": "L"}]}]}'    
@app.route('/mwl/create_from_json', methods=['POST'])
def create_from_json():

    hasdata, hasJSON, hasForm, data = filter_post(request)
    response = dict()
    if not hasdata:
        response['status'] = "No Data Posted"
        return json.dumps(response), 400
        
    elif (hasJSON):
        
        print("Making MWL from JSON")
        dataset = Dataset()
        dataset = getMWLFromJSON(data, dataset)
        dataset.is_little_endian = True # 'Dataset.is_little_endian' and 'Dataset.is_implicit_VR' must be set appropriately before saving
        dataset.is_implicit_VR = True
        # Set creation date/time
        dt = datetime.now()
        dataset.ContentDate = dt.strftime('%Y%m%d')
        timeStr = dt.strftime('%H%M%S.%f')  # long format with micro seconds
        dataset.ContentTime = timeStr
        response = SaveDatasetDB(data, dataset)
        print("Writing test file", response['filename'])
        dataset.save_as('/MWL/' + response['filename'],write_like_original=True) # True takes care of not explcitly setting (0002, 0002) MediaStorageSOPClassUID & (0002, 0003) MediaStorageSOPInstanceUID
        response['status'] = "OK"
        response['dataset'] = "Dataset Created and Saved"
        return response, 201

    elif (hasForm):
        print("Has Form Data")
        response['status'] = "Form Data"
        response['form'] = data
        return response, 201
    else:
        response['status'] = "Error with Request"
        return response, 400
        
# ROUTE TO ACCEPT POSTED JSON AND THEN SEARCH A SHARED MWL FOLDER USING THE ORTHANC REST API CALL, PRETTY SIMPLE REALLY, RETURNS THE RESULTS AS A STATUS AND JSON ARRAY OF MATCHES.
# THE MPPS / DICOM SERVER CAN DO THE SAME, BUT USING THE DICOM PROTOCOL FOR A MODALITY.
        
# curl -X POST -H "Content-Type: application/json" http://192.168.0.100:5000/mwl/findscu_MWLFolder -d '{"Modality": "MR","Allergies": "","PatientID": "","Occupation": "","PatientSex": "","PatientName": "","PatientSize": "","ImageComments": "","MedicalAlerts": "","OperatorsName": "","PatientWeight": "","PatientAddress": "","AccessionNumber": "","PatientComments": "","PatientBirthDate": "","StudyDescription": "","StudyInstanceUID": "","SpecificCharacterSet": "","ReferringPhysicianName": "","ReferencedStudySequence": [],"AdditionalPatientHistory": "","PatientTelecomInformation": "","AdmittingDiagnosesDescription": "","RequestedProcedureDescription": "","ScheduledProcedureStepSequence": [{"Modality": "MR","AccessionNumber": "","RequestedProcedureID": "","ScheduledStationAETitle": "","ScheduledProcedureStepID": "","ScheduledProcedureStepStartDate": "","ScheduledProcedureStepStartTime": ""}]}'       

@app.route('/mwl/findscu_MWLFolder', methods=['POST'])
def findscu_MWLFolder():
    hasdata, hasJSON, hasForm, data = filter_post(request)
    response = dict()
    if not hasdata:
        response['status'] = "No Data Posted"
        return json.dumps(response), 400
        
    elif (hasJSON):
        
        print("Searching Shared MWL Folder Using Orthanc REST API")
        print(data)
        results = orthanc.post_request('http://pacs:8042/modalities/SELF/find-worklist', data) # this thing can take a dict
        response['status'] = "OK"
        response['results'] = results
        return response, 201

    elif (hasForm):
        print("Has Form Data")
        response['status'] = "Form Data"
        response['form'] = data
        return response, 201
    else:
        response['status'] = "Error with Request"
        return response, 400

# ROUTE TO ACCEPT POSTED JSON AND THEN SEARCH THE ACTUAL DB BY THE FEW COLUMNS THAT ARE INDICES, CAN RETURN JSON OR A DATASET
# LIMITED TO AccessionNumber, StudyInstanceUID, STudyDate, AET for Now
# curl -X POST -H "Content-Type: application/json" http://192.168.0.100:5000/mwl/findscu_DB -d '{"AccessionNumber": "CMACC00000002","StudyInstanceUID": "1.3.6.1.4.1.56016.1.1.1.48.1625271194","ScheduledProcedureStepStartDate":"20210704","ScheduledStationAETitle": "DVT"}'

# Need to figure out how to structure the SELECT, either limited number of params, or actually search the JSON column.

@app.route('/mwl/findscu_DB', methods=['POST'])
def findscu_DB():
    response = dict()
    conn = get_DB()
    if (conn):
        hasdata, hasJSON, hasForm, data = filter_post(request)
        if not hasdata:
            response['status'] = "No Data Posted"
            return json.dumps(response), 400
        
        elif (hasJSON):
        
            print("Searching MySQL DB")
            mycursor = MY_DB.cursor(dictionary=True)
            mycursor.execute("SELECT MWLJSON, Dataset from mwl")
            response['JSON'] = []
            response['Datasets'] = []
            for row in mycursor:
                response['JSON'].append(json.loads(row['MWLJSON']))
                response['Datasets'].append({"Dataset": str(row['Dataset'])})
            mycursor.close()
            response['status'] = "OK"
            return response, 201

        elif (hasForm):
            print("Has Form Data")
            response['status'] = "Form Data"
            response['form'] = data
            return response, 201
        else:
            response['status'] = "Error with Request"
            return response, 400
    else:
        response['status'] = "DB Not Available"
        return response, 400
        
if __name__ == '__main__':

    #START UP THE SERVER
    #define the localhost ip and the port that is going to be used
    app.run(host='0.0.0.0', port=os.getenv('DOCKERPORT'))
   