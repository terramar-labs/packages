set :application, "packages"
set :repository,  "https://github.com/terramar-labs/packages"
set :branch, "2-0-develop"

default_run_options[:pty] = true
set :use_sudo, true

set :user, "terramar"
server "#{application}.local", :app, :web
set :deploy_to, "/var/www/#{application}"

set :php_bin, "php"
set :phpunit_bin, "vendor/bin/phpunit"

after "deploy:update_code", "composer:install"
before "composer:install", "composer:copy_vendors"
after "composer:install", "phpunit:run_tests"

namespace :deploy do
    task :finalize_update do
        data = File.read "config.yml"
        put data, "#{release_path}/config.yml"
        
        run "#{try_sudo} chown -R apache:apache #{release_path}"
    end

    task :restart do
        run "#{try_sudo} service httpd restart"
        run "#{try_sudo} service php-fpm restart"
        run "cd #{release_path} && sudo ./bin/console update"
        run "cd #{release_path} && sudo ./bin/console build --no-html-output"
    end
end

namespace :composer do
    desc "Copy vendors from previous release"
    task :copy_vendors, :except => { :no_release => true } do
        run "if [ -d #{previous_release}/vendor ]; then sudo cp -a #{previous_release}/vendor #{latest_release}/vendor; fi"
    end
    
    task :install do
        run "cd #{release_path} && sudo composer update --dev -o"
    end
end

namespace :phpunit do
    desc "Test before making live"
        task :run_tests, :roles => :app do
        run "cd #{latest_release} && #{phpunit_bin}"
    end
end