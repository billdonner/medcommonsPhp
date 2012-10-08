    <?if($info):?>
      <p>Open or create a CCR to see details here.</p>
      <div class="demobutton">
      <?if(!isset($info->practice)): /* is not a practice member */ ?>
        <a href="<?=new_ccr_url($info->accid)?>" target="ccr">
          <img type="image" src="images/createphr.png" title="Create your PHR"/>
        </a>
      <?endif;?>
    <?else:?>
      <p>Open a CCR or Log in to see your Current CCR here!</p>
      <div class="demobutton">
      <a href="patientdemo" target="ccr"><a href="patientdemo" target="ccr"><img type="image" src="images/patientdemob.png" title="Try Patient Demo >>"/></a>
    <?endif;?>
    </div>
