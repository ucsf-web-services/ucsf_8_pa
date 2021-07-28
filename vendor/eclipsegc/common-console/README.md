# Common Console 
Common Console is a tool based on Symfony Console Component that provides a set of command line interfaces that allows 
for automating scripted operations in different sites in a platform. A platform is a local or remote environment that
hosts different web sites.

The Common Console by itself provides out of the box commands for the creation of basic platforms, but it is 
extensible to allow for the creation of more platforms and commands. Its potential resides in the extensibility of 
the tool. 

# Installation
Install the package with the latest version of composer:

    $composer require acquia/contenthub-console
    $composer install
    
# Usage
Once installed you could create basic platforms:

    ./bin/commoncli
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
      help               Displays help for a command
      list               Lists commands
     platform
      platform:create    [pc] Create a new platform on which to execute common console commands.
      platform:delete    [pdel] Deletes the specified platform.
      platform:describe  [pd] Obtain more details about a platform.
      platform:list      [pl] List available platforms.
      platform:sites     List available sites registered in the platform.

# Extending Common Console
There are different projects that extend this tool to allow for creation of platforms and commands that operate on 
those platforms:

* [Acquia Cloud Content Hub Console](https://github.com/acquia/cloud-contenthub-console)
* [Acquia Site Factory Content Hub Console](https://github.com/acquia/acsf-contenthub-console)
* [Acquia Content Hub Console](https://github.com/acquia/contenthub-console)
