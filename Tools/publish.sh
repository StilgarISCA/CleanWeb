#!/bin/bash
#
# This script simply publishes CleanWeb locally for development testing
# It mearly is a shortcut for copying files to what I have configured
# as my user's wwwroot, and makes a lot of assumptions about how the
# environment is configured.
#
echo "Publishing..."
cp -r ~/Projects/CleanWeb/Source/ ~/Sites
cp -r ~/Projects/CleanWeb/IntegrationTests/ ~/Sites
echo "Done."
echo "Setting permissions..."
chmod -R +rx ~/Sites
echo "Done."
