-- Not implemented

function tableHasKey(table,key)
    return table[key] ~= nil and table[key] ~= ''
end


function ReceivedInstanceFilter(dicom, origin, info)

   -- Only allow incoming MR images
   if origin['RequestOrigin'] ~= 'Lua' then
--      PrintRecursive(dicom)
--      PrintRecursive(origin)
      return true
   else
      return true
   end
end
