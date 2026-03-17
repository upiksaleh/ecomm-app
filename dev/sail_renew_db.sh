sail=./vendor/bin/sail
$sail down
docker volume rm ecommerce_sail-pgsql
$sail up -d

