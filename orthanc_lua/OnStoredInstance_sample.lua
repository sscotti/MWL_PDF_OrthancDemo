function OnStoredInstance(instanceId, tags, metadata, origin)

-- Ignore the instances that result from a modification to avoid
-- infinite loops
--    PrintRecursive(instanceId)
--    PrintRecursive(metadata)
--    PrintRecursive(tags)
--    PrintRecursive(origin)

--[
--   {
--      "StudyInstanceUID" : "1.2.826.0.1.3680043.2.133.1.3.1.36.40.10436",
--      "PatientID" : "DEV0000018",
--      "SpecificCharacterSet" : "ISO_IR 192"
--   }
--]
   if (origin['RequestOrigin'] == 'DicomProtocol') then -- Could add a check for the origin[RemoteAET] ==   and origin[RequestOrigin] == 'DicomProtocol'   also

--     local mwlquery = {}
--     mwlquery['StudyInstanceUID'] = '1.3.6.1.4.1.56016.1.1.1.103.1620244246'
--     mwlquery['StudyDescription'] = ''
--     mwlquery['ContentCreatorName'] = ''  0070,0084    0008,1070
--
--     local MWL = RestApiPost('/modalities/PACS1/find-worklist', DumpJson(mwlquery, true))
--
--     print(MWL)
     -- The tags to be replaced
      PrintRecursive(tags)
      PrintRecursive(origin)
      local command = {}
      local replace = {}
      local remove = {}
      local keep = {'SOPInstanceUID'}
      replace['0008,1070'] = 'Tech^1:SS'  -- initials for the tech  OperatorsName
      replace['0020,4000'] = 'Tech^1:SS'  -- initials for the tech  ImageComments

      if (tags['RequestAttributesSequence'] ~= nil) then
         replace['StudyDescription'] = tags['RequestAttributesSequence'][1]['ScheduledProcedureStepDescription']
      end

      command['Force'] = true
      command['Replace'] = replace
      command['Keep'] = keep
      command['Remove'] = remove
      PrintRecursive(command)
      local modifiedFile = RestApiPost('/instances/' .. instanceId .. '/modify', DumpJson(command), true)
      local modifiedId = ParseJson(RestApiPost('/instances/', modifiedFile)) ['ID']

      -- Delete the original instance
      -- Delete(instanceId)
   end
end

--function OnStableStudy(studyId, tags, metadata)
--
-- if (metadata['ModifiedFrom'] == nil and metadata['AnonymizedFrom'] == nil) then
--
--   local reconstructed = RestApiPost('/studies/' .. studyId .. '/reconstruct','{}')
--   print(reconstructed)
--   local studyinfo = {}
--   studyinfo['metadata'] = metadata
--   studyinfo['uuid'] = studyId
--   studyinfo['tags'] = DumpJson(tags, true)
--   print(DumpJson(studyinfo, true))
--   local savedstudy = RestApiPost('/studies/processed',DumpJson(studyinfo, true), false)  -- url, body, builtin, headers
--   print(savedstudy)
--
-- end
--
--end