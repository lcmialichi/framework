# QuickStart
---
```bash
# copy .env
    cp example/.env .env
# build docker image
    docker-compose build
# run container as robot
    docker-compose up -d
# update composer`s dependencies
    docker-compose exec app composer update
 ````