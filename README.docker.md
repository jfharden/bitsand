# Local development in docker

## Prerequisites

1. docker engine 1.13.0+
2. docker-compose 1.10.0+

## First time setup

1. Build and start the container

       docker-compose build
       docker-compose up

2. Visit the install page at http://127.0.0.1/install/

3. Go through the usual install on that page described in the main bitsand README
   (https://github.com/PeteAUK/bitsand/blob/master/README.md)

## Day to day usage

1. Just start the container

       docker-compose up

## Running the production-like container

The default container will mount the bitsand directory into the container as the website to serve, this means changes
you make to the files on disk locally will be reflected instantly in the running container. The downside to this is you
are not doing a real test of the docker container which you want to run in production, you also have the NON\_WEB and
install directories available, these should _not_ be available when running bitsand in production, having them is an
extremely serious security risk. 

You can start the production version of the container by running with the production docker-compose file instead.

      docker-compose -f docker-compose.production.yaml up

Remember, while running the production container no changes you make locally will be reflected in the running
container, to see any change in this container you will need to rebuild the container and start it again.

      docker-compose -f docker-compose.production.yaml down
      docker-compose -f docker-compose.production.yaml build
      docker-compose -f docker-compose.production.yaml up

## Emails

The docker-compose will run a service called Mailhog which will capture all emails sent by bitsand, you can view any
emails it sends at http://127.0.0.1:8025

Note all emails will be lost if you stop and start the service
