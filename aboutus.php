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
        <h4>This webpage is dedicated to showing off the works of Common Good Atlanta students. It acts as a platform for
          students to display their projects that they have worked on through out the span of their Shakespeare course.
        </h4><br>
        <p>What is Common Good Atlanta? Common Good Atlanta provides incarcerated people and formerly incarcerated people
          with broad, democratic access to higher education so they can develop a better understanding of both themselves
          and the societal forces at work around them. </p>
        <br><button class="btn myButton btn-lg">Get in Touch</button>
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
        <h2>About Our Shakespeare Course </h2><br>
        <h4><strong>MISSION:</strong> INSERT SHAKEPEARE COURSE DESCRIPTION: Our mission lorem ipsum dolor sit amet,
          consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
          veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</h4><br>
        <p><strong>VISION:</strong> Our vision Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
          tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
          laboris nisi ut aliquip ex ea commodo consequat.
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
          consequat.</p>
      </div>
    </div>
  </div>

  <div id="services" class="container-fluid text-center">
    <h2>CONTACT US</h2>
    <h4>Email: abc1234@gmail.com</h4>
    <p>If you have any questions please feel free to reach out to us. Our goal is to create an online presence for the
      Common Good Atlanta students, and we want to help them engage with the community.</p>
    <br>
  </div>

  <?php
    include "get-footer.php";
  ?>
</body>

</html>