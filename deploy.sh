#!/bin/bash

apt update && apt install -y ssh && php deployer.phar deploy development -vvv
