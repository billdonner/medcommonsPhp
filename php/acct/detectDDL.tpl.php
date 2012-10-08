<?
/**
 * General DDL detection HTML.
 * <p>
 * This code works with dod.js to automatically detect and coach the user through
 * starting or updating their DDL.  Would be nice to rework it as pure javascript.
 */
?><div id='wholeStep1'>
    <div id='step1boxes' class='section'>
        <p class='middled' id='detecting'><img src='/yui/2.6.0/assets/skins/sam/loading.gif'/> Please wait while 
           we detect your current system setup!</p>
        
        <p class='middled hidden' id='waiting'><img src='/yui/2.6.0/assets/skins/sam/loading.gif'/> 
           Waiting for DDL to start on your computer.  This may take a few minutes. You may see
           some security warnings while the startup or installation is proceeding.</p>
           
        <div id='installDDLStep' class='hidden'>
            <p>No existing DDL was detected.  In order to upload images, you must first install 
               and / or start the MedCommons DDL service on your computer:</p>
                <p><a href='<?=$startDDLUrl?>' id='startDDLLink'>Click Here to Start DDL.</a></p>
        </div>
        <div id='foundDDL' class='hidden'>
            <p class='middled'><img src='images/bigtick.png'/> &nbsp; A local DDL was found running on your computer!  
                You are ready to upload data.</p>
            <p><button id='continueFillOutFormButton' onclick='blindUp("step1boxes",{duration:0.5}); blindDown("fillOutFormStep",{duration:0.5}); removeElementClass($$("#wholeStep1 h3 img")[0],"hidden");'>Continue</button>
        </div>
        <div id='restartDDLStep' class='hidden'>
            <p><b>Update Required</b></p>
            <p>A DDL was detected on your computer, but it needs to be restarted to
               update to work with this page.</p>
                <p><a href='<?=$startDDLUrl?>' id='restartDDLLink'>Click Here to Update Your DDL.</a></p>
        </div>
        <div id='restartingDDL' class='hidden'>
            <p><b>DDL Updating</b></p>
            <p>Please wait until your DDL has updated and restarted, then click below to refresh your page.</p>
                <p><a href='javascript:window.location.reload()'>Click Here when DDL has Updated</a></p>
        </div>
    </div>
</div>
