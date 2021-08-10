# -*- mode: ruby -*-
# vi: set ft=ruby :
require 'yaml'

Vagrant.require_version '>= 1.8.6'

Vagrant.configure("2") do |config|
  # Load default configuration settings.
   _conf = YAML.load(
    File.open(
      File.join(File.dirname(__FILE__), 'provision/default.yml'),
      File::RDONLY
    ).read
  )

  # Load custom configuration settings - project specific.
  if File.exists?(File.join(File.dirname(__FILE__), 'site.yml'))
     _site = YAML.load(
       File.open(
         File.join(File.dirname(__FILE__), 'site.yml'),
         File::RDONLY
       ).read
     )
     _conf.merge!(_site) if _site.is_a?(Hash)
   end

  config.vm.define _conf['hostname'] do |v|
  end

  # Box to use.
  config.vm.box = ENV['box'] || _conf['box']
  config.ssh.insert_key = false
  config.vbguest.auto_update = true
  config.vm.box_check_update = true
  config.vm.network :private_network, ip: _conf['ip']
  config.vm.hostname = _conf['hostname']

  if Vagrant.has_plugin?('vagrant-hostsupdater')
    config.hostsupdater.aliases = _conf['hostname_aliases']
    config.hostsupdater.remove_on_suspend = true
  end

  if Vagrant.has_plugin?('vagrant-vbguest')
    config.vbguest.auto_update = false
  end

  config.vm.provider :virtualbox do |vb|
      vb.name = _conf['hostname']
      vb.memory = _conf['memory'].to_i
      vb.cpus = _conf['cpus'].to_i
      if 1 < _conf['cpus'].to_i
        vb.customize ['modifyvm', :id, '--ioapic', 'on']
      end
      vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
      vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
      vb.customize ['setextradata', :id, 'VBoxInternal/Devices/VMMDev/0/Config/GetHostTimeDisabled', 0]
    end

   config.vm.synced_folder _conf['synced_folder'],
      _conf['document_root'], :create => "true", :mount_options => ['dmode=755', 'fmode=644'],
      SharedFoldersEnableSymlinksCreate: false

  config.vm.provision "ansible_local" do |ansible|
    ansible.extra_vars = {
      settings: _conf
    }
    ansible.playbook = "provision/playbook.yml"
  end
end
