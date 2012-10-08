<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title></title>
    <link rel='Stylesheet' type='text/css' href='style.css' />
  </head>
  <body>

    <form method='get' action='search.php'>
      <label>Tracking number or MCID:
        <input type='text' name='q' value='<?php echo $q; ?>' /></label>
      <input type='submit' value='Go' />
    </form>

<?php if (isset($error)) { ?>
  <div class='error'>
    <?php echo $error; ?>
  </div>
<?php } ?>
  </body>
</html>
