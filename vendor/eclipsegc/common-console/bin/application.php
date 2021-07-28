<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$container = include __DIR__ .'/../includes/container.php';
/** @var \Symfony\Component\Console\Application $application */
$application = $container->get('common_console_application');
$application->setDispatcher($container->get('event_dispatcher'));
$application->run($container->get('console.input'), $container->get('console.output'));
