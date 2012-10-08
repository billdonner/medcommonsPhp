<p>Thanks, <?=$given?> for granting us access to your health record to help you understand
your risk of cancer better.</p>
<p>Here are the results of our analysis:</p>
<ul>
<li>According to your CCR, you are about <b><?=$age?></b> years old and your gender is <b><?=$gender?></b>.</li>
<li>The incidence of cancer for someone of your age and gender is <span style='font-size: large; color: red;'><?=$incidence?> in every 100,000 persons</span></li>
<li>This means that your personal risk of cancer is approximately <?= $incidence / 100000 ?>, or about <?= 100 * $incidence / 100000 ?> %</li>
</ul>
<p><a href='my_cancer_risk.php'>Try another CCR</a></p>
<p style='font-size: small;'>Note: The Authorization Token that we used for accessing your CCR was <?=$token?></p>
