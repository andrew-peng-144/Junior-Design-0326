<?php
  //directly output contents of footer.html 
  $footer_html = "";
  $fh = fopen("footer.html", 'r');
  while ($line = fgets($fh)) {
    $footer_html .= $line;
  }
  fclose($fh);
  echo $footer_html;

?>