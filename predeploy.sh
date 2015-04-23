#!/bin/bash

set -x
set -e

BUILD_SERVER=fr-hq-build-02.corp.withings.com
BUILD_USER=scaleweb
BUILD_PATH=/home/$BUILD_USER/guidra

ENVIRONMENT=$1
COMMIT=$2

# Sync source
#rsync -az --delete --exclude=".git/" --exclude="dist/" --exclude="bower_components/" --exclude="node_modules/" ./ $BUILD_USER@$BUILD_SERVER:$BUILD_PATH/
rsync -az --exclude=".git/" --exclude="dist/" --exclude="bower_components/" --exclude="node_modules/" ./ $BUILD_USER@$BUILD_SERVER:$BUILD_PATH/

# Build
ssh $BUILD_USER@$BUILD_SERVER "cd $BUILD_PATH; npm install"
ssh $BUILD_USER@$BUILD_SERVER "cd $BUILD_PATH; ./node_modules/bower/bin/bower install"
ssh $BUILD_USER@$BUILD_SERVER "cd $BUILD_PATH; ./node_modules/ember-cli/bin/ember build --environment production"

# Retrieve build
if [ -d dist ]; then
  rm -rf dist
fi
mkdir dist
rsync -az $BUILD_USER@$BUILD_SERVER:$BUILD_PATH/dist/ dist/

