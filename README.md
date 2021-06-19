This application is the test task for "ITRANSITION" company.

Application represents a command-line interface for data import from CSV table to MySQL database table.

Application provides an opportunity to test import from CSV without insertion data into database
(use --test option in command line for this).

ATTENTION!

- columns delimiter in CSV-file MUST be "," (comma)
- If Discontinued is pointed in CSV-file as supported for the Product, Discontinued date in database table sets to current date

Application does NOT import from CSV:
 - records with products, which costs less than 5 currency units and quantity of them is less than 10
 - records with products, which costs more than 1000 currency units
 - records with string columns, which contain "," and are not in quotes
   
Application imports WRONGLY from CSV
 - records with float or int columns, which contain non-numeric data. They might be set to 0 in the database table

INSTALLATION

After copying The Project files to the catalog you want, do the following.

    - make sure that php and composer are available by these names from console on your OS (environment variable contains correct path to them)
    - open console
    - change current directory of console to where you have just located the Project
    - execute the following commands

        composer install
        php bin/console doctrine:migrations:migrate

Use and enjoy!

USAGE

 Open console in your OS, change current directory to the directory of this Project and type:

    php bin/console app:product:import --filepath="/path/to/your/csv/file.csv"

 NOTICE: parameter "filepath" is required.

If you want to run this command in a test mode (import from CSV without insertion into database), run:

    php bin/console app:product:import --filepath="/path/to/your/csv/file.csv" --test

 

        