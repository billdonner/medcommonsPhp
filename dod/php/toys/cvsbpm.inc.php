<?php

function vx($a,$b,$c,$d,$e,$f,$g,$h,$i,$j)
{ 
// handle messages from cvs blood pressume meter looking like	
//100015|0172112852|Adrian|Gropper|agropper@medcommons.net|1000068|128|75
//   $a      $b       $c     $d        $e                      $f   $g  $h
$signature = "MEDCOMMONSDEVELOPERKEYGOESHERE"; // MedCommons Developer Key
$xml=<<<XXX
<ContinuityOfCareRecord xmlns="urn:astm-org:CCR" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:astm-org:CCR CCR_20051109.xsd">
  <CCRDocumentObjectID>p82CmfJp4o6Me6Gb4QdxRw1oBqk=</CCRDocumentObjectID>
  <Language>
    <Text>English</Text>
  </Language>
  <Version>V1.0</Version>
  <DateTime>
    <ExactDateTime>2006-09-12T00:45:23Z</ExactDateTime>
  </DateTime>
  <Patient>
    <ActorID>Patient1</ActorID>
  </Patient>
  <From>
    <ActorLink>
      <ActorID>$b</ActorID>
      <ActorRole>
        <Text>cvs blood pressure meter $a observation $f</Text>
      </ActorRole>
    </ActorLink>
  </From>
  <Body />
  <Actors>
    <Actor>
      <ActorObjectID>AA0001</ActorObjectID>
      <InformationSystem>
        <Name>Observation $g $h</Name>
        <Type>Repository</Type>
        <Version>V1.0 BETA</Version>
      </InformationSystem>
      <Source>
        <Actor>
          <ActorID>AA0001</ActorID>
        </Actor>
      </Source>
    </Actor>
    <Actor>
      <ActorObjectID>Patient1</ActorObjectID>
      <Person>
        <DateOfBirth>
          <ApproximateDateTime>
            <Text>$b</Text>
          </ApproximateDateTime>
        </DateOfBirth>
      </Person>
      <Source>
        <Actor>
          <ActorID>AA0002</ActorID>
        </Actor>
      </Source>
    </Actor>
    <Actor>
      <ActorObjectID>To1</ActorObjectID>
      <Source>
        <Actor>
          <ActorID>AA0001</ActorID>
        </Actor>
      </Source>
    </Actor>
    <Actor>
      <ActorObjectID>From1</ActorObjectID>
      <Source>
        <Actor>
          <ActorID>AA0003</ActorID>
        </Actor>
      </Source>
    </Actor>
    <Actor>
      <ActorObjectID>5447948900602971</ActorObjectID>
      <InformationSystem>
        <Name>$c $d</Name>
        <Type>Repository</Type>
      </InformationSystem>
      <EMail>
        <Value>$e</Value>
      </EMail>
      <Source>
        <Actor>
          <ActorID>5447948900602971</ActorID>
        </Actor>
      </Source>
    </Actor>
  </Actors>
</ContinuityOfCareRecord>


XXX;
return array($signature,$xml);
}
?>