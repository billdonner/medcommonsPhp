<div id='helpbar'><p><label>Problems?</label>&nbsp;&nbsp;&nbsp;<a href='#'>Click Here</a></p></div>
<div id='helpers' class='hidden'>
    <h5>Sorry you're having problems!</h5>
    <div class='inner'>
        <div id='problemInputFields'>
	        <form name='supportForm'>
		         <input type='hidden' name='problemId'/>
			         <label for='description'>Describe what's going wrong:</label>
			            <textarea name='description' rows="5" cols="50"></textarea>
			            <input type='submit' name='submit' id='submitProblem' value='Send Problem Report'/>
	        </form>
	        <div id='privacy'>A log of your session will be sent along with your report.</div>
        </div>
        
        <div id='problemResultFields' class='hidden'>
            <p>Your problem has been reported and a log of your session has been 
                   uploaded for review.</p>
                <p>This problem report has been assigned an ID:  <span id='problemIdResult'></span></p>
                <p><a href='javascript:cancelReload()'>Click here</a> to reload your page and try again.</p>
                <p>Thank you for reporting this issue!</p>
            </div>
		            
        
    </div>
</div>
