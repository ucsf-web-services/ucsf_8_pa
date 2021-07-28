# Acquia Cloud Content Hub Console
Acquia Cloud Content Hub Console provides a command line tool to execute commands on all sites that belong to an Acquia
Cloud subscription. 
Depending on the platform that you create, you can execute the same command on all your Acquia Cloud sites or your 
Acquia Cloud Multi-sites. 

# Installation
Install the package with the latest version of composer:

    $composer require acquia/cloud-contenthub-console
    $composer install

Note that this package must be installed locally and in the codebase on your remote platform (Acquia Cloud) in order for
commands to work. 

# Create An Acquia Cloud Platform

In order for this tool to execute commands remotely on your Acquia Cloud Platform, you would need to first create a 
platform with the following command:

    $./vendor/bin/commoncli pc
    
This command will guide you to create a platform where to execute commands to. Notice that the alias given to this 
platform will be what you will use later to point to when executing commands.

When the command ask you to choose an application, you can select multiple ones separated by commas.    
    
    ./vendor/bin/commoncli pc
    This command will step you through the process of creating a new platform on which to perform common console commands.
    Platform Type:
      [0] SSH
      [1] DDEV
      [2] Acquia Cloud
      [3] Acquia Cloud Multi Site
     > 2
    Name: Test Acquia Cloud Platform
    Alias: test-ac-platform
    Acquia Cloud API Key? (Instructions: https://docs.acquia.com/acquia-cloud/develop/api/auth/) 00000000-0000-0000-0000-000000000000
    Acquia Cloud Secret? 1111111111111111111111111111111111111111111=
    Choose an Application:
      [00000000-bff1-4bb3-b1ec-9f3f6331be4a] test0000001
      [00000000-3d80-4dac-99e5-6c96ee297c31] test0000002
      [00000000-4fc4-102f-a6e5-1231390f9c61] test0000003
      [00000000-2db6-4b55-9b74-92f578fc0c5d] test0000004
      [00000000-2e84-44bb-ab30-47984919871f] test0000005
      [00000000-95ff-42eb-aafb-4db25a3255d9] test0000006
      [00000000-6086-4838-b45e-93df289ea6d8] test0000007
      [00000000-50f9-4c06-8d16-6a8c540d25e5] test0000008
      [00000000-c351-41f6-a0aa-2e07f4d9bcac] test0000009

     > test0000002
    Choose an Environment:
      [dev ] dev
      [prod] prod
      [test] test
     > dev
    +-------------------------------+----------------------------------------------+
    | Property                      | Value                                        |
    +-------------------------------+----------------------------------------------+
    | platform.type                 | Acquia Cloud                                 |
    | platform.name                 | Test Acquia Cloud Platform                   |
    | platform.alias                | test-ac-platform                             |
    | acquia.cloud.api_key          | 00000000-0000-0000-0000-000000000000         |
    | acquia.cloud.api_secret       | 1111111111111111111111111111111111111111111= |
    | acquia.cloud.application_ids  | 00000000-3d80-4dac-99e5-6c96ee297c31         |
    | acquia.cloud.environment.name | dev                                          |
    +-------------------------------+----------------------------------------------+
    Are these config correct? yes
    Console now trying to locate vendor directory within your platform.
    Vendor directory located successfully and saved in your platform configuration.
    "We assume that all your sites are using HTTPS."
    <warning>Is this assumption correct?</warning>yes
    Successfully saved.

    
    
# Usage
The following are some of the commands that are available to you to be used once deployed to Acquia Cloud:

    ./vendor/bin/commoncli
    CommonConsole 0.0.1
    
    Usage:
      command [options] [arguments]
    
    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
          --uri[=URI]       The url from which to mock a request.
          --bare            Prevents output styling.
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
    
    Available commands:
      help                               Displays help for a command
      list                               Lists commands
     ace
      ace:cron:list                      [ace-cl] Lists Scheduled Jobs.
      ace:database:backup:create         [ace-dbcr] Creates database backups.
      ace:database:backup:delete         [ace-dbdel] Deletes database backups.
      ace:database:backup:list           [ace-dbl] Lists database backups.
      ace:database:backup:restore        [ace-dbres] Restores database backups.
     ace-multi
      ace-multi:database:backup:create   [ace-dbcrm] Creates database backups for ACE Multi-site environments.
      ace-multi:database:backup:delete   [ace-dbdelm] Deletes database backups for ACE Multi-site environments.
      ace-multi:database:backup:list     [ace-dblm] Lists database backups for ACE Multi-site environments.
      ace-multi:database:backup:restore  [ace-dbresm] Restores database backups for ACE Multisite environments.
     platform
      platform:create                    [pc] Create a new platform on which to execute common console commands.
      platform:delete                    [pdel] Deletes the specified platform.
      platform:describe                  [pd] Obtain more details about a platform.
      platform:list                      [pl] List available platforms.
      platform:sites                     List available sites registered in the platform.

Now that you have a platform, you can execute a command like:

    $./vendor/bin/commoncli ace:database:backup:create @test-ac-platform
   
This command will execute a database creation in all sites in the "Test Acquia Cloud Platform". 

## Copyright and license

Copyright &copy; 2021 Acquia Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
