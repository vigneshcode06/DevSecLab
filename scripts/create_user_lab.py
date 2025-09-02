import os
import sys

username = sys.argv[1]
base_path = f"C:/xampp/htdocs/DevSecLab/user_labs/{username}"
password = f"{username}@911_labs"

# Create user lab folders
paths = [
    f"{base_path}/kali_linux/mission",
    f"{base_path}/ubuntu/mission"
]

for path in paths:
    os.makedirs(path, exist_ok=True)

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

print(f"[+] Created lab folders and Dockerfiles for {username}")
