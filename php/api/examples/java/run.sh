#!/usr/bin/env bash
echo 
echo "Compiling ..."
echo
javac -classpath "lib/commons-codec.jar:lib/jsonrpc.jar:lib/oauth.jar" -d . src/GetCCR.java || exit 1

echo 
echo "Running ..."
echo
echo "----------------- Output ---------------------------------"
java -classpath "lib/commons-codec.jar:lib/jsonrpc.jar:lib/oauth.jar:." GetCCR
echo "----------------- End Output -----------------------------"
echo 
