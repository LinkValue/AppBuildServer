# Staging environment configuration

set :stage, :staging

set :ssh_user, 'linkvalue-deploy'
set :tmp_dir, -> { "/home/#{fetch(:ssh_user)}/tmp/capistrano" }

set :branch, 'master'
set :deploy_to, -> { "/var/www/AppBuildServer" }

server 'preweb001.link-value.fr',
  roles: %w{web app db},
  user: fetch(:ssh_user),
  ssh_options: {
    auth_methods: %w(publickey),
    keys: %w(capistrano/.ssh/staging_rsa)
  }
