@echo off
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root depedzc_inventory > "C:\Users\Axxer\Documents\Data\Axxer's Environment\depedzc_inventory_db_backup.md"
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root bwai_hackathon > "C:\Users\Axxer\Documents\Data\Axxer's Environment\bwai_hackathon_db_backup.md"
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root inventory_db > "C:\Users\Axxer\Documents\Data\Axxer's Environment\inventory_db_db_backup.md"
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root kevintory_db > "C:\Users\Axxer\Documents\Data\Axxer's Environment\kevintory_db_db_backup.md"
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root test > "C:\Users\Axxer\Documents\Data\Axxer's Environment\test_db_backup.md"
echo Backup Completed
