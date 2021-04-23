<?//The "About Us" page. No PHP, except for including the topnav and footer.?>

<!DOCTYPE html>
<html lang='en'>

<head>
  <title>About CGA Project Showcase</title>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

  <link rel="stylesheet" type="text/css" href="css/aboutus.css">
</head>

<body>
  <?php
  include "get-topnav.php";
  ?>

  <div id="about" class="container-fluid">
    <div class="row">
      <div class="col-sm-8">
        <h2>About Us </h2><br>
        <h4>The Common Good Atlanta Project Showcase is dedicated to sharing the works of Common Good Atlanta students.
        We encourage visitors to take inspiration from these works and use them to create assignments and lessons for their own pedagogical endeavors.
        </h4><br>
        <p>What is <a href="http://www.commongoodatlanta.com">Common Good Atlanta</a>? Common Good Atlanta provides incarcerated people and formerly
        incarcerated people with broad, democratic access to higher education so they can develop a better understanding of both themselves and the
        societal forces at work around them.</p>
        <br><button class="btn myButton btn-lg" onclick="location.href = '#contact'">Get in Touch</button>
      </div>
      <div class="col-sm-4">
        <img src="data/home/cgalogo.png">
      </div>
    </div>
  </div>

  <div class="container-fluid bg-grey">
    <div class="row">
      <div class="col-sm-4">
        <img src="data/home/shakespeare.png">
      </div>
      <div class="col-sm-8">
        <h2>About the Shakespeare and the Common Good in Atlanta Course </h2><br>
        <h4>Taught in Fall 2020 and Spring 2021, the Shakespeare and the Common Good in Atlanta
        offered students an interdisciplinary survey of Shakespeare and his works.</h4><br>
        <p>Hosted by Common Good Atlanta and taught in collaboration with actors from the
        <a href="https://www.shakespearetavern.com">Shakespeare Tavern Playhouse</a>,
        this weekly remote online course provided returning citizens with a space to read a selection of Shakespeare's
        play and poems, perform dramatic readings of notable speeches, and author original essays, poems, and plays
        about literature and the humanities. The materials on this site have been authored and inspired by students
        and are designed to help the instruction of Shakespeare, literature, and the humanities to college programs
        inside and outside of prison. Common Good Atlanta will continue to expand this site and include student-led
        pedagogical materials that result from its future class offerings.</p>

        <p>Both the Shakespeare and the Common Good in Atlanta class and the Common Good Atlanta Showcase site were made
          possible thanks to a grant from the <a href="https://www.whiting.org">Whiting Foundation</a>.</p>
      </div>
    </div>
  </div>

  <div id="contact" class="container-fluid text-center">
    <h2>CONTACT US</h2>
    <h4>Email: jonathan.shelley@lmc.gatech.edu</h4>
    <p>If you have any questions, please reach out. This site is a long-term project to promote the voices of Common Good
      Atlanta students so that they have a presence online and in the development of educational curriculum.</p>
    <br>
  </div>

  <?php
  include "get-footer.php";
  ?>
</body>

</html>