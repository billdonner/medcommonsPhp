---
--- Decided to link internal rls's to practices rather than groups.
--- 
alter table groupccrevents change column groupinstanceid practiceid int(11); 
rename table groupccrevents to practiceccrevents;

