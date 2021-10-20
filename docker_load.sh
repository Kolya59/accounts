docker-compose up -d
sleep 20
cat ./data/docker-entrypoint-initdb.d/user.sql | docker exec -i homework_mysql_1 mysql -uroot --password=123456
cat ./data/docker-entrypoint-initdb.d/accounts.sql | docker exec -i homework_mysql_1 mysql -uroot --password=123456
