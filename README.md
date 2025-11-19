**Clothing Click Project**

Setup Clothing Click Project in local for Window
1.	Install XAMPP that support php7.4 version:
•	Download and install XAMPP from the official website: https://www.apachefriends.org/index.html
•	Follow the installation instructions provided.
2.	Start Apache and MySQL:
•	Once XAMPP is installed, open the XAMPP Control Panel.
•	Start the Apache and MySQL services by clicking on their respective "Start" buttons.
3.	Clone or Copy Your Laravel Project:
•	Copy your clothing project directory into the htdocs directory of your XAMPP installation. By default, this directory is located at C:\xampp\htdocs.
4.	Install Composer (If not already installed):
•	Composer is a dependency manager for PHP. Download and install Composer from https://getcomposer.org/download/
•	Follow the installation instructions provided.
5.	Install Dependencies:
•	Open a terminal (Command Prompt or PowerShell) on your computer.
•	Navigate to the root directory of Clothing click project.
•	Run the following command to install project dependencies:
 
6.	Set Up Environment Configuration:
•	Duplicate the .env.example file in project directory and rename it to .env.
•	Create new database in phpMyAdmin, and import database file **(Also in root folder you can find the sql file jeelinfotech_clothaapp.sql for database)**
•	Open the .env file and configure your database connection settings according to your local MySQL configuration in XAMPP.
•	 
7.	Generate Application Key:
•	While still in the terminal and within your Laravel project directory, run the following command to generate an application key:
•	 
8.	Access Project:
•	Open a web browser and navigate to http://localhost/clothing-click-app/public.
•	You should see your Laravel application running locally.

