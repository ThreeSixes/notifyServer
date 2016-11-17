#!/usr/bin/python

try:
	import config
except:
	print("Failed to open config.py. Please copy config.py.example to config.py and edit it.")

import notifyHandler
import time
import traceback
from pprint import pprint


# If we're just being called...
if __name__ == "__main__":
	# Set up the client object and listen on the specified queue.
	client = notifyHandler.notifyListener(
		config.notifySettings['redisHost'],
		config.notifySettings['redisPort'],
		config.notifySettings['redisChannels']
	)
	
	# Add a hook to pprint each message, filter for an appName of 'ipReporter'.
	# All filter pairs are evaluted as an "AND". If any one filter doesn't match
	# the hook doesn't match. Any hooks without a filter are executed on every
	# message. Filters are executed in series.
	client.registerHook(pprint, {'appName': 'ipReporter'})
	
	# Add another hook to pprint any message.
	client.registerHook(pprint)
	
	# We want the fate of our notifyListener instance
	# to be tied to the main thread process.
	client.daemon = True
	client.start()
	
	try:
		# Fix bug that doesn't allow Ctrl + C to kill the script
		while True: time.sleep(10)
	
	except (KeyboardInterrupt, SystemExit):
		# Die incely.
		quit()
	
	except Exception as e:
		tb = traceback.format_exc()
		print("Unhandled exception:\n%s" %tb)
