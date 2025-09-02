FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

# Create user vignesh and set password
RUN apt-get update && \
    apt-get install -y openssh-server curl sudo && \
    useradd -m -s /bin/bash vignesh && \
    echo 'vignesh:admin123' | chpasswd && \
    echo 'vignesh ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

# Install and configure SSH
RUN mkdir /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    echo 'root:admin123' | chpasswd

# Install Code Server
RUN curl -fsSL https://code-server.dev/install.sh | sh

# Setup Code Server for user vignesh
RUN mkdir -p /home/vignesh/.config/code-server && \
    echo 'bind-addr: 0.0.0.0:8080' > /home/vignesh/.config/code-server/config.yaml && \
    echo 'auth: password' >> /home/vignesh/.config/code-server/config.yaml && \
    echo 'password: vscode123' >> /home/vignesh/.config/code-server/config.yaml && \
    echo 'cert: false' >> /home/vignesh/.config/code-server/config.yaml && \
    chown -R vignesh:vignesh /home/vignesh/.config

# Expose SSH and Code Server ports
EXPOSE 22 8080

# Start SSH and Code Server when container starts
CMD service ssh start && sudo -u vignesh code-server

