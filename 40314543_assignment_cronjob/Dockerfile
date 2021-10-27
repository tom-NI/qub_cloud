FROM php:7.4-apache 

RUN apt-get update && apt-get -y install cron wget

RUN (crontab -l ; echo "*/5 * * * * wget http://monmet.40314543.qpc.hal.davecutting.uk/index.php") | crontab

CMD ["cron", "-f"]