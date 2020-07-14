RUN apt-get install -y gnupg2
RUN rm rf /var/lib/apt/lists/ && curl -sL https://deb.nodesource.com/setup_13.x | bash -
RUN apt-get install nodejs -y