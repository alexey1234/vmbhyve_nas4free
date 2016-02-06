#!/bin/sh

#
# File name:	bhyve_install.sh
# Author:      Alexey Kruglov
# Modified:		Feb 2016
# 
# Purpose: 		This script is used to intall/update the extension used by
# 				Nas4Free's lighttpd webgui server. It checks the environment,
#				then downloads the latest copy of the software from GitHub,
#				extracts it to a staging directory, checks the upgrade
#				path/status, installs the new software and creates the 
#				appropriate symlinks.
#  
# Cloned from thebrig installer


# define our bail out shortcut function anytime there is an error - display the error message, then exit
# returning 1.
exerr () { echo -e "$*" >&2 ; exit 1; }

# Determine the current directory
# Method adapted from user apokalyptik at
# http://hintsforums.macworld.com/archive/index.php/t-73839.html
STAT=$(procstat -f $$ | grep -E "/"$(basename $0)"$")
FULL_PATH=$(echo $STAT | sed -r s/'^([^\/]+)\/'/'\/'/1 2>/dev/null)
START_FOLDER=$(dirname $FULL_PATH | sed 's|/thebrig_install.sh||')

# First stop any users older than 9.3 from installing
MAJ_REL=$(uname -r | cut -d- -f1 | cut -d. -f1)
MIN_REL=$(uname -r | cut -d- -f1 | cut -d. -f2)

# Prevent users from breaking their system
if [ $MAJ_REL -lt 10 -a $MIN_REL -lt 2 ]; then
	echo "ERROR: This version of Bhyve is incompatible with your system!"
	exerr "ERROR: Please upgrade Nas4Free to version 10.2 or higher!"
fi

# Store the script's current location in a file

#Store user's inputs
# This first checks to see that the user has supplied an argument
if [ ! -z $1 ]; then
    # The first argument will be the path that the user wants to be the root folder.
    # If this directory does not exist, it is created
    EXTENSION_ROOT=$1    
    
    # This checks if the supplied argument is a directory. If it is not
    # then we will try to create it
    if [ ! -d ${START_FOLDER} ]; then
        echo "Attempting to create a new destination directory....."
        mkdir -p ${START_FOLDER} || exerr "ERROR: Could not create directory!"
    fi
else
# We are here because the user did not specify an alternate location. Thus, we should use the 
# current directory as the root.
    EXTENSION_ROOT=${START_FOLDER}
fi

# Make and move into the install staging folder
mkdir -p ${START_FOLDER}/install_stage || exerr "ERROR: Could not create staging directory!"
cd ${START_FOLDER}/install_stage || exerr "ERROR: Could not access staging directory!"

echo "Retrieving the vmbhyve_nas4free branch as a zip file"
fetch https://github.com/alexey1234/vmbhyve_nas4free/archive/master.zip || exerr "ERROR: Could not write to install directory!"

# Extract the files we want, stripping the leading directory, and exclude
# the git nonsense
echo "Unpacking the tarball..."
tar -xf master.zip --exclude='.git*' --strip-components 1
echo "Done!"
rm master.zip

echo "Detecting current configuration..."
. /etc/rc.subr
. /etc/configxml.subr
. /etc/util.subr

INSTALLED=`configxml_get //bhyve/homefolder`
if [ ! -z ${INSTALLED} ]; then
	echo "Look like update extension"
	EXTENSION_ROOT=${INSTALLED}
	cp -f -R ${START_FOLDER}/install_stage/* ${EXTENSION_ROOT}/
	echo "Congratulations! You have fresh version."
	ACTION_MSG="Updated"
else
	echo "Look like fresh install"
	cp -f -R ${START_FOLDER}/install_stage/* ${EXTENSION_ROOT}/
	# Create the symlinks/schema. We can't use thebrig_start since
	# there is nothing for the brig in the config XML
	mkdir -p /usr/local/www/ext
	ln -s ${EXTENSION_ROOT}/conf/ext /usr/local/www/ext/vm_bhyve
	cd /usr/local/www
	# For each of the php files in the extensions folder
	for file in ${EXTENSION_ROOT}/conf/ext/*.php
		do
			# Create link
			ln -s "$file" "${file##*/}"
		done
	echo "Congratulations! The Bhyve extension was installed. Navigate to rudimentary config tab and push Save."
	ACTION_MSG="fresh installed"
fi
# Store the install destination into the /tmp/bhyve.install
echo ${EXTENSION_ROOT} > /tmp/bhyve.install
# Get rid of staged updates & cleanup
cd ${START_FOLDER}
rm -rf install_stage
chmod -f -R 755 ${EXTENSION_ROOT}/conf/bin
chmod -f -R 755 ${EXTENSION_ROOT}/conf/lib
# Log it!
CURRENTDATE=`date -j +"%Y-%m-%d %H:%M:%S"`
logger "[$CURRENTDATE]: Bhyve installer!: installer: ${ACTION_MSG} successfully"
