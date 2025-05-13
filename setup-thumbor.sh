#!/bin/bash

set -e

echo "Setting up Thumbor..."

echo -e "For more information, visit:\nhttps://github.com/thumbor/thumbor?tab=readme-ov-file"

echo "..."

# function to install Docker
install_docker() {
    echo "🐳 Docker not found. Installing Docker..."

    sudo apt update
    sudo apt install -y apt-transport-https ca-certificates curl software-properties-common gnupg lsb-release

    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io
}

# Check if Docker is already installed
if ! command -v docker &> /dev/null; then
    install_docker
else
    echo "Docker is already installed."
fi

# Check if Docker is running
sudo systemctl start docker
sudo systemctl enable docker

# Set up a Thumbor container
echo "Setting up a Thumbor container..."


