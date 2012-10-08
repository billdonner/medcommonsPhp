there is a database in phr.siriushealthcare.com called nba
2 tables:

team
player

all obvious



nbaspider crawls the nba site and fills in the team and player tables

makeHealthURLs then adds healthurls and perfurls to the players 

pagemaker builds a et of static pages for sirius and medcommons to run the system
later we will make the team pages dynamic

footers and headers are trivial for now



n.b. - accounts are made on healthurl.myhealthespace.com, I think it better to do this
on another machine because we might actually separate all the teams onto separate appliances


finally, i just hacked a small rest interface into healthurl.myhealthespace.com/healthbook/rest.php
because i new it would work from my other hacking

thanks for having a look