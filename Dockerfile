FROM php:8.1-cli

WORKDIR /app
COPY . /app

EXPOSE 10000
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
