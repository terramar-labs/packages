#!/bin/bash

cp /etc/redis/sentinel.conf.orig /etc/redis/sentinel.conf
touch /tmp/sentinel.log

redis-server /etc/redis/sentinel.conf --sentinel >> /tmp/sentinel.log 2>&1 &

redis-server >> /tmp/sentinel.log 2>&1 &

tail -f /tmp/sentinel.log