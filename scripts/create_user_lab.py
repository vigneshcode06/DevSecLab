
import os
import sys

username = sys.argv[1]
base_path = f"C:/xampp/htdocs/DevSecLab/user_labs/{username}"
password = f"{username}@911_labs"

ssh_port = 2222
vscode_port = 8080

# Create user lab folders
labs = ["kali_linux", "ubuntu"]
for lab in labs:
    os.makedirs(f"{base_path}/{lab}/mission", exist_ok=True)

# this yml tempulate 

docker_compose_yml = f"""

version: '3.8'

services:
  {lab}_lab:
    build: .
    container_name: {username}_{lab}_lab
    ports:
      - "{ssh_port}:22"      # SSH Port
      - "{vscode_port}:8080"    # Code Server
    volumes:
      - ./mission:/home/{username}/mission  # Persisted folder
    restart: unless-stopped
    tty: true

""" 
# mession ubuntu is here tempulate  
dockerfile_template = f"""
FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

# Create user and set password
RUN apt-get update && \
    apt-get install -y openssh-server curl sudo && \
    useradd -m -s /bin/bash {username} && \
    echo '{username}:{password}' | chpasswd && \
    echo '{username} ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

# Install and configure SSH
RUN mkdir /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    echo 'root:{password}' | chpasswd

# Install Code Server
RUN curl -fsSL https://code-server.dev/install.sh | sh

# Setup Code Server for user 
RUN mkdir -p /home/{username}/.config/code-server && \
    echo 'bind-addr: 0.0.0.0:8080' > /home/{username}/.config/code-server/config.yaml && \
    echo 'auth: password' >> /home/{username}/.config/code-server/config.yaml && \
    echo 'password: vscode123' >> /home/{username}/.config/code-server/config.yaml && \
    echo 'cert: false' >> /home/{username}/.config/code-server/config.yaml && \
    chown -R {username}:{username} /home/{username}/.config

# Expose SSH and Code Server ports
EXPOSE 22 8080

# Start SSH and Code Server when container starts
CMD service ssh start && sudo -u {username} code-server
"""





with open("Dockerfile", "a") as f:
  f.write(dockerfile_template)  
  
  
with open("docker-compose.yml", "a") as f:
  f.write(docker_compose_yml)