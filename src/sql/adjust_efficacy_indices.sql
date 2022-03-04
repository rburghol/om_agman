
-- find efficacy props needing adjustment
-- todo: this is a hack to display the best efficacy of a given week
--       for a specific pathogen.  This is a great goal, but the technique
--       adapts to limitations of views by giving each efficacy a numerical index
select featureid, entity_type, propname, propvalue, propcode 
from dh_properties 
where varid in (
  select hydroid from dh_variabledefinition 
  where plugin = 'dHVariablePluginEfficacy'
)
;