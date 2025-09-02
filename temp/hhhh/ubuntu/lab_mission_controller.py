import sys
import os 
mission = sys.argv[1]

if mission == "start": # starting the docker mission 
    os.system("docker-compose up -d") 
elif mission == "stop": # all the files will deleted inside the mession simple term "exit"
    os.system("docker-compose stop")
elif mission == "remove":
    os.system("docker-compose stop")