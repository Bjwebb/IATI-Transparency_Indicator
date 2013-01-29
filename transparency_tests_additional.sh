#! /bin/bash
#  
##The tests. Feel free to comment out ones you don't want to run
echo "Timeliness_Transactions_1.1.php"
php Timeliness_Transactions_1.1.php > csv/Timeliness_Transactions_1.1.csv

echo "Alignement_with_financial_year_Transactions_4.1.php"
php Alignement_with_financial_year_Transactions_4.1.php > csv/Alignement_with_financial_year_Transactions_4.1.csv
  
echo "Alignement_with_financial_year_Budgets_4.2.php"
php Alignement_with_financial_year_Budgets_4.2.php > csv/Alignement_with_financial_year_Budgets_4.2.csv

echo "Activity_planning_3.php"
php Activity_planning_3.php > csv/Activity_planning_3.csv
  



