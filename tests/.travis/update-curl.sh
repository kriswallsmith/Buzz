#! /usr/bin/env bash

# Install any build dependencies needed for curl
apt-get build-dep curl

# Get build requirements
# Some of these are used for the Python bindings
# this package also installs
apt-get install g++ make binutils autoconf automake autotools-dev libtool pkg-config \
  zlib1g-dev libcunit1-dev libssl-dev libxml2-dev libev-dev libevent-dev libjansson-dev \
  libjemalloc-dev cython python3-dev python-setuptools

# Build nghttp2 from source
git clone https://github.com/tatsuhiro-t/nghttp2.git
cd nghttp2
autoreconf -i
automake
autoconf
./configure
make
make install

# Get latest (as of Dec 31, 2016) libcurl
mkdir ~/curl
cd ~/curl
wget http://curl.haxx.se/download/curl-7.52.1.tar.bz2
tar -xvjf curl-7.52.1.tar.bz2
cd curl-7.52.1

# The usual steps for building an app from source
# ./configure
# ./make
# sudo make install
./configure --with-nghttp2=/usr/local --with-ssl
make
make install

# Resolve any issues of C-level lib
# location caches ("shared library cache")
ldconfig