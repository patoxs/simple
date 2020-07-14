docker-compose up --build -d && \
echo "Please wait while service is up..." && \
sleep 5 && \
docker exec simple2_web bash /var/www/simple/setup/simple.sh && \
echo "All done"