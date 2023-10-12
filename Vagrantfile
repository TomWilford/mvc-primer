# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|

  config.vm.box = "centos/7"

    config.vm.network "forwarded_port", guest: 80, host: 8081
    config.vm.network "forwarded_port", guest: 3306, host: 3307

    #config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

    # Create a private network, which allows host-only access to the machine
    # using a specific IP.
    config.vm.network "private_network", ip: "192.168.33.10"

    config.vm.network "public_network"

    config.vm.synced_folder ".", "/var/www/html"

    config.vm.provider "virtualbox" do |vb|
      vb.gui = false
      vb.memory = "1024"
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    end

    config.vm.provision :shell, :path => "vagrant/startup.sh"

end
