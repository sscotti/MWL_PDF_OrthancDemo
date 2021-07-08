function OnStoredInstance(instanceId, tags, metadata, origin)

-- Ignore the instances that result from a modification to avoid
-- infinite loops
-- PrintRecursive(instanceId)
-- PrintRecursive(metadata)
-- PrintRecursive(tags)
-- PrintRecursive(origin)
-- curl -k --request POST --url https://cayman.medical.ky/pacs-1/modalities/PACS1/find-worklist --data '{"PatientID" : "DEV0000018"}}'
   
   if (origin['RequestOrigin'] == 'DicomProtocol') then -- Could add a check for the origin[RemoteAET] ==   and origin['RequestOrigin'] ~= 'Lua'

      local command = {}
      local replace = {}
      local remove = {}
      
      replace['OperatorsName'] = 'SP'  -- initials for the tech, OperatorsName
      replace['SOPInstanceUID'] = tags['SOPInstanceUID']
      if (tags['RequestAttributesSequence'] ~= nil) then
         replace['StudyDescription'] = tags['RequestAttributesSequence'][1]['ScheduledProcedureStepDescription']
         replace['StudyID'] = tags['RequestAttributesSequence'][1]['ScheduledProcedureStepID']
      end

      command['Replace'] = replace
      command['Remove'] = remove
      command['Force'] = true
      
      local modifiedFile = RestApiPost('/instances/' .. instanceId .. '/modify', DumpJson(command, true))
      local uploadResponse = ParseJson(RestApiPost('/instances', modifiedFile))
      if (uploadResponse["Status"] == 'AlreadyStored') then
         print("Are you sure you've enabled 'OverwriteInstances' option ?")
      end
      -- local modifiedId = uploadResponse['ID']
   end
end
-- Moved to Python Script completely, which also replaced the above.
--function OnStableStudy(studyId, tags, metadata)
--
--  local reconstructed = RestApiPost('/studies/' .. studyId .. '/reconstruct','{}')
--  local studyinfo = {}
--  studyinfo['metadata'] = metadata
--  studyinfo['uuid'] = studyId
--  studyinfo['tags'] = tags
--  studyinfo['RISDB'] = "RISDEV"
--  local savedstudy = RestApiPost('/studies/processed',DumpJson(studyinfo, true), false)  -- url, body, builtin, headers
--  print(savedstudy)
-- 
--end