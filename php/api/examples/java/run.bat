@echo off
echo .
echo Compiling ...
javac -classpath lib/commons-codec.jar;lib/jsonrpc.jar;lib/oauth.jar -d . src/GetCCR.java

echo .
echo Running ...
echo .
echo ----------------- Output ---------------------------------
java -classpath lib/commons-codec.jar;lib/jsonrpc.jar;lib/oauth.jar;. GetCCR
echo .
echo ----------------- End Output -----------------------------
echo .
