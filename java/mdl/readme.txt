To build - first make sure you have the latest wsdl:
http://ibmod235.dal-ebis.ihost.com:8090/bridge/services/ohf-bridge?wsdl
and put that into etc/ohf/ohf-bridge.xml

Then create the stub files with ant generate-ohf-bridge-stub

and then just run ant clean; ant
to build everything else.

Drop build/dist/mdl.war into your webapp directory - it should just deploy.
