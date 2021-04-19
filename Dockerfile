FROM ubuntu:18.04 AS builder

WORKDIR /dist

RUN apt-get -y install && apt-get update
RUN apt-get -y install wget unzip zip

ADD https://downloads.wordpress.org/plugin/paid-memberships-pro.latest-stable.zip /dist
RUN cd /dist && unzip paid-memberships-pro.latest-stable.zip && rm paid-memberships-pro.latest-stable.zip

ADD https://downloads.wordpress.org/plugin/wp-debugging.2.9.2.zip /dist
RUN cd /dist && unzip wp-debugging.2.9.2.zip && rm wp-debugging.2.9.2.zip

FROM wordpress:php7.2

WORKDIR /var/www/html/

COPY . ./wp-content/plugins/paystack
COPY --from=builder /dist/ ./wp-content/plugins
