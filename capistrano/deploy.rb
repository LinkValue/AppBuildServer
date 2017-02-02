# config valid only for current version of Capistrano
lock '3.6.1'

# Project
set :application, 'MajoraOTAStore'
set :keep_releases, 3

# VCS
set :scm, :git
set :repo_url, 'git@github.com:LinkValue/MajoraOTAStore.git'

# Shared dirs/files
set :linked_dirs, fetch(:linked_dirs, []).push('wallet', 'web/uploads', 'build')
set :linked_files, fetch(:linked_files, []).push('app/config/parameters.yml')

# Remote environment
set :default_env, {}

# Logs
set :log_level, :debug
set :format_options, log_file: 'capistrano/logs/capistrano.log'

# Force upload linked_files even if they already exists
set :force_upload_linked_files, true
