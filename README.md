# CGA Project Showcase
The following Customer Delivery Document provided by Junior design team JID 0326 is intended for use by Dr. Jonathan Shelley as well as a future Junior Design team.

The recommended skillset for the next team would be knowledge of PHP, familiarity with Linux, and knowledge of development on the LAMP stack (Linux, Apache, MariaDB/MySQL, PHP).

# Release Notes
### Features

The current website at [cgaprojectshowcase.com](https://www.cgaprojectshowcase.com) was created from scratch. This website hosts projects made by students of Common Good Atlanta (CGA), as well as containing portfolio-like profiles for these students. Any visitor to the site is able to browse these projects and student profiles. More details can be found in the Detailed Design Document. These are the main features we have implemented so far.

- Homepage has a search bar.
- Users can search for any student profile or project in the database.
- Homepage has a section that displays featured projects
- Projects can get tagged as “featured” by instructors, so that they show up in the homepage
- Users can click on a search result to be brought to a page that explains the project or student profile in more detail.
- The page that explains the project in more detail is `project.php`
- For student profiles: `student.php`
- Users can perform advanced search filters
  - Filter by project file extension (`.pdf`, `.png`, etc) or only select project or student search results
- Administrators can upload projects to the site
  - Respective page demonstrating this feature: `admin-upload.project.php`
- Administrators can create student profiles
  - Page: `admin-create-student.php`
- Administrators can edit the information in uploaded projects and student profiles.
  - Pages: `admin-edit-project.php` and `admin-edit-student.php`
- Administrators can delete projects and student profiles from the database.
  - Pages: `admin-remove-projects.php` and `admin-remove-students.php`
- Administrators must log-in before being able to do any of the above actions.
- Data for a project and student is stored in a MariaDB (MySQL) database.
- Larger data such as images and long descriptions are stored in the filesystem, under the directory `data/`.

### Known Issues

- **Important**: Administrators have the ability to delete projects from the system and only a couple people should know the credentials. However, if anyone else got ahold of the credentials, then they would be able to add/delete/edit any project or student profile on the website. This is extremely dangerous and all the site material can be compromised if a single password was leaked.
  - Possible fix: We could use the Apache configuration files to limit the administrator-only pages (any php file with `admin-` in the name) to only the specific IP addresses of the CGA administrators.
  - Possible fix: 
- Currently, only PNG images may be uploaded for a project’s cover image or a student’s profile picture.
  - This is because the server always looks to display the image named portrait.png or cover_image.png, and it doesn’t account for other image file types like portrait.jpg.
  - Possible fix: Let’s say the instructor uploads an image “apple.jpg” as a project’s cover image. Instead of storing it in the filesystem as just a generic “cover_image.png”, we store it as “apple.jpg” keeping the same name. And make sure to have the database entry path_to_cover_image pointing to that filename. So then the client can just draw apple.jpg instead of always looking for a file named cover_image.png.
- The MySQL credentials are hardcoded.
  - See the files mysql-connect.php and admin-mysql-connect.php
  - This means that any change to the mysql username/passwords would need to be reflected. A more long-term solution would be to use environment variables.
- The forms that administrators use to upload projects and create student profiles are vulnerable to SQL injection.
  - However, the admin is meant to be a user with many permissions anyway, such as editing profiles and even deleting projects.
Since the admin has permission to remove projects anyway, it is not a priority to prevent SQL injection   from the admin’s point of view, because the admin is meant to have these kinds of permissions. From a “good code” standpoint however, this may be slightly undesirable.
- The “About Us” page needs to have its text updated.
- Website directories’ layouts are visible to any user. Test it by typing cgaprojectshowcase.com/data.
  - Use Apache configuration files to limit access.


# Install Guide

This is the guide for testing the website code on a local Windows machine, using a software called WAMPserver that emulates a LAMP stack on your Windows machine. If you are using Linux, then you may use a LAMP stack installer and install apache on your own.

WAMPserver is not required. Any method that allows you to host LAMP stack on your local machine for testing will suffice, such as a virtual machine.

### Prerequisites:
Windows 7 or up, 64-bit

### Dependencies:
- Download WAMPserver, 64-bit version (Site: https://www.wampserver.com/en/)
- Sourceforge [Download](https://sourceforge.net/projects/wampserver/files/ )
- Follow the installation process. Make note of where the `wamp64` directory is installed. It may be directly under the C: drive root.

### Download:
The code repository is currently hosted by __________________________

### Build:
Clone the repository to your local machine. You can clone it to anywhere you like.

### Installation:


Now we need the WAMPserver to host the code from the Github repo.
- Create a new directory under `wamp64/www` titled `cgaprojectshowcase`.
- Path should look like `wamp64/www/cgaprojectshowcase`
- Copy the code from the repository and paste it into that new directory.
- Alternatively, you could have cloned the repository directly into `cgaprojectshowcase`. This could be more convenient when editing the code, as it skips the copy and pasting every time you want to push a change to the repo.

The WAMPserver now hosts the code for the website. However, the MariaDB database has not been set-up yet, so if we tried to access the site, it would throw errors because there are no tables in the database yet. Let’s first run the site to see if it works on the WAMPserver, then we’ll set up the database.


### Running the Application:
- Navigate to the `wamp64` folder and double-click `wampmanager.exe`. This starts the WAMPserver.
- In a web browser, navigate to the page `localhost/cgaprojectshowcase`
It should take you to the homepage. But it will throw some errors because there are no tables in the database yet.


**Let’s set up the database tables.**

- Access the MariaDB console: Left-click -> MariaDB -> MariaDB Console
- Just enter `root` as username, and enter nothing for password.
- Enter command `CREATE DATABASE cga_showcase;`
- Under the `dev-test` folder, open `create-tables.txt`. It contains four SQL commands to create the four tables for the database. Run all four commands.
- Under the `dev-test` folder, open `add-test-admin.txt`. Under the dashed line, there is the SQL command to add an admin to the `admins` table. It uses placeholder credentials for testing purposes. Run the command.

**Next, set up the MariaDB users.**

- Note that having these users is mainly for organizational purposes. They are not strictly necessary for security because only the server-side itself is directly connecting to the database. Site visitors don’t directly access the database; rather, the PHP code (that runs for each site visitor) is executed by the server and connects to the database itself.

For consistency with the live server on EC2, we should set the users and their credentials to be the same as those on the live server.

- Under the `dev-test` folder, open `create-db-users.txt`. Run the two commands to create the `cga-admin` and the `visitor` users.
- Open `db-user-perms.txt` and run those commands to set the user permissions

Now the database should be fully set-up and have identical settings to the database on the live server.

Last quick thing, let’s configure the PHP version of the WAMPserver to keep the environment consistent with the one on the live server (which runs PHP 7.2).
- On the bottom-right of your screen (far-right of the taskbar), locate the icon for the WAMPserver (should look like a 'w' with a rounded square around it.)
 
- Set PHP version: Left-click -> PHP -> Version -> 7.2.4

- You may also click this icon to start/stop the WAMPserver and edit configuration files for Apache, MariaDB/MySQL, and PHP.

So we can test the site fully now:
- Navigate to `localhost/cgaprojectshowcase`. Try logging-in, with username `admintest` and `passwordtesting123` respectively. Note that these credentials are stored in the `admins` table in MariaDB, and were added from the command in `add-test-admin.txt`.
- You can now click `Upload Projects` or `Add Student` at the top and follow the instructions accordingly.
- For a student or project that you added, you should be able to search for from the homepage search bar. Test if that functionality works properly.




### To change a password:
1. Start the WAMPserver.
2. Generate the hash for the new password that you want to change it to. Under the `dev-test` folder, edit the `hash-gen.php` file. Change the `$password` variable to the new password.
3. In a web browser, navigate to `localhost/cgaprojectshowcase/dev-test/hash-gen.php`. The page should just have the hash of the password.
4. Use a mySQL command to edit the password of a user in the `admins` table. The new password value should be the hash.

**Please feel free to change any placeholder credentials. This also includes the passwords for MariaDB users that were created from the commands in `create-db-users.txt`**

# Deployment Guide

This is the guide for deploying code to the live server, hosted on the EC2 Cloud of Amazon Web Services (AWS). We will use WinSCP for file transfer the webserver, and PuTTY to execute commands on the linux machine on the cloud. (For more information on how we set-up the EC2 cloud, see the following section “Amazon Web Services (AWS) Set-Up Information”).

Certain files and credentials vital to deployment will be needed. Before you start this guide, please be in contact with Dr. Jonathan Shelley, who has access to these vital materials that will be mentioned in the steps below.

### Give SSH access to your IP

First, you need to allow your computer to run commands on the webserver in order to transfer files.
1. Log into Amazon Web Services (AWS). Please contact Dr. Jonathan Shelley for the credentials.
2. After you have signed in, navigate to the EC2 console https://console.aws.amazon.com/ec2/v2/
3. Click on “Instances”.
4. Click on the Instance ID of “CGA project showcase site” (it should be the only one there)
5. Click on the “Security” tab below the Instance Summary panel.
6. Under “Security Groups”, click on the only security group there (should be `sg-0a1b907c100d7a158 (jd_SG_useast1)`)
7. Under “Inbound Rules”, click “Edit Inbound Rules”.
8. Click “Add Rule”
9. Select “SSH” for the type (SSH is port 22 and is the port you connect to in order to run commands on the server)
10. Select “Custom” for the source, and type your computer’s public IP address there https://whatismyipaddress.com/ 

Note: It is extremely important that only a select few IP addresses are allowed to run commands and transfer files to the server… so your IP will likely be one of very few that port 22 is opened to.

11. Save the rule.


We still need to download software to connect to the server through port 22. We also need a “key file” which is basically the password to use when connecting to the server through port 22. Please contact Dr. Jonathan Shelley for the `.ppk` key file. Handle the key with extreme caution however, as anyone with the key file can edit files on the server as a root user and basically do anything on the server.

### Install and run PuTTY

PuTTY will be used for running commands on the live server:
1. Download PuTTY 64-bit [here](https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html)
2. Run PuTTY
3. For the host name, put `ec2-user@cgaprojectshowcase.com`. ec2-user has sudo access.
4. Connection type, put SSH.
5. On the Category panel on the left, navigate to Connection -> SSH -> Auth. Click on “Browse”, then select the `.ppk` file.
6. On the Category panel, click “Session”, enter a name for the session in Saved Sessions, and then click “Save”.
7. Click “Open” to connect to the server.
8. If successful, you should now be able to run Linux commands on the server.

### Install and run WinSCP

WinSCP will be used for transferring files to the live server:
1. Download WinSCP [here](https://winscp.net/eng/download.php)
2. Launch WinSCP.
3. Under Session, fill in the following:

- File Protocol: SFTP
- Host Name: cgaprojectshowcase.com
- User name: ec2-user

4. Click on “Advanced”, then click “Authentication” on the left, then where it says Private Key File, browse for the `.ppk` file.
5. Click “Save” to save these settings
6. Click “Login”
7. If successful, you should see a filesystem on the left and on the right. You should now be able to drag files from your computer (left) to the server (right).

The typical workflow is to work on some code, then save it, then manually drag the new code to the server filesystem.

From the Github repo, you can transfer any code to the server EXCEPT the folder `dev-test`! It contains the commands to setup database records and add passwords. (used in Post-Configuration section in Install Guide above). Accidental deployment would expose the database setup to any site visitor. `dev-test` is supposed to only be used in development, on a test server using WAMPserver for example, which was used in the “Installation” section of this document.

**Important**: The password for admin is extremely important and if it gets leaked then all the content on the site risks getting deleted or vandalized since admins can remove and edit projects. So… in the case that the admin password gets leaked… a developer who setup the WAMPserver should generate a new password with `hash-gen.php` and come up with a new username, then edit the mysql table on the live server to have those new credentials. Then they need to restart everyone’s sessions… do this by deleting every file on the live server in /var/lib/php/session.

# Amazon Web Services (AWS) Set-up Information

From Amazon Web Services, we used:

**Amazon Elastic Compute Cloud (EC2)**

- This cloud hosts the webserver on a linux machine

**Amazon Route 53**

- This simply routes the registered domain `cgaprojectshowcase.com` and `www.cgaprojectshowcase.com` to the static IP address 18.208.49.70

### Create EC2 Instance

First, we had to set-up an EC2 instance, which would be a virtual Linux machine on the cloud. We followed these tutorials

- [Set up security group and key file](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/get-set-up-for-amazon-ec2.html)
- [Create and launch an EC2 instance](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/EC2_GetStarted.html#ec2-launch-instance)

### Install LAMP server on instance

Next, once we have launched the instance, we needed to install the LAMP server on the Linux machine. We followed [this tutorial](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ec2-lamp-amazon-linux-2.html)
Except that we did not do “Step 4” so we did not install phpMyAdmin (and still haven’t). This was because we didn’t have HTTPS set up yet.

### Register Domain Name

Now, the site was able to be accessed, but only through a temporary IP address. So our next steps were to register a domain name for the site and a Static IP address (a permanent IP address). We followed [this](https://aws.amazon.com/getting-started/hands-on/get-a-domain/)

### Secure the site with HTTPS

Now that the site could be accessed by typing [cgaprojectshowcase.com](https://cgaprojectshowcase.com) in your browser. But it was not secured by HTTPS yet, so the browser would give warning to anyone visiting the site, plus any login information would be visible to everyone. That means the next step was to secure the site with HTTPS by obtaining a TLS (or SSL) certificate.

We followed this for SSL installation: [SSL on amazon linux 2](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/SSL-on-amazon-linux-2.html)

We completed “Step 1” and the “Certificate automation: Let's Encrypt with Certbot on Amazon Linux 2” sections.
This means
- We enabled TLS on the server, and used a self-signed certificate temporarily (followed “step 1”)
- We used Certbot as a Certificate Authority (CA) to generate a CA-signed certificate.
- Since the certificate expires after 90 days, we automated the certificate renewal using a cron job. (Followed the instructions in the “Configure automated certificate renewal” section).

After Certbot generated a certificate for us, it left us with the following info for reference.
```
IMPORTANT NOTES:
    Congratulations! Your certificate and chain have been saved at:
    /etc/letsencrypt/live/cgaprojectshowcase.com/fullchain.pem
    Your key file has been saved at:
    /etc/letsencrypt/live/cgaprojectshowcase.com/privkey.pem
```
# Questions?
Contact the previous junior design team member Andrew Peng: apeng34@gatech.edu 
