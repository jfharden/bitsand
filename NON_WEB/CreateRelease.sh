#!/bin/bash
# Script to create a Bitsand release

# Do not include trailing /
SOURCE_DIR=/home/russ/Local
WORK_DIR=/home/russ/tmp

clear

read -p "Enter version number: " VERSION
RELEASE_DIR="$WORK_DIR/Bitsand_$VERSION"
mkdir -p $RELEASE_DIR
cp -r $SOURCE_DIR"/bitsand" $RELEASE_DIR
rm -rf $RELEASE_DIR"/bitsand/NON_WEB"

# Copy documentation
cp $SOURCE_DIR"/bitsand/NON_WEB/ChangeLog.txt" $RELEASE_DIR"/CHANGE_LOG.txt"
cp $SOURCE_DIR"/bitsand/NON_WEB/BlankSign-inSheet.pdf" $RELEASE_DIR"/BlankSign-inSheet.pdf"

# Remove .svn folders
rm -rf $RELEASE_DIR"/bitsand/.svn/"
rm -rf $RELEASE_DIR"/bitsand/admin/.svn/"
rm -rf $RELEASE_DIR"/bitsand/help/.svn/"
rm -rf $RELEASE_DIR"/bitsand/img/.svn/"
rm -rf $RELEASE_DIR"/bitsand/inc/.svn/"
rm -rf $RELEASE_DIR"/bitsand/install/.svn/"

cp $SOURCE_DIR"/bitsand/NON_WEB/README.txt" $RELEASE_DIR"/README.txt"
