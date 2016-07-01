#
# Plugins recommended for development:
#   - vagrant-bindfs
#   - vagrant-cachier
#   - vagrant-lxc
#   - vagrant-vbguest
#

require "yaml"

vf = YAML.load(<<-'YAML')
---
#
# Configuration from this point should not need modification create a
# vagrant.yml file in the project directory to define overrides.
#

hosts:
  # Name the default development container

  slackbot:
    mount:
      vagrant: { src: ".", dst: "/vagrant" }
    forward:
      http: { host: 8080, guest: 80 }
    provision:
      deps:
        priority: 20
        inline: |
          export DEBIAN_FRONTEND=noninteractive
          export CACHE_EXPIRY=86400

          # update apt-cache
          if [ -f /tmp/apt-update.force ] || [ $(expr $(date +%s) - $(stat --format %Y /var/log/apt/history.log 2>/dev/null || echo 0)) -gt $CACHE_EXPIRY ]; then
            apt-get -qqy update
            rm -f /tmp/apt-update.force 2>/dev/null
          fi

          # install apache and php
          apt-get -qqy install libapache2-mod-php5 \
            php5-apcu php5-cli php5-common php5-curl php5-dev php5-gd php5-gmp \
            php5-imap php5-intl php5-json php5-mcrypt php5-pgsql php5-readline \
            php5-tidy php5-xdebug php5-xsl

          # install composer
          if [ ! -f /usr/local/bin/composer ]; then
            curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin
            ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
          elif [ $(expr $(date +%s) - $(date -d $(composer --version 2>/dev/null | tail -n1 | perl -pe 's/^.+ ([0-9-]+) ([0-9:]+)$/\1T\2/') +%s 2>/dev/null || echo 0)) -gt $CACHE_EXPIRY ]; then
            composer self-update
          fi

          # configure apache virtualhost
          a2dissite 000-default 2>/dev/null
          cat << EOF > /etc/apache2/sites-available/slackbot.conf
          <VirtualHost *:80>
              DocumentRoot /vagrant/public
              FallbackResource /zend_index.php
              <Directory /vagrant/public>
                  DirectoryIndex index.php
                  Options -Indexes
                  AllowOverride All
                  Require all granted
              </Directory>
          </VirtualHost>
          EOF
          a2ensite slackbot
          apache2ctl restart

      src:
        privileged: false
        priority: 30
        inline: |
          cd /vagrant
          composer install

      src:
        privileged: false
        priority: 100
        run: always
        inline: |
          cd /vagrant
          [[ "$(egrep -q "\btoken\b.+[A-Za-z0-9]+" config/local.php 2>/dev/null; echo $?)" == "0" ]] || echo "config/local.php does not contain a token" >&2


groups:
  # Global defaults for all hosts.

  all:
    # The number of cpus visible to the guest, defaults to 1 if VirtualBox or
    # host/2.
    cpus: nil

    # The amount of memory available to the guest in MB, defaults between 1 and
    # 3 GB depending on host installed
    mem: nil

    # The default provisioners.
    provision:
      devinit:
        priority: 10
        inline: |
          # cachefilesd is a daemon to accelerate NFS mounts. The cachefilesd
          # provisioner is run for Virtualbox guests when the host is running
          # Linux or OS X.
          if [ $(mount | egrep '[(,]fsc[,)]' | wc -l) > 0 ]; then
            if [ -f /etc/debian_version ]; then
              export DEBIAN_FRONTEND=noninteractive
              [[ $(which cachefilesd >/dev/null 2>&1; echo $?) -ne 0 ]] && apt-get -qqy update && apt-get -qqy install cachefilesd
              [[ $(grep -q 'RUN=yes' /etc/default/cachefilesd; echo $?) -ne 0 ]] && echo "RUN=yes" > /etc/default/cachefilesd
              [[ $(service cachefilesd status >/dev/null 2>&1; echo $?) -ne 0 ]] && service cachefilesd start
            elif [ -f /etc/redhat-release ]; then
              [[ $(which cachefilesd >/dev/null 2>&1; echo $?) -ne 0 ]] && yum -y install cachefilesd
              [[ $(lsmod | grep -q cachefiles; echo $?) -ne 0 ]] && modprobe cachefiles
              [[ $(service cachefilesd status >/dev/null 2>&1; echo $?) -ne 0 ]] && service cachefilesd start
            fi
            cachefilesd --version | head -n1 | awk '{ print "cachefilesd version: "$3 }'
          fi
          # Fix vagrant issue 1673 "stdin: is not a tty".
          # See: https://github.com/mitchellh/vagrant/issues/1673
          if [ -f /etc/debian_version ]; then
            [[ $(egrep -q '^mesg n' /root/.profile; echo $?) -ne 0 ]] || perl -i -pe 's/^mesg n/tty -s && mesg n/' /root/.profile
          fi

      # Displays system information after provisining of running vagrant up.
      sysinfo:
        privileged: false
        run: always
        priority: 50
        inline: |
          cat /proc/loadavg | awk '{ print "System load: "$3 }'
          echo "Processes: $(expr $(ps ax | wc -l))"
          free | grep 'Mem:' | awk '{ printf "Memory usage: %.1f%% of %.1fGB", ($2-$4)/$2*100, $2/1024/1024 }'
          free | grep 'Swap:' | awk '{ printf "Swap usage: %.1f%% of %.1fGB", ($2-$4)/$2*100, $2/1024/1024 }' 2>/dev/null
          df -lP 2>/dev/null | egrep '/$' | head -n1 | awk '{ printf "Disk usage /: %.1f%% of %.1fGB", $3/$2*100, $2/1024/1024 }'
          ifconfig | perl -0777 -pe 's/\n / /g' | grep 'inet addr:' | grep ':Link' | perl -pe 's/^(.+?) .+?addr:(.+?) .+/IP address for \1: \2/'

config:
  # The settings within config.ssh relate to configuring how Vagrant will access
  # your machine over SSH. As with most Vagrant settings, the defaults are
  # typically fine, but you can fine tune whatever you would like.
  #
  # See: https://www.vagrantup.com/docs/vagrantfile/ssh_settings.html
  ssh:
    forward_agent: true
    insert_key: false
    shell: "bash -c 'BASH_ENV=/etc/profile exec bash'"
    private_key_path: [ "~/.vagrant.d/insecure_private_key", "~/.ssh/id_rsa" ]

YAML


#
# Environment detection
#

# detect the host operating system and resources
if RbConfig::CONFIG["host_os"] =~ /linux/
  host_cpu_count = `nproc`.to_i
  host_memory_mb = `grep "MemTotal" /proc/meminfo | sed -e "s/MemTotal://" -e "s/ kB//"`.to_i / 1024
  os = "linux"
elsif RbConfig::CONFIG["host_os"] =~ /darwin/
  host_cpu_count = `sysctl -n hw.ncpu`.to_i
  host_memory_mb = `sysctl -n hw.memsize`.to_i / 1024 / 1024
  os = "darwin"
else
  host_cpu_count = `wmic cpu get NumberOfCores`.split("\n")[2].to_i
  host_memory_mb = `wmic OS get TotalVisibleMemorySize`.split("\n")[2].to_i / 1024
  os = "windows"
end

# set in vf config
vf["groups"]["all"]["cpus"] = (host_cpu_count/2).ceil
vf["groups"]["all"]["mem"] = [[(host_memory_mb/2.75/512-1).floor*512,1024].max,3072].min

# create a default host with all avilable groups if none exist
project_name = File.basename(File.dirname(__FILE__))
vf["hosts"] ||= { project_name => { "hostname" => project_name } }


#
# Vagrant configuration
#

Vagrant.configure(2) do |config|
  # merge local config if it exists
  if File.exists?(File.join(File.dirname(__FILE__), "vagrant.yml"))
    vf = vf.deep_merge YAML.load(File.open(File.join(File.dirname(__FILE__), "vagrant.yml")))
  end

  # remove disabled hosts
  vf["hosts"] = filter_provider_os "hosts", vf, nil, os

  # merge vagrant config with vf
  merge_config config, vf["config"]

  vf["hosts"].each do |host_id, host|
    # merge group configuration for the host
    groups = {}
    (["all"]+(host["groups"] || [])).uniq.each do |group|
      groups = groups.deep_merge vf["groups"][group]
    end
    host = groups.deep_merge host
    host.delete "groups"

    host["hostname"] ||= host_id

    config.vm.define host["hostname"] do |box|
      # set the hostname
      box.vm.hostname = host["hostname"]

      # configure port forwarding and default auto_correct to true
      (host["forward"] || []).select { |v| not v["disabled"] }.each do |n, port|
        box.vm.network :forwarded_port, host: port["host"], guest: port["guest"], auto_correct: port["auto_correct"].nil? ? true : port["auto_correct"]
      end

      # virtualbox provider
      box.vm.provider :virtualbox do |vbox, override|
        override.vm.box = host["box"] || "ubuntu/trusty64"
        vbox.customize ["modifyvm", :id, "--memory", host["mem"]]
        vbox.customize ["modifyvm", :id, "--cpus", 1]
        vbox.customize ["modifyvm", :id, "--name", host["hostname"]]

        sync_folders override, host, "virtualbox", os
        provisioners override, host, "virtualbox", os

        (host["interfaces"].nil? ? [ "dhcp" ] : host["interfaces"]).each do |ip|
          params = ip == "dhcp" ? { :type => "dhcp" } : { :ip => ip }
          override.vm.network :private_network, params
        end
        
        if Vagrant.has_plugin?("vagrant-bindfs")
          (host["mount"] || []).select { |folder| not folder["disabled"] }.each do |n, folder|
            params = {
              :owner => host["owner"] || folder["owner"] || 1000,
              :group => host["group"] || folder["group"] || 1000
            }
            params[:perms] = "u=rwX:go=rX:go-w" if os == "windows"
            override.bindfs.bind_folder folder["dst"], folder["dst"], params
          end
        else
          $stderr.puts "WARNING: plugin not found: vagrant-bindfs\n"
        end
      end

      # lxc provider
      box.vm.provider :lxc do |lxc, override|
        override.vm.box = "fgrehm/trusty64-lxc"
        lxc.customize "cgroup.memory.limit_in_bytes", "#{host["mem"]}M"
        lxc.customize "mount.auto", "cgroup"
        lxc.customize "aa_profile", "unconfined"
        lxc.customize "cgroup.devices.allow", "a"

        sync_folders override, host, "lxc", os
        provisioners override, host, "lxc", os

        (host["interfaces"] || []).each do |ip|
          if ip =~ /^10.0.3./
            if not host["lxc_ipv4"]
              lxc.customize "network.ipv4", ip
              host["lxc_ipv4"] = true
            end
          elsif ip != "dhcp"
            override.vm.network :private_network, ip: ip, lxc__bridge_name: host["bridge"] || "vlxcbr1"
          end
        end
      end
    end
  end

  if Vagrant.has_plugin?("vagrant-cachier")
    config.cache.scope ||= :machine
  end
end


#
# Helper functions
#

class ::Hash
  # http://stackoverflow.com/a/30225093/650329
  def deep_merge(second)
    merger = proc { |key, v1, v2| Hash === v1 && Hash === v2 ? v1.merge(v2, &merger) : Array === v1 && Array === v2 ? v1 | v2 : [:undefined, nil, :nil].include?(v2) ? v1 : v2 }
    self.merge(second.to_h, &merger)
  end
end

def merge_config(config, override)
  override.each do |k, v|
    if k[0] == "."
      v.each do |params| config.send(k[1..-1], *params) end
    elsif v.is_a? Hash
      merge_config config.send(k), v
    else
      config.send "#{k}=", v
    end
  end
end

def filter_provider_os(key, host, provider, os)
  return (host[key] || {}).select { |k, v| not v["disabled"] and (v["providers"]||[provider]).include?(provider) and (v["hosts"]||[os]).include?(os) }
end

def sync_folders(override, host, provider, os)
  filter_provider_os("mount", host, provider, os).each do |n, folder|
    # default to nfs on supported platforms
    if provider != "lxc" or folder["readonly"]
      params = { :type => folder["type"], :mount_options => [] }
      params[:type] = "nfs" if folder["type"].nil? and os != "windows"
      params[:mount_options] += [ "dmode=775", "fmode=664" ] if os == "windows"
      params[:mount_options] += [ "ro" ] if folder["readonly"]
      params[:mount_options] += [ "vers=3", "udp", "actimeo=1", "rsize=65536", "wsize=65536", "noatime", "nolock", "fsc" ] if params[:type] == "nfs"
    else
      params = {}
    end
    override.vm.synced_folder folder["src"], folder["dst"], params
  end
end

def provisioners(override, host, provider, os)
  provisioners = filter_provider_os("provision", host, provider, os)
  provisioners = provisioners.sort_by { |k, v| v["priority"] || 20 }
  provisioners.each do |n, p|
    override.vm.provision n, type: "shell",
        privileged: (p["privileged"].nil? ? true : p["privileged"]),
        run: (p["run"] || "once") do |s|
      s.inline = p["inline"]
    end
  end
end