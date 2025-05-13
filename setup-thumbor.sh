#!/bin/bash

set -e

echo "Setting up Thumbor..."

# function to install Docker
install_docker() {
    echo "Installing Docker..."
    sudo apt-get update
    sudo apt-get install -y docker.io
    sudo systemctl start docker
    sudo systemctl enable docker
}

# Check if Docker is already installed
if ! command -v docker &> /dev/null; then
    install_docker
else
    echo "Docker is already installed."
fi