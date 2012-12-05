create table inventionChance(typeid int,chance float);
insert into inventionChance (typeid,chance)
select typeid,CASE
WHEN t.groupID IN (419,27) OR t.typeID = 17476
THEN 0.20
WHEN t.groupID IN (26,28) OR t.typeID = 17478
THEN 0.25
WHEN t.groupID IN (25,420,513) OR t.typeID = 17480
THEN 0.30
WHEN EXISTS (SELECT * FROM eve.invMetaTypes WHERE parentTypeID = t.typeID AND metaGroupID = 2)
THEN 0.40
ELSE 0.00 
end
from eve.invTypes t,eve.invBlueprintTypes b where b.producttypeid=t.typeid;
