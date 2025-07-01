import sys 
import os 
mission = sys.argv[1]

if mission == "start":
    os.system("docker-compose up -d") 
elif mission == "stop":
    os.system("docker-compose stop")
elif mission == "remove":
    os.system("docker-compose stop")
