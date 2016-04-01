# Script that launches all the goodies
# Will be run from the process that starts
# at startup/boot in the Rasperry.
# In this case I'll use the chrontab.

# Find the correct directory
cd /
cd /Desktop/Master Thesis

# Launche the controller and tell it what
# door to listen to and how long to run.
sudo python3 doorController.py

# Exit the dirctory
cd /