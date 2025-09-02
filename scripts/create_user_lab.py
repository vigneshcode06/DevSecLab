import os
import sys

username = sys.argv[1]
base_path = f"C:/xampp/htdocs/DevSecLab/user_labs/{username}"
password = f"{username}@911_labs"

# Create user lab folders
labs = ["kali_linux", "ubuntu"]
for lab in labs:
    os.makedirs(f"{base_path}/{lab}/mission", exist_ok=True)

# Dockerfile template
dockerfile_template = f"""
FROM ubuntu:20.04
RUN apt-get update && apt-get install -y openssh-server sudo
RUN useradd -ms /bin/bash {username}
RUN echo '{username}:{password}' | chpasswd
RUN adduser {username} sudo
CMD ["/usr/sbin/sshd","-D"]
"""

# Ubuntu Dockerfile
with open(f"{base_path}/ubuntu/Dockerfile", "w") as f:
    f.write(dockerfile_template)

# Kali Dockerfile
kali_template = dockerfile_template.replace("ubuntu:20.04", "kalilinux/kali-rolling")
with open(f"{base_path}/kali_linux/Dockerfile", "w") as f:
    f.write(kali_template)

# Docker Compose template
def compose_template(lab, image, ssh_port, vscode_port):
    return f"""
version: '3.8'
services:
  {lab}_lab:
    build: .
    container_name: {username}_{lab}_lab
    ports:
      - "{ssh_port}:22"
      - "{vscode_port}:8080"
    volumes:
      - ./mission:/home/{username}/mission
    tty: true
"""

# Write Ubuntu docker-compose.yml
with open(f"{base_path}/ubuntu/docker-compose.yml", "w") as f:
    f.write(compose_template("ubuntu", "ubuntu:20.04", 2222, 8080))

# Write Kali docker-compose.yml
with open(f"{base_path}/kali_linux/docker-compose.yml", "w") as f:
    f.write(compose_template("kali", "kalilinux/kali-rolling", 2223, 8080))

print(f"[+] Created lab folders, Dockerfiles, and docker-compose.yml for {username}")
