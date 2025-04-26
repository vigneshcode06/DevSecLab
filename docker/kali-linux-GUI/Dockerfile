FROM kalilinux/kali-rolling

ENV DEBIAN_FRONTEND=noninteractive

# Update and install GNOME, xRDP, SSH, and other tools
RUN apt-get update && \
    apt-get install -y kali-desktop-gnome xrdp openssh-server sudo curl && \
    apt-get clean

# Set root password
RUN echo 'root:admin123' | chpasswd

# Enable root login for SSH and xRDP
RUN sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    echo "gnome-session" > /root/.xsession

# Configure xRDP
RUN systemctl enable xrdp

# Expose ports for SSH and RDP
EXPOSE 22 3389

# Start both xRDP and SSH on container run
CMD service ssh start && service xrdp start && tail -f /dev/null
